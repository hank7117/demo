<?php


namespace App\Base;

use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * Class ThirdPartyLoginBase
 * @package App\Base
 * 所有第三方登入的抽象類別
 */
abstract class FacebookLoginBase
{
    //第三方登入類型
    protected $type;
    public $user;

    abstract function callback();

    function redirect(){
        return Socialite::driver($this->type)->redirect();
    }
}
