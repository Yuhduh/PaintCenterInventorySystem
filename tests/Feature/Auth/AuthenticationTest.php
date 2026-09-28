<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response
        ->assertOk()
        ->assertSeeVolt('pages.auth.login')
        ->assertSee('Grade A Paint Center')
        ->assertSee('POS &amp; Inventory Management System', false)
        ->assertSee('Show password')
        ->assertDontSee('Forgot your password');
});

test('login requires a username and password', function () {
    Volt::test('pages.auth.login')
        ->call('login')
        ->assertHasErrors(['form.username', 'form.password'])
        ->assertSee('Please enter your username.')
        ->assertSee('Please enter your password.');
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'password');

    $component->call('login');

    $component
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $component = Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'wrong-password');

    $component->call('login');

    $component
        ->assertHasErrors()
        ->assertSee('Incorrect username or password.')
        ->assertNoRedirect();

    $this->assertGuest();
});

test('deactivated users cannot authenticate', function () {
    $user = User::factory()->create(['active' => false]);

    Volt::test('pages.auth.login')
        ->set('form.username', $user->username)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasErrors('form.username');

    $this->assertGuest();
});

test('a deactivated authenticated user is signed out on the next request', function () {
    $user = User::factory()->create(['active' => false]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('navigation menu can be rendered', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeVolt('layout.navigation');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Volt::test('layout.navigation');

    $component->call('logout');

    $component
        ->assertHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
});
