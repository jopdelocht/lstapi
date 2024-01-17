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

//Stockitems
Route::get('/stockitems', function () {
   $results = DB::table('stockitems')
       ->leftJoin('suppliers', 'suppliers.id', '=', 'stockitems.supplier_id')
       ->leftJoin('ingredients', 'ingredients.id', '=', 'stockitems.ingredient_id')
       ->select([
           'stockitems.id',
           'stockitems.name',
           'stockitems.quantity',
           'stockitems.expirationdate',
           'stockitems.isfood',
           'suppliers.name as supplier',
           'ingredients.name as ingredient'
       ])
       ->get();
   return response()->json($results);
});

Route::post('/stockitems', function (Request $request) {
   $name = $request->name;
   $quantity = $request->quantity;
   $expirationdate = $request->expirationdate;
   $isfood = $request->isfood;
   $supplier_id = $request->supplier_id;
   $ingredient_id = $request->ingredient_id;

    DB::insert('INSERT INTO stockitems (name, quantity, expirationdate, isfood, supplier_id, ingredient_id) 
    VALUES (?, ?, ?, ?, ?, ?)', [$name, $quantity, $expirationdate, $isfood, $supplier_id, $ingredient_id]);
   return response()->json(['message' => 'Stockitem created successfully'], 201);
 });

// {
//    "name": "Rond taartdeeg",
//    "quantity": 250,
//    "expirationdate": "10/10/2030",
//    "isfood": 1,
//    "supplier_id": "1",
//    "ingredient_id": "1"
// }
 
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