<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Transaction;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;

class AccountingExportController extends Controller
{
    public function exportZip(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer'
        ]);

        $userId = Auth::id();
        $month = $request->month;
        $year = $request->year;

        $dt = Carbon::create($year, $month, 1);
        $periodName = $dt->format('F_Y');
        
        $zipFileName = "Fechamento_Contabil_{$periodName}.zip";
        $zipFilePath = storage_path("app/private/" . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // 1. Gerar e anexar CSV do Balanço Financeiro
            $transactions = Transaction::with('category')->where('user_id', $userId)
                                       ->whereMonth('date', $month)
                                       ->whereYear('date', $year)
                                       ->orderBy('date', 'asc')->get();

            $csvData = "Data;Tipo;Categoria;Descricao;Valor (R$)\n";
            foreach ($transactions as $tx) {
                $cat = $tx->category ? $tx->category->name : 'Sem Categoria';
                $csvData .= "{$tx->date};{$tx->type};{$cat};{$tx->description};{$tx->amount}\n";
            }
            $zip->addFromString("Livro_Caixa_{$periodName}.csv", $csvData);

            // 2. Anexar todos os Arquivos Inteligentes Fisicos do Cofre que batem com aquele mês!
            $documents = Document::where('user_id', $userId)
                                 ->whereMonth('created_at', $month)
                                 ->whereYear('created_at', $year)
                                 ->get();
            
            foreach ($documents as $doc) {
                if (Storage::disk('local')->exists($doc->file_path)) {
                    $localPath = Storage::disk('local')->path($doc->file_path);
                    // Adiciona o documento dentro da pasta 'Notas Fiscais e Comprovantes' do ZIP
                    // Preserva a extensão base do arquivo original
                    $ext = pathinfo($doc->title, PATHINFO_EXTENSION);
                    $cleanTitle = pathinfo($doc->title, PATHINFO_FILENAME);
                    if (!$ext) { $ext = explode('/', $doc->file_type)[1] ?? 'bin'; }
                    
                    $safeTitle = preg_replace('/[^A-Za-z0-9\- \_]/', '', $cleanTitle);
                    $zip->addFile($localPath, "Comprovantes/{$safeTitle}_ID{$doc->id}.{$ext}");
                }
            }

            $zip->close();
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
