<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
route::get('trial', function (){
    return response()->json([
        'Kamougue est une mere maintenant !'
    ]);
    return 'Kamougue est une mere maintenant !';

} );
route::prefix('filesshare')->group(function () {
    Route::post('register', [AuthController::class, 'handle_register']);
    Route::post('login', [AuthController::class, 'handle_login']);
    route::get('/delete/{id}', [AuthController::class, 'delete_user']);
    route::get('/all_users', [AuthController::class, 'List_user']);
    Route::get('/one_user/{id}', [AuthController::class, 'Current_user']);

    Route::post('/CreateGroup', [GroupController::class, 'CreateGroup']);
    // Route::get('/ListGroup', [GroupController::class, 'ListGroup']);
    Route::post('/AddMember/{groupId}', [GroupController::class, 'AddMember']);
    Route::get('/SelectGroups/{memberId}', [GroupController::class, 'SelectGroupOfaMember']);

    route::post('/sendFile/{id}/{groupId}', [GroupController::class, 'sendFile']);
    route::get('/getFiles/{groupId}', [GroupController::class, 'displayFile']);
    




 
    
});