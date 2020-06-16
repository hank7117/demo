<?php

namespace App\Http\Controllers;

use App\Services\FacebookAccountService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function __construct()
    {
        $app = app();
        $this->service = $app->make('App\Services\FacebookAccountService',["facebook"]);
    }

    /**
     * 重導至facebook
     *
     * @return void
     */
    public function redirect()
    {
        return $this->service->redirect();
    }

    /**
     * facebook callback 處理
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback(Request $request)
    {
        if (! $request->input('code')) {
            return redirect('login')->withErrors('Login failed: '.$request->input('error_code').' - '.$request->input('error_message'));
        }
        $this->service->callback();
        $user = $this->service->user;
        auth()->login($user);
        return redirect()->to('/home');
    }
}
