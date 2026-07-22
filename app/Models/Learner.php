<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Learner extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'email',
        'grade_level',
        'section',
    ];

    protected static function booted(): void
    {
        static::creating(function (Learner $learner): void {
            if (! $learner->qr_code) {
                do {
                    $code = 'LEMS-'.Str::upper(Str::random(16));
                } while (self::where('qr_code', $code)->exists());

                $learner->qr_code = $code;
            }
        });
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(LearnerAttendance::class);
    }
}
