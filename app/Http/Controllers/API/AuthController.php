<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\UserCode;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'min:8', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        if($validator->fails()){
            return response()->json([
                'validation_errors'=>$validator->messages()
            ]);
        }
        else{
            $user =  User::create([
                'firstname' => $request['firstname'],
                'lastname' => $request['lastname'],
                'email' => $request['email'],
                'phone' => $request['phone'],
                'password' => Hash::make($request['password'])
            ]);
            $token = $user->createToken($user->email.'_Token')->plainTextToken;

            $code = rand(1000,9999);

            $userCode = UserCode::updateOrCreate(
                ['phone'=> $user->phone],
                ['code'=> $code]
            );

            return response()->json([
                'status'=>200,
                'username'=>$user->full_name,
                'token'=>$token,
                'message'=>'Registered successfully',
                'phone'=>$user->phone
            ]);
        }
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
           'phone' => ['required', 'string', 'min:8'],
           'password' => ['required', 'string', 'min:8']
       ]);

       if($validator->fails()){
            return response()->json([
               'validation_errors'=>$validator->messages()
           ]);
       }
       else{
           $user = User::where('phone', $request->phone)->first();

           if (! $user || ! Hash::check($request->password, $user->password)) {
               return response()->json([
                  'status'=>401,
                  'message'=>'The provided credentials are incorrect.'
               ]);
           }
           else{
               $token = $user->createToken($user->email.'_Token')->plainTextToken;
               return response()->json([
                   'status'=>200,
                   'username'=>$user->full_name,
                   'token'=>$token,
                   'message'=>'Logged In successfully',
               ]);
           }
       }
    }

     public function logout(Request $request){
        auth()->user()->tokens()->delete();
         return response()->json([
          'status'=>200,
          'message'=>'Logout successfully'
       ]);
     }

     public function smsVerify(Request $request){
          $validator = Validator::make($request->all(), [
                'phone' => ['required', 'string', 'min:8'],
                'code' => ['required', 'string', 'min:4']
          ]);

         if($validator->fails()){
             return response()->json([
                'validation_errors'=>$validator->messages()
            ]);
         }
         else{
            $userCode = UserCode::where('phone', $request->phone)->where("code",$request->code)->first();
            if(!$userCode){
                 return response()->json([
                      'status'=>401,
                      'message'=>'The provided credentials are incorrect.'
                 ]);
            }
            else {
                if($userCode->isExpired()){
                     return response()->json([
                          'status'=>403,
                          'message'=>'Code was expired'
                     ]);
                }
                else{
                    // update user status false->active
                    $user = User::where("phone",$request->phone)->first();
                    $user->active = true;
                    $user->save();
                     return response()->json([
                      'status'=>200,
                      'message'=>'Sms verification end successfully!!!'
                   ]);
                }
            }
         }
     }

       public function resendSms(Request $request){
            $validator = Validator::make($request->all(), [
               'phone' => ['required', 'string', 'min:8']
           ]);

            if($validator->fails()){
                return response()->json([
                   'validation_errors'=>$validator->messages()
               ]);
            }
            else{
                $user = User::where("phone",$request->phone)->first();
                $code = rand(1000,9999);
                $userCode = UserCode::updateOrCreate(
                    ['phone'=> $user->phone],
                    ['code'=> $code]
                );

                return response()->json([
                    'status'=>200,
                    'message'=>'Sms resended successfully'
                ]);
            }
       }
}
