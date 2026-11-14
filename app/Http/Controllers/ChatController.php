<?php

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ChatController
{
    public function index(Request $request): View
    {
        $conversations = Conversation::query()
            ->where('session_id', $request->session()->getId())
            ->latest()
            ->limit(20)
            ->get();

        return view('chat', compact('conversations'));
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:12000'],
            'conversation_id' => ['nullable', 'integer'],
            'mode' => ['required', 'in:chat,write,summarize'],
        ]);

        $sessionId = $request->session()->getId();
        $conversation = isset($validated['conversation_id'])
            ? Conversation::query()->where('session_id', $sessionId)->findOrFail($validated['conversation_id'])
            : Conversation::create([
                'session_id' => $sessionId,
                'title' => mb_substr($validated['message'], 0, 80),
            ]);

        $conversation->messages()->create(['role' => 'user', 'content' => $validated['message']]);

        try {
            $history = $conversation->messages()
                ->latest('id')
                ->limit(20)
                ->get()
                ->reverse()
                ->values()
                ->map(fn ($item) => ['role' => $item->role, 'content' => $item->content]);

            $response = Http::timeout(70)->post(rtrim(config('services.ai.url'), '/').'/chat', [
                'mode' => $validated['mode'],
                'messages' => $history,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => $response->json('detail', 'AI service is unavailable. Check its configuration and try again.'),
                ], 502);
            }

            $answer = $response->json('answer');
            if (!is_string($answer) || $answer === '') {
                return response()->json(['error' => 'The AI service returned an invalid response.'], 502);
            }

            $conversation->messages()->create(['role' => 'assistant', 'content' => $answer]);

            return response()->json(['answer' => $answer, 'conversation_id' => $conversation->id]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['error' => 'Could not reach the AI service. Start it and try again.'], 502);
        }
    }
}
