<?php

/*
 * Sign-in is handled by the Filament admin panel. The application keeps a /login
 * address that sends visitors there, and does not serve the starter-kit login form
 * (routes/auth.php is not loaded in routes/web.php).
 */

test('login address sends visitors to the Filament admin login', function () {
    $response = $this->get('/login');

    $response->assertRedirect(route('filament.admin.auth.login'));
});

test('Filament admin login screen can be rendered', function () {
    $this->get(route('filament.admin.auth.login'))->assertOk();
});

test('the starter-kit login form is not served', function () {
    $this->post('/login', ['email' => 'user@example.com', 'password' => 'password'])
        ->assertStatus(405);
    $this->assertGuest();
});
