<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

/**
 * 
 *
 * @property int $id
 * @property string $reference
 * @property int $litigeable_id
 * @property string $litigeable_type
 * @property string $reference_contrat
 * @property string $titre
 * @property string $description
 * @property string $statut
 * @property string $type
 * @property string|null $decision Décision finale
 * @property string|null $resolution_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Contrat $contrat
 * @property-read Model|\Eloquent $litigeable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereDecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereLitigeableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereLitigeableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereReferenceContrat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereResolutionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereStatut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereTitre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Litige whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
