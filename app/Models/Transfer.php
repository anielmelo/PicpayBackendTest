<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender', 
        'recipient', 
        'amount'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient');
    }
}
