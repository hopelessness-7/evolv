<?php

namespace App\Modules\Coach\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property \Carbon\CarbonImmutable|\Carbon\Carbon $plan_date
 * @property int $energy
 * @property int $focus
 * @property int $practice_ready
 * @property string|null $note
 */
class DailyCheckIn extends Model
{
    protected $table = 'daily_check_ins';

    protected $fillable = [
        'user_id',
        'plan_date',
        'energy',
        'focus',
        'practice_ready',
        'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'plan_date' => 'date',
            'energy' => 'integer',
            'focus' => 'integer',
            'practice_ready' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
