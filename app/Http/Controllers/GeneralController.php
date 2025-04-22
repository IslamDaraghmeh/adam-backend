<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    //
    public function index(){
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
}
