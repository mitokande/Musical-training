<?php

namespace Tests\Feature;

use App\Livewire\Messenger;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_page_loads(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/messages')->assertOk();
    }

    public function test_user_can_send_a_message(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a);

        Livewire::test(Messenger::class)
            ->set('activeUserId', $b->id)
            ->set('body', 'Hey there')
            ->call('sendMessage')
            ->assertSet('body', '');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $a->id,
            'receiver_id' => $b->id,
            'body' => 'Hey there',
            'type' => 'message',
            'status' => 'unread',
        ]);
    }

    public function test_opening_a_conversation_marks_messages_read(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $msg = Message::create([
            'sender_id' => $a->id,
            'receiver_id' => $b->id,
            'body' => 'Unread one',
            'type' => 'message',
            'status' => 'unread',
        ]);

        $this->actingAs($b);

        Livewire::test(Messenger::class)
            ->call('selectConversation', $a->id);

        $this->assertDatabaseHas('messages', ['id' => $msg->id, 'status' => 'read']);
    }

    public function test_mount_with_to_opens_conversation(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a);

        Livewire::test(Messenger::class, ['to' => $b->username])
            ->assertSet('activeUserId', $b->id);
    }
}
