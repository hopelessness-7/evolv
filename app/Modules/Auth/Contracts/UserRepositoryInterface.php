<?php

namespace App\Modules\Auth\Contracts;

use App\Models\User;
use App\Modules\Auth\DTO\Input\RegisterData;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(RegisterData $data): User;
}
