<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PracticeSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'source' => $this->source,
            'practice_type' => $this->practice_type,
            'exercise' => $this->whenLoaded('exercise', fn () => [
                'slug' => $this->exercise->slug,
                'title' => $this->exercise->getLocalizedTitle(),
            ]),
            'question_count' => $this->question_count,
            'current_index' => $this->current_index,
            'answered_count' => $this->answered_count,
            'correct_count' => $this->correct_count,
            'score' => $this->score,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
