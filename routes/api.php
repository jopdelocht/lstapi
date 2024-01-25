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
      ->select([
          'stockitems.id',
          'products.name AS product',
          'stockitems.quantity',
          'stockitems.expirationdate',
          'suppliers.name AS supplier'
      ])
      ->leftJoin('products', 'products.id', '=', 'stockitems.product_id')
      ->leftJoin('suppliers', 'suppliers.id', '=', 'stockitems.supplier_id')
      ->get();

      return response()->json($results);
});

Route::post('/stockitems', function (Request $request) {
   $product_id = $request->product_id;
   $quantity = $request->quantity;
   $expirationdate = $request->expirationdate;
  //  $isfood = $request->isfood;
   $supplier_id = $request->supplier_id;
  //  $ingredient_id = $request->ingredient_id;

    DB::insert('INSERT INTO stockitems (product_id, quantity, expirationdate, supplier_id) 
    VALUES (?, ?, ?, ?)', [$product_id, $quantity, $expirationdate, $supplier_id]);
   return response()->json(['message' => 'Stockitem created successfully'], 201);
 });

// Route for the stockitems delete
Route::delete('/stockitems/{id}', function ($id) {
  DB::delete('DELETE FROM stockitems WHERE id = ?', [$id]);
  return response()->json(['message' => 'Stockitem deleted successfully'], 200);
});

// Route for the stockitems update
Route::patch ('/stockitems/{id}', function ($id, Request $request) {
  DB::update('UPDATE stockitems SET quantity = ?, expirationdate = ?, supplier_id = ? WHERE id = ?', [$request->quantity, $request->expirationdate, $request->supplier_id, $id]);
  return response()->json(['message' => 'Stockitem updated successfully'], 200);
});

// Products
 Route::get('/products', function () {
  $products = DB::table('products')
      ->select('products.id', 'products.name AS productname', 'ingredients.name AS ingredientname',  'types.name AS type')
      ->leftJoin('ingredients', 'ingredients.id', '=', 'products.ingredients')
      // ->leftJoin('allergens', 'allergens.id', '=', 'products.allergen_id')
      ->leftJoin('types', 'types.id', '=', 'products.type_id')
      ->orderBy('productname', 'ASC')
      ->get();

  return response()->json($products);
});


//POST-method for inserting new products
Route::post('/products', function (Request $request) {
  $name = $request->name;
  $ingredients = $request->ingredients;
  $isfood = $request->isfood;
  $type_id = $request->type_id;

  DB::insert('INSERT INTO products (name, ingredients, isfood, type_id) VALUES (?, ?, ?, ?)', [$name, $ingredients, $isfood, $type_id]);
  return response()->json(['message' => 'added successfully'], 201);
});


 Route::get('/users', function () {
   $users = DB::table('users')->get();
   return response()->json($users);
 });

//  POST-method for inserting new registered user
 Route::post('/users', function (Request $request) {
  $validatedData = $request->validate([
      'name' => 'required|max:255|unique:users',
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
 Route::get('/ingredients', function (Request $request) {
  $result = DB::select("
  SELECT 
    ingredients.id AS Ingredient_id,
    ingredients.name AS Ingredient, 
    GROUP_CONCAT(allergens.name SEPARATOR ', ') AS Allergen
FROM ingredients
JOIN allergens ON CONCAT(',', ingredients.allergens, ',') LIKE CONCAT('%,', allergens.id, ',%')
GROUP BY ingredients.id, ingredients.name;
  ");
  return response()->json($result, 200);
});

// POST-method for inserting new ingredients
 Route::post('/ingredients', function (Request $request) {

  // Check if name is empty or not
  if (empty($request->name)) {
    return response()->json([
      'message' => 'Ingredient name cannot be empty',
      'success' => false
    ], 400);
  }

  $name = sanitizeInput($request->name);

  if (empty($name)) {
    return response()->json([
      'message' => 'Ingredient name cannot be empty',
      'success' => false
    ], 400);
  }

  $allergens = '';
  if (!empty($request->name)) {
    $allergens = sanitizeInput($request->allergens);
  }

  DB::insert('INSERT INTO ingredients (name, allergens) VALUES (?, ?)', [$name, $allergens]);
  return response()->json([
    'message' => 'Ingredient added successfully',
    'success' => true
  ], 201);
});
 


// Allergens 
 Route::get('/allergens', function () {
    return DB::table('allergens')->get();
 });

// Suppliers
 Route::get('/suppliers', function () {
    return DB::table('suppliers')->get();
 });
 
// POST-method for inserting new suppliers
 Route::post('/suppliers', function (Request $request) {
  $name = $request->name;

function sanitizeInput(string $input): string
 {
  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  $input = strtolower($input);
  $input = ucfirst($input);
  return $input;
}
   
  DB::insert('INSERT INTO suppliers (name) VALUES (?)', [$name]);
  return response()->json(['message' => 'added successfully'], 201);
 });

 //RECIPE_PRODUCT
 //GET for recipe_product
 Route::get('/recipe_product_all', function () {
  $results = DB::select("SELECT
  recipe_product.id,
  recipes.name AS recipename,
  recipe_product.basevalue,
  products.id AS product_id,
  products.name AS productname,
  recipe_product.quantity,
  types.name AS measurement,
  products.ingredients
FROM recipe_product
LEFT JOIN products ON products.id = recipe_product.product_id
LEFT JOIN recipes ON recipes.id = recipe_product.recipe_id
LEFT JOIN types ON types.id = recipe_product.type_id
LEFT JOIN ingredients ON products.id = ingredients
ORDER BY recipe_product.id ASC");

  return response()->json($results);
});


//ORDERS
//get
Route::get('/orders', function () {
  return DB::select("
  SELECT
  orders.id AS orderid,
  clients.id AS clientid,
  clients.lastname AS clientname,
  orders.totalquantity,
  orders.recipe,
  orders.productquantity,
  orders.type,
  orders.product,
  orders.ingredient,
  orders.allergen
  FROM orders
  LEFT JOIN clients ON clients.id = orders.client_id
  ");
});


//post
Route::post('/orders', function (Request $request) {
  $client_id = $request -> client_id;
  $totalquantity = $request -> totalquantity;
  $recipe = $request -> recipe;
  $productquantity = $request -> productquantity;
  $product = $request -> product;
  $type = $request -> type;
  $ingredient = $request -> ingredient;
  $allergen = $request -> allergen;

  DB::insert('INSERT INTO orders (client_id, totalquantity, recipe, productquantity, product, type, ingredient, allergen)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$client_id, $totalquantity, $recipe, $productquantity, $product, $type, $ingredient, $allergen]);
  return response()->json(['message' => 'Order added successfully'], 201);
});

// Clients
Route::get('/clients', function () {
  return DB::table('clients')->get();
});