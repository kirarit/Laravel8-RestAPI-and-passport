<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function userSignUp(Request $request)
    {   
        $data = $request->all();

        $validator = Validator::make($data, [
            "name" => "required",
            "email" => "required|unique:users|email",
            "password" => "required",
            "phone" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "failed", "message" => "validation_error", "errors" => $validator->errors()]);
        }

        $name = $request->name;
        $name = explode(" ", $name);
        $first_name = $name[0];
        $last_name = "";

        if (isset($name[1])) {
            $last_name = $name[1];
        }

        $array_data = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "full_name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "phone" => $request->phone
        );  

        $user = User::create($array_data);

        if (!is_null($user)) {
            $accessToken = $user->createToken('authToken')->accessToken;
            return response([ 'user' => $user, 'access_token' => $accessToken]);
            // return response([ 'user' => new UserResource($user), 'message' => 'User registered successfully'], 200);
        } else {
            return response()->json(["status" => "failed", "success" => false, "message" => "failed to register"]);
        }
    }


    // ------------ [ User Login ] -------------------
    public function userLogin(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Invalid Credentials']);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['user' => auth()->user(), 'access_token' => $accessToken]);
    }


    // ------------------ [ User Detail ] ---------------------
    public function userDetail($email)
    {     
        if ($email != "") {
            return new UserResource(User::where("email", $email)->first());        
        }
    }
}
