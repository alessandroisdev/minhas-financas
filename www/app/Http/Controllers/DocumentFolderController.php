<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DocumentFolder;
use Illuminate\Support\Facades\Auth;

class DocumentFolderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:document_folders,id',
            'color' => 'nullable|string|max:20'
        ]);

        DocumentFolder::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'color' => $request->color,
        ]);

        return redirect()->route('documents.index', ['folder_id' => $request->parent_id])->with('status', 'Pasta criada com sucesso!');
    }

    public function destroy(DocumentFolder $documentFolder)
    {
        if ($documentFolder->user_id !== Auth::id()) abort(403);
        
        $documentFolder->delete();

        return back()->with('status', 'Pasta vaporizada.');
    }
}
