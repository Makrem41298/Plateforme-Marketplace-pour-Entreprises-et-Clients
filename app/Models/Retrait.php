<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * 
 *
 * @property int $id
 * @property string $reference Référence unique du retrait
 * @property int $entreprise_id
 * @property string $montant Montant demandé a
 * @property string $statut
 * @property string $info_compte_
 * @property string $methode
 * @property string|null $notes_administratives
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Entreprise $entreprise
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereEntrepriseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereInfoCompte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereMethode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereNotesAdministratives($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Retrait whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
