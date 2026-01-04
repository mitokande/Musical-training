<?php

namespace App\Http\Controllers;

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
                'json_schema' => [
                    'name' => 'interval_direction_practice',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'clef' => [
                                'type' => 'string',
                                'enum' => ['treble', 'alto', 'bass'],
                                'description' => 'The clef for displaying the notes'
                            ],
                            'note1' => [
                                'type' => 'string',
                                'description' => 'The first note (e.g., C, D#, Eb)'
                            ],
                            'note2' => [
                                'type' => 'string',
                                'description' => 'The second note (e.g., C, D#, Eb)'
                            ],
                            'direction' => [
                                'type' => 'string',
                                'enum' => ['ascending', 'descending'],
                                'description' => 'Whether the interval goes up (ascending) or down (descending)'
                            ],
                            'octave' => [
                                'type' => 'string',
                                'enum' => ['2', '3', '4', '5', '6'],
                                'description' => 'The octave number for the notes'
                            ],
                        ],
                        'required' => ['clef', 'note1', 'note2', 'direction', 'octave'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        $totalTokens = $response->usage->toArray()['total_tokens'];
        $costPerThousandTokens = 0.01;
        $cost = ($totalTokens / 1000) * $costPerThousandTokens;
        \Log::info("OpenAI API call cost estimate: $" . number_format($cost, 6) . " for {$totalTokens} tokens.");
        
        return json_decode($response->choices[0]->message->content, true);
    }
}
