<?php


namespace App\Services;


use App\Base\FacebookLoginBase;
use App\Models\FacebookAccount;
use App\Models\User;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

class FacebookAccountService extends FacebookLoginBase
{
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function createOrGetUser(ProviderUser $providerUser)
    {
        $account = FacebookAccount::whereProvider($this->type)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        $user = null;
        if ($account) {
            $user = $account->user;
        } else {
            $account = new FacebookAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $this->type
            ]);
            $user = User::whereEmail($providerUser->getEmail())->first();
            if (!$user) {
                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                    'password' => md5(rand(1, 10000)),
                ]);
            }
            $account->user()->associate($user);
            $account->save();
            $user = $user;
        }
        $this->user = $user;
    }

    function callback()
    {
        $this->createOrGetUser(Socialite::driver($this->type)->user());
    }
}
