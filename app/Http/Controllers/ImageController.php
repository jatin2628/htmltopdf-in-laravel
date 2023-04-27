<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;

class ImageController extends Controller
{
    function createImage(Request $req){
        $userid = auth()->user();

        if(!$userid){
            return response()->json(["success"=>"fail","msg"=>"Please login to continue"]);
        }

        $imgdata = $req->file('url');
        $data = [];
        foreach ($imgdata as $value) {

            $filename = $value->getClientOriginalName();
            $value->move(public_path('/images/'),$filename);
            $image = new Image;
            $image->url = $filename;
            $image->save();
            $data[] = $image;
        }

        return response()->json(["success"=>"true","msg"=>"images inserted","data"=>$data]);

    }      
}
