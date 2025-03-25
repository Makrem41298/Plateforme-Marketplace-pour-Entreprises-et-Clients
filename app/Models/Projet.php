<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projet extends Model
{
    protected $guarded=[];
    public function client():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function entreprise():BelongsToMany
    {
        return $this->belongsToMany(Entreprise::class,'offers')->using(Offre::class);
    }
}
