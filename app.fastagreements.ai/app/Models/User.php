<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The accessors to append to the model's array/JSON form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'photo_url',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'status',
        'mobile',
        'address',
        'is_company',
        'company_name',
        'gst_number',
        'location',
        'signature',
        'occupation',
        'date_of_birth',
        'gender',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_company' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get the user's status.
     *
     * @param  string  $value
     * @return bool
     */

    /**
     * Set the user's status.
     *
     * @param  mixed  $value
     * @return void
     */

    /** Full URL for the uploaded photo, or null when none was set. */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->profile_picture ? asset('images/profiles/' . $this->profile_picture) : null;
    }

    /** JWTSubject: the value put in the token's `sub` claim. */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWTSubject: extra claims merged into the token.
     */
    public function getJWTCustomClaims()
    {
        return ['type' => 'user'];
    }

    /**
     * Get all active tokens/sessions registered by the user.
     */
    public function tokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserToken::class);
    }
}
