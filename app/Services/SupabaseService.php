namespace App\Services;

use Illuminate\Support\Facades\Http;

class SupabaseService
{
   protected $url;
   protected $bucket_subscription;
   protected $bucket_history;
   
   public function __construct()
   {
       $this->url = env('SUPABASE_URL');
       $this->bucket_subscription = env('SUPABASE_BUCKET_SUBSCRIPTION');
       $this->bucket_history = env('SUPABASE_BUCKET_HISTORY');
   }

   public function uploadImageSubscription($file, $filename){
      $path = "{$this->bucket_subscription}/{$filename}";
      try{
         $response = Http::withHeaders([
               'Content-Type' => 'multipart/form-data', 
         ])->post("{$this->url}/storage/v1/object/public/{$path}", [
               'file' => fopen($file->getPathname(), 'r'),
         ]);

         if ($response->failed()) {
               throw new \Exception('Error uploading file: ' . $response->body());
         }

         return $response->json();
      }
      catch(\Exception $e){
         throw new \Exception($e->getMessage());
      }
   }

   public function getImageSubscription($fileName)
   {
     return "{$this->url}/storage/v1/object/public/{$this->bucket_subscription}/{$fileName}";
   }

   public function uploadImageHistory(){
      $path = "{$this->bucket_history}/{$filename}";
      try{
         $response = Http::withHeaders([
               'Content-Type' => 'multipart/form-data', 
         ])->post("{$this->url}/storage/v1/object/public/{$path}", [
               'file' => fopen($file->getPathname(), 'r'),
         ]);

         if ($response->failed()) {
               throw new \Exception('Error uploading file: ' . $response->body());
         }

         return $response->json();
      }
      catch(\Exception $e){
         throw new \Exception($e->getMessage());
      }
   }

   public function getImageHistory($fileName)
   {
      return "{$this->url}/storage/v1/object/public/{$this->bucket_history}/{$fileName}";
   }
}