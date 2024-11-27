<?php

namespace App\Models;

use Carbon\Traits\Timestamp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = "purchase";
    public $timestamps = false;
    protected $fillable = [
        "product",
        "quantity",
        "supplier",
        "date",
        "status",
        "other",
    ];
}
