<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Retrait extends Model
{
    protected $guarded=[];
    public function entreprise():BelongsTo
    {
        return $this->belongsTo(Entreprise::class);

    }
    protected static function booted()
    {
        static::creating(function ($retrait) {
            DB::transaction(function () use ($retrait) {
                $lastId = static::lockForUpdate()->count() + 1;
                $retrait->reference = 'RET-' . date('Y') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
            });
        });
    }

}
