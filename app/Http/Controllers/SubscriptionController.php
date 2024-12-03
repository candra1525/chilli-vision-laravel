<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as Response;
use App\Services\SupabaseService;

class SubscriptionController extends Controller
{
    // All Data Subscription
    public function index()
    {
        try {
            $supabase = new SupabaseService();
            $subs = Subscription::all(); // Mengambil semua data
    
            // Jika $subs kosong
            if ($subs->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Null',
                ], Response::HTTP_BAD_REQUEST);
            }
    
            // Iterasi setiap data pada koleksi
            foreach ($subs as $s) {
                // Mengakses atribut image_transaction pada setiap item
                $s->url_image = $supabase->getImageSubscription($s->image_transaction);
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data retrieved successfully',
                'data' => $subs
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

    // Buy Subscription
    public function store(Request $request)
    {
        try{
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'title' => 'required|string|max:100',
                'image_transaction' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'user_id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate history data',
                    'error' => $validate->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();


            $subs = new Subscription();
            $subs->title = $validated['title'];

            $file = $request->file('image_transaction');
            $filename = 'transaction_'.time().'.'.$file->getClientOriginalExtension();

         
            $supabase = new SupabaseService();
            $response = $supabase->uploadImageSubscription($file, $filename);

            \Log::info('Supabase upload response:', ['response' => $response['url_image']]);

            if(isset($response['error'])){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error uploading image: '.$response['error']
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $subs->image_transaction = $filename;

            $subs->start_date = $validated['start_date'];
            $subs->end_date = $validated['end_date'];
            $subs->status = 0;
            $subs->user_id = $validated['user_id'];
            
            $subs->save();

            $subs->image_url = $response['url_image'];
            \Log::info('Subscription saved:', $subs->toArray());
            
            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully',
                'data' => $subs->toArray(),
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
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:subscription,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Failed to validate subscription data',
                    'error' => $validate->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validated = $validate->validated();

            $subs = Subscription::where('id', $validated['id'])->first();
            $supabase = new SupabaseService();
            $subs->url_image = $supabase->getImageSubscription($subs->image_transaction);
            
            if(!$subs){
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Subscription not found'
                ], Response::HTTP_NOT_FOUND);
            }
          
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
                    'message' => 'Data updated successfully',
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
    
    // Delete Subscription (Soft Delete)
    public function destroy(string $id)
    {
        try{
            $subs = Subscription::find($id);
            if($subs){
                $subs->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data deleted successfully',
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

    // List Subs User ID
    public function listSubsUser(string $id)
    {
        try{
            $validate = Validator::make(['id' => $id], [
                'id' => 'required|exists:users,id'
            ]);

            if($validate->fails()){
                return response()->json([
                    'status' => 'error',
                    'message' => $validate->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            $validated = $validate->validated();

            // Descending berdasarkan created_at
            $subs = Subscription::where('user_id', $validated['id'])->orderBy('created_at', 'desc')->get();

            if($subs->isEmpty()){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Langganan Kosong'
                ], Response::HTTP_BAD_REQUEST);
            }

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
}
