<?php

namespace App\Http\Controllers;

use App\Models\HistorySubscriptions;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;

class HistorySubscriptionsController extends Controller
{
    // List Subs User ID
    public function index(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $supabase = new SupabaseService();
            // Descending berdasarkan created_at
            $hs = HistorySubscriptions::where('user_id', $validated['id'])->orderBy('created_at', 'desc')->get();

            if ($hs->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan Kosong'
                ], Response::HTTP_OK);
            }

            foreach ($hs as $h) {
                $h->image_url = $supabase->getImageHistorySubscription($h->image_transaction);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat langganan berhasil diambil',
                'data' => $hs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Store
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'subscription_id' => 'required|exists:subscriptions,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'status' => 'nullable|in:active,pending,expired,cancel',
                'payment_method' => 'nullable|string',
                'image_transaction' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();
            $hs = new HistorySubscriptions();

            $hs->status = $validated['status'] ?? 'pending';
            $hs->payment_method = $validated['payment_method'] ?? 'transfer';

            $file = $request->file('image_transaction');
            $filename = 'history_subscriptions_' . time() . '_' . $file->getClientOriginalName();

            $supabase = new SupabaseService();
            $response = $supabase->uploadImageHistorySubscription($file, $filename);

            Log::info('Supabase upload response:', ['response' => $response]);

            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to upload image',
                    'error' => $response['error'],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $hs->image_transaction = $filename;
            $hs->user_id = $validated['user_id'];
            $hs->subscription_id = $validated['subscription_id'];
            $hs->start_date = $validated['start_date'];
            $hs->end_date = $validated['end_date'];

            $hs->save();

            $hs->image_url = $response['url_image'];


            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat langganan berhasil ditambahkan',
                'data' => $hs
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Show
    public function show(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history_subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $hs = HistorySubscriptions::find($validated['id'])->first();
            $supabase = new SupabaseService();

            $hs->image_url = $supabase->getImageHistorySubscription($hs->image_transaction);

            if (!$hs) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_OK);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat langganan berhasil diambil',
                'data' => $hs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update
    public function update(Request $request, string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history_subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $hs = HistorySubscriptions::findOrFail($validated['id']);

            if ($hs) {
                $validate = Validator::make($request->all(), [
                    'start_date' => 'nullable|date',
                    'end_date' => 'nullable|date',
                    'status' => 'nullable|in:active,pending,expired,cancel',
                    'payment_method' => 'nullable|string',
                    'image_transaction' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'user_id' => 'nullable|exists:users,id',
                    'subscription_id' => 'nullable|exists:subscriptions,id',
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

                $filename = $hs->image_transaction;
                $response = ['url_image' => $supabase->getImageSubscription($filename)];

                if ($request->hasFile('image_transaction')) {
                    $file = $request->file('image_transaction');
                    $filename = 'history_subscriptions_' . time() . '.' . $file->getClientOriginalExtension();

                    $response = $supabase->uploadImageHistorySubscription($file, $filename);

                    if (isset($response['error'])) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Error uploading image: ' . $response['error']
                        ], Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    $hs->image_transaction = $filename;
                    // $hs->image_url = $response['url_image'];
                }

                $hs->start_date = $validated['start_date'] ?? $hs->start_date;
                $hs->end_date = $validated['end_date'] ?? $hs->end_date;
                $hs->status = $validated['status'] ?? $hs->status;
                $hs->payment_method = $validated['payment_method'] ?? $hs->payment_method;
                $hs->user_id = $validated['user_id'] ?? $hs->user_id;
                $hs->subscription_id = $validated['subscription_id'] ?? $hs->subscription_id;


                $hs->save();

                $hs->image_url = $response['url_image'];

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data riwayat langganan berhasil diperbarui',
                    'data' => $hs
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data riwayat langganan tidak ditemukan'
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

    // Delete
    public function destroy(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history_subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $hs = HistorySubscriptions::find($validated['id'])->first();

            if (!$hs) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_OK);
            }

            $hs->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat langganan berhasil dihapus'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Check Expired atau belum subs nya
    public function checkExpired(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history_subscriptions,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            $hs = HistorySubscriptions::find($validated['id'])->first();

            if (!$hs) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data langganan tidak ditemukan'
                ], Response::HTTP_OK);
            }

            $now = now();
            $end_date = $hs->end_date;

            if ($now > $end_date) {
                $hs->status = 'expired';
                $hs->save();
            }

            $hs->remaining = $now->diffInDays($end_date, false);
            $hs->remaining = intval($hs->remaining);


            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat langganan berhasil diambil',
                'data' => $hs
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
