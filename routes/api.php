<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('/stockitems', function () {
    return DB::table('stockitems')->get();
 });
 
 Route::get('/users', function () {
    return DB::table('users')->get();
 });

 Route::get('/ingredients', function () {
    return DB::table('ingredients')->get();
 });
 
 Route::get('/allergens', function () {
    return DB::table('allergens')->get();
 });

 Route::get('/suppliers', function () {
    return DB::table('suppliers')->get();
 });