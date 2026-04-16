<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));
        
        $transactions = Transaction::where('user_id', Auth::id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'desc')
            ->get();
            
        $categories = Category::where('user_id', Auth::id())->get();
        return view('transactions.index', compact('transactions', 'categories', 'month', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category_id' => 'nullable|exists:categories,id',
            'date' => 'required|date',
            'recurrence_type' => 'nullable|in:monthly,weekly,yearly',
            'recurrence_installments' => 'nullable|integer|min:1|max:120'
        ]);

        $baseDate = Carbon::parse($request->date);
        
        $parent = Transaction::create([
            'user_id' => Auth::id(),
            'description' => $request->description,
            'amount' => $request->amount,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'date' => $baseDate->format('Y-m-d'),
            'status' => 'pending',
            'recurrence_type' => $request->recurrence_type,
        ]);

        // Gerar Recorrências Imediatamente
        if ($request->filled('recurrence_type') && $request->filled('recurrence_installments')) {
            $installments = (int) $request->recurrence_installments;
            for ($i = 1; $i < $installments; $i++) {
                $nextDate = clone $baseDate;
                if ($request->recurrence_type == 'monthly') {
                    $nextDate->addMonths($i);
                } elseif ($request->recurrence_type == 'weekly') {
                    $nextDate->addWeeks($i);
                } elseif ($request->recurrence_type == 'yearly') {
                    $nextDate->addYears($i);
                }

                Transaction::create([
                    'user_id' => Auth::id(),
                    'description' => $request->description . " (" . ($i+1) . "/{$installments})",
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'category_id' => $request->category_id,
                    'date' => $nextDate->format('Y-m-d'),
                    'status' => 'pending',
                    'parent_id' => $parent->id,
                ]);
            }
        }

        return redirect()->route('transactions.index')->with('status', 'Transação cadastrada com sucesso!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) abort(403);
        
        // Se deletar algo com parent ou que é parent, pergunta se quer deletar os futuros?
        // Vamos manter simples: Deleta apenas a atual. 
        $transaction->delete();
        
        return redirect()->back()->with('status', 'Transação removida!');
    }

    public function markAsPaid(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) abort(403);
        
        $transaction->update(['status' => 'paid']);
        return redirect()->back()->with('status', 'Marcado como pago!');
    }
}
