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
  // Assuming the user ID is sent with the request
  // and you are authenticated to get the user ID from the request
  $userId = $request->user()->id;
  $user = User::find($userId);

  if ($user) {
      $token = $user->createToken('mynewtoken');
      // Update the user's remember_token in the database
      $user->update(['remember_token' => $token->plainTextToken]);
      
      // Return the token
      return ['token' => $token->plainTextToken];
  }

  // Handle the case where the user is not found
  return response()->json(['error' => 'User not found'], 404);
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

 // Ingredients
 Route::post('/ingredients', function (Request $request) {
  $name = $request->name;
  $allergen_id = $request->allergen_id;

  DB::insert('INSERT INTO ingredients (name, allergen_id) VALUES (?, ?)', [$name, $allergen_id]);
  return response()->json(['message' => 'added successfully'], 201);
});