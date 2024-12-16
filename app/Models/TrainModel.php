<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainModel extends Model
{
    use HasFactory;
    protected $table = "train_model";
    protected $fillable = [
        "imgUrl",
        "name",
    ];
}
