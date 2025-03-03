<?php

namespace App\Http\Controllers;

use App\Models\HistorySubscriptions;
use App\Models\User;
use App\Services\SupabaseService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as Response;

class UsersController extends Controller
{
    // All User
    public function index()
    {
        try {
            $user = User::all();
            return response()->json([
                'message' => 'success',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Detail Account
    public function show(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $user = User::where('id', $validated['id'])->first();
            if (!$user) {
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
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update Account
    public function update(Request $request, string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data pengguna',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            DB::beginTransaction();
            $user = User::where('id', $validated['id'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Pengguna tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }

            $validate2 = Validator::make($request->all(), [
                'fullname' => 'string|max:55',
                'no_handphone' => 'string|max:13',
            ]);

            if ($validate2->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data pengguna',
                    'errors' => $validate2->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated2 = $validate2->validated();
            $user->fullname = $validated2['fullname'] ?? $user->fullname;
            $user->no_handphone = $validated2['no_handphone'] ?? $user->no_handphone;

            $user->save();

            $supabase = new SupabaseService();
            $filename = $user->image;
            if ($filename !== null) {
                $response = ['url_image' => $supabase->getImageUser($filename)];
                $user->image_url = $response['url_image'];
            } else {
                $user->image_url = null;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'User has been updated',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // public function updatePhoto(Request $request, string $id)
    // {
    //     try {
    //         $validate = Validator::make(['id' => $id], [
    //             'id' => 'required|exists:users,id'
    //         ]);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Gagal melakukan validasi tipe data pengguna',
    //                 'errors' => $validate->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated = $validate->validated();

    //         DB::beginTransaction();
    //         $user = User::where('id', $validated['id'])->first();

    //         if (!$user) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Pengguna tidak ditemukan'
    //             ], Response::HTTP_NOT_FOUND);
    //         }

    //         $validate2 = Validator::make($request->all(), [
    //             'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
    //         ]);

    //         if ($validate2->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Gagal melakukan validasi tipe data pengguna',
    //                 'errors' => $validate2->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $supabase = new SupabaseService();
    //         $filename = $user->image;
    //         // $response = ['url_image' => $supabase->getImageUser($filename)];

    //         // image
    //         if ($request->hasFile('image')) {
    //             // Hapus Image yang lama berdasarkan Id
    //             if ($filename != null) {
    //                 $supabase->deleteImageUser($filename);
    //             }
    //             $file = $request->file('image');
    //             $filename = 'users_' . time() . '.' . $file->getClientOriginalExtension();

    //             $response = $supabase->uploadImageUser($file, $filename);

    //             if (isset($response['error'])) {
    //                 return response()->json([
    //                     'status' => 'error',
    //                     'message' => 'Error uploading image: ' . $response['error']
    //                 ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //             }

    //             $user->image = $filename;
    //             $user->save();
    //             $response = ['url_image' => $supabase->getImageUser($user->image)];
    //         }

    //         $user->url_image = $response['url_image'];

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Foto profil berhasil diubah',
    //             'data' => $user
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function updatePhoto(Request $request, string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data pengguna',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            DB::beginTransaction();
            $user = User::where('id', $validated['id'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Pengguna tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }

            $validate2 = Validator::make($request->all(), [
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validate2->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data pengguna',
                    'errors' => $validate2->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $supabase = new SupabaseService();
            $filenameLama = $user->image;
            $filenameBaru = $filenameLama; // Default, jika tidak ada gambar baru

            // Upload gambar baru dulu
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filenameBaru = 'users_' . time() . '.' . $file->getClientOriginalExtension();

                $response = $supabase->uploadImageUser($file, $filenameBaru);

                // Jika gagal upload, jangan ubah foto lama
                if (isset($response['error'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error uploading image: ' . $response['error']
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                // Jika berhasil, baru update nama file di database
                $user->image = $filenameBaru;
                $user->save();

                // Jika ada foto lama, baru hapus
                if ($filenameLama != null) {
                    $supabase->deleteImageUser($filenameLama);
                }
            }

            // Ambil URL gambar yang baru
            $user->url_image = $supabase->getImageUser($user->image);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Foto profil berhasil diubah',
                'data' => $user
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if (!$user) {
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
        } catch (\Exception $e) {
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

            $user = User::with([
                'history_subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->latest('created_at')
                        ->limit(1);
                }
            ])->where('no_handphone', $username)->first();

            if ($user && $user->history_subscriptions->isNotEmpty()) {
                $latestHistory = $user->history_subscriptions->first();

                $currentDate = new DateTime();
                $endDate = new DateTime($latestHistory->end_date);
                $interval = $endDate->diff($currentDate);

                if ($interval->invert == 0) { // `invert == 0` artinya `end_date` sudah lewat
                    $findHistorySubs = HistorySubscriptions::where('id', $latestHistory->id)->first();
                    if ($findHistorySubs) {
                        $findHistorySubs->status = 'expired';
                        $findHistorySubs->save();
                    }

                    $user = User::with([
                        'history_subscriptions' => function ($query) {
                            $query->where('status', 'active')
                                ->latest('created_at')
                                ->limit(1);
                        }
                    ])->where('no_handphone', $username)->first();
                }
            }

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

                $supabase = new SupabaseService();
                $filename = $user->image;
                if ($filename !== null) {
                    $response = ['url_image' => $supabase->getImageUser($filename)];
                    $user->image_url = $response['url_image'];
                } else {
                    $user->image_url = null;
                }

                if ($user->history_subscriptions->isNotEmpty() && $user->history_subscriptions[0]->image_transaction !== null) {
                    $user->history_subscriptions[0]->image_url = $supabase->getImageHistorySubscription($user->history_subscriptions[0]->image_transaction);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login success',
                    'data' => array_merge($user->toArray(), [
                        'history_subscriptions' => $user->history_subscriptions->first() ?? null
                    ]),
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
                'no_handphone' => 'nullable|string|max:13',
                'password' => 'required|string|max:20|min:8',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
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
            if ($searchNoHandphone) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'No handphone telah terdaftar sebelumnya'
                ], Response::HTTP_CONFLICT);
            }
            $validated['id'] = Str::uuid()->toString();
            $validated['fullname'] = $validated['fullname'] ?? null;
            $validated['image'] = null;
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
        try {
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal melakukan validasi tipe data user',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $validate2 = Validator::make($request->all(), [
                'old_password' => 'required|string',
                'password' => 'required|string|max:20',
            ]);

            if ($validate2->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate2->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated2 = $validate2->validated();

            if (!password_verify($validated2['old_password'], $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Kata sandi lama tidak sesuai'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user->password = bcrypt($validated2['password']);
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kata sandi berhasil diubah',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Count User
    public function countUser(Request $request)
    {
        try {
            $user = User::count();
            return response()->json([
                'status' => 'success',
                'message' => 'Count user success',
                'data' => $user,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
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
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not authenticated'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Hapus hanya token yang sedang digunakan
            $user->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    // Delete Image User
    public function deletePhoto(string $id)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'errors' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $user = User::where('id', $validated['id'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $supabase = new SupabaseService();
            $filename = $user->image;
            $response = $supabase->deleteImageUser($filename);

            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error deleting image: ' . (is_array($response) ? $response['error'] : 'Unknown error')
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $user->image = null;
            $user->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Gambar berhasil dihapus',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
