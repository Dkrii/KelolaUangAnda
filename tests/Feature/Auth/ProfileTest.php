<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_guest_cannot_view_profile_page(): void
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'currency' => 'USD',
            'timezone' => 'Asia/Makassar',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('USD', $user->currency);
        $this->assertSame('Asia/Makassar', $user->timezone);
    }

    public function test_user_cannot_update_email_to_another_users_email(): void
    {
        $userA = User::factory()->create(['email' => 'a@example.com']);
        $userB = User::factory()->create(['email' => 'b@example.com']);

        $response = $this->actingAs($userA)->patch('/profile', [
            'name' => 'User A Updated',
            'email' => 'b@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertSame('a@example.com', $userA->fresh()->email);
    }

    public function test_password_can_be_updated_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password123',
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'old-password123',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('new-password123', $user->fresh()->password));
    }

    public function test_password_cannot_be_updated_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'real-password123',
        ]);

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('real-password123', $user->fresh()->password));
    }
}
