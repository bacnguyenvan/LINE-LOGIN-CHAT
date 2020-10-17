<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotifyController;
use App\Notifications\LineNotifyTest;
use Illuminate\Support\Facades\Route;
use Revolution\Line\Facades\Bot;
use Revolution\Line\Facades\LineNotify;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [LoginController::class, 'login'])->name('login');
Route::get('callback', [LoginController::class, 'callback']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');


Route::get('/home', HomeController::class)->name('home');

Route::get('info', function () {
    //dump(Bot::info());
    dump(Bot::verifyWebhook());
});

Route::middleware('auth')->group(function () {
    Route::get('notify/login', [NotifyController::class, 'login']);
    Route::get('notify/callback', [NotifyController::class, 'callback']);

    Route::get('notify', function () {
        $status = LineNotify::withToken(auth()->user()->notify_token)->status();

        auth()->user()->notify(new LineNotifyTest(json_encode($status)));
    });
});
