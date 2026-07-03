<?php

namespace App\Modules\Content\Models;

use App\Modules\Content\Enums\AtomKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentAtom extends Model
{
    protected $fillable = [
        'version_id',
        'kind',
        'body_md',
        'meta',
        'order_in_version',
        'qdrant_point_id',
    ];

    protected function casts(): array
    {
        return [
            'kind' => AtomKind::class,
            'meta' => 'array',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ContentVersion::class, 'version_id');
    }
}
