<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $request->session()->put('google_oauth_user_id', $request->user()->id);

        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/drive'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
            ])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $oauthUser = Socialite::driver('google')->stateless()->user();
        $user = $request->user();

        abort_unless($user && (int) $request->session()->pull('google_oauth_user_id') === $user->id, 403);

        $user->update([
            'google_id' => $oauthUser->getId(),
            'google_token' => json_encode([
                'access_token' => $oauthUser->token,
                'refresh_token' => $oauthUser->refreshToken,
                'expires_in' => $oauthUser->expiresIn,
                'created' => now()->timestamp,
            ], JSON_THROW_ON_ERROR),
            'google_refresh_token' => $oauthUser->refreshToken ?: $user->google_refresh_token,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Google Drive berhasil terhubung.']);

        return to_route('profile.edit');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
        ]);

        return back()->with('toast', [
            'type' => 'info',
            'message' => 'Koneksi Google Drive telah diputus.'
        ]);
    }
}
