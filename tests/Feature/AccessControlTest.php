<?php

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Volt\Volt;

test('mixers can use sales but cannot manage catalog inventory or reports', function () {
    $mixer = User::factory()->create(['role' => 'mixer']);

    $this->actingAs($mixer)->get('/sales')->assertOk();
    $this->actingAs($mixer)->get('/products')->assertForbidden();
    $this->actingAs($mixer)->get('/inventory/stock-in')->assertForbidden();
    $this->actingAs($mixer)->get('/reports/inventory')->assertForbidden();
});

test('managers can manage operational screens but not administration', function () {
    $manager = User::factory()->create(['role' => 'manager']);

    $this->actingAs($manager)->get('/products')->assertOk();
    $this->actingAs($manager)->get('/inventory/stock-in')->assertOk();
    $this->actingAs($manager)->get('/reports/inventory')->assertOk();
    $this->actingAs($manager)->get('/user-access')->assertForbidden();
});

test('administrators cannot change their own role or status', function () {
    $administrator = User::factory()->create(['role' => 'admin']);

    Volt::actingAs($administrator)
        ->test('pages.user-access.index')
        ->call('toggleActive', $administrator->id)
        ->assertHasErrors('access')
        ->call('changeRole', $administrator->id, 'manager')
        ->assertHasErrors('access');

    expect($administrator->fresh()->active)->toBeTrue()
        ->and($administrator->fresh()->role)->toBe('admin');
});

test('the final active administrator cannot be deactivated', function () {
    $manager = User::factory()->create(['role' => 'manager']);
    $administrator = User::factory()->create(['role' => 'admin']);

    Volt::actingAs($manager)
        ->test('pages.user-access.index')
        ->call('toggleActive', $administrator->id)
        ->assertHasErrors('access');

    expect($administrator->fresh()->active)->toBeTrue();
});

test('user access changes are audited', function () {
    $administrator = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'mixer']);

    Volt::actingAs($administrator)
        ->test('pages.user-access.index')
        ->call('changeRole', $user->id, 'manager')
        ->assertHasNoErrors()
        ->call('toggleActive', $user->id)
        ->assertHasNoErrors();

    expect(AuditLog::query()->where('auditable_type', User::class)->where('auditable_id', $user->id)->where('event', 'user_role_changed')->exists())->toBeTrue()
        ->and(AuditLog::query()->where('auditable_type', User::class)->where('auditable_id', $user->id)->where('event', 'user_deactivated')->exists())->toBeTrue();
});
