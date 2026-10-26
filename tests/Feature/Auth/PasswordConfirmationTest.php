<?php

/*
 * The starter-kit confirm-password routes are not loaded.
 */

test('confirm password screen is not served', function () {
    $this->get('/confirm-password')->assertNotFound();
});

test('a password cannot be confirmed through the starter-kit route', function () {
    $this->post('/confirm-password', ['password' => 'password'])->assertNotFound();
});

test('the confirm password route has no name in the application', function () {
    expect(Route::has('password.confirm'))->toBeFalse();
});
