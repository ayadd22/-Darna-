<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'aya@example.test',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'aya@example.test',
            'password' => 'Password123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'office_id',
                ],
            ])
            ->assertJsonPath('user.id', $user->id);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => $user->getMorphClass(),
            'name' => 'darna-api',
        ]);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'aya@example.test',
            'password' => 'Password123!',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'aya@example.test',
            'password' => 'WrongPassword!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_can_logout_and_revoke_current_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('darna-api');

        $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJson([
                'message' => 'تم تسجيل الخروج بنجاح.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $this->postJson('/api/v1/logout')
            ->assertUnauthorized();
    }

    public function test_protected_property_creation_requires_authentication(): void
    {
        $this->postJson('/api/v1/properties', [])
            ->assertUnauthorized();
    }

    
public function test_user_cannot_login_with_nonexistent_email(): void
{
    $response = $this->postJson('/api/v1/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'Password123!',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
}

public function test_login_requires_email_and_password(): void
{
    $response = $this->postJson('/api/v1/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);

    $this->assertDatabaseCount('personal_access_tokens', 0);
}

}
