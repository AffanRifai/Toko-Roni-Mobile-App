<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{

    public function index()
    {
        return response()->json(Product::all());
    }

    public function show($id)
    {
        return response()->json(Product::findOrFail($id));
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());

        return response()->json($product);
    }

    public function update(Request $request,$id)
    {
        $product = Product::findOrFail($id);

        $product->update($request->all());

        return response()->json($product);
    }

    public function destroy($id)
    {
        Product::destroy($id);

        return response()->json([
            "message"=>"Product deleted"
        ]);
    }

}
