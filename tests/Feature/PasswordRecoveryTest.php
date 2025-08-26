<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecoveryLinkMail;
use App\Models\Radcheck;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that requesting recovery for a non-existent email does not leak user existence.
     */
    public function test_recovery_request_for_non_existent_email_is_handled_gracefully(): void
    {
        Mail::fake();

        $response = $this->post(route('password.request'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info', 'Se um email correspondente for encontrado, um link de recuperação foi enviado. Verifique sua caixa de entrada e spam.');
        $response->assertSessionHasNoErrors();

        Mail::assertNothingSent();
    }

    /**
     * Test that requesting recovery for an existing email sends the recovery link.
     */
    public function test_recovery_request_for_existing_email_sends_link(): void
    {
        Mail::fake();

        // Create a dummy user record in the radcheck table
        Radcheck::factory()->create([
            'username' => 'testuser',
            'email' => 'existing@example.com',
            'attribute' => 'SSHA-Password',
            'op' => ':=',
            'value' => 'somehash',
        ]);

        $response = $this->post(route('password.request'), [
            'email' => 'existing@example.com',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
        $response->assertSessionHasNoErrors();

        Mail::assertSent(RecoveryLinkMail::class, function ($mail) {
            return $mail->hasTo('existing@example.com');
        });
    }
}
