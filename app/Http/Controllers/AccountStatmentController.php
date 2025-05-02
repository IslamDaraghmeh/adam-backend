<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FCMNotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Log;

class AccountStatmentController extends Controller
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

    /**
     * Store a newly created resource in storage.
     */

    // public function store(Request $request, $user_id)
    // {
    //     //
    //     $validated = $request->validate([
    //         'file_name' => 'required|string|max:255',
    //         'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg|max:2048',
    //         'tracking_number' => 'nullable|string|max:255',
    //     ]);
    //     try {
    //         $user = User::findOrFail($user_id);
    //         $file = $request->file('file_path');
    //         $fileName = $file->getClientOriginalName();
    //         $path = $file->store('account_statments', 'public');
    //         $this->generalController->getPresignedUrl($path);

    //         $user->account_statments()->delete();
    //         $accountStatment = $user->account_statments()->create([
    //             'file_name' => $validated['file_name'],
    //             'file_path' => $path,
    //             'tracking_number' => $validated['tracking_number'],
    //         ]);

    //         Log::info("Account-statment created ..!, " . $accountStatment->file_name);
    //         //Send fcm ! 
    //         $user_ids = [$user->id];
    //         $subject = "اصدار كشف حساب";
    //         $message = "عزيزي [اسم الزبون] ، تم إصدار كشف حساب جديد خاص بك. يمكنك عرض التفاصيل من خلال التطبيق.";
    //         $message = str_replace(["[اسم الزبون]"], [$user->name], $message);

    //         $response = $this->fcmService->sendNotification(
    //             $user_ids,
    //             $subject,
    //             $message
    //         );
    //         return response()->json([
    //             'message' => 'Account-statment created successfully.',
    //             'account_statment' => $accountStatment,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Account-statment not created ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function store(Request $request, $user_id)
    {
        $validated = $request->validate([
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|file|mimes:doc,docx,pdf,xls,xlsx,png,jpg,jpeg,gif,svg|max:2048',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        try {
            $user = User::findOrFail($user_id);

            // رفع الملف إلى S3 داخل مجلد account_statments
            $file = $request->file('file_path');
            $path = $file->store('account_statments', 's3');

            // توليد presigned URL لمدة 20 دقيقة (اختياري)
            $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(20));

            // حذف أي كشف حساب سابق
            $user->account_statments()->delete();

            // إنشاء كشف حساب جديد
            $accountStatment = $user->account_statments()->create([
                'file_name' => $validated['file_name'],
                'file_path' => $path, // يمكن تخزين $url بدلاً من المسار إذا كنت تحتاج الرابط المباشر
                'tracking_number' => $validated['tracking_number'],
            ]);

            Log::info("Account-statment created ..!, " . $accountStatment->file_name);

            // إرسال إشعار FCM
            $user_ids = [$user->id];
            $subject = "اصدار كشف حساب";
            $message = "عزيزي [اسم الزبون] ، تم إصدار كشف حساب جديد خاص بك. يمكنك عرض التفاصيل من خلال التطبيق.";
            $message = str_replace("[اسم الزبون]", $user->name, $message);

            $this->fcmService->sendNotification($user_ids, $subject, $message);

            return response()->json([
                'message' => 'Account-statment created successfully.',
                'account_statment' => $accountStatment,
                'presigned_url' => $url, // إضافي فقط إذا تحتاجه للعرض الفوري
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Account-statment not created: ' . $e->getMessage(),
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
