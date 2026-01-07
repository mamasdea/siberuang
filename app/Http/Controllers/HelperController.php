<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelperController extends Controller
{
    public function showPicture(Request $request)
    {
        $disk = $request->query('disk', 'public'); // Default to public if not specified

        if (Storage::disk($disk)->exists($request->path)) {
            return Storage::disk($disk)->response($request->path);
        }

        return "File tidak ditemukan";
    }
}
