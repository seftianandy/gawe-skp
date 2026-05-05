<?php

test('terms of service page is publicly accessible', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Legal\\/TermsOfService', false);
});

test('privacy policy page is publicly accessible', function () {
    $this->get(route('privacy-policy'))
        ->assertOk()
        ->assertSee('Legal\\/PrivacyPolicy', false);
});
