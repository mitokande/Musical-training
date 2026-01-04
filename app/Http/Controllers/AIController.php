<?php

namespace App\Http\Controllers;

use App\Models\IntervalDirectionPractice;
use App\Models\Practice;
use App\Models\SingleNotePractice;
use Illuminate\Http\Request;
use OpenAI;

class AIController extends Controller
{

    public function generateIntervalDirectionPractice() {
        $apikey = env('OPENAI_API_KEY');
        $client = OpenAI::client($apikey);
        
        $response = $client->chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are a music theory expert that generates interval direction practice questions. Generate two different notes where a student must identify if the interval is ascending or descending.'
                ],
                [
                    'role' => 'user', 
                    'content' => 'Generate an interval direction practice question with two notes.'
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => IntervalDirectionPractice::schema(),
            ],
        ]);

        $totalTokens = $response->usage->toArray()['total_tokens'];
        $costPerThousandTokens = 0.01;
        $cost = ($totalTokens / 1000) * $costPerThousandTokens;
        \Log::info("OpenAI API call cost estimate: $" . number_format($cost, 6) . " for {$totalTokens} tokens.");
        \Log::info(json_encode($response));
        return json_decode($response->choices[0]->message->content, true);
    }


    public function generatePractices(Request $request) {
        $data = $request->all();
        $practiceTypes = $data['exercise_types'];
        $practiceTypes = Practice::whereIn('id', $practiceTypes)->get();
        // $practiceTypes = $practiceTypes->pluck('name')->toArray();
        $practices = [];
        // dd($practiceTypes);
        $practiceClasses = [
            'single-note-practice' => SingleNotePractice::class,
            'interval-direction-practice' => IntervalDirectionPractice::class,
        ];
        foreach ($practiceTypes as $practiceType) {
            $practiceClass = $practiceClasses[$practiceType->slug];
            $practice = $practiceClass::schema();
            $practices[] = $practice;
        }

        $practiceNames = $practiceTypes->pluck('name')->toArray();
        $apikey = env('OPENAI_API_KEY');
        $client = OpenAI::client($apikey);
        $response = $client->chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are a music theory expert that generates practice questions. Generate ' . implode(', ', $practiceNames) . ' practice questions.'
                ],
                [
                    'role' => 'user', 
                    'content' => 'Generate ' . $data['num_questions'] . ' ' . $data['difficulty'] . ' of type ' . implode(', ', $practiceNames) . ' questions.'
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'practice_questions',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'questions' => [
                                'type' => 'array',
                                'items' => [
                                    'anyOf' => $practices,
                                ],
                            ],
                        ],
                        'required' => ['questions'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);
        // dd($response);
        $totalTokens = $response->usage->toArray()['total_tokens'];
        $costPerThousandTokens = 0.01;
        $cost = ($totalTokens / 1000) * $costPerThousandTokens;
        \Log::info("OpenAI API call cost estimate: $" . number_format($cost, 6) . " for {$totalTokens} tokens.");
        \Log::info(json_encode($response));
        return json_decode($response->choices[0]->message->content, true);
    }
}
