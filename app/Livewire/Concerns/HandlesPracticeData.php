<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Converts Eloquent models to plain PHP arrays for Livewire state storage.
 * Livewire's ModelSynth cannot serialize unsaved models (LP-mode questions);
 * storing as arrays avoids that problem while keeping the render() model API intact.
 */
trait HandlesPracticeData
{
    public array $practiceDataArray = [];

    protected function serializePractices($practices): array
    {
        return collect($practices)
            ->map(fn($p) => $this->serializeOnePractice($p))
            ->values()
            ->toArray();
    }

    protected function serializeOnePractice($practice): array
    {
        if (!($practice instanceof Model)) {
            return is_array($practice) ? $practice : (array) $practice;
        }

        $attrs = $practice->getAttributes();
        $lpId  = $practice->id;

        foreach ($attrs as $key => $value) {
            if (is_string($value) && strlen($value) > 0 && $value[0] === '[') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $attrs[$key] = $decoded;
                }
            }
        }

        // Preserve note2_octave even when set as a plain property (unsaved generated models)
        if (!isset($attrs['note2_octave']) && isset($practice->note2_octave)) {
            $attrs['note2_octave'] = $practice->note2_octave;
        }

        $attrs['_lp_id'] = $lpId;

        return $attrs;
    }

    protected function buildModelFromData(string $class, ?array $data): ?object
    {
        if ($data === null) return null;

        $model = new $class();
        foreach ($data as $key => $value) {
            if ($key === '_lp_id') {
                $model->id = $value;
            } else {
                $model->{$key} = $value;
            }
        }
        return $model;
    }

    protected function getCurrentPracticeData(): ?array
    {
        return $this->practiceDataArray[$this->currentPracticeIndex] ?? null;
    }
}
