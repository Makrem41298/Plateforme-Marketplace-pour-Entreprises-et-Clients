<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Contrat extends Model
{
    protected $guarded=[];
public function offre():BelongsTo
{
    return $this->belongsTo(Offre::class);
}
public function litiges():HasMany
{
    return $this->HasMany(Litige::class);
}
    protected static function booted()
    {
        static::creating(function ($contrat) {
            DB::transaction(function () use ($contrat) {
                $lastId = static::lockForUpdate()->count() + 1;
                $contrat->reference = 'CONT-' . date('Y') . '-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
            });
        });
    }
}
