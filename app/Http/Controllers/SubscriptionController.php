<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;

class SubscriptionController extends Controller
{
    // All Data Subscription
    public function index()
    {
        try{
            // Custom Return Subscription
            $supabase = new SupabaseService();
            $subs = Subscription::all();
            $subs->url_image = $supabase->getSubscriptionImage($subs->image_transaction);

            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'data' => $subs
            ], Response::HTTP_OK);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Buy Subscription
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();

            $validate_subs = Validator::make($request->all(), [
                'title' => 'required|string|max:100',
                'image_transaction' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'user_id' => 'required|exists:users,id'
            ]);

            $validated_subs = $validate_subs->validated();

            if($validate_subs->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => $validate_subs->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $subs = new Subscription();
            $subs->title = $validated_subs['title'];
            $file = $request->file('image_transaction');
            $filename = 'transaction_'.time().'.'.$file->getClientOriginalExtension();
            $supabase = new SupabaseService();
            $response = $supabase->uploadImageSubscription($file, $filename);
            if(isset($response['error'])){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error uploading image: '.$response['error']
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $subs->image_transaction = $filename;
            $subs->start_date = $validated_subs['start_date'];
            $subs->end_date = $validated_subs['end_date'];
            $subs->status = 0;
            $subs->user_id = $validated_subs['user_id'];

            $subs->save();
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully'
            ], Response::HTTP_CREATED);
        }
        catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Show Detail User Subscription
    public function show(string $id)
    {
        try{
            $validate_id = Validator::make(['id' => $id], [
                'id' => 'required|exists:subscription,id'
            ]);

            $validated_id = $validate_id->validated();

            if($validate_id->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => $validate_id->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $subs = Subscription::where('id', $validated_id['id'])->first();
            
            if($subs){
                $supabase = new SupabaseService();
                $subs->url_image = $supabase->getSubscriptionImage($subs->image_transaction);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data retrieved successfully',
                    'data' => $subs
                ], Response::HTTP_OK);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], Response::HTTP_NOT_FOUND);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Update Subscription
    public function update(Request $request, string $id)
    {
        //
    }

    // Update Status Subscription
    public function updateStatus(string $id)
    {
        try{
            $validate_id = Validator::make(['id' => $id], [
                'id' => 'required|exists:subscription,id'
            ]);

            $validated_id = $validate_id->validated();

            if($validate_id->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => $validate_id->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $subs = Subscription::find($validated_id['id']);
            if($subs){
                $subs->status = 1;
                $subs->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data updated successfully'
                ], Response::HTTP_OK);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], Response::HTTP_NOT_FOUND);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // Delete Subscription (Soft Delete)
    public function destroy(string $id)
    {
        try{
            $subs = Subscription::find($id);
            if($subs){
                $subs->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data deleted successfully'
                ], Response::HTTP_OK);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data not found'
                ], Response::HTTP_NOT_FOUND);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
