<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'sender'    => ['required', 'numeric', 'exists:users,id'],
                'recipient' => ['required', 'numeric', 'exists:users,id'],
                'amount'    => ['required', 'numeric', 'min:1'],
            ], [
                'sender.require'    => 'O remetente é obrigatório.',
                'recipient.require' => 'O destinatário é obrigatório.',
                'amount.require'    => 'O valor é obrigatório.',

                'sender.exists'     => 'Remetente não encontrado.',
                'recipient.exists'  => 'Destinatário não encontrado.',

                'sender.numeric'    => 'Rementente deve ser um número',
                'recipient.numeric' => 'Destinatário deve ser um número',
    
                'amount.numeric'    => 'O valor deve ser um número.',
                'amount.min'        => 'O valor deve ser no mínimo R$ :min.',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['erro' => $e->validator->errors()->first()], 422);
        }

        $sender = User::findOrFail($request->sender);
        $recipient = User::findOrFail($request->recipient);
        $amount = $request->amount;

        if ($sender->type === 'MERCHANT') {
            return response()->json(['erro' => 'MERCHANT não pode realizar transações.'], 403);
        }

        if ($sender->balance < $amount) {
            return response()->json(['erro' => 'Saldo insuficiente.'], 403);
        }

        DB::beginTransaction();
        try {

            $responseAuthorization = Http::get('https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc');

            $authorization = $responseAuthorization->json();

            if ((!$responseAuthorization->successful()) || ($authorization['message'] != 'Autorizado')) {
                return response()->json(['erro' => 'Transação não autorizada.'], 403);
            }

            $sender->balance -= $amount;
            $sender->save();

            $recipient->balance += $amount;
            $recipient->save();

            Transfer::create([
                'sender'    => $sender->id,
                'recipient' => $recipient->id,
                'amount'    => $amount,
            ]);

            DB::commit();

            return response()->json(['sucesso' => 'Transação realizada com sucesso.'], 201);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['erro'    => 'Ocorreu um erro durante a transação.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
