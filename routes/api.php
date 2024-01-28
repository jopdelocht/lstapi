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

Route::middleware ('auth:sanctum')->post('/stockitems', function (Request $request) {
   $product_id = $request->product_id;
   $quantity = $request->quantity;
   $expirationdate = $request->expirationdate;
   $supplier_id = $request->supplier_id;

    DB::insert('INSERT INTO stockitems (product_id, quantity, expirationdate, supplier_id) 
    VALUES (?, ?, ?, ?)', [$product_id, $quantity, $expirationdate, $supplier_id]);
   return response()->json(['message' => 'Stockitem created successfully'], 201);
 });

// Route for the stockitems delete
Route::middleware ('auth:sanctum')->delete('/stockitems/{id}', function ($id) {
  DB::delete('DELETE FROM stockitems WHERE id = ?', [$id]);
  return response()->json(['message' => 'Stockitem deleted successfully'], 200);
});

// Route for the stockitems update
Route::middleware ('auth:sanctum')->patch ('/stockitems/{id}', function ($id, Request $request) {
  DB::update('UPDATE stockitems SET product_id = ?, quantity = ?, expirationdate = ?, supplier_id = ? WHERE id = ?', [$request ->product_id, $request->quantity, $request->expirationdate, $request->supplier_id, $id]);
  return response()->json(['message' => 'Stockitem updated successfully'], 200);
});

 // Products
 Route::get('/products', function (Request $request) {
  $result = DB::select("
  SELECT 
  products.id AS Product_id,
  products.name AS Product, 
  `types`.`name` AS Type,
  products.isfood AS IsFood,
  GROUP_CONCAT(IFNULL(ingredients.name, '') SEPARATOR ', ') AS Ingredient
  FROM products
  LEFT JOIN ingredients ON CONCAT(',', products.ingredients, ',') LIKE CONCAT('%,', ingredients.id, ',%')
  JOIN `types` ON `types`.id = products.type_id
  GROUP BY products.id, products.name;
  ");
  return response()->json($result, 200);
});

//POST-method for inserting new products
Route::middleware ('auth:sanctum')->post('/products', function (Request $request) {
  $name = $request->name;
  $ingredients = $request->ingredients;
  $isfood = $request->isfood;
  $type_id = $request->type_id;

  DB::insert('INSERT INTO products (name, ingredients, isfood, type_id) VALUES (?, ?, ?, ?)', [$name, $ingredients, $isfood, $type_id]);
  return response()->json(['message' => 'added successfully'], 201);
});

// PATCH-method for updating products
Route::middleware ('auth:sanctum')->patch('/products/{id}', function (Request $request, $id) {
    $name = $request->name;
    $ingredients = $request->ingredients;
    $isfood = $request->isfood;
    $type_id = $request->type_id;

    DB::update('UPDATE products SET name = ?, ingredients = ?, isfood = ?, type_id = ? WHERE id = ?', [$name, $ingredients, $isfood, $type_id, $id]);
    return response()->json(['message' => 'Product updated successfully'], 200);
});

// DELETE-method for deleting products
Route::middleware ('auth:sanctum')->delete('/products/{id}', function ($id) {
  DB::delete('DELETE FROM products WHERE id = ?', [$id]);
  return response()->json(['message' => 'Product deleted successfully'], 200);
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
      GROUP_CONCAT(IFNULL(allergens.name, '') SEPARATOR ', ') AS Allergen
  FROM ingredients
  LEFT JOIN allergens ON CONCAT(',', ingredients.allergens, ',') LIKE CONCAT('%,', allergens.id, ',%')
  GROUP BY ingredients.id, ingredients.name;
  ");
  return response()->json($result, 200);
});

// POST-method for inserting new ingredients
Route::middleware ('auth:sanctum')->post('/ingredients', function (Request $request) {
  DB::insert('INSERT INTO ingredients (name, allergens) VALUES (?, ?)', [$request->name, $request->allergens]);
  return response()->json([
      'message' => 'Ingredient added successfully',
      'success' => true
  ], 201);
});

//  route for the ingredients delete
Route::middleware ('auth:sanctum')->delete('/ingredients/{id}', function ($id) {
  DB::delete('DELETE FROM ingredients WHERE id = ?', [$id]);
  return response()->json(['message' => 'Ingredient deleted successfully'], 200);
});

// route for the ingredients update
Route::middleware ('auth:sanctum')->patch ('/ingredients/{id}', function ($id, Request $request) {
  DB::update('UPDATE ingredients SET name = ?, allergens = ? WHERE id = ?', [$request->name, $request->allergens, $id]);
  return response()->json(['message' => 'Ingredient updated successfully'], 200);
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
 Route::middleware ('auth:sanctum')->post('/suppliers', function (Request $request) {
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

 //  Route for the suppliers delete
 Route::middleware ('auth:sanctum')->delete('/suppliers/{id}', function ($id) {
  DB::delete('DELETE FROM suppliers WHERE id = ?', [$id]);
  return response()->json(['message' => 'Supplier deleted successfully'], 200);
});

//  Route for the suppliers update
 Route::middleware ('auth:sanctum')->patch ('/suppliers/{id}', function ($id, Request $request) {
  DB::update('UPDATE suppliers SET name = ? WHERE id = ?', [$request->name, $id]);
  return response()->json(['message' => 'Supplier updated successfully'], 200);
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
  orders.allergen,
  orders.deliverydate,
  orders.orderdate
  FROM orders
  LEFT JOIN clients ON clients.id = orders.client_id
  ");
});


// route for order post
Route::middleware ('auth:sanctum')->post('/orders', function (Request $request) {
  $client_id = $request -> client_id;
  $totalquantity = $request -> totalquantity;
  $recipe = $request -> recipe;
  $productquantity = $request -> productquantity;
  $product = $request -> product;
  $type = $request -> type;
  $ingredient = $request -> ingredient;
  $allergen = $request -> allergen;
  $deliverydate = $request -> deliverydate;
  $orderdate = $request -> orderdate;

  DB::insert('INSERT INTO orders (client_id, totalquantity, recipe, productquantity, product, type, ingredient, allergen, deliverydate, orderdate)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$client_id, $totalquantity, $recipe, $productquantity, $product, $type, $ingredient, $allergen, $deliverydate, $orderdate]);
  return response()->json(['message' => 'Order added successfully'], 201);
});


// Clients
Route::get('/clients', function () {
  return DB::table('clients')->get();
});

// LineItem fullfill
Route::middleware ('auth:sanctum')->post('/fullfill_line_item', function (Request $request) {
  $orderId = $request->orderId;

  // Fetch line item from db
  $lineItem = DB::select("
    SELECT id, productquantity, product
    FROM orders
    WHERE id = " . $orderId . "
  ")[0];
  
  $stockToRemove = $lineItem->productquantity;

  // Fetch product assigned to line item from db
  $product = DB::select("
  SELECT id
  FROM products
  WHERE name = '" . $lineItem->product . "'
  ")[0];

  // With the product id get the relevant stock from db in order of expiry date
  $productStock = DB::select("
  SELECT id, quantity, expirationDate
  FROM stockitems
  WHERE product_id = " . $product->id . "
  ORDER BY expirationDate ASC
  ");

  foreach ($productStock as $key => $stock) {
    $stockToRemove -= $stock->quantity;

    // if stockToRemove is lower than zero
    if ($stockToRemove <= 0) {

      // update stock in db
      DB::update("
      UPDATE stockitems
      SET quantity = " . abs($stockToRemove) . "
      WHERE id = " . $stock->id . "
      ");

      continue;
    }

    // If last stock item we can go below 0
    if ($key === array_key_last($productStock)) {
      // update stock to negative whatever is left in $stockToRemove
      $newStock =  $stock->quantity - $stockToRemove;

      // update stock in db
      DB::update("
      UPDATE stockitems
      SET quantity = " . $newStock . "
      WHERE id = " . $stock->id . "
      ");

      continue;
    }

    // Update stock in db to 0
    DB::update("
    UPDATE stockitems
    SET quantity = " . 0 . "
    WHERE id = " . $stock->id . "
    ");
  }

});

// route for the orders delete
Route::middleware ('auth:sanctum')->delete('/orders/{id}', function ($id) {
  DB::delete('DELETE FROM orders WHERE id = ?', [$id]);
  return response()->json(['message' => 'Order deleted successfully'], 200);
});
// route for orders edit
Route::middleware ('auth:sanctum')->patch('/orders/{id}', function ($id, Request $request) {
    // Retrieve the order from the database
    $order = DB::table('orders')->where('id', $id)->first();

    // Update the order fields with the new values from the request
    $order->totalquantity = $request->totalquantity;
    $order->recipe = $request->recipe;
    $order->productquantity = $request->productquantity;
    $order->product = $request->product;
    $order->type = $request->type;
    $order->ingredient = $request->ingredient;
    $order->allergen = $request->allergen;

    // Save the updated order back to the database
    DB::table('orders')->where('id', $id)->update((array) $order);

    return response()->json(['message' => 'Order updated successfully'], 200);
});