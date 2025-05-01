<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * 
 *
 * @property int $id
 * @property string $reference
 * @property string $statut
 * @property int $offer_id
 * @property string|null $date_debut
 * @property string|null $date_fin
 * @property string $termes
 * @property string|null $premiere_tranche
 * @property string $montant_total
 * @property string|null $signe_le
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Litige> $litiges
 * @property-read int|null $litiges_count
 * @property-read \App\Models\Offre $offre
 * @method static Builder<static>|Contrat clinet()
 * @method static Builder<static>|Contrat entreprise()
 * @method static Builder<static>|Contrat newModelQuery()
 * @method static Builder<static>|Contrat newQuery()
 * @method static Builder<static>|Contrat query()
 * @method static Builder<static>|Contrat whereCreatedAt($value)
 * @method static Builder<static>|Contrat whereDateDebut($value)
 * @method static Builder<static>|Contrat whereDateFin($value)
 * @method static Builder<static>|Contrat whereId($value)
 * @method static Builder<static>|Contrat whereMontantTotal($value)
 * @method static Builder<static>|Contrat whereOfferId($value)
 * @method static Builder<static>|Contrat wherePremiereTranche($value)
 * @method static Builder<static>|Contrat whereReference($value)
 * @method static Builder<static>|Contrat whereSigneLe($value)
 * @method static Builder<static>|Contrat whereStatut($value)
 * @method static Builder<static>|Contrat whereTermes($value)
 * @method static Builder<static>|Contrat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
