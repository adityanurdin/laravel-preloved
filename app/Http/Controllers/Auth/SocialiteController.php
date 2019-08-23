<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Socialite;
use App\Models\SocialAccount;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $th) {
            return redirect('/login')->with('status' , 'Failed to login');
        }

        $authUser = $this->findOrCreateUser($user , $provider);
        Auth::login($authUser , true);

        return redirect('/home');
    }

    public function findOrCreateUser($socialUser , $provider)
    {
        $socialAccount = SocialAccount::where('provider_id' , $socialUser->getId())
                        ->where('provider_name' , $provider)
                        ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        } else {
            $user = User::where('email' , $socialUser->getMail())->first();
            if (! $user) {
                $user = User::create([
                    'name'  => $socialUser->getName(),
                    'email' => $socialUser->getEmail()
                ]);
            }

            $user->SocialAccounts()->create([
                'provider_id'   => $socialUser->getId(),
                'provider_name' => $provider
            ]);

            return $user;
        }

    }


}
