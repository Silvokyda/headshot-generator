<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary; 

class ImageController extends Controller
{
    public function upload(Request $request)
    {

        $request->validate([
            'image' => 'required|image|max:2048', 
        ]);

        $userId = auth()->id();

        $image = $request->file('image');
        $cloudinaryUpload = Cloudinary::upload($image->getRealPath(), [
            'folder' => 'uploads', 
            'public_id' => $userId . '_' . uniqid(), 
        ]);

        // Create a new image record in the database
        $image = new Image();
        $image->user_id = $userId;
        $image->upload_ref = $cloudinaryUpload->getPublicId();
        $image->image_name = $cloudinaryUpload->getSecurePath();
        $image->save();

        return response()->json(['success' => true, 'image_url' => $cloudinaryUpload->getSecurePath()]);
    }
}
