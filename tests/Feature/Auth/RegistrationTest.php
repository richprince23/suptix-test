<?php

/*
 * Registration is handled by the Filament admin panel; /register only forwards there.
 */

test('register address sends visitors to the Filament admin registration', function () {
    $this->get('/register')->assertRedirect(route('filament.admin.auth.register'));
});

test('Filament admin registration screen can be rendered', function () {
    $this->get(route('filament.admin.auth.register'))->assertOk();
});

test('the starter-kit registration form is not served', function () {
    $this->post('/register', ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password'])
        ->assertStatus(405);
    $this->assertGuest();
});
