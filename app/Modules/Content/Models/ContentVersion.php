<?php

namespace App\Modules\Content\Models;

use App\Modules\Content\Enums\VersionStatus;
use App\Modules\Curriculum\Models\KnowledgeNode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentVersion extends Model
{
    protected $fillable = [
        'node_id',
        'version_no',
        'parent_version_id',
        'status',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => VersionStatus::class,
        ];
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(KnowledgeNode::class, 'node_id');
    }

    public function atoms(): HasMany
    {
        return $this->hasMany(ContentAtom::class, 'version_id');
    }
}
