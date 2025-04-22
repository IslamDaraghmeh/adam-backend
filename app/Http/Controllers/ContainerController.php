<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Http\Request;
use Log;

class ContainerController extends Controller
{
    protected FCMNotificationService $fcmService;

    public function __construct(FCMNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $user_id)
    {
        //
        Log::info("Store container .. file path! " . $request->file('file_path'));
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg', // Add file validation
            'type' => 'required|integer|in:0,1',
            'tracking_number' => 'nullable|string|max:255',
        ]);
        try {
            $user = User::findOrFail($user_id);
            $file = $request->file('file_path');
            $fileName = $file->getClientOriginalName();
            $path = $file->store('containers', 'public');

            // Create container associated with the user
            $container = $user->containers()->create([
                'file_name' => $validated['file_name'],
                'file_path' => $path,
                'type' => $validated['type'],
                'tracking_number' => $validated['tracking_number'],
            ]);

            Log::info("Container created ..!, " . $container->file_name);
            //Send fcm ! 
            $user_ids = [$user->id];
            $subject = "شحنه جديده";
            $message = "عزيزي [اسم الزبون]، تمت إضافة شحنة جديدة ( نوع الشحنة ) إلى حسابك. يمكنك عرض تفاصيل الشحنة من خلال التطبيق.";
            $type = $request->type ? 'شحن جزئي' : 'شحن كلي';
            $message = str_replace(["[اسم الزبون]", "( نوع الشحنة )"], [$user->name, $type], $message);

            $response = $this->fcmService->sendNotification(
                $user_ids,
                $subject,
                $message
            );
            return response()->json([
                'message' => 'Container created successfully.',
                'container' => $container,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Container not created ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
