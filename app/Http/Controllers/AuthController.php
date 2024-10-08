<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Mail\AccountMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function handle_register(RegisterRequest $request){
        // return response()->json($request->all());
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        
        if($request->hasFile('avatar')){
            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);
            $user->avatar = $filename;
        }
        
        $user->save();
        
        Mail::to($request->email)->send(new AccountMail($request->name));

        return response()->json([
            'message' => 'Inscription reussie!',
            'user' => $user,
        ], 201);
        

    }
    public function handle_login(Request $request){
        $user = User::where('email', $request->email)->first();
        if($user && Hash::check($request->password, $user->password)){
            return response()->json([
                'message' => 'Connexion reussie!',
                'user' => $user,
            ], 200);
        }else{
            return response()->json([
                'message' => 'Identifiants invalides!',
            ], 401);
        }
    }
    public function delete_user($id){
        $user = User::find($id);
        if($user){
            $user->delete();
            return response()->json([
               'message' => 'User deleted successfully',
                'user' => $user,
            ], 200);
        }
        return response()->json([
           'message' => 'User not found',
        ], 404);
    }
    public function List_user(){
        try {
            $users = User::select('id', 'name', 'email', 'avatar')->get();
            return response()->json($users->map(function ($user) {
                if ($user->avatar) {
                    $user->avatar = asset('uploads/' . $user->avatar);
                }
                return $user;
            }));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch users'], 500);
        }
   
    }
    public function Current_user($id) {
        try {
            $user = User::find($id);
            
            if ($user) {
                if ($user->avatar) {
                    $user->avatar = asset('uploads/' . $user->avatar);
                }
                return response()->json($user, 200);
            } else {
                return response()->json([
                    'message' => 'User not found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the user details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
