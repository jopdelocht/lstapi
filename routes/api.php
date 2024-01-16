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

// Stock
Route::get('/stockitems', function () {
    return DB::table('stockitems')->get();
 });
 
//  Users
 Route::get('/users', function () {
    return DB::table('users')->get();
 });

 Route::post('/users', function (Request $request) {
   $name = $request->name;
   $email = $request->email;
   $password = $request->password;
 
   DB::insert('INSERT INTO users (name, email, password) VALUES (?, ?, ?)', [$name, $email, $password]);
   return response()->json(['message' => 'registered successfully'], 201);
 });


// Ingredients
//  Route::get('/ingredients', function () {
//     return DB::table('ingredients')->get();
//  });

Route::get('/ingredients', function (Request $request) {
   $ingredients = DB::table('ingredients')
       ->join('allergens', 'allergens.id', '=', 'ingredients.allergen_id')
       ->select('allergens.id as allergen', 'ingredients.id as ingredientId', 'ingredients.name as ingredient')
       ->get();

   return response()->json($ingredients);
});

// Allergens
 Route::get('/allergens', function () {
    return DB::table('allergens')->get();
 });

// Suppliers
 Route::get('/suppliers', function () {
    return DB::table('suppliers')->get();
 });