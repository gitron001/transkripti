<?php

use App\Http\Controllers\User\VoiceIsolatorController;
use App\Http\Controllers\Admin\Extensions\VoiceIsolatorSettingController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'middleware' => ['verified', '2fa.verify', 'role:admin', 'PreventBackHistory']], function() {
    Route::controller(VoiceIsolatorSettingController::class)->group(function() {
        Route::get('/davinci/configs/voice-isolator', 'index')->name('admin.davinci.configs.voice.isolator');
        Route::post('/davinci/configs/voice-isolator', 'store')->name('admin.davinci.configs.voice.isolator.store');
    }); 
});

Route::group(['prefix' => 'user', 'middleware' => ['verified', '2fa.verify', 'role:user|admin|subscriber', 'subscription.check', 'PreventBackHistory']], function() {
    // USER AI VIDEO ROUTES
    Route::controller(VoiceIsolatorController::class)->group(function () {
        Route::get('/voice-isolator', 'index')->name('user.voice.isolator');       
        Route::post('/voice-isolator/create', 'create')->name('user.voice.isolator.create');         
        Route::post('/voice-isolator/delete', 'delete');                                                   
    });

});