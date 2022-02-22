<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultiProductionLine extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        "outputs" => "array",
        "choices" => "array",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
