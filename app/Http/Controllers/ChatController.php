<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class ChatController
{
    public function index(): View
    {
        $conversations = Conversation::query()->latest()->limit(20)->get();
        return view('chat', compact('conversations'));
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:12000'],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'mode' => ['required', 'in:chat,write,summarize'],
        ]);

        $conversation = isset($validated['conversation_id'])
            ? Conversation::findOrFail($validated['conversation_id'])
            : Conversation::create(['title' => mb_substr($validated['message'], 0, 80)]);
        $conversation->messages()->create(['role' => 'user', 'content' => $validated['message']]);

        try {
            $response = Http::timeout(70)->post(rtrim(config('services.ai.url', env('AI_SERVICE_URL', 'http://127.0.0.1:8000')), '/').'/chat', [
                'mode' => $validated['mode'],
                'messages' => $conversation->messages()->latest('id')->limit(20)->get()->reverse()->values()->map(fn ($item) => ['role' => $item->role, 'content' => $item->content]),
            ]);
            if (!$response->successful()) {
                return response()->json(['error' => $response->json('detail', 'AI service is unavailable. Check its configuration and try again.')], 502);
            }
            $answer = (string) $response->json('answer');
            $conversation->messages()->create(['role' => 'assistant', 'content' => $answer]);
            return response()->json(['answer' => $answer, 'conversation_id' => $conversation->id]);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['error' => 'Could not reach the AI service. Start the Python service and try again.'], 502);
        }
    }
}
