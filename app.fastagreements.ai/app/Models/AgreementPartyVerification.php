<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per person on an agreement who needs to confirm their number.
 *
 * See the migration for why this is a side table rather than columns on
 * `agreements`.
 */
class AgreementPartyVerification extends Model
{
    use HasFactory;

    protected $table = 'agreement_party_verifications';

    public const ROLE_PARTY_1 = 'party_1';
    public const ROLE_PARTY_2 = 'party_2';
    public const ROLE_GUARANTOR = 'guarantor';

    public const VIA_FIREBASE = 'firebase';
    public const VIA_NONE = 'none';

    protected $fillable = [
        'agreement_id',
        'role',
        'position',
        'mobile',
        'verified_at',
        'firebase_uid',
        'verified_via',
    ];

    protected $casts = [
        'agreement_id' => 'integer',
        'position' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Aggriment::class, 'agreement_id', 'id');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /** @return array<int, string> */
    public static function roles(): array
    {
        return [self::ROLE_PARTY_1, self::ROLE_PARTY_2, self::ROLE_GUARANTOR];
    }
}
