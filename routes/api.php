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

// TRIED MAKING ROUTE TO RETURN ALL STOCK ITEMS
// Route::get('/stockitems', function (Request $request) {
//    $stockitems = DB::table('stockitems')
//       ->join('ingredients', 'ingredient_id', '=', 'stockitems.ingredient_id')
//       ->join('suppliers', 'suppliers.id', '=', 'stockitems.supplier_id')
//       ->select('stockitems.id', 'stockitems.name', 'stockitems.quantity', 'ingredients.name as ingredients', 'stockitems.expirationdate', 'stockitems.supplier_id', 'stockitems.isfood', 'suppliers.name as supplier_name')
//       ->get();
//    return response()->json($stockitems);
// });

Route::get('/stockitems', function () {
   $results = DB::table('stockitems')
       ->leftJoin('suppliers', 'suppliers.id', '=', 'stockitems.supplier_id')
       ->leftJoin('ingredients', 'ingredients.id', '=', 'stockitems.ingredient_id')
       ->select([
           'stockitems.id',
           'stockitems.quantity',
           'stockitems.expirationdate',
           'stockitems.name',
           'suppliers.name as supplier',
           'ingredients.name as ingredient'
       ])
       ->get();

   return response()->json($results);
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
        ->select('ingredients.id', 'ingredients.name as ingredienten', 'allergens.name as allergenen',)
        ->get();
    return response()->json($ingredients);
 });
 
 Route::get('/allergens', function () {
    return DB::table('allergens')->get();
 });

// Suppliers
 Route::get('/suppliers', function () {
    return DB::table('suppliers')->get();
 });