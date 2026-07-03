<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Contracts\AccessTokenRepositoryInterface;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use App\Modules\Auth\DTO\AuthTokenData;
use App\Modules\Auth\DTO\Input\LoginData;
use App\Modules\Auth\DTO\Input\LogoutData;
use App\Modules\Auth\DTO\Input\RegisterData;
use App\Modules\Auth\DTO\UserData;
use App\Modules\Auth\Exceptions\AuthException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AccessTokenRepositoryInterface $tokens,
    ) {}

    public function register(RegisterData $data): AuthTokenData
    {
        if ($this->users->findByEmail($data->email) !== null) {
            throw AuthException::emailTaken();
        }

        return $this->issueToken($this->users->create($data));
    }

    public function login(LoginData $data): AuthTokenData
    {
        $user = $this->users->findByEmail($data->email);

        if ($user === null || ! Hash::check($data->password, $user->password)) {
            throw AuthException::invalidCredentials();
        }

        return $this->issueToken($user);
    }

    public function logout(LogoutData $data): void
    {
        $this->tokens->revokeByPlainText($data->bearerToken);
    }

    public function me(User $user): UserData
    {
        return UserData::fromModel($user);
    }

    private function issueToken(User $user): AuthTokenData
    {
        return new AuthTokenData(
            user: UserData::fromModel($user),
            token: $this->tokens->issueForUser($user),
        );
    }
}
