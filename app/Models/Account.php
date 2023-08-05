<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['account_number', 'balance', 'account_type', 'client_id'];

    protected $primaryKey = 'account_number';

    public function client() : BelongsTo
    {
        return $this->belongsTo(Client::class, "client_id");
    }

    protected $casts = [
        "account_number" => "string",
    ];
}
