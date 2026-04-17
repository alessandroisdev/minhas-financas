<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\DocumentFolder;

class DocumentController extends Controller
{

    public function index(Request $request)
    {
        $currentFolderId = $request->get('folder_id'); // null equals root
        
        // Se pediu busca global agressive Frontend
        $documentsQuery = Document::where('user_id', Auth::id());
        $foldersQuery = DocumentFolder::where('user_id', Auth::id());
        
        if ($request->has('q') && !empty($request->q)) {
            if ($request->get('search_type') == 'content') {
                // OCR Fulltext Deep Search 
                $documents = $documentsQuery->whereRaw("MATCH(title, content_text) AGAINST(? IN BOOLEAN MODE)", [$request->q . '*'])->get();
            } else {
                // Simple Title Search
                $documents = $documentsQuery->where('title', 'like', '%' . $request->q . '%')->latest()->get();
            }
            $folders = collect(); // Normalmente esconde pastas na busca global pura ou exibe pastas com aquele nome
        } else {
            $documents = $documentsQuery->where('folder_id', $currentFolderId)->latest()->get();
            $folders = $foldersQuery->where('parent_id', $currentFolderId)->orderBy('name')->get();
        }

        // Parent tree for Breadcrumbs
        $breadcrumbs = collect();
        $tempFolder = $currentFolderId ? DocumentFolder::find($currentFolderId) : null;
        while ($tempFolder) {
            $breadcrumbs->prepend($tempFolder);
            $tempFolder = $tempFolder->parent;
        }

        return view('documents.index', compact('documents', 'folders', 'currentFolderId', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:51200', // Max 50MB
            'paths.*' => 'nullable|string',
            'current_folder_id' => 'nullable|exists:document_folders,id',
            'is_secured' => 'nullable|boolean',
        ]);

        if($request->hasFile('files')) {
            $files = $request->file('files');
            $paths = $request->input('paths', []);
            $baseFolderId = $request->input('current_folder_id');

            foreach($files as $index => $file) {
                $relativePath = rtrim(isset($paths[$index]) && trim($paths[$index]) !== '' ? $paths[$index] : $file->getClientOriginalName(), '/');
                $parts = explode('/', $relativePath);
                
                // Pop do final que é o nome do próprio arquivo
                $fileName = array_pop($parts);
                
                // Reconstrói as pastas para descobrir o folder_id final
                $parentFolderId = $baseFolderId;
                foreach ($parts as $folderName) {
                    $folder = DocumentFolder::firstOrCreate([
                        'user_id' => Auth::id(),
                        'name' => $folderName,
                        'parent_id' => $parentFolderId
                    ]);
                    $parentFolderId = $folder->id;
                }

                $path = $file->store('documents/' . Auth::id(), 'local');
                
                $createdDoc = Document::create([
                    'user_id' => Auth::id(),
                    'title' => $fileName,
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'folder_id' => $parentFolderId,
                    'is_secured' => $request->has('is_secured'),
                ]);

                // Dispara o Scanner Neural / PDF de extração no Background 
                \App\Jobs\ProcessDocumentContent::dispatch($createdDoc);
            }
        }

        return redirect()->route('documents.index', ['folder_id' => $request->current_folder_id])->with('status', 'Envio de Árvore Concluído!');
    }

    public function download(Document $document, Request $request)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }

        // Validação de Segredo
        if ($document->is_secured) {
            $request->validate([
                'password' => 'required|string'
            ]);
            
            if (!Hash::check($request->password, Auth::user()->password)) {
                return redirect()->back()->withErrors(['password' => 'Senha Incorreta para Desbloqueio do Cofre.']);
            }
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found on disk');
        }

        return Storage::disk('local')->download($document->file_path, $document->title);
    }

    public function destroy(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            abort(403);
        }
        
        Storage::disk('local')->delete($document->file_path);
        $document->delete();

        return redirect()->route('documents.index')->with('status', 'Documento triturado!');
    }
}
