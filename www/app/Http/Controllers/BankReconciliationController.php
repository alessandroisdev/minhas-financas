<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BankTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class BankReconciliationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // OFXs pendentes
        $pendingBankTransactions = BankTransaction::whereHas('bankImport', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('status', 'pending')
          ->orderBy('date', 'desc')
          ->get();

        // Lançamentos manuais/sistema da empresa não conciliados ainda
        $unreconciledTransactions = Transaction::where('user_id', $userId)
            ->whereIn('status', ['pending', 'paid']) // Somente os que ainda não receberam match (não tem tag reconciled)
            // No futuro add where('is_reconciled', false) se houver coluna, ou assumir q não tem fitid do banco
            ->orderBy('date', 'desc')
            ->limit(50) // Limita a listagem lateral pre carregada
            ->get();

        return view('finance.reconciliation', compact('pendingBankTransactions', 'unreconciledTransactions'));
    }

    public function fastCreate(Request $request)
    {
        $request->validate([
            'bank_transaction_id' => 'required|exists:bank_transactions,id'
        ]);

        $bankTx = BankTransaction::whereHas('bankImport', fn($q) => $q->where('user_id', Auth::id()))
                                  ->findOrFail($request->bank_transaction_id);

        if ($bankTx->status !== 'pending') {
            return back()->withErrors(['msg' => 'Transação bancária já foi liquidada.']);
        }

        // Fast Create: Gera a transação espelho no sistema da empresa
        $systemTx = Transaction::create([
            'user_id' => Auth::id(),
            'amount' => abs($bankTx->amount),
            'type' => $bankTx->amount < 0 ? 'expense' : 'income',
            'description' => $bankTx->description . ' [Importado do OFX]',
            'date' => $bankTx->date,
            'status' => 'paid',
            // Defaulting category_id could be smart mapped, mas passaremos null pra mvp ou required
            'category_id' => null, 
        ]);

        // Marca OFX como conciliado
        $bankTx->update([
            'status' => 'matched',
            'transaction_id' => $systemTx->id
        ]);

        return back()->with('status', 'Transação absorvida para sua base instantaneamente!');
    }

    public function match(Request $request)
    {
        $request->validate([
            'bank_transaction_id' => 'required|exists:bank_transactions,id',
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        $bankTx = BankTransaction::whereHas('bankImport', fn($q) => $q->where('user_id', Auth::id()))
                                  ->findOrFail($request->bank_transaction_id);

        $systemTx = Transaction::where('user_id', Auth::id())->findOrFail($request->transaction_id);

        $bankTx->update([
            'status' => 'matched',
            'transaction_id' => $systemTx->id
        ]);
        
        // Pode add um marker de reconciled na sua table transactions nativa
        $systemTx->update(['status' => 'paid']); // Ensure it's paid

        return back()->with('status', 'Conciliação entre OFX e Caixa vinculada com sucesso.');
    }
}
