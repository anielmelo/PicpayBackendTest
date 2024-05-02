<?php

namespace App\Observers;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransferObserver
{
    /**
     * Handle the Transfer "created" event.
     */
    public function created(Transfer $transfer): void
    {
        $sender = User::findOrFail($transfer->sender);
        $recipient = User::findOrFail($transfer->recipient);
        $amount = $transfer->amount;

        $responseSend = Http::get('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6');
        $sendService = $responseSend->json();

        if ((!$responseSend->successful()) || ($sendService['message'] != true)) {
            return;
        }

        Log::info('Transferência realizada com sucesso!');
        Log::info('
            Remetente: ' . $sender->name . ' 
            Destinatário: ' . $recipient->name . ' 
            Valor: $' . $amount
        );
    }

    /**
     * Handle the Transfer "updated" event.
     */
    public function updated(Transfer $transfer): void
    {
        //
    }

    /**
     * Handle the Transfer "deleted" event.
     */
    public function deleted(Transfer $transfer): void
    {
        //
    }

    /**
     * Handle the Transfer "restored" event.
     */
    public function restored(Transfer $transfer): void
    {
        //
    }

    /**
     * Handle the Transfer "force deleted" event.
     */
    public function forceDeleted(Transfer $transfer): void
    {
        //
    }
}
