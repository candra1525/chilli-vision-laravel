<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $user = User::all();
            return response()->json([
                'message' => 'success',
                'data' => $user
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'no_handphone' => 'required|string',
                'password' => 'required|string'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data user',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            $validated = $validator->validated();
            $username = $validated['no_handphone'];
            $password = $validated['password'];
          
            $user = User::where('no_handphone', $username)->first();
            
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            if ($user->deleted_at !== null) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Akun telah dihapus, tidak bisa login'
                ], Response::HTTP_UNAUTHORIZED);
            }
    
    
            if (password_verify($password, $user->password)) {
                $token = $user->createToken('access_token')->plainTextToken;
                $user->token = $token;  
    
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login success',
                    'data' => $user,
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Username atau password salah'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
