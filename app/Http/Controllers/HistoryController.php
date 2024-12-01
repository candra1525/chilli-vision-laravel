<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Services\SupabaseService;

class HistoryController extends Controller
{
    // All History
    public function index()
    {
        try{
            $supabase = new SupabaseService();
            $history = History::all();
            foreach($history as $h){
                $h->url_image = $supabase->getImageHistory($h->image);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully get all history',
                'data' => $history
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to get history',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'title' => 'required|string|max:100',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'description' => 'required|string',
                'user_id' => 'required|exists:users,id|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate history data',
                    'error' => $validate->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $validated = $validate->validated();
            \Log::info('Validation passed:', $validated);

            $history = new History();
            $history->title = $validated['title'];

            $file = $request->file('image');
            $filename = 'history_' . time() . '.' . $file->getClientOriginalExtension();

            $supabase = new SupabaseService();
            $response = $supabase->uploadImageHistory($file, $filename);

            \Log::info('Supabase upload response:', ['response' => $response]);

            if (isset($response['error'])) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to upload image',
                    'error' => $response['error'],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $history->image = $filename;
            $history->description = $validated['description'];
            $history->user_id = $validated['user_id'];

            $history->save();

            $history->url_image = $response['url_image'];
            \Log::info('History saved:', $history->toArray());

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'History saved successfully',
                'data' => $history->toArray(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in storing history:', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to save history',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    // Show History by Id User
    public function show(string $id)
    {
        try{
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate history data',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $history = History::where('id', $validated['id'])->first();
            $supabase = new SupabaseService();  
            $history->url_image = $supabase->getImageHistory($history->image);
            
            if(!$history){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'History data by id',
                'data' => $history
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to get history',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // listing History by Id User
    public function showHistoryUserById(string $idUser)
    {
        try{
            $validate = Validator::make(['id' => $idUser], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate user data',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $supabase = new SupabaseService();

            $history = History::where('user_id', $validated['id'])->get();

            foreach($history as $h){
                $h->url_image = $supabase->getImageHistory  ($h->image);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'History data by user id',
                'data' => $history
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to get history',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Delete History
    public function destroy(string $id)
    {
        try{
            DB::beginTransaction();
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:history,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate history data',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();
            $history = History::where('id', $validated['id'])->first();

            if(!$history){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'History not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $history->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'History deleted successfully'
            ], Response::HTTP_OK);

        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete history',
                'error' => $e->getMessage()
            ], 500);
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
