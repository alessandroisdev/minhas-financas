<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::where('user_id', Auth::id());
        
        // Agile Backend Search se necessário (O front fará em realtime via JS mas o backend suporta)
        if ($request->has('q')) {
            $query->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('tags', 'like', '%' . $request->q . '%');
        }

        $documents = $query->latest()->get();
        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240', // Max 10MB
            'title' => 'nullable|string|max:255',
            'typology' => 'required|in:invoice,receipt,statement,contract,declaration,other',
            'reference_date' => 'nullable|date',
            'is_secured' => 'nullable|boolean',
        ]);

        if($request->hasFile('files')) {
            foreach($request->file('files') as $file) {
                // Arquivologia S3-like: armazenamento isolado na storage (não acessível publicamente no navegador)
                $path = $file->store('documents/' . Auth::id(), 'local');
                
                Document::create([
                    'user_id' => Auth::id(),
                    'title' => $request->title ?: $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'typology' => $request->typology,
                    'reference_date' => $request->reference_date,
                    'is_secured' => $request->has('is_secured'),
                ]);
            }
        }

        return redirect()->route('documents.index')->with('status', 'Documento(s) arquivado(s) com sucesso!');
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
