<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;

class UserAPI extends User
{
    use HasApiTokens;

    protected string $guard_name = 'api';

    protected $table = 'users';
}