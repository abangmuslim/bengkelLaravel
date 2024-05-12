<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    use HasFactory;

    protected $fillable=['pelanggan_id','user_id','invoice','total'];
    
    public function detiltransaksi():HasMany
    {
        return $this->hasMany(Detiltransaksi::class);
    }

    public function pelanggan():BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
