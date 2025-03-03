<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $notifications = Notification::where('status', 'publish')
                ->where('publish_date', '<=', date('Y-m-d'))
                ->get();

            if ($notifications->isEmpty()) {
                return response()->json([
                    'message' => 'Data notifikasi tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Success to get data',
                'data' => $notifications
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to get data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'description' => 'required|string',
                'publish_date' => 'required|date',
                'status' => 'required|in:publish,unpublish'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'error' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validator->validated();

            $validated['id'] = Str::uuid()->toString();
            $notification = Notification::create($validated);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil menyimpan data notifikasi',
                'data' => $notification
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to save data',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $notification = Notification::findOrFail($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Data notifikasi tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Berhasil mendapatkan data notifikasi',
                'data' => $notification
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mendapatkan data notifikasi',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $notification = Notification::findOrFail($id);

            if (!$notification) {
                return response()->json([
                    'message' => 'Data notifikasi tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }


            $validator = Validator::make($request->all(), [
                'title' => 'string',
                'description' => 'string',
                'publish_date' => 'date',
                'status' => 'in:publish,unpublish|default:publish'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'error' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validator->validated();
            $notification->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Berhasil memperbarui data notifikasi',
                'data' => $notification
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui data notifikasi',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $notification = Notification::findOrFail($id);
            if (!$notification) {
                return response()->json([
                    'message' => 'Data notifikasi tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }
            $notification->delete();
            DB::commit();
            return response()->json([
                'message' => 'Berhasil menghapus data notifikasi'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus data notifikasi',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
