<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Log;

class ContainerController extends Controller
{
    protected FCMNotificationService $fcmService;
    protected $generalController;

    public function __construct(FCMNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
        $this->generalController = new GeneralController();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request, $user_id)
    // {
    //     Log::info("Store container .. file path! " . $request->file('file_path'));
    //     $validated = $request->validate([
    //         'file_name' => 'required|string|max:255',
    //         'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg',
    //         'type' => 'required|integer|in:0,1',
    //         'tracking_number' => 'nullable|string|max:255',
    //     ]);
    //     try {
    //         $user = User::findOrFail($user_id);
    //         $file = $request->file('file_path');
    //         $fileName = $file->getClientOriginalName();
    //         $path = $file->store('containers', options: 'public');
    //         $this->generalController->getPresignedUrl($path);

    //         // Create container associated with the user
    //         $container = $user->containers()->create([
    //             'file_name' => $validated['file_name'],
    //             'file_path' => $path,
    //             'type' => $validated['type'],
    //             'tracking_number' => $validated['tracking_number'],
    //         ]);

    //         Log::info("Container created ..!, " . $container->file_name);
    //         //Send fcm ! 
    //         $user_ids = [$user->id];
    //         $subject = "شحنه جديده";
    //         $message = "عزيزي [اسم الزبون]، تمت إضافة شحنة جديدة ( نوع الشحنة ) إلى حسابك. يمكنك عرض تفاصيل الشحنة من خلال التطبيق.";
    //         $type = $request->type ? 'شحن جزئي' : 'شحن كلي';
    //         $message = str_replace(["[اسم الزبون]", "( نوع الشحنة )"], [$user->name, $type], $message);

    //         $response = $this->fcmService->sendNotification(
    //             $user_ids,
    //             $subject,
    //             $message
    //         );
    //         return response()->json([
    //             'message' => 'Container created successfully.',
    //             'container' => $container,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Container not created ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }


    public function store(Request $request, $user_id)
    {
        Log::info("Store container .. file path! " . $request->file('file_path'));

        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg',
            'type' => 'required|integer|in:0,1',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        try {
            $user = User::findOrFail($user_id);
            $file = $request->file('file_path');

            // Use S3 disk
            $path = $file->store('containers', 's3');

            // Optional: generate presigned URL if needed
            $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(20));

            // Save the path (or URL if you prefer) in DB
            $container = $user->containers()->create([
                'file_name' => $validated['file_name'],
                'file_path' => $path, // Or use $url if you want to save the full presigned link
                'type' => $validated['type'],
                'tracking_number' => $validated['tracking_number'],
            ]);

            Log::info("Container created ..!, " . $container->file_name);

            // Send FCM Notification
            $user_ids = [$user->id];
            $subject = "شحنة جديدة";
            $message = "عزيزي [اسم الزبون]، تمت إضافة شحنة جديدة ( نوع الشحنة ) إلى حسابك. يمكنك عرض تفاصيل الشحنة من خلال التطبيق.";
            $typeText = $request->type ? 'شحن جزئي' : 'شحن كلي';
            $message = str_replace(["[اسم الزبون]", "( نوع الشحنة )"], [$user->name, $typeText], $message);

            $this->fcmService->sendNotification($user_ids, $subject, $message);

            return response()->json([
                'message' => 'Container created successfully.',
                'container' => $container,
                'presigned_url' => $url, // Optional for frontend
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Container not created: ' . $e->getMessage(),
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
