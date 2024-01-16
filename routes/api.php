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

 // Routes and endpoints for users
 Route::get('/users', function () {
   $users = DB::table('users')->get();
   return response()->json($users);
 });

//  POST-method for inserting new registered user
 Route::post('/users', function (Request $request) {
  $validatedData = $request->validate([
      'name' => 'required|max:255',
      'email' => 'required|email|unique:users',
      'password' => 'required',
  ]);
//   Upon creating a new entry in the user table => insert timestamp
  $user = User::create([
      'name' => $validatedData['name'],
      'email' => $validatedData['email'],
      'password' => $validatedData['password'],
      'created_at' => now(),
      'updated_at' => now(),
  ]);
  // Ensure the User model is using the HasApiTokens trait
  $token = $user->createToken('auth_token')->plainTextToken;
  // Update the remember_token in the database with the new token
  DB::table('users')
      ->where('id', $user->id)
      ->update(['remember_token' => $token]);
// Display message and return json for $token
  return response()->json(['id' => $user->id, 'token' => $token], 201);
});
 // token routes
 Route::post('/tokens/create', function (Request $request) {
    $user = User::find($user->id);
    //return $user;
    $token = $user->createToken('mynewtoken');
    return ['token' => $token->plainTextToken];
    // post the plaintexttoken to the user in the database
    $user:: where('id', $user->id)->update(['remember_token' => $token->plainTextToken]);
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