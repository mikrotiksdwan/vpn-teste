<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Vite;
use App\Models\Radcheck;
use Tests\TestCase;

class AdminActionsTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Create an admin user
        $this->adminUser = Radcheck::factory()->create([
            'username' => 'testadmin',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create a regular user
        $this->regularUser = Radcheck::factory()->create([
            'username' => 'testuser',
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);
    }

    private function actingAsAdmin()
    {
        return $this->withSession([
            'user_logged_in' => true,
            'is_admin' => true,
            'user_email' => $this->adminUser->email,
        ]);
    }

    private function actingAsUser()
    {
        return $this->withSession([
            'user_logged_in' => true,
            'is_admin' => false,
            'user_email' => $this->regularUser->email,
        ]);
    }

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAsUser()->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_dashboard_and_see_users()
    {
        $response = $this->actingAsAdmin()->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee($this->adminUser->username);
        $response->assertSee($this->regularUser->username);
    }

    public function test_admin_can_invite_a_new_user()
    {
        $response = $this->actingAsAdmin()->post(route('admin.users.store'), [
            'username' => 'newuser',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseHas('radcheck', ['username' => 'newuser', 'email' => 'new@example.com']);
    }

    public function test_admin_can_delete_a_user()
    {
        $response = $this->actingAsAdmin()->delete(route('admin.users.destroy', $this->regularUser->id));
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('radcheck', ['username' => $this->regularUser->username]);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $response = $this->actingAsAdmin()->delete(route('admin.users.destroy', $this->adminUser->id));
        $response->assertSessionHas('error', 'Você não pode excluir sua própria conta.');
        $this->assertDatabaseHas('radcheck', ['username' => $this->adminUser->username]);
    }

    public function test_admin_can_promote_a_user()
    {
        $this->assertFalse((bool)$this->regularUser->is_admin);

        $response = $this->actingAsAdmin()->post(route('admin.users.promote', $this->regularUser->id));
        $response->assertRedirect(route('admin.dashboard'));

        $this->regularUser->refresh();
        $this->assertTrue((bool)$this->regularUser->is_admin);
    }
}
