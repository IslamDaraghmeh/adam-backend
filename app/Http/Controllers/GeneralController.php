<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GeneralController extends Controller
{
    //
    public function index()
    {
        $usersCount = User::where('is_admin', 0)->count();
        $paritalContainersCount = Container::where('type', 1)->count();
        $fullContainersCount = Container::where('type', 0)->count();
        $placesCount = Place::count();
        return response()->json([
            'usersCount' => $usersCount,
            'paritalContainersCount' => $paritalContainersCount,
            'fullContainersCount' => $fullContainersCount,
            'placesCount' => $placesCount,
        ], 200);
    }


    public function getPresignedUrl($path)
    {
        $disk = Storage::disk('s3');

        if (!$disk->exists($path)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $url = $disk->temporaryUrl(
            $path,
            Carbon::now()->addMinutes(15)
        );

        return response()->json(['url' => $url]);
    }
}
