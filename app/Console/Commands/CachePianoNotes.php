<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CachePianoNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piano:cache-notes 
                            {--duration=1 : Duration of each note in seconds}
                            {--force : Force re-download even if files exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all piano notes from the external API for offline/faster playback';

    /**
     * All notes to cache (4 octaves: C2-B5)
     */
    protected array $notes = [
        // Octave 2 (C2-B2)
        ['note' => 'C', 'octave' => 2, 'isSharp' => false],
        ['note' => 'C', 'octave' => 2, 'isSharp' => true],   // C#
        ['note' => 'D', 'octave' => 2, 'isSharp' => false],
        ['note' => 'D', 'octave' => 2, 'isSharp' => true],   // D#
        ['note' => 'E', 'octave' => 2, 'isSharp' => false],
        ['note' => 'F', 'octave' => 2, 'isSharp' => false],
        ['note' => 'F', 'octave' => 2, 'isSharp' => true],   // F#
        ['note' => 'G', 'octave' => 2, 'isSharp' => false],
        ['note' => 'G', 'octave' => 2, 'isSharp' => true],   // G#
        ['note' => 'A', 'octave' => 2, 'isSharp' => false],
        ['note' => 'A', 'octave' => 2, 'isSharp' => true],   // A#
        ['note' => 'B', 'octave' => 2, 'isSharp' => false],
        // Octave 3 (C3-B3)
        ['note' => 'C', 'octave' => 3, 'isSharp' => false],
        ['note' => 'C', 'octave' => 3, 'isSharp' => true],   // C#
        ['note' => 'D', 'octave' => 3, 'isSharp' => false],
        ['note' => 'D', 'octave' => 3, 'isSharp' => true],   // D#
        ['note' => 'E', 'octave' => 3, 'isSharp' => false],
        ['note' => 'F', 'octave' => 3, 'isSharp' => false],
        ['note' => 'F', 'octave' => 3, 'isSharp' => true],   // F#
        ['note' => 'G', 'octave' => 3, 'isSharp' => false],
        ['note' => 'G', 'octave' => 3, 'isSharp' => true],   // G#
        ['note' => 'A', 'octave' => 3, 'isSharp' => false],
        ['note' => 'A', 'octave' => 3, 'isSharp' => true],   // A#
        ['note' => 'B', 'octave' => 3, 'isSharp' => false],
        // Octave 4 (C4-B4)
        ['note' => 'C', 'octave' => 4, 'isSharp' => false],
        ['note' => 'C', 'octave' => 4, 'isSharp' => true],   // C#
        ['note' => 'D', 'octave' => 4, 'isSharp' => false],
        ['note' => 'D', 'octave' => 4, 'isSharp' => true],   // D#
        ['note' => 'E', 'octave' => 4, 'isSharp' => false],
        ['note' => 'F', 'octave' => 4, 'isSharp' => false],
        ['note' => 'F', 'octave' => 4, 'isSharp' => true],   // F#
        ['note' => 'G', 'octave' => 4, 'isSharp' => false],
        ['note' => 'G', 'octave' => 4, 'isSharp' => true],   // G#
        ['note' => 'A', 'octave' => 4, 'isSharp' => false],
        ['note' => 'A', 'octave' => 4, 'isSharp' => true],   // A#
        ['note' => 'B', 'octave' => 4, 'isSharp' => false],
        // Octave 5 (C5-B5)
        ['note' => 'C', 'octave' => 5, 'isSharp' => false],
        ['note' => 'C', 'octave' => 5, 'isSharp' => true],   // C#
        ['note' => 'D', 'octave' => 5, 'isSharp' => false],
        ['note' => 'D', 'octave' => 5, 'isSharp' => true],   // D#
        ['note' => 'E', 'octave' => 5, 'isSharp' => false],
        ['note' => 'F', 'octave' => 5, 'isSharp' => false],
        ['note' => 'F', 'octave' => 5, 'isSharp' => true],   // F#
        ['note' => 'G', 'octave' => 5, 'isSharp' => false],
        ['note' => 'G', 'octave' => 5, 'isSharp' => true],   // G#
        ['note' => 'A', 'octave' => 5, 'isSharp' => false],
        ['note' => 'A', 'octave' => 5, 'isSharp' => true],   // A#
        ['note' => 'B', 'octave' => 5, 'isSharp' => false],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $duration = $this->option('duration');
        $force = $this->option('force');
        
        $apiBaseUrl = 'https://mithatck.com/music/api/note.php';
        $cachePath = public_path('audio/piano');
        
        // Create cache directory if it doesn't exist
        if (!File::isDirectory($cachePath)) {
            File::makeDirectory($cachePath, 0755, true);
            $this->info("Created cache directory: {$cachePath}");
        }
        
        $this->info("Caching piano notes from API...");
        $this->info("Duration: {$duration}s | Force: " . ($force ? 'Yes' : 'No'));
        $this->newLine();
        
        $progressBar = $this->output->createProgressBar(count($this->notes));
        $progressBar->start();
        
        $cached = 0;
        $skipped = 0;
        $failed = 0;
        
        foreach ($this->notes as $noteData) {
            $note = $noteData['note'];
            $octave = $noteData['octave'];
            $isSharp = $noteData['isSharp'];
            
            // Build note name for filename (use 's' for sharp, e.g., Cs for C#)
            $filenameNote = $isSharp ? "{$note}s" : $note;
            $filename = "{$filenameNote}{$octave}_d{$duration}.mp3";
            $filePath = "{$cachePath}/{$filename}";
            
            // Skip if file exists and not forcing
            if (File::exists($filePath) && !$force) {
                $skipped++;
                $progressBar->advance();
                continue;
            }
            
            // Build API URL (use %23 for #)
            $apiNote = $isSharp ? "{$note}%23" : $note;
            $apiUrl = "{$apiBaseUrl}?note={$apiNote}{$octave}&duration={$duration}";
            
            try {
                $response = Http::timeout(30)->get($apiUrl);
                
                $displayNote = $isSharp ? "{$note}#{$octave}" : "{$note}{$octave}";
                
                if ($response->successful()) {
                    File::put($filePath, $response->body());
                    $cached++;
                } else {
                    $this->newLine();
                    $this->error("Failed to fetch {$displayNote}: HTTP {$response->status()}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error fetching {$displayNote}: {$e->getMessage()}");
                $failed++;
            }
            
            $progressBar->advance();
            
            // Small delay to avoid overwhelming the API
            usleep(100000); // 100ms
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("Cache complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Cached', $cached],
                ['Skipped (existing)', $skipped],
                ['Failed', $failed],
            ]
        );
        
        $this->newLine();
        $this->info("Audio files cached at: {$cachePath}");
        
        if ($cached > 0 || $skipped > 0) {
            $this->newLine();
            $this->comment("To use cached notes, update the piano game to use local files:");
            $this->comment("const apiUrl = `/audio/piano/\${noteName}\${octave}_d1.mp3`;");
        }
        
        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
