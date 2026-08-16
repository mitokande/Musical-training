<?php

namespace Tests\Feature;

use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Jane Smith',
        'email' => 'Jane@Example.com',
        'subject' => 'billing',
        'message' => 'My invoice shows the wrong amount for last month.',
    ];

    public function test_contact_page_form_posts_to_a_real_route(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('action="'.route('contact.submit').'"', false);
    }

    public function test_submission_lands_in_the_support_inbox(): void
    {
        $this->post('/contact', $this->payload)
            ->assertRedirect()
            ->assertSessionHas('contact_status', 'sent');

        $conversation = SupportConversation::sole();

        $this->assertSame('jane@example.com', $conversation->contact_email);
        $this->assertSame('Jane Smith', $conversation->contact_name);
        $this->assertSame('open', $conversation->status);
        $this->assertSame(1, $conversation->message_count);
        $this->assertStringContainsString('Billing', $conversation->subject);

        $message = SupportMessage::sole();
        $this->assertSame('inbound', $message->direction);
        $this->assertSame($this->payload['message'], $message->plain_text_body);
    }

    public function test_validation_errors_are_returned(): void
    {
        $this->from('/contact')
            ->post('/contact', ['name' => '', 'email' => 'nope', 'subject' => 'hacking', 'message' => 'short'])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertSame(0, SupportConversation::count());
    }

    public function test_honeypot_silently_discards_bot_submissions(): void
    {
        $this->post('/contact', $this->payload + ['website' => 'http://spam.example'])
            ->assertSessionHas('contact_status', 'sent');

        $this->assertSame(0, SupportConversation::count());
    }
}
