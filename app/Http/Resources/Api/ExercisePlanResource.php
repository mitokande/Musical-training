<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A saved practice plan, as the mobile app sees it.
 *
 * `settings_json` is passed through untouched: nothing server-side has ever
 * read or reshaped it — the website writes whatever its setup screen holds and
 * reads the same bag back — so a client is free to store its own keys there and
 * find them again. Renamed to `settings` here because the column's `_json`
 * suffix is a storage detail, not part of the contract.
 */
class ExercisePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category,
            'exercise_type' => $this->exercise_type,
            'settings' => $this->settings_json ?? [],
            'is_ai_generated' => (bool) $this->is_ai_generated,
            'is_favorite' => (bool) $this->is_favorite,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
