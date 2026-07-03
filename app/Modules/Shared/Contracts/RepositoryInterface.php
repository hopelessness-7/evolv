<?php

namespace App\Modules\Shared\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template T of Model
 */
interface RepositoryInterface
{
    /** @return T|null */
    public function findById(int|string $id): ?Model;

    /** @return T */
    public function create(array $attributes): Model;

    /** @return T */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;
}
