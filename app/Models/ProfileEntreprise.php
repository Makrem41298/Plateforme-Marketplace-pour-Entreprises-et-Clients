<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $entreprise_id
 * @property string $address
 * @property string $city
 * @property string $country
 * @property string $postal_code
 * @property string $phone
 * @property string|null $fax
 * @property string|null $website
 * @property string $description
 * @property string $sector
 * @property string $company_type
 * @property string|null $linkedin_url
 * @property string|null $facebook_url
 * @property string|null $twitter_handle
 * @property string|null $instagram_url
 * @property int $employees_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Entreprise $entreprise
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereCompanyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereEmployeesCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereEntrepriseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereFacebookUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereInstagramUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereLinkedinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereSector($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereTwitterHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProfileEntreprise whereWebsite($value)
 * @mixin \Eloquent
 */
class ProfileEntreprise extends Model
{
    protected $guarded=[];
    protected $table='entreprise_profiles';

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);

    }
}
