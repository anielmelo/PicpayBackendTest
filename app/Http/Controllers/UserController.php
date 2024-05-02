<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return csrf_token();
        return 'hello world';
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
                'name'     => ['required'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8'],
                'document' => ['required', 'unique:users,document'],
                'balance'  => ['required', 'numeric', 'min:0'],
                'type'     => ['required', 'in:COMMON,MERCHANT'],
            ], [
                'name.required'     => 'O campo nome é obrigatório.',
                'email.required'    => 'O campo email é obrigatório.',
                'password.required' => 'O campo senha é obrigatório.',
                'document.required' => 'O campo documento é obrigatório.',
                'balance.required'  => 'O campo saldo é obrigatório.',
                'type.required'     => 'O campo tipo é obrigatório.',

                'email.email'       => 'O email informado não é válido.',
                'email.unique'      => 'Usuário já cadastrado.',
                
                'password.min'      => 'A senha deve ter pelo menos :min caracteres.',

                'document.unique'   => 'Usuário já cadastrado.',
                
                'balance.numeric'   => 'O saldo deve ser um número.',
                'balance.min'       => 'O saldo não pode ser negativo.',
                
                'type.in'           => 'O tipo deve ser "COMMON" ou "MERCHANT".',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['erro' => $e->validator->errors()->first()], 400);
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->document = $request->document;
        $user->balance = $request->balance;
        $user->type = $request->type;

        $user->save();

        return response()->json(['sucesso' => 'Usuário criado com sucesso.'], 201);
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
