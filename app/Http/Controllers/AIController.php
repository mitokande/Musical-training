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
        $questions = json_decode($response->choices[0]->message->content, true);
        \Log::info("OpenAI API call cost estimate: $" . number_format($cost, 6) . " for {$totalTokens} tokens.");
        \Log::info(json_encode($questions));
        
        
        // Store questions in session and redirect to AI practice view
        session(['ai_practice_questions' => $questions['questions']]);
        session(['ai_practice_title' => 'AI Generated Practice']);
        
        return redirect()->route('practice.ai');
    }

    public function generateCoachNotes($data) {
        $questions = $data['questions'] ?? [];
        $answers = $data['answers'] ?? [];
        
        $apikey = env('OPENAI_API_KEY');
        $client = OpenAI::client($apikey);
        
        $response = $client->chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are an encouraging and supportive ear training coach. Analyze practice sessions and provide helpful, personalized feedback to students. Always be positive and constructive.'
                ],
                [
                    'role' => 'user', 
                    'content' => "Analyze this ear training session:
                        Questions: " . json_encode($questions) . "
                        Answers: " . json_encode($answers) . "
                        
                        Provide helpful feedback to the student.
                        1. Summarize their performance.
                        2. Identify strengths.
                        3. Identify weak areas (e.g. specific intervals, directions).
                        4. Suggest what to practice next.
                        
                        Keep it encouraging and concise. Address the student directly."
                ],
            ],
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'coach_feedback',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'summary' => [
                                'type' => 'string',
                                'description' => 'A brief summary of the student performance'
                            ],
                            'score_percentage' => [
                                'type' => 'number',
                                'description' => 'The percentage score (0-100)'
                            ],
                            'strengths' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'List of areas where the student performed well'
                            ],
                            'weak_areas' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'List of areas that need improvement'
                            ],
                            'suggestions' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Specific practice suggestions for improvement'
                            ],
                            'encouragement' => [
                                'type' => 'string',
                                'description' => 'An encouraging message for the student'
                            ]
                        ],
                        'required' => ['summary', 'score_percentage', 'strengths', 'weak_areas', 'suggestions', 'encouragement'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);
        
        $totalTokens = $response->usage->toArray()['total_tokens'];
        $costPerThousandTokens = 0.01;
        $cost = ($totalTokens / 1000) * $costPerThousandTokens;
        $coachNotes = json_decode($response->choices[0]->message->content, true);
        
        \Log::info("OpenAI API call cost estimate: $" . number_format($cost, 6) . " for {$totalTokens} tokens.");
        \Log::info(json_encode($coachNotes));
        
        return $coachNotes;
    }
}
