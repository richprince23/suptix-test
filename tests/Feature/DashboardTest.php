<?php

use App\Models\User;

/*
 * The starter-kit dashboard route is commented out in routes/web.php: signed-in
 * users work in the Filament admin panel.
 */

test('the starter-kit dashboard is not served to guests', function () {
    $this->get('/dashboard')->assertNotFound();
});

test('the starter-kit dashboard is not served to signed-in users', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/dashboard')->assertNotFound();
});
