<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
use Telegram\Bot\Laravel\Facades\Telegram;

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

// Route::get('/', function () {
//     return view('zalupjuha');
// });

Route::get('/test', [TestController::class, 'index'])->name('test');

Route::get('/', [TestController::class, 'index'])->name('zalupjuha');

// Route::get('/bot/getupdates', function () {
//     $updates = Telegram::getUpdates();
//     return (json_encode($updates));
// });

// Route::get('bot/sendmessage', function() {
//     Telegram::sendMessage([
//         'chat_id' => '642114867',
//         'text' => 'Hello world!'
//     ]);
//     return;
// });