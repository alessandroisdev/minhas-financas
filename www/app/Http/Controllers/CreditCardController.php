<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use App\Models\CreditCardTransaction;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreditCardController extends Controller
{
    public function index()
    {
        $cards = CreditCard::where('user_id', Auth::id())->get();
        return view('credit-cards.index', compact('cards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'limit' => 'required|numeric|min:0',
            'closing_day' => 'required|integer|min:1|max:31',
            'due_day' => 'required|integer|min:1|max:31',
            'color' => 'nullable|string'
        ]);

        CreditCard::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'limit' => $request->limit,
            'closing_day' => $request->closing_day,
            'due_day' => $request->due_day,
            'color' => $request->color ?? '#000000',
            'brand' => $request->brand ?? 'MasterCard'
        ]);

        return back()->with('status', 'Cartão adicionado com sucesso!');
    }

    public function show($id, Request $request)
    {
        $card = CreditCard::where('user_id', Auth::id())->findOrFail($id);
        
        // Month offset para navegar entre faturas passadas e futuras
        $monthOffset = $request->get('offset', 0);
        
        // Determina a Fatura Mestre focada
        $referenceDate = now()->addMonths($monthOffset);
        
        // Se a data de hoje já passou do closing day, a fatura corrente natural virou a do mês seguinte.
        if (now()->day > $card->closing_day && $monthOffset == 0) {
            $referenceDate->addMonth();
        }

        // Calcula a janela matemática da fatura aberta
        // Inicia no dia seguinte do corte do mes passado
        $startWindow = $referenceDate->copy()->subMonth()->day($card->closing_day + 1)->startOfDay();
        // Termina no dia do corte desse ano/mes
        $endWindow = $referenceDate->copy()->day($card->closing_day)->endOfDay();
        $dueDate = $referenceDate->copy()->day($card->due_day);
        
        // Se o due day for menor que o closing day, significa que vence no mês subsequente
        if ($card->due_day < $card->closing_day) {
            $dueDate->addMonth();
        }

        // Busca gastas dessa janela de Fatura Específica
        $invoiceTransactions = CreditCardTransaction::where('credit_card_id', $card->id)
            ->whereBetween('date', [$startWindow->format('Y-m-d'), $endWindow->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        $invoiceTotal = $invoiceTransactions->sum('amount');
        $availableLimit = $card->limit - CreditCardTransaction::where('credit_card_id', $card->id)->where('date', '>=', now()->subDays(40))->sum('amount'); // rough measure

        return view('credit-cards.show', compact('card', 'invoiceTransactions', 'invoiceTotal', 'startWindow', 'endWindow', 'dueDate', 'monthOffset', 'availableLimit'));
    }

    public function storeTransaction(Request $request, $id)
    {
        $card = CreditCard::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'description' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'installments' => 'required|integer|min:1|max:120'
        ]);

        $installments = $request->installments;
        $amountPerInstallment = $request->amount / $installments;
        $groupId = \Illuminate\Support\Str::uuid();
        $baseDate = Carbon::parse($request->date);

        for ($i = 1; $i <= $installments; $i++) {
            CreditCardTransaction::create([
                'credit_card_id' => $card->id,
                'description' => $installments > 1 ? "{$request->description} ({$i}/{$installments})" : $request->description,
                'amount' => $amountPerInstallment,
                // Time-shift loop para parcelas futuras
                'date' => $i === 1 ? $baseDate : $baseDate->copy()->addMonths($i - 1),
                'installments' => $installments,
                'current_installment' => $i,
                'installment_group_id' => $groupId,
                'category_id' => $request->category_id
            ]);
        }

        return back()->with('status', 'Compra lançada na fatura com sucesso!');
    }

    public function payInvoice(Request $request, $id)
    {
        // Ao Liquidar uma Fatura, empurramos o saldo como 1 Saída na Conta Bancaria Principal 
        $card = CreditCard::where('user_id', Auth::id())->findOrFail($id);
        
        $request->validate([
            'invoice_total' => 'required|numeric'
        ]);

        Transaction::create([
            'user_id' => Auth::id(),
            'amount' => $request->invoice_total,
            'type' => 'expense',
            'description' => "Pagamento de Fatura - {$card->name}",
            'date' => now(),
            'status' => 'paid',
        ]);

        return redirect()->route('dashboard')->with('status', 'Fatura debitada do caixa central com sucesso!');
    }
}
