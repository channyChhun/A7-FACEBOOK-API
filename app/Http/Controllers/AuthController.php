<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);

        $user = User::create($validatedData);
        $token = $user->createToken('mytoken')->plainTextToken;

        return response()->json([
            'message' => 'register successfully',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function store(Request $request)
    {
        // Initialize the $user variable as a new User instance
        $user = new User();
        
        // Assign values to the user's properties
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        
        // Save the user to the database
        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }
    
    
    public function login(Request $request){
        $request->validate([
            'email'=>'required',
            'password'=>'required',
        ]);
        $user=User::where('email',$request->email)->first();
        if(!$user){
            return response()->json([
               'message' => 'User not found','status' => false,
            ]);
        }
        if(Hash::check($request->password,$user->password)){
            $access_token=$user->createToken('authToken')->plainTextToken;
            return response([
                'message'=>'login successful',
                'success'=>true,
                'user'=>$user,
                'access_token'=>$access_token,
            ]);
        }
        return response([
            'message'=>'login failed',
           'success'=>false,

        ]);

    }
   
    public function logout(Request $request){
        $user=Auth::user();
        // $user->tokens()->delete();
        return response()->json([
           'message' => 'Successfully logged out'
        ]);

    }
    
    // ========code resetPassw=======
    public function sendEmailVerify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = DB::table('users')->where('email', '=', $request->email)->first();

        if ($user) {
            $passcord = Str::random(6);

            DB::table('reset_passwords')->insert([
                'email' => $request->email,
                'passcord' => $passcord,
            ]);

            return response()->json(['message' => 'Password reset email sent successfully', 'passcord' => $passcord]);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function resetPassword(Request $request)
    {
        $resetData = DB::table('reset_passwords')
            ->where('email', $request->email)
            ->where('passcord', $request->passcord)
            ->first();

        if (!$resetData) {
            return response()->json(['message' => 'Reset password link is invalid'], 404);
        }

        $user = User::where('email', $resetData->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('reset_passwords')->where('passcord', $resetData->passcord)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
