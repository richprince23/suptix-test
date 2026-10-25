<?php

/*
 * Email verification is not part of the application's own routes: the starter-kit
 * verification routes are not loaded.
 */

test('email verification screen is not served', function () {
    $this->get('/verify-email')->assertNotFound();
});

test('there is no email verification link route', function () {
    expect(Route::has('verification.verify'))->toBeFalse();
});

test('a verification email cannot be requested through the starter-kit route', function () {
    $this->post('/email/verification-notification')->assertNotFound();
});
