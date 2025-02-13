<?php

namespace App\Http\Controllers;

use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Services\SupabaseService;
use Illuminate\Support\Facades\Log;

class SubscriptionsController extends Controller
{
    // All Data Subscription
    public function index()
    {
        try {
            $supabase = new SupabaseService();
            $subs = Subscriptions::all();

            // Jika $subs kosong
            if ($subs->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada data langganan',
                    'data' => $subs
                ], Response::HTTP_OK);
            }

            foreach ($subs as $s) {
                $s->url_image = $supabase->getImageSubscription($s->image_subscriptions);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data langganan berhasil ditampilkan',
                'data' => $subs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data langganan gagal ditampilkan',
                'error' => 'Error: ' . $e->getMessage()
            ], status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Create Data Subscription
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'title' => 'required|string|max:100',
                'image_subscriptions' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'price' => 'required|integer',
                'description' => 'required|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal validasi data langganan',
                    'error' => $validate->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $subs = new Subscriptions();

            $subs->title = $validated['title'];

            $file = $request->file('image_subscriptions');
            $filename = 'subscriptions_' . time() . '.' . $file->getClientOriginalExtension();

            $supabase = new SupabaseService();
            $response = $supabase->uploadImageSubscription($file, $filename);

            Log::info('Supabase upload response:', ['response' => $response]);

            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to upload image',
                    'error' => $response['error'],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $subs->image_subscriptions = $filename;

            $subs->price = $validated['price'];
            $subs->description = $validated['description'];

            $subs->save();

            $subs->image_url = $response['url_image'];
            Log::info('Subscription saved:', $subs->toArray());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Data langganan berhasil disimpan',
                'data' => $subs->toArray(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Data langganan gagal disimpan',
                'error' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Show Detail Data Subscription
    public function show(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate subscription data',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $subs = Subscriptions::where('id', $validated['id'])->first();
            $supabase = new SupabaseService();
            $subs->url_image = $supabase->getImageSubscription($subs->image_subscriptions);

            if (!$subs) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data langganan berhasil ditampilkan',
                'data' => $subs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Data langganan gagal ditampilkan',
                'error' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update Subscription
    public function update(Request $request, string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $subs = Subscriptions::findOrFail($validated['id']);

            if ($subs) {
                $validate = Validator::make($request->all(), [
                    'title' => 'string|max:100',
                    'image_subscriptions' => 'image|mimes:jpeg,png,jpg|max:2048',
                    'price' => 'integer',
                    'description' => 'string',
                ]);

                if ($validate->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $validate->errors()
                    ], Response::HTTP_BAD_REQUEST);
                }

                $validated = $validate->validated();

                DB::beginTransaction();
                $supabase = new SupabaseService();

                $filename = $subs->image_subscriptions;
                $response = ['url_image' => $supabase->getImageSubscription($filename)];

                if ($request->hasFile('image_subscriptions')) {
                    $file = $request->file('image_subscriptions');
                    $filename = 'subscriptions_' . time() . '.' . $file->getClientOriginalExtension();

                    $response = $supabase->uploadImageSubscription($file, $filename);

                    if (isset($response['error'])) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Error uploading image: ' . $response['error']
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    $subs->image_subscriptions = $filename;
                    $subs->image_url = $response['url_image'];
                }

                $subs->title = $validated['title'] ?? $subs->title;
                $subs->image_subscriptions = $filename;
                $subs->price = $validated['price'] ?? $subs->price;
                $subs->description = $validated['description'] ?? $subs->description;

                $subs->save();

                $subs->image_url = $response['url_image'];

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan berhasil diperbarui',
                    'data' => $subs
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete Subscription (Soft Delete)
    public function destroy(string $id)
    {
        try {
            $subs = Subscriptions::find($id);
            if ($subs) {
                $subs->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan berhasil dihapus',
                    'data' => $subs
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Count Subscriptions
    public function countSubscriptions()
    {
        try {
            $subs = Subscriptions::count();
            return response()->json([
                'status' => 'success',
                'message' => 'Jumlah data langganan',
                'data' => $subs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
