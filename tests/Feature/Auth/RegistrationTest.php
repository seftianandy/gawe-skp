<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'nama_instansi' => 'BKD Kota',
        'nama' => 'Test User',
        'nip' => '198901012026051111',
        'jabatan' => 'Analis Kepegawaian',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->user()->name)->toBe('Test User')
        ->and(auth()->user()->nama)->toBe('Test User')
        ->and(auth()->user()->nip)->toBe('198901012026051111');
});
