<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use  Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','verifyUser']]);
    }

    public function register(Request $req){

        $rules = array(
            'first_name'=>'required|string',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
            'address'=>'required'
        );
    
        $validator = Validator::make($req->all(),$rules);
        
        if($validator->fails()){
            return $validator->errors();
        }


        $user = new User;
        $user->first_name = $req->first_name;
        $user->last_name = $req->last_name;
        $user->email = $req->email;
        $user->password = Hash::make($req->password);
        $user->address = $req->address;
        $user->save();

        $credentials = ['email'=>$req->email, 'password'=>$req->password];

        if(!$token = auth()->attempt($credentials)){
            return response()->json(['status'=>'false','msg'=>'Registration failed']);
        }



        $addr = URL::to('/api');
        $url = $addr.'/verifyuser?token='.$token;

        $data = [
            'url'=>$url,
            'subject'=>'Verify Your Enail address',
            'email'=>$req->email,
            'body'=>'Click link to verify your email address'
        ];

        $mail = Mail::send('mail',$data,function($message) use ($data){
            $message->to($data['email']);
            $message->subject($data['subject']);
        });

        if($mail){
            return response()->json(['success'=>'ok','msg'=>"verify email mail sent succesfully to your mail id"]);
        }


        return response()->json(['status'=>'false','msg'=>'internal servor error']);
    }

    public function login(Request $req)
    {
        $rules = array(
            'email'=>'required|email',
            'password'=>'required|min:6'
        );
    
        $validator = Validator::make($req->all(),$rules);
        
        if($validator->fails()){
            return $validator->errors();
        }

        $check = User::where('email',$req->email)->first();
        if(!$check){
            return response()->json(['msg'=>'Invalid Credentials']);
        }

        $userpass = Hash::check($req->password,$check->password);
        if(!$userpass){
            return response()->json(['msg'=>'Invalid Credentials']);    
        }

        if($check->verified==0){
            $credentials = ['email'=>$req->email, 'password'=>$req->password];

        $token = auth()->attempt($credentials);
        if(!$token){
            return response()->json(['status'=>'false','msg'=>'Registration failed']);
        }

        $addr = URL::to('/api');
        $url = $addr.'/verifyuser?token='.$token;

        $data = [
            'url'=>$url,
            'subject'=>'Verify Your Enail address',
            'email'=>$req->email,
            'body'=>'Click link to verify your email address'
        ];

        $mail = Mail::send('mail',$data,function($message) use ($data){
            $message->to($data['email']);
            $message->subject($data['subject']);
        });

        if($mail){
            return response()->json(['success'=>'ok','msg'=>"verify email mail sent succesfully to your mail id"]);
        }
        }
            
        $credentials = ['email'=>$req->email, 'password'=>$req->password];

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['status'=>'ok','msg'=>'Login successful','data'=>$check,'token'=>$token]);
    }

    function verifyUser(Request $req){
        $user = auth()->user();

        if(!$user){
            return response()->json(['status'=>'fail','msg'=>'link has been expired']);
        }
        $us = User::find($user->id);
        $us->verified = 1;
        $us->save();

        return response()->json(['status'=>'true','msg'=>'Email verified succesfully']);
    }
}
