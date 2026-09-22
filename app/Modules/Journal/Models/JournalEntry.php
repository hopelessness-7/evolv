<?php

namespace App\Modules\Journal\Models;

use App\Models\User;
use App\Modules\Journal\Enums\EntryKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'user_id',
        'kind',
        'body',
        'node_slug',
        'plan_date',
    ];

    protected function casts(): array
    {
        return [
            'kind' => EntryKind::class,
            'plan_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
