<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\NudgeService;
use App\Services\ChatbotService;

class SmartAssistantController extends Controller
{
    /**
     * Get personalized smart nudges for the authenticated user.
     */
    public function getNudges(Request $request)
    {
        $userId = $request->user()->id_user;
        $nudges = NudgeService::getSmartNudges((int) $userId);

        return response()->json([
            'success' => true,
            'data' => $nudges,
        ]);
    }

    /**
     * Process a chatbot message and return a contextual reply.
     */
    public function askChatbot(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = $request->user()->id_user;
        $result = ChatbotService::processMessage((int) $userId, $request->message);

        return response()->json($result);
    }

    /**
     * Reset chatbot conversation state (clear server-side cache).
     */
    public function resetChatbot(Request $request)
    {
        // Clear any session-based conversation memory
        $userId = $request->user()->id_user;

        // If using cache for conversation history
        $cacheKey = 'chatbot_history_' . $userId;
        cache()->forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat obrolan berhasil dihapus.',
        ]);
    }
}
