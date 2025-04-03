<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

class Litige extends Model
{
    protected $guarded=[];
    public function contrat():BelongsTo
    {
        return $this->belongsTo(Contrat::class, 'reference_contrat', 'reference');
    }
    public function litigeable(): MorphTo
    {
        return $this->morphTo();
    }
    protected static function booted()
    {
        static::creating(function ($Iitige) {
            DB::transaction(function () use ($Iitige) {
                $lastId = static::lockForUpdate()->max('id') + 1;
                $Iitige->reference = 'LIT-'.date('Y').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
            });
        });
    }
}
