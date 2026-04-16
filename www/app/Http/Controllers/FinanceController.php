<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankImport;
use App\Jobs\ProcessBankImportJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    public function index()
    {
        $imports = BankImport::where('user_id', Auth::id())->latest()->get();
        
        $month = date('m');
        $year = date('Y');

        $incomes = \App\Models\Transaction::where('user_id', Auth::id())->where('type', 'income')
            ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            
        $expenses = \App\Models\Transaction::where('user_id', Auth::id())->where('type', 'expense')
            ->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            
        $balance = clone $incomes - $expenses; // Simplificado

        return view('finance.index', compact('imports', 'incomes', 'expenses', 'balance'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'ofx_file' => 'required|file',
        ]);

        $file = $request->file('ofx_file');
        
        $path = $file->store('bank_imports', 'local');
        $fullPath = storage_path('app/' . $path);

        $import = BankImport::create([
            'user_id' => Auth::id(),
            'filename' => $file->getClientOriginalName(),
            'status' => 'processing',
            'total_items' => 0,
            'processed_items' => 0,
        ]);

        ProcessBankImportJob::dispatch($import, $fullPath);

        // Dispara uma notificação inicial para SSE
        \Illuminate\Support\Facades\Redis::publish("user-channel-".Auth::id(), json_encode([
            'type' => 'toast', 
            'message' => 'Arquivo adicionado a fila de background...',
            'status' => 'primary'
        ]));

        return redirect()->route('bank.reconciliation', $import->id)->with('status', 'Conciliação em background iniciada.');
    }

    public function showReconciliation(BankImport $import)
    {
        if($import->user_id !== Auth::id()) abort(403);

        $pendingBankTxs = $import->bankTransactions()->where('status', 'pending')->get();
        $matchedBankTxs = $import->bankTransactions()->where('status', 'matched')->get();
        
        $categories = \App\Models\Category::where('user_id', Auth::id())->get();

        return view('finance.reconciliation', compact('import', 'pendingBankTxs', 'matchedBankTxs', 'categories'));
    }

    public function manualMatch(Request $request, \App\Models\BankTransaction $bankTransaction)
    {
        if ($bankTransaction->bankImport->user_id !== Auth::id()) abort(403);
        
        // Se usuário escolheu associar a uma nova entrada
        $tx = \App\Models\Transaction::create([
            'user_id' => Auth::id(),
            'description' => $bankTransaction->description,
            'amount' => $bankTransaction->amount,
            'type' => $bankTransaction->amount < 0 ? 'expense' : 'income',
            'category_id' => $request->category_id,
            'date' => $bankTransaction->date,
            'status' => 'reconciled'
        ]);

        $bankTransaction->update([
            'status' => 'manual',
            'transaction_id' => $tx->id
        ]);

        return redirect()->back()->with('status', 'Transação conciliada manualmente!');
    }
}
