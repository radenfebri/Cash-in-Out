<?php

namespace App\Http\Controllers;

use App\Http\Resources\CashResource;
use App\Models\Cash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CashController extends Controller
{
    public function index()
    {
        $from = request('from');
        $to  = request('to');
        if ($from && $to) {
            $debit = $this->getBalances($from, $to, ">=");
            $credit = $this->getBalances($from, $to, "<");
            $transaction = Auth::user()->cashes()->whereBetween('when', [ $from, $to ])->latest()->get();
        } else {
            $debit = $this->getBalances(now()->firstOfMonth(), now(), ">=");
            $credit = $this->getBalances(now()->firstOfMonth(), now(), "<");
            $transaction = Auth::user()->cashes()->whereBetween('when', [ now()->firstOfMonth(), now() ])->latest()->get();
        }

        return response()->json([
            'balances' => str_replace(',' , '.' , number_format(Auth::user()->cashes()->get('amount')->sum('amount'))),
            'debit' => str_replace(',' , '.' , number_format($debit)),
            'credit' => str_replace(',' , '.' , number_format($credit)),
            'transaction' => CashResource::collection($transaction),
            'now' => now()->format('Y-m-d'),
            'firstOfMonth' => now()->firstOfMonth()->format('Y-m-d'),
        ]);
    }


    public function store()
    {
        request()->validate([
            'name' => 'required',
            'amount' => 'required|numeric',
        ]);


        $slug = request('name') . "-" . Str::random(6);
        $when = request('when') ?? now();

        $cash = Auth::user()->cashes()->create([
            'name' => request('name'),
            'slug' => Str::slug($slug),
            'when' => $when,
            'amount' => request('amount'),
            'description' => request('description'),
        ]);

        return response()->json([
            'message' => 'The transaction has been saved.',
            'cash' => new CashResource($cash)
        ]);

    }


    public function show(Cash $cash)
    {
        $this->authorize('show', $cash);

        return new CashResource($cash);
    }


    public function getBalances($from, $to, $operator)
    {
        return Auth::user()->cashes()
            ->whereBetween('when', [ $from, $to ])
            ->where('amount', $operator, 0)
            ->get('amount')
            ->sum('amount');
    }
}
