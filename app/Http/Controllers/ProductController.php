<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    function createProduct(Request $req){
        $rules = array(
            'product_name'=>'required|string',
            'price'=>'required|string',
            'description'=>'required|string',

        );
    
        $validator = Validator::make($req->all(),$rules);
        
        if($validator->fails()){
            return $validator->errors();
        }

        $userid = auth()->user();

        if(!$userid){
            return response()->json(["success"=>"fail","msg"=>"Please login to continue"]);
        }

        $product = new Product;
        $product->product_name = $req->product_name;
        $product->price = $req->price;
        $product->description = $req->description;
        $product->user_id =$userid;
        $product->save();

        $imgdata = $req->images;
        $im=[];
        foreach ($imgdata as $value) {
            $im[] = $value->id;
        }

        $product->attach($im);

        return response()->json(["success"=>"true","msg"=>"product data inserted","data"=>$product]);


    
}
}