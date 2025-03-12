<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
      protected $url;
      protected $key;
      protected $bucket_subscription;
      protected $bucket_history;
      protected $bucket_user;
      protected $bucket_history_subscription;

      public function __construct()
      {
            $this->url = env('SUPABASE_URL');
            $this->bucket_subscription = env('SUPABASE_BUCKET_SUBSCRIPTION');
            $this->bucket_history = env('SUPABASE_BUCKET_HISTORY');
            $this->bucket_user = env('SUPABASE_BUCKET_USER');
            $this->bucket_history_subscription = env('SUPABASE_BUCKET_HISTORY_SUBSCRIPTION');
            $this->key = env('SUPABASE_API_KEY');
      }

      // Subscriptions
      public function uploadImageSubscription($file, $filename)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_subscription}/{$filename}";

            Log::info('Path:', ['path' => $path]);


            try {
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $filename
                  )->post("{$baseUrl}/storage/v1/object/{$path}");

                  Log::info('Supabase Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error uploading file: ' . $response->body());
                  }
                  $image = "{$baseUrl}/storage/v1/object/{$path}";
                  return ['url_image' => $image];
            } catch (\Exception $e) {
                  Log::error('Error uploading to Supabase:', ['message' => $e->getMessage()]);
                  throw new \Exception($e->getMessage());
            }
      }

      public function getImageSubscription($fileName)
      {
            return "{$this->url}/storage/v1/object/public/{$this->bucket_subscription}/{$fileName}";
      }

      public function deleteImageSubscription($fileName)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_subscription}/{$fileName}";

            try {
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  // Kirim permintaan DELETE ke Supabase
                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->delete("{$baseUrl}/storage/v1/object/{$path}");

                  Log::info('Supabase Delete Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error deleting file: ' . $response->body());
                  }

                  return ['status' => 'success', 'message' => 'File deleted successfully'];
            } catch (\Exception $e) {
                  Log::error('Error deleting from Supabase:', ['message' => $e->getMessage()]);
                  return ['status' => 'error', 'message' => $e->getMessage()];
            }
      }


      // User
      public function uploadImageUser($file, $filename)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_user}/{$filename}";

            Log::info('Path:', ['path' => $path]);


            try {
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $filename
                  )->post("{$baseUrl}/storage/v1/object/{$path}");

                  Log::info('Supabase Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error uploading file: ' . $response->body());
                  }
                  $image = "{$baseUrl}/storage/v1/object/{$path}";
                  return ['url_image' => $image];
            } catch (\Exception $e) {
                  Log::error('Error uploading to Supabase:', ['message' => $e->getMessage()]);
                  throw new \Exception($e->getMessage());
            }
      }

      public function getImageUser($fileName)
      {
            return "{$this->url}/storage/v1/object/public/{$this->bucket_user}/{$fileName}";
      }

      // History Subscriptions
      public function uploadImageHistorySubscription($file, $filename)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_history_subscription}/{$filename}";

            Log::info('Path:', ['path' => $path]);


            try {
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $filename
                  )->post("{$baseUrl}/storage/v1/object/{$path}");

                  Log::info('Supabase Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error uploading file: ' . $response->body());
                  }
                  $image = "{$baseUrl}/storage/v1/object/{$path}";
                  return ['url_image' => $image];
            } catch (\Exception $e) {
                  Log::error('Error uploading to Supabase:', ['message' => $e->getMessage()]);
                  throw new \Exception($e->getMessage());
            }
      }

      public function getImageHistorySubscription($fileName)
      {
            return "{$this->url}/storage/v1/object/public/{$this->bucket_history_subscription}/{$fileName}";
      }

      // History
      public function uploadImageHistory($file, $filename)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_history}/{$filename}";

            try {
                  // Pastikan base URL sudah diatur
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $filename
                  )->post("{$baseUrl}/storage/v1/object/{$path}");

                  // Log respons dari Supabase
                  Log::info('Supabase Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error uploading file: ' . $response->body());
                  }

                  $image = "{$baseUrl}/storage/v1/object/{$path}";
                  return ['url_image' => $image];
            } catch (\Exception $e) {
                  Log::error('Error uploading to Supabase:', ['message' => $e->getMessage()]);
                  throw new \Exception($e->getMessage());
            }
      }

      public function getImageHistory($fileName)
      {
            return "{$this->url}/storage/v1/object/public/{$this->bucket_history}/{$fileName}";
      }

      // Delete Image User
      public function deleteImageUser($fileName)
      {
            $baseUrl = $this->url;
            $path = "{$this->bucket_user}/{$fileName}";

            try {
                  // Pastikan base URL sudah diatur
                  if (!$baseUrl) {
                        throw new \Exception('Base URL for Supabase is not configured.');
                  }

                  $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->key,
                  ])->delete("{$baseUrl}/storage/v1/object/{$path}");

                  // Log respons dari Supabase
                  Log::info('Supabase Response:', ['response' => $response->body()]);

                  if ($response->failed()) {
                        throw new \Exception('Error deleting file: ' . $response->body());
                  }

                  return true;
            } catch (\Exception $e) {
                  Log::error('Error deleting to Supabase:', ['message' => $e->getMessage()]);
                  throw new \Exception($e->getMessage());
            }
      }
}
