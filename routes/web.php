<?php

use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\UserOTP;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
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


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';



Route::get('/{user_id}/otp-verification', function ($user_id) {

    $user = User::find($user_id);

    return view('otp-verification', compact('user'));
})->name('otp-verification');


Route::post('/{user_id}/otp-validation', function ($user_id, Request $request) {
    
    $otp = UserOTP::where('otp_code', $request->otp_code)->where('user_id', $user_id)->where('expired_at', '<=', 'now()')->first();

    if (!$otp) {
        return redirect()->back()->withErrors([
            'otp_code' => 'OTP CODE tidak ditemukan.'
        ]);
    }
    
    $otp->user->email_verified_at = Date::now();
    $otp->user->save();
    
    Auth::login($otp->user);
    
    return redirect(RouteServiceProvider::HOME);

})->middleware([])->name('otp.validation');
