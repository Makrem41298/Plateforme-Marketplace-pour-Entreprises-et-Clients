<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Contrat extends Model
{
    protected $guarded=[];

   public function scopeEntreprise(Builder $query):void
   {
       $entreprise = auth()->guard('entreprise')->user();
       $query->whereHas('offre', function ($q) use ($entreprise) {
           $q->where('entreprise_id', $entreprise->id);
       });
   }
    public function scopeClinet(Builder $query):void
    {
        $user = auth()->user();
        $query->whereHas('offre.projet', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

    }


    public function offre():BelongsTo
{
    return $this->belongsTo(Offre::class,'offer_id');
}
public function litiges():HasMany
{
    return $this->HasMany(Litige::class);
}
    protected static function booted()
    {
        static::creating(function ($contrat) {
            DB::transaction(function () use ($contrat) {
                $lastId = static::lockForUpdate()->max('id') + 1;
                $contrat->reference = 'CONT-'.date('Y').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
            });
        });
    }
}
