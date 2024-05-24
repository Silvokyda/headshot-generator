<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Image;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ImageController extends Controller
{

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $userId = auth()->id();

        if (Session::has('upload_ref')) {
            $uploadRef = Session::get('upload_ref');
            $imageUrl = Cloudinary::getImage($uploadRef)->getSecurePath();
            return response()->json(['success' => true, 'image_url' => $imageUrl]);
        }

        $image = $request->file('image');
        $cloudinaryUpload = Cloudinary::upload($image->getRealPath(), [
            'folder' => 'uploads',
            'public_id' => $userId . '_' . uniqid(),
        ]);

        $imageModel = new Image();
        $imageModel->user_id = $userId;
        $imageModel->upload_ref = $cloudinaryUpload->getPublicId();
        $imageModel->image_name = $cloudinaryUpload->getSecurePath();
        $imageModel->save();

        Session::put('image_name', $cloudinaryUpload->getSecurePath());

        return response()->json(['success' => true, 'image_url' => $cloudinaryUpload->getSecurePath()]);
    }


    public function OpenAIgenerateHeadshot(Request $request)
    {
        // Validate the request data
        $request->validate([
            'gender' => 'required|string|max:255',
            'age' => 'required|integer',
            'ethnicity' => 'required|string|max:255',
            'hair_color' => 'required|string|max:255',
            'hair_length' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        // Collect data from the form
        $data = $request->only(['gender', 'age', 'ethnicity', 'hair_color', 'hair_length']);

        // Generate prompt for Stable Diffusion XL API
        $prompt = "Generate a professional headshot of a {$data['age']} year old {$data['gender']} {$data['ethnicity']} person with {$data['hair_color']} hair and {$data['hair_length']} hair length. The person should be dressed in professional attire and have a confident and friendly expression.";

        // Handle image upload
        $imagePath = $request->file('image')->store('uploads', 'public');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_IMAGE_API_KEY'),
        ])->post('https://api.openai.com/v1/images/generate', [
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
        ]);

        // Check if the request was successful
        if ($response->successful()) {
            $imageUrl = $response->json()['data'][0]['url'];

            // Store image information in the database
            Image::create([
                'user_id' => auth()->id(),
                'image_path' => $imageUrl,
                'upload_reference' => $imagePath,
            ]);

            // Redirect back with the generated image URL
            return back()->with('success', 'Image generated successfully.')->with('image_url', $imageUrl);
        } else {
            // Handle errors
            return back()->with('error', 'Failed to generate image.');
        }
    }

    public function generateHeadshot(Request $request)
    {
        $request->validate([
            'gender' => 'required|string|max:255',
            'age' => 'required|integer',
            'ethnicity' => 'required|string|max:255',
            'hair_color' => 'required|string|max:255',
            'hair_length' => 'required|string|max:255',
        ]);

        // Collect data from the form
        $data = $request->only(['gender', 'age', 'ethnicity', 'hair_color', 'hair_length']);

        // Prepare the prompts for the API requests
        $prompt1 = "a professional headshot of a {$data['age']} year old {$data['gender']} with {$data['hair_length']} {$data['hair_color']} hair and {$data['ethnicity']} ethnicity, wearing professional clothes, in a studio setup with a slightly blurred background, as if taken by a professional photographer";
        $prompt2 = "a professional headshot of a {$data['age']} year old {$data['gender']} with {$data['hair_length']} {$data['hair_color']} hair and {$data['ethnicity']} ethnicity, dressed in formal attire, in a studio environment with a softly blurred background, photographed by a professional";

        // Function to generate and upload an image
        function generateAndUploadImage($prompt, $headers)
        {
            $url = "https://api.stability.ai/v2beta/stable-image/generate/core";
            $postData = [
                'prompt' => $prompt,
                'output_format' => 'jpeg',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                $tempImagePath = tempnam(sys_get_temp_dir(), 'headshot_') . '.jpeg';
                file_put_contents($tempImagePath, $response);

                $userId = auth()->user()->id;
                $cloudinaryUpload = Cloudinary::upload($tempImagePath, [
                    'folder' => "generated/{$userId}",
                    'public_id' => $userId . '_' . uniqid(),
                ]);

                unlink($tempImagePath);

                return $cloudinaryUpload->getSecurePath();
            } else {
                $errorResponse = json_decode($response, true);
                Log::error("API error response: " . $response);
                return null;
            }
        }

        $headers = [
            'Authorization: Bearer ' . env('STABILITY_API_KEY'),
            'Accept: image/*'
        ];

        $imageUrl1 = generateAndUploadImage($prompt1, $headers);
        $imageUrl2 = generateAndUploadImage($prompt2, $headers);

        // $imageUrl1 = 'https://res.cloudinary.com/duwy7nk8w/image/upload/v1716583648/generated/1/1_6650fcdd40a3c.jpg';
        // $imageUrl2 = 'https://res.cloudinary.com/duwy7nk8w/image/upload/v1716583629/generated/1/1_6650fcca1a2f8.jpg';

        if ($imageUrl1 && $imageUrl2) {
            return back()->with('success', 'Images generated successfully.')->with('image_url1', $imageUrl1)->with('image_url2', $imageUrl2);
        } else {
            return back()->with('error', 'Failed to generate images.');
        }
    }


    public function faceSwap(Request $request)
    {
        $replicateApiToken = env('REPLICATE_API_TOKEN');

        $swapImageUrl = $request->swap_image_url;
        $targetImageUrl = $request->target_image_url;

        // Make a POST request to the Replicate API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $replicateApiToken,
            'Content-Type' => 'application/json',
        ])->post('https://api.replicate.com/v1/predictions', [
            'version' => 'c2d783366e8d32e6e82c40682fab6b4c23b9c6eff2692c0cf7585fc16c238cfe',
            'input' => [
                'swap_image' => $swapImageUrl,
                'target_image' => $targetImageUrl,
            ],
        ]);

        Log::info("Replicate API response: " . $response->body());

        // Extract the prediction ID from the response
        $predictionId = $response->json('id');

        $maxAttempts = 100;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            // Get the prediction status
            $statusResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $replicateApiToken,
                'Content-Type' => 'application/json',
            ])->get('https://api.replicate.com/v1/predictions/' . $predictionId);

            $status = $statusResponse->json('status');

            if ($status === 'succeeded') {
                // Extract the swapped image URL from the prediction data
                $swappedImageUrl = $statusResponse->json('output');
                return response()->json(['swapped_image_url' => $swappedImageUrl]);
            } elseif ($status === 'failed') {
                // Handle prediction failure
                return response()->json(['error' => 'Prediction failed'], 500);
            }

            usleep(500000); 
            $attempts++;
        }

        return response()->json(['error' => 'Prediction did not complete within the expected time'], 500);
    }
}
