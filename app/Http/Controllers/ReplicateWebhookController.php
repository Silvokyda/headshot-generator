<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReplicateWebhookController extends Controller
{
    public function replicateWebhook(Request $request)
    {
        // Parse the JSON data from the request
        $prediction = $request->json()->all();

        Log::info("Replicate API response: " . json_encode($prediction));

        // Check if the prediction has completed
        if ($prediction['status'] === 'successful') {
            
        
            $swappedImageUrl = $prediction['output']; // Assuming 'output' contains the swapped image URL
            
            // Perform any additional processing with the swapped image URL
            // For example, display the swapped image or save it to the database
        }

        // Return a response to acknowledge the webhook
        return response()->json(['message' => 'Webhook received successfully']);
    }
}

