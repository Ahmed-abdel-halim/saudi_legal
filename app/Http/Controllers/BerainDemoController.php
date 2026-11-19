<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppRagService;
use App\Services\AudioTranscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BerainDemoController extends Controller
{
    public function __construct(
        protected WhatsAppRagService        $rag,
        protected AudioTranscriptionService $audioService
    ) {}

    /**
     * عرض صفحة محاكي الواتساب التفاعلي لديمو مياه بيرين (شخصية نادية)
     */
    public function index()
    {
        $botPhone = config('services.twilio.whatsapp_from', env('TWILIO_WHATSAPP_FROM', '+966570079182'));
        $cleanBotPhone = preg_replace('/\D/', '', $botPhone);

        // تحميل بيانات الديمو
        $jsonPath = storage_path('app/demo/berain_complete_structured_database.json');
        if (!file_exists($jsonPath)) {
            $jsonPath = base_path('code_artifact.md');
        }
        $dbData = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath), true) : [];

        $customer = $dbData['customer_profile'] ?? [
            'full_name'     => 'محمد عبدالحميد (Mohammed Abdulhamid)',
            'mobile_number' => '966540506796',
            'city_location' => 'الرياض (حي الورود) / مكة',
        ];

        $lastOrder = $dbData['last_order_summary'] ?? [
            'order_id' => '695818066',
            'amount'   => '48.77',
            'date'     => '2026-05-24 14:36:35',
        ];

        $points = $dbData['loyalty_and_wallet']['current_points'] ?? 58.8;

        return view('demo.whatsapp_berain', compact('cleanBotPhone', 'customer', 'lastOrder', 'points', 'dbData'));
    }

    /**
     * إرسال رسالة واستقبال رد نادية الفوري عبر الذكاء الاصطناعي
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array',
        ]);

        $message = trim($request->input('message'));
        $history = $request->input('history', []);

        try {
            $reply = $this->rag->handleBerainDemoQuery($history, $message);

            return response()->json([
                'success'   => true,
                'reply'     => $reply,
                'timestamp' => now()->format('h:i A'),
            ]);
        } catch (\Exception $e) {
            Log::error('[BerainDemoController] Chat error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'reply'   => 'يا هلا والله أستاذ محمد! 🤍 اعتذر منك حدث ضغط بسيط على السيرفر، جرب ترسل رسالتك مرة ثانية وأنا معك.',
            ], 500);
        }
    }

    /**
     * تفريغ صوتي لمقطع المايكروفون المرسل من محاكي المتصفح
     */
    public function transcribe(Request $request): JsonResponse
    {
        if (!$request->hasFile('audio')) {
            return response()->json(['success' => false, 'error' => 'No audio file provided'], 400);
        }

        $file = $request->file('audio');
        $mime = $file->getMimeType() ?: 'audio/webm';
        $bytes = file_get_contents($file->getRealPath());

        $text = $this->audioService->transcribe($bytes, $mime, $file->getClientOriginalName());

        if (!empty($text)) {
            return response()->json([
                'success' => true,
                'text'    => $text,
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => 'تعذر تفريغ الصوت، يرجى المحاولة بصوت أوضح أو كتابة الرسالة.',
        ], 422);
    }
}
