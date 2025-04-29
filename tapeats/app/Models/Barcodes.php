<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barcodes extends Model
{
    use HasFactory;
//tes
    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class);
        
    }
}
