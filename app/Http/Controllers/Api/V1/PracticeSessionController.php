<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PracticeSessionResource;
use App\Models\PracticeSession;
use App\Services\Practice\PracticeSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PracticeSessionController extends Controller
{
    public function __construct(private readonly PracticeSessionService $sessions) {}

    public function index(Request $request): JsonResponse
    {
        $query = PracticeSession::forUser($request->user()->id)->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json([
            'data' => PracticeSessionResource::collection($query->limit(20)->get()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', 'in:studio,learning_path'],
            'practice_type' => ['required_if:source,studio', 'nullable', 'string', 'max:60'],
            'exercise_slug' => ['required_if:source,learning_path', 'nullable', 'string', 'max:120'],
            'question_count' => ['required', 'integer', 'min:1', 'max:20'],
            'config' => ['sometimes', 'array'],
        ]);

        $user = $request->user();
        $requested = (int) $validated['question_count'];

        $session = $validated['source'] === 'learning_path'
            ? $this->sessions->createLearningPathSession($user, $validated['exercise_slug'], $requested)
            : $this->sessions->createStudioSession(
                $user,
                $validated['practice_type'],
                $requested,
                $validated['config'] ?? [],
            );

        return response()->json([
            'data' => [
                'session' => new PracticeSessionResource($session),
                'questions' => $this->sessions->questions($session),
            ],
            'meta' => [
                // The free plan caps run length; the app surfaces this.
                'clamped' => $session->question_count < $requested,
                'requested_question_count' => $requested,
            ],
        ], 201);
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        $session = $this->find($request, $uuid);

        return response()->json([
            'data' => [
                'session' => new PracticeSessionResource($session),
                'questions' => $this->sessions->questions($session),
                'answers' => $this->sessions->review($session),
            ],
        ]);
    }

    public function answer(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'answer' => ['required', 'string', 'max:1000'],
            'elapsed_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $session = $this->find($request, $uuid);

        $result = $this->sessions->submitAnswer(
            $session,
            (int) $validated['index'],
            $validated['answer'],
            $validated['elapsed_ms'] ?? null,
        );

        return response()->json([
            'data' => $result + ['session' => new PracticeSessionResource($session->refresh())],
        ]);
    }

    public function complete(Request $request, string $uuid): JsonResponse
    {
        $session = $this->sessions->complete($this->find($request, $uuid));

        return response()->json([
            'data' => [
                'session' => new PracticeSessionResource($session),
                'review' => $this->sessions->review($session),
            ],
        ]);
    }

    public function abandon(Request $request, string $uuid): JsonResponse
    {
        $session = $this->sessions->complete(
            $this->find($request, $uuid),
            PracticeSession::STATUS_ABANDONED,
        );

        return response()->json(['data' => new PracticeSessionResource($session)]);
    }

    /**
     * Scoped to the owner so another user's uuid 404s rather than 403s — the
     * existence of a session is not leaked.
     */
    private function find(Request $request, string $uuid): PracticeSession
    {
        $session = PracticeSession::forUser($request->user()->id)->where('uuid', $uuid)->first();

        if (! $session) {
            throw ApiException::notFound(__('Session not found.'));
        }

        return $session;
    }
}
