<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 *
 *
 * @property int $id
 * @property string $titre
 * @property string $slug
 * @property string $description
 * @property string|null $budget
 * @property int $user_id
 * @property string $status
 * @property string $type
 * @property int|null $Delai
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Offre> $offre
 * @property-read int|null $offre_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDelai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereTitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Projet whereUserId($value)
 * @mixin \Eloquent
 */
class Projet extends Model
{
    protected $guarded=[];
    public function client():BelongsTo
    {
        return $this->belongsTo(User::class,'user_id');
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
