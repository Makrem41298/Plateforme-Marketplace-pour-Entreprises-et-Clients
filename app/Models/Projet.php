<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Projet extends Model
{
    protected $guarded=[];
    public function client():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function offre(): HasMany
    {
        return $this->hasMany(Offre::class);

    }
    public static function getAvailableTypes()
    {
        return [
            'developpement_web',
            'developpement_mobile',
            'design_graphique',
            'marketing_digital',
            'redaction_de_contenu',
            'conseil_en_affaires',
            'intelligence_artificielle',
            'autre'
        ];
    }


}
