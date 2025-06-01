<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SebastianBergmann\CodeCoverage\Report\Xml\Project;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 *
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $status_account
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Litige> $litiges
 * @property-read int|null $litiges_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Offre> $offre
 * @property-read int|null $offre_count
 * @property-read \App\Models\ProfileEntreprise|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $receivedMessages
 * @property-read int|null $received_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Retrait> $retraits
 * @property-read int|null $retraits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Message> $sentMessages
 * @property-read int|null $sent_messages_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereStatusAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Entreprise whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Entreprise extends Authenticatable implements JWTSubject,MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'status_account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function offre(): HasMany
    {
        return $this->hasMany(Offre::class);

    }
    public function retraits(): HasMany
    {
        return $this->hasMany(Retrait::class);
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function litiges(): MorphMany
    {
        return $this->morphMany(Litige::class, 'litigeable');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(ProfileEntreprise::class);
    }
    // In User/Entreprise models
    public function sentMessages()
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function receivedMessages()
    {
        return $this->morphMany(Message::class, 'receiver');
    }
    // Spécifier le garde à utiliser

}
