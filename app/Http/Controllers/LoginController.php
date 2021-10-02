<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use Revolution\Line\Facades\Bot;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if($request->isMethod('POST')){
            $data = [
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'country' => $request->country,
            ];
            $request->session()->put('info_user', $data);

            return Socialite::driver('line-login')->with([
                'prompt' => 'consent',
                'bot_prompt' => 'normal',
            ])->redirect();
        }
        
        return view('form');
    }


    public function callback(Request $request)
    {
        $data = $request->session()->get('info_user');

        if ($request->missing('code')) {
            dd($request);
        }

        /**
         * @var \Laravel\Socialite\Two\User
         */
        $user = Socialite::driver('line-login')->user();

        $loginUser = User::updateOrCreate([
            'line_id' => $user->id,
        ], [
            'name' => 'User', //$user->nickname,
            'avatar' => $user->avatar,
            'access_token' => $user->token,
            'refresh_token' => $user->refreshToken,
        ]);

        auth()->login($loginUser, true);

        // save info
        \App\Models\Info::updateOrCreate(
            ['user_id' => $loginUser->id],$data
        );

        // send message
        $msg = "Hello ".$data['first_name']." ".$data['last_name'].".Country: ".$data['country'].".Welcome you to Line";
        $message = new TextMessageBuilder($msg);

        Bot::pushMessage($request->user()->line_id, $message);
        // $response = Bot::multicast([$request->user()->line_id], $message);

        // remove session
        $request->session()->forget('info_user');

        return redirect()->route('home');
    }

    public function logout()
    {
        auth()->logout();

        return redirect('/');
    }
}
