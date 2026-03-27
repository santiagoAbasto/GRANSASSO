<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSubmission extends Model
{
    protected $table = 'payment_submissions';

    protected $fillable = [
        'private_user_id',
        'payment_date',
        'amount',
        'bank',
        'branch',
        'invoices_cancelled',
        'observations',
        'file_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function privateUser()
    {
        return $this->belongsTo(PrivateUser::class);
    }
}
