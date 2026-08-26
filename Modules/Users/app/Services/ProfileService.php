<?php

namespace Modules\Users\Services;

class ProfileService
{
    public function __construct(private \App\Models\User|\App\Models\UserAPI $user)
    {
    }

    public function update(array $data)
    {
        if (isset($data['avatar'])) {
            $data['avatar_url'] = asset($data['avatar']->store('avatars', 'public'));
            unset($data['avatar']);
        }

        $this->user->profile->update($data);
    }
}