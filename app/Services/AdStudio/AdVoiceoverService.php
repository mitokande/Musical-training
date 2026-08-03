<?php

namespace App\Services\AdStudio;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Narration for an ad creative, via Google Gemini TTS.
 *
 * This is a port of the hand-authored scripts/gemini-tts.mjs, moved into PHP for
 * one reason: the studio needs the MEASURED duration of every take. Frame
 * windows are cut to those numbers, which is what makes a 30s target actually
 * land on 30s when the copy changes. Guessing from word count would drift by
 * seconds across ten lines.
 *
 * Takes are cached on disk by a hash of (text, voice, direction, model), so
 * editing one line re-synthesizes one line. That also keeps a creative's timings
 * stable: a re-build with unchanged copy reuses the exact takes the frames were
 * planned against, and the API is nondeterministic.
 */
class AdVoiceoverService
{
    private const CACHE_DIR = 'ad-studio/voice';

    /**
     * Synthesize every line, returning the manifest the timing planner consumes.
     *
     * @param  array<string, string>  $lines  line key => spoken text
     * @return array<string, array{text: string, voice: string, hash: string, path: string, seconds: float}>
     */
    public function synthesizeAll(array $lines, string $voice, string $direction): array
    {
        $this->assertUsable();

        $manifest = [];

        foreach ($lines as $key => $text) {
            $text = trim((string) $text);

            if ($text === '') {
                throw new AdStudioException("Script line [$key] is empty — every line needs spoken text or the frame has no window to cut to.");
            }

            $manifest[$key] = $this->synthesizeLine($key, $text, $voice, $direction);
        }

        return $manifest;
    }

    /**
     * One line. Returns the cached take when the text, voice and direction are
     * unchanged — the API is billed and nondeterministic, so re-rolling a take
     * nobody asked to change is both wasteful and a source of timing drift.
     */
    public function synthesizeLine(string $key, string $text, string $voice, string $direction): array
    {
        $hash = $this->hash($text, $voice, $direction);
        $trimmed = $this->cachePath("$hash.wav");

        if (! is_file($trimmed)) {
            $raw = $this->cachePath("$hash.raw.wav");

            if (! is_file($raw)) {
                $this->ensureCacheDir();
                file_put_contents($raw, $this->wrapPcm($this->request($this->spoken($text), $voice, $direction)));
            }

            $this->trimSilence($raw, $trimmed);
        }

        $seconds = $this->duration($trimmed);

        if ($seconds <= 0.05) {
            throw new AdStudioException("Gemini returned a silent take for line [$key]. Re-word the line and try again.");
        }

        return [
            'text' => $text,
            'voice' => $voice,
            'hash' => $hash,
            'path' => $trimmed,
            'seconds' => $seconds,
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('ad_studio.tts.key'));
    }

    /**
     * Fail loudly and early rather than half-way through a ten-line run: a
     * missing binary here means every duration would be a guess.
     */
    public function assertUsable(): void
    {
        if (! $this->isConfigured()) {
            throw new AdStudioException('No GEMINI_API_KEY is configured, so the studio cannot generate narration.');
        }

        foreach (['ffmpeg', 'ffprobe'] as $binary) {
            $path = config("ad_studio.$binary");

            if (! Process::run([$path, '-version'])->successful()) {
                throw new AdStudioException("[$path] is not runnable on this server. Narration cannot be measured without it, and frame windows are cut to those measurements.");
            }
        }
    }

    /**
     * Gemini's TTS preview endpoint returns sporadic 500s. Retry before giving
     * up so one flaky call does not abort a ten-line run that already spent its
     * earlier calls.
     */
    private function request(string $text, string $voice, string $direction): string
    {
        $model = config('ad_studio.tts.model');
        $attempts = max(1, (int) config('ad_studio.tts.retries'));

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->requestOnce($text, $voice, $direction, $model);
            } catch (AdStudioException $e) {
                if ($attempt === $attempts) {
                    throw $e;
                }

                Log::warning('Ad Studio TTS retry', ['attempt' => $attempt, 'error' => $e->getMessage()]);
                usleep(2000 * 1000 * $attempt);
            }
        }

        throw new AdStudioException('Gemini TTS did not return audio.');
    }

    private function requestOnce(string $text, string $voice, string $direction, string $model): string
    {
        $response = Http::withHeaders(['x-goog-api-key' => config('ad_studio.tts.key')])
            ->timeout((int) config('ad_studio.tts.timeout'))
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                // Gemini takes delivery direction as plain prose in the prompt;
                // there is no separate style parameter on this endpoint.
                'contents' => [['parts' => [['text' => rtrim($direction, ': ').' Say only the quoted line: "'.$text.'"']]]],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO'],
                    'speechConfig' => ['voiceConfig' => ['prebuiltVoiceConfig' => ['voiceName' => $voice]]],
                ],
            ]);

        if (! $response->successful()) {
            throw new AdStudioException('Gemini TTS '.$response->status().': '.Str::limit($response->body(), 300));
        }

        $data = data_get($response->json(), 'candidates.0.content.parts.0.inlineData.data');

        if (! is_string($data) || $data === '') {
            throw new AdStudioException('Gemini TTS returned no audio for this line.');
        }

        return base64_decode($data, true) ?: throw new AdStudioException('Gemini TTS returned audio that could not be decoded.');
    }

    /**
     * Gemini returns raw signed-16-bit little-endian mono PCM, not a container,
     * so every response needs a WAV header bolted on before ffmpeg will read it.
     */
    private function wrapPcm(string $pcm): string
    {
        $rate = (int) config('ad_studio.tts.sample_rate');
        $bytes = strlen($pcm);

        return 'RIFF'
            .pack('V', 36 + $bytes)
            .'WAVEfmt '
            .pack('V', 16)      // fmt chunk size
            .pack('v', 1)       // PCM
            .pack('v', 1)       // mono
            .pack('V', $rate)
            .pack('V', $rate * 2) // byte rate
            .pack('v', 2)       // block align
            .pack('v', 16)      // bits per sample
            .'data'
            .pack('V', $bytes)
            .$pcm;
    }

    /**
     * Gemini pads each clip with leading and trailing silence of unpredictable
     * length. Trimming it is what makes the measured duration usable as a timing
     * number — frame windows are cut to these values, so stray padding would
     * render as dead air in the middle of a fast cut.
     */
    private function trimSilence(string $source, string $destination): void
    {
        $gate = 'silenceremove=start_periods=1:start_duration=0:start_threshold=-45dB:detection=peak';

        $result = Process::timeout(120)->run([
            config('ad_studio.ffmpeg'),
            '-y', '-loglevel', 'error',
            '-i', $source,
            '-af', "$gate,areverse,$gate,areverse",
            $destination,
        ]);

        if (! $result->successful() || ! is_file($destination)) {
            throw new AdStudioException('ffmpeg could not trim the narration take: '.Str::limit(trim($result->errorOutput()), 300));
        }
    }

    private function duration(string $file): float
    {
        $result = Process::timeout(60)->run([
            config('ad_studio.ffprobe'),
            '-v', 'error',
            '-show_entries', 'format=duration',
            '-of', 'default=nw=1:nk=1',
            $file,
        ]);

        if (! $result->successful()) {
            throw new AdStudioException('ffprobe could not measure the narration take: '.Str::limit(trim($result->errorOutput()), 300));
        }

        return round((float) trim($result->output()), 3);
    }

    /**
     * Pronunciation respellings are applied to the SYNTHESIS PROMPT ONLY. The
     * stored script keeps the real spelling, so the panel stays readable and the
     * generated SCRIPT.md is the human-facing source of truth.
     */
    private function spoken(string $text): string
    {
        foreach ((array) config('ad_studio.tts.say_as', []) as $written => $said) {
            $text = preg_replace('/\b'.preg_quote($written, '/').'\b/', $said, $text);
        }

        return $text;
    }

    private function hash(string $text, string $voice, string $direction): string
    {
        return substr(hash('sha256', implode('|', [
            config('ad_studio.tts.model'),
            $voice,
            $direction,
            $text,
        ])), 0, 32);
    }

    private function cachePath(string $file): string
    {
        return storage_path('app/'.self::CACHE_DIR.'/'.$file);
    }

    private function ensureCacheDir(): void
    {
        $dir = storage_path('app/'.self::CACHE_DIR);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}
