<?php

namespace App\Services\Meetings;

use RuntimeException;

/**
 * Every licensed Zoom host is already running a lesson in this window.
 *
 * Not an error the teacher caused: the caller catches it, leaves the
 * appointment on the manual provider, and tells the teacher to supply their own
 * link. Frequent occurrences are the signal to buy another Zoom licence.
 */
class HostPoolExhausted extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No Zoom host licence is free for this lesson window.');
    }
}
