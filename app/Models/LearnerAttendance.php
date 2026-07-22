<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerAttendance extends Model
{
    use HasFactory;

    protected $table = 'learner_attendance';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'learner_id',
        'date',
        'am_in',
        'am_out',
        'pm_in',
        'pm_out',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function learner(): BelongsTo
    {
        return $this->belongsTo(Learner::class);
    }
}
