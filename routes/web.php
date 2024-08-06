<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/chat', [MessageController::class, 'showChat'])->middleware('auth');

Route::get('/users', [MessageController::class, 'getUsers'])->middleware('auth');
Route::post('/start-conversation', [MessageController::class, 'startConversation'])->middleware('auth');
Route::post('/send-message', [MessageController::class, 'sendMessage'])->middleware('auth');
Route::get('/messages/{conversationId}', [MessageController::class, 'getMessages'])->middleware('auth');
// Route::get('/test-broadcast', function () {
//     broadcast(new \App\Events\MessageSent(\App\Models\Message::find(1)));
//     return 'Event has been broadcast!';
// });

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
