# Solução para o Desafio Back-end PicPay

### ![image](https://github.com/anielmelo/PicpayBackendTest/assets/103321497/b591e8ff-1069-4429-89f5-53393b1f0507)&nbsp; <a href="https://github.com/PicPay/picpay-desafio-backend">Repositório do desafio PicPay</a> 


<br>
<br>

## Tecnologias utilizadas
![Laravel](https://img.shields.io/badge/laravel-%23FF2D20.svg?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/mysql-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)

<br>

## Minhas soluções para os requisitos

1. Coletar informações de cadastro de usuário
- Validação:
```php
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
    ‌
} catch (ValidationException $e) {
    return response()->json(['erro' => $e->validator->errors()->first()], 400);
}
```
- Cadastro:
```php
$user = new User();
$user->name = $request->name;
$user->email = $request->email;
$user->password = Hash::make($request->password);
$user->document = $request->document;
$user->balance = $request->balance;
$user->type = $request->type;
‌
$user->save();
‌
return response()->json(['sucesso' => 'Usuário criado com sucesso.'], 201);
```
2. Implementar funcionalidade de transferência
- Validação:
 ```php
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
‌
} catch (ValidationException $e) {
    return response()->json(['erro' => $e->validator->errors()->first()], 422);
}
‌
$sender = User::findOrFail($request->sender);
$recipient = User::findOrFail($request->recipient);
$amount = $request->amount;
‌
if ($sender->type === 'MERCHANT') {
    return response()->json(['erro' => 'MERCHANT não pode realizar transações.'], 403);
}
‌
if ($sender->balance < $amount) {
    return response()->json(['erro' => 'Saldo insuficiente.'], 403);
}
 ```
- Processo de transação:
 ```php
DB::beginTransaction();
try {
    $responseAuthorization = Http::get('https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc');
    $authorization = $responseAuthorization->json();
    ‌
    if ((!$responseAuthorization->successful()) || ($authorization['message'] != 'Autorizado')) {
        return response()->json(['erro' => 'Transação não autorizada.'], 403);
    }
    ‌
    $sender->balance -= $amount;
    $sender->save();
    ‌
    $recipient->balance += $amount;
    $recipient->save();
    ‌
    Transfer::create([
        'sender'    => $sender->id,
        'recipient' => $recipient->id,
        'amount'    => $amount,
    ]);
    ‌
    DB::commit();
    return response()->json(['sucesso' => 'Transação realizada com sucesso.'], 201);
} catch (Exception $e) {
    DB::rollBack();
    return response()->json(['erro'    => 'Ocorreu um erro durante a transação.'], 500);
}
 ```
3. Verificações de envio
- Bloquear envio de dinheiro apenas para lojistas:
 ```php
if ($sender->type === 'MERCHANT') {
    return response()->json(['erro' => 'MERCHANT não pode realizar transações.'], 403);
}
 ```
- Verificar saldo antes do envio de dinheiro:
 ```php
if ($sender->balance < $amount) {
    return response()->json(['erro' => 'Saldo insuficiente.'], 403);
}
 ```
4. Consultar serviço autorizador externo antes de finalizar transferência
- Utilizando mocky:
 ```php
$responseAuthorization = Http::get('https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc');
$authorization = $responseAuthorization->json();
‌
if ((!$responseAuthorization->successful()) || ($authorization['message'] != 'Autorizado')) {
    return response()->json(['erro' => 'Transação não autorizada.'], 403);
}
 ```
5. Garantir tratamento de transferência como transação reversível
- Tratamento de transação reversível:
 ```php
DB::beginTransaction();
try {
    // código da transação
    DB::commit();
    return response()->json(['sucesso' => 'Transação realizada com sucesso.'], 201);
} catch (Exception $e) {
    DB::rollBack();
    return response()->json(['erro'    => 'Ocorreu um erro durante a transação.'], 500);
}
 ```
6. Configurar notificações de pagamento
- TransferObserver (created):
 ```php
$sender = User::findOrFail($transfer->sender);
$recipient = User::findOrFail($transfer->recipient);
$amount = $transfer->amount;
‌
$responseSend = Http::get('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6');
$sendService = $responseSend->json();
‌
if ((!$responseSend->successful()) || ($sendService['message'] != true)) {
    return;
}
‌
Log::info('Transferência realizada com sucesso!');
Log::info('
    Remetente: ' . $sender->name . ' 
    Destinatário: ' . $recipient->name . ' 
    Valor: $' . $amount
);
 ```
- Utilizando mocky:
 ```php
 $responseSend = Http::get('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6');
 $sendService = $responseSend->json();
    ‌
 if ((!$responseSend->successful()) || ($sendService['message'] != true)) {
    return;
 }  
 ```
- Simulação de envio de notificação:
```php
Log::info('Transferência realizada com sucesso!');
Log::info('
    Remetente: ' . $sender->name . ' 
    Destinatário: ' . $recipient->name . ' 
    Valor: $' . $amount
);
```
7. Assegurar disponibilidade do serviço seguindo padrão RESTful
- Rotas:
```php
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/transfers', [TransferController::class, 'store']);
```

## Endpoints

```bash
GET   api/users        #GET_ALL 
POST  api/users        #STORE
POST  api/transfers    #STORE
```

## Exemplos de uso
<p>Para utilizar a API, siga os seguintes passos:</p>

### Cadastro de usuário
<p>Envie uma solicitação <code>POST</code> para o endpoint <code>/users.</code> incluindo os detalhes do usuário a ser cadastrado no corpo da solicitação.</p>

```sh
# Exemplo de usuário

{
    "name": "Maria",
    "email": "maria@example.com",
    "password": "98765432",
    "document": "876.543.210-98",
    "balance": 50.00,
    "type": "COMMON"
}
```

### Consulta de usuários
<p>Para visualizar todos os usuários cadastrados no banco de dados, faça uma solicitação <code>GET</code> para o endpoint <code>/users.</code></p>

```sh
# Exemplo de retorno

{
    "token": "csrf_token",
    "users": []
}
```

### Realizando transferência
<p>Para realizar uma transferência de fundos, envie uma solicitação <code>POST</code> para o endpoint <code>/transfers</code>, fornecendo os detalhes da transferência, como o remetente, destinatário e o valor a ser transferido.</p>


```sh
# Exemplo de tranferência

{
    "sender": 1,
    "recipient": 2,
    "amount": 50.0
}
```
