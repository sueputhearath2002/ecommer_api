<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class outgoing extends Model
{
    use HasFactory;
    protected $table = "outgoing";
    public $timestamps = false;
    protected $fillable = [
        "product",
        "quantity",
        "date",
        "customer",
    ];

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }
}
