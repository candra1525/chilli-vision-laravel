<?php

namespace App\Http\Controllers;

use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;

class HistoryController extends Controller
{
    // All History
    public function index()
    {
        //
    }

    // Save History
    public function store(Request $request)
    {
        //
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

    // listing History by Id User (Need Validation)
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
            $history = History::where('user_id', $validated['id'])->get();

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

    // Update History (Rarely Used)
    public function update(Request $request, string $id)
    {
        //
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
}
