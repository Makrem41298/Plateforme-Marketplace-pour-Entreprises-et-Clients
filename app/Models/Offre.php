<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 
 *
 * @property int $id
 * @property string $montant_propose
 * @property int $delai
 * @property string $description
 * @property int $projet_id
 * @property int $entreprise_id
 * @property string $statut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contrat|null $contrat
 * @property-read \App\Models\Entreprise $entreprise
 * @property-read \App\Models\Projet $projet
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereDelai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereEntrepriseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereMontantPropose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereProjetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offre whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Offre extends Model
{
    public $incrementing=true;
    public $table = 'offres';
    protected $guarded;
    public function contrat():HasOne{
        return $this->hasOne(Contrat::class);
    }
    public static function getAvailableStatus()
    {
        return ['en_attente'
                 , 'acceptee',
                'rejetee'];

    }
    // In Offre model
    public function projet()
    {
        return $this->belongsTo(Projet::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

}
