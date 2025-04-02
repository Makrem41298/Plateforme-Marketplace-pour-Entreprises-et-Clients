<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
