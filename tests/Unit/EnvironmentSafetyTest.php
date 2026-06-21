<?php

namespace Tests\Unit;

use Tests\TestCase;

class EnvironmentSafetyTest extends TestCase
{
    public function test_suite_uses_in_memory_sqlite_and_no_production_services(): void
    {
        $this->assertTrue(app()->environment('testing'), 'Tests must run in the testing environment');
        $this->assertSame('sqlite', config('database.default'), 'Tests must never use the production MySQL connection');
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('sync', config('queue.default'));
        $this->assertSame('array', config('mail.default'));
        $this->assertEmpty(config('openai.api_key'), 'Tests must not call the real OpenAI API');
    }
}
