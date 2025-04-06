<?php

namespace App\Http\Controllers;

use App\Models\Histories;
use App\Models\HistoryDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Services\SupabaseService;

class HistoriesController extends Controller
{
    // All History
    // public function index()
    // {
    //     try {
    //         $supabase = new SupabaseService();
    //         $history = Histories::all();

    //         if ($history->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Belum ada riwayat'
    //             ], Response::HTTP_NOT_FOUND);
    //         }

    //         foreach ($history as $h) {
    //             $h->url_image = $supabase->getImageHistory($h->image);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Riwayat berhasil ditampilkan',
    //             'data' => $history
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Gagal menampilkan riwayat',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function index()
    {
        try {
            $history = Histories::with('historyDetail')->get();

            if ($history->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Belum ada riwayat'
                ], Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat berhasil ditampilkan',
                'data' => $history
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menampilkan riwayat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // History by Id User
    // public function indexByIdUser(string $idUser)
    // {
    //     try {
    //         $validate = Validator::make(['id' => $idUser], [
    //             'id' => 'required|exists:users,id'
    //         ]);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Gagal memvalidasi data riwayat',
    //                 'error' => $validate->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated = $validate->validated();
    //         $supabase = new SupabaseService();

    //         $history = Histories::where('user_id', $validated['id'])
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         if ($history->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Belum ada riwayat'
    //             ], Response::HTTP_NOT_FOUND);
    //         }


    //         foreach ($history as $h) {
    //             $h->url_image = $supabase->getImageHistory($h->image);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Riwayat berhasil ditampilkan',
    //             'data' => $history
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Gagal menampilkan riwayat',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function indexByIdUser(string $idUser)
    {
        try {
            $validate = Validator::make(['id' => $idUser], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal memvalidasi data riwayat',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();


            $history = Histories::with('historyDetail')->where('user_id', $validated['id'])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($history->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Belum ada riwayat'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat berhasil ditampilkan',
                'data' => $history
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menampilkan riwayat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Create History
    // public function store(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $validate = Validator::make($request->all(), [
    //             'title' => 'required|string|max:100',
    //             'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    //             'description' => 'required|string',
    //             'user_id' => 'required|exists:users,id|string',
    //         ]);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Gagal memvalidasi data riwayat',
    //                 'error' => $validate->errors(),
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated = $validate->validated();
    //         Log::info('Validation passed:', $validated);

    //         $history = new Histories();
    //         $history->title = $validated['title'];

    //         $file = $request->file('image');
    //         $filename = 'history_' . time() . '.' . $file->getClientOriginalExtension();

    //         $supabase = new SupabaseService();
    //         $response = $supabase->uploadImageHistory($file, $filename);

    //         Log::info('Supabase upload response:', ['response' => $response]);

    //         if (isset($response['error'])) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Failed to upload image',
    //                 'error' => $response['error'],
    //             ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //         }

    //         $history->image = $filename;
    //         $history->description = $validated['description'];
    //         $history->user_id = $validated['user_id'];

    //         $history->save();

    //         $history->url_image = $response['url_image'];
    //         Log::info('History saved:', $history->toArray());

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Riwayat berhasil disimpan',
    //             'data' => $history->toArray(),
    //         ], Response::HTTP_CREATED);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error in storing history:', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'message' => 'Riwayat gagal disimpan',
    //             'error' => $e->getMessage(),
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'image' => 'required|string',
                'detection_time' => 'required|string|max:100',
                "history_details" => 'required|array',
                'history_details.*.name_disease' => 'required|string|max:255',
                'history_details.*.another_name_disease' => 'required|string|max:255',
                'history_details.*.symptom' => 'required|string',
                'history_details.*.reason' => 'required|string',
                'history_details.*.preventive_meansure' => 'required|string',
                'history_details.*.source' => 'required|string',
                'history_details.*.confidence_score' => 'required|string|max:100',
                'user_id' => 'required|exists:users,id|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal memvalidasi data riwayat',
                    'error' => $validate->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $history = new Histories();
            $history->detection_time = $validated['detection_time'];
            $history->image = $validated['image'];
            $history->user_id = $validated['user_id'];
            $history->save();

            // loop through each history detail
            foreach ($validated['history_details'] as $detail) {
                $historyDetails = new HistoryDetails();
                $historyDetails->name_disease = $detail['name_disease'];
                $historyDetails->another_name_disease = $detail['another_name_disease'];
                $historyDetails->symptom = $detail['symptom'];
                $historyDetails->reason = $detail['reason'];
                $historyDetails->preventive_measure = $detail['preventive_meansure'];
                $historyDetails->source = $detail['source'];
                $historyDetails->confidence_score = $detail['confidence_score'];
                $historyDetails->history_id = $history->id;
                $historyDetails->save();
            }

            $history->url_image = $history->image;
            $hd = HistoryDetails::where('history_id', $history->id)->get();
            $history->history_details = $hd;

            Log::info('History saved:', $history->toArray());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat berhasil disimpan',
                'data' => $history->toArray(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in storing history:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Riwayat gagal disimpan',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Detail History
    // public function show(string $id)
    // {
    //     try {
    //         $validate = Validator::make(['id' => $id], [
    //             'id' => 'required|exists:histories,id'
    //         ]);

    //         if ($validate->fails()) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Gagal memvalidasi riwayat',
    //                 'error' => $validate->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated = $validate->validated();

    //         $history = Histories::where('id', $validated['id'])->first();

    //         $supabase = new SupabaseService();
    //         $history->url_image = $supabase->getImageHistory($history->image);

    //         if (!$history) {
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Riwayat tidak ditemukan'
    //             ], Response::HTTP_NOT_FOUND);
    //         }

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Data riwayat berhasil ditampilkan',
    //             'data' => $history
    //         ], Response::HTTP_OK);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Gagal menampilkan riwayat',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
    public function show(string $id)
    {
        try {
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:histories,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal memvalidasi riwayat',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $history = Histories::where('id', $validated['id'])->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Data riwayat berhasil ditampilkan',
                'data' => $history
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menampilkan riwayat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete History
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:histories,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal memvalidasi data riwayat',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $history = Histories::where('id', $validated['id'])->first();

            if (!$history) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Riwayat tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }

            $history->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat berhasil dihapus'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus riwayat',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Check how much history data is stored
    public function countHistory(string $idUser)
    {
        try {
            $validate = Validator::make(['id' => $idUser], [
                'id' => 'required|exists:users,id'
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Gagal memvalidasi data riwayat',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $history = Histories::where('user_id', $validated['id'])->count();

            // Jika Id user tidak ditemukan 
            if (!$history) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Riwayat tidak ditemukan',
                    'data' => 0
                ], Response::HTTP_OK);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat berhasil ditampilkan',
                'data' => $history
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menampilkan riwayat',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Update History (Rarely Used)
    // public function update(Request $request, string $id)
    // {
    //     try{
    //         DB::beginTransaction();

    //         $validate = Validator::make(['id' => $id], [
    //             'id' => 'required|exists:history,id'
    //         ]);

    //         if($validate->fails()){
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Failed to validate history data',
    //                 'error' => $validate->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated = $validate->validated();
    //         $history = History::where('id', $validated['id'])->first();

    //         if(!$history){
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'History not found'
    //             ], Response::HTTP_NOT_FOUND);
    //         }

    //         $validate2 = Validator::make($request->all(), [
    //             'title' => 'required|string',
    //             'image' => 'string|nullable',
    //             'description' => 'required|string',
    //             'user_id' => 'required|exists:users,id|string',
    //         ]);

    //         if($validate2->fails()){
    //             return response()->json([
    //                 'status' => 'failed',
    //                 'message' => 'Failed to validate history data',
    //                 'error' => $validate2->errors()
    //             ], Response::HTTP_UNPROCESSABLE_ENTITY);
    //         }

    //         $validated2 = $validate2->validated();
    //         // $validated2['image'] = $validated2['image'] ?? $history->image;

    //         $history->update($validated2);

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'History updated successfully',
    //             'data' => $history
    //         ], Response::HTTP_OK);
    //     }
    //     catch(\Exception $e){
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Failed to update history',
    //             'error' => $e->getMessage()
    //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
}
