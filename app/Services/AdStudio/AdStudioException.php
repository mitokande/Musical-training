<?php

namespace App\Services\AdStudio;

use RuntimeException;

/**
 * Anything the Ad Studio can fail at that an admin should read verbatim:
 * an unfittable script, a missing donor asset, a TTS refusal, a failed render.
 *
 * These messages are surfaced in the panel, so they are written for a human who
 * knows the creative but not the codebase.
 */
class AdStudioException extends RuntimeException {}
