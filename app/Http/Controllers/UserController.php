<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;

class UserController extends Controller
{
    // All User
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

    // Detail Account
    public function show(string $id)
    {
        try{
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $user = User::where('id', $validated['id'] )->first();
            if(!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User found',
                'data' => $user
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update Account
    public function update(Request $request, string $id)
    {
        try{
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if(!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $validate2 = Validator::make($request->all(), [
                'fullname' => 'required|string|max:55',
                'email' => 'nullable|email:rfc,dns',
                'no_handphone' => 'nullable|string|max:13',
            ]);

            if($validate2->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate2->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated2 = $validate2->validated();
            $validated2['email'] = $validated2['email'] ?? null;

            $user->update($validated2);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User has been updated',
                'data' => $user
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Remove Account
    public function destroy(string $id)
    {
        try{
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if(!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $user->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User has been deleted'
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Login
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

    // Register 
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
        
            $validator = Validator::make($request->all(), [
                'fullname' => 'required|string|max:55',
                'email' => 'nullable|email:rfc,dns',
                'no_handphone' => 'nullable|string|max:13',
                'password' => 'required|string|max:20',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    "status" => "failed",
                    "message" => "Gagal melakukan validasi tipe data user",
                    "errors" => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

          
        
            $validated = $validator->validated();

            $searchNoHandphone = User::where('no_handphone', $validated['no_handphone'])->first();
            if($searchNoHandphone){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No handphone telah terdaftar sebelumnya'
                ], Response::HTTP_CONFLICT);
            }
            
            $validated['email'] = $validated['email'] ?? null;
            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);
        
            DB::commit();
        
            return response()->json([
                "status" => "success",
                "message" => "Data user berhasil ditambahkan",
                "data" => $user
            ], Response::HTTP_CREATED);
        
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Change Password
    public function changePassword(Request $request, string $id)
    {
        try{
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data user',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if(!$user){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $validate2 = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'password' => 'required|string|max:20',
            ]);

            if($validate2->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate2->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated2 = $validate2->validated();

            if(!password_verify($validated2['old_password'], $user->password)){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Old password is wrong'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->password = bcrypt($validated2['password']);
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Password has been changed'
            ], Response::HTTP_OK);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } 

    // Logout
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Logout success'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
