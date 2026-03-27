<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'gearbox_id',
        'name'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function gearbox()
    {
        return $this->belongsTo(Gearbox::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_applications');
    }
}