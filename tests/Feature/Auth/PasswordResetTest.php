<?php

/*
 * Password reset is handled by the Filament admin panel; the starter-kit
 * forgot-password and reset-password routes are not loaded.
 */

test('the starter-kit reset password link screen is not served', function () {
    $this->get('/forgot-password')->assertNotFound();
});

test('a reset link cannot be requested through the starter-kit route', function () {
    $this->post('/forgot-password', ['email' => 'user@example.com'])->assertNotFound();
});

test('the starter-kit reset password screen and form are not served', function () {
    $this->get('/reset-password/token')->assertNotFound();
    $this->post('/reset-password', ['token' => 'token'])->assertNotFound();
});

test('Filament admin password reset request screen can be rendered', function () {
    $this->get('/admin/password-reset/request')->assertOk();
});
