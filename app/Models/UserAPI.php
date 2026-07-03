<?php

namespace App\Models;

class UserAPI extends User
{
    protected string $guard_name = 'api';

    protected $table = 'users';
}