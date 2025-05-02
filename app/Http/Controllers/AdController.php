<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use Illuminate\Http\Request;
use Log;

class AdController extends Controller
{
    protected $generalController;
    public function __construct()
    {
        $this->generalController = new GeneralController();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $ads = Ad::where('is_published', 1)->get();
        Log::info("All ads fetched ..!");
        return response()->json([
            'ads' => $ads,
        ], 200);
    }
    public function getAllAds()
    {
        $ads = Ad::get();
        Log::info("All ads fetched ..!");
        return response()->json([
            'ads' => $ads,
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image_path' => 'required|file|mimes:png,jpg,jpeg,gif,svg',
            'image_name' => 'required',
        ]);
        try {
            $file = $request->file('image_path');
            $path = $file->store('ads', 'public');
            $this->generalController->getPresignedUrl($path);
            $ad = Ad::create(array_merge($validated, ['image_path' => $path]));
            Log::info("Ad created ..!, " . $ad->title);
            return response()->json([
                'ad' => $ad,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ad not created ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $ad = Ad::findOrFail($id);
            Log::info("Ad fetched ..!, " . $ad->title);
            return response()->json([
                'ad' => $ad,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ad not fetched ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image_path' => 'required|file|mimes:png,jpg,jpeg,gif,svg',
            'image_name' => 'required',
            'is_published' => 'required',
        ]);
        try {
            $ad = Ad::findOrFail($id);
            $file = $request->file('image_path');
            $path = $file->store('ads', 'public');
            $this->generalController->getPresignedUrl($path);

            $ad->update(array_merge($validated, ['image_path' => $path]));
            Log::info("Ad updated ..!, " . $ad->title);
            return response()->json([
                'ad' => $ad,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ad not updated ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $ad = Ad::findOrFail($id);
            $ad->delete();
            Log::info("Ad deleted ..!, " . $ad->title);
            return response()->json([
                "message" => "Resource deleted successfully.",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ad not found',
            ]);
        }
    }
    public function publishAndUnpublish(Request $request, string $id)
    {
        try {
            $ad = Ad::findOrFail($id);
            $ad->update(['is_published' => !$ad->is_published]);
            Log::info("Ad updated ..!, " . $ad->title);
            return response()->json([
                'ad' => $ad,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ad not updated ' . $e->getMessage(),
            ], 500);
        }
    }
}
