<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

test('authenticated user is redirected to google oauth', function () {
    Socialite::fake('google');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('google.redirect'))
        ->assertRedirect();
});

test('google callback stores drive tokens on current user', function () {
    $user = User::factory()->create();

    $socialiteUser = (new SocialiteUser())->map([
        'id' => 'google-user-123',
        'name' => 'Drive User',
        'email' => 'drive@example.com',
    ])->setToken('access-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    Socialite::fake('google', $socialiteUser);

    $this->actingAs($user)
        ->withSession(['google_oauth_user_id' => $user->id])
        ->get(route('google.callback'))
        ->assertRedirect(route('profile.edit'));

    $updatedUser = $user->fresh();

    expect($updatedUser->google_id)->toBe('google-user-123')
        ->and($updatedUser->google_refresh_token)->toBe('refresh-token')
        ->and($updatedUser->google_token)->not->toBeNull();
});

test('authenticated user can disconnect google drive', function () {
    $user = User::factory()->create([
        'google_id' => 'google-user-123',
        'google_refresh_token' => 'refresh-token',
        'google_token' => json_encode([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
        ], JSON_THROW_ON_ERROR),
    ]);

    $this->actingAs($user)
        ->post(route('google.disconnect'))
        ->assertRedirect();

    $updatedUser = $user->fresh();

    expect($updatedUser->google_id)->toBeNull()
        ->and($updatedUser->google_refresh_token)->toBeNull()
        ->and($updatedUser->google_token)->toBeNull();
});
