<?php

namespace App\Modules\Curriculum\Models;

use App\Modules\Curriculum\Enums\EdgeKind;
use App\Modules\Curriculum\Enums\NodeStatus;
use App\Modules\Curriculum\Enums\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeNode extends Model
{
    protected $table = 'knowledge_nodes';

    protected $fillable = [
        'slug',
        'track',
        'title',
        'summary',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'track' => Track::class,
            'status' => NodeStatus::class,
            'meta' => 'array',
        ];
    }

    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(KnowledgeEdge::class, 'from_node_id');
    }

    public function incomingEdges(): HasMany
    {
        return $this->hasMany(KnowledgeEdge::class, 'to_node_id');
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'knowledge_edges',
            'from_node_id',
            'to_node_id',
        )->withPivot('kind')->wherePivot('kind', EdgeKind::Requires->value);
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'knowledge_edges',
            'to_node_id',
            'from_node_id',
        )->withPivot('kind')->wherePivot('kind', EdgeKind::Requires->value);
    }

    public function relatedNodes(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'knowledge_edges',
            'from_node_id',
            'to_node_id',
        )->withPivot('kind')->wherePivot('kind', EdgeKind::RelatedTo->value);
    }
}
