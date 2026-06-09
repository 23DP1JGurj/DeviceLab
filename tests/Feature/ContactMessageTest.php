<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_message_and_sends_mail_to_service(): void
    {
        Mail::fake();
        config(['mail.contact_to' => 'devicelab@example.test']);

        $this->postJson('/api/contact', [
            'name' => 'Client User',
            'email' => 'client@example.test',
            'phone' => '+37120000000',
            'message' => 'Need help with repair.',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Client User',
            'email' => 'client@example.test',
            'phone' => '+37120000000',
            'status' => 'new',
        ]);

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('devicelab@example.test');
        });
    }

    public function test_contact_form_rejects_invalid_phone(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', [
            'name' => 'Client User',
            'email' => 'client@example.test',
            'phone' => 'phone123',
            'message' => 'Need help with repair.',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);

        Mail::assertNothingSent();
    }
}
