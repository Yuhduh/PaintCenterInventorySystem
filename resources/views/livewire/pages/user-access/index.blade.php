<?php
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public string $search = '';
    public function toggleActive(int $id): void
    {
        if ($id === (int) auth()->id()) {
            throw ValidationException::withMessages(['access' => 'You cannot change your own account status.']);
        }

        DB::transaction(function () use ($id): void {
            $user = User::query()->lockForUpdate()->findOrFail($id);
            if ($user->active && $user->hasRole(['dev', 'admin']) && $this->activeAdministratorCount() <= 1) {
                throw ValidationException::withMessages(['access' => 'At least one active developer or administrator is required.']);
            }

            $previous = $user->active;
            $user->update(['active' => ! $user->active]);
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => $user->active ? 'user_reactivated' : 'user_deactivated',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'context' => ['active_before' => $previous, 'active_after' => $user->active],
            ]);
        });

        session()->flash('status', 'User access updated.');
    }
    public function changeRole(int $id, string $role): void
    {
        if (! in_array($role, ['dev', 'admin', 'manager', 'mixer'], true)) {
            throw ValidationException::withMessages(['access' => 'The selected role is invalid.']);
        }
        if ($id === (int) auth()->id()) {
            throw ValidationException::withMessages(['access' => 'You cannot change your own role.']);
        }

        DB::transaction(function () use ($id, $role): void {
            $user = User::query()->lockForUpdate()->findOrFail($id);
            if ($user->active && $user->hasRole(['dev', 'admin']) && ! in_array($role, ['dev', 'admin'], true) && $this->activeAdministratorCount() <= 1) {
                throw ValidationException::withMessages(['access' => 'At least one active developer or administrator is required.']);
            }

            $previous = $user->role;
            $user->update(['role' => $role]);
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'user_role_changed',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'context' => ['role_before' => $previous, 'role_after' => $role],
            ]);
        });

        session()->flash('status', 'User role updated.');
    }
    private function activeAdministratorCount(): int
    {
        return User::query()
            ->where('active', true)
            ->whereIn('role', ['dev', 'admin'])
            ->lockForUpdate()
            ->get(['id'])
            ->count();
    }
    public function render(): mixed
    {
        return view('livewire.pages.user-access.index', ['users' => User::where(fn($query) => $query->where('name', 'like', '%' . $this->search . '%')->orWhere('username', 'like', '%' . $this->search . '%'))->latest()->paginate(20)]);
    }
}; ?>
<div class="app-page max-w-6xl">
    <div class="app-page-header">
        <div><h1 class="app-page-title">User management</h1>
        <p class="app-page-description">Manage access state and application roles without exposing passwords.</p></div>
    </div>
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    @error('access')
        <div class="rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-800">{{ $message }}</div>
    @enderror
    <div class="app-toolbar"><input wire:model.live.debounce.300ms="search" type="search" placeholder="Search users" aria-label="Search users"
        class="app-input w-full max-w-sm"></div>
    <div class="app-panel overflow-x-auto">
        <table class="app-table min-w-full">
            <thead>
                <tr>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Username</th>
                    <th class="px-3 py-2">Role</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                    <tr>
                        <td class="px-3 py-2">{{ $user->name }}</td>
                        <td class="px-3 py-2">{{ $user->username }}</td>
                        <td class="px-3 py-2"><select aria-label="Role for {{ $user->name }}"
                                x-on:change="
                                    const selectedRole = $event.target.value;
                                    $event.target.value = @js($user->role);
                                    $dispatch('request-confirmation', {
                                        title: 'Change user role?',
                                        message: @js('Change ' . $user->name . ' from ' . ucfirst($user->role) . ' to ') + selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1) + '? Their permissions will change immediately.',
                                        action: 'changeRole',
                                        arguments: [{{ $user->id }}, selectedRole],
                                        confirmText: 'Change role',
                                        tone: 'warning',
                                    });
                                "
                                class="app-select text-sm">
                                <option value="dev" @selected($user->role === 'dev')>Dev</option>
                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                <option value="manager" @selected($user->role === 'manager')>Manager</option>
                                <option value="mixer" @selected($user->role === 'mixer')>Mixer</option>
                            </select></td>
                        <td class="px-3 py-2"><span
                                class="{{ $user->active ? 'app-status-success' : 'app-status-neutral' }}">{{ $user->active ? 'Active' : 'Deactivated' }}</span>
                        </td>
                        <td class="px-3 py-2"><button
                                x-on:click="$dispatch('request-confirmation', {
                                    title: @js($user->active ? 'Deactivate user?' : 'Reactivate user?'),
                                    message: @js(($user->active ? 'Deactivate ' : 'Reactivate ') . $user->name . '? Their account access status will change immediately.'),
                                    action: 'toggleActive',
                                    arguments: [{{ $user->id }}],
                                    confirmText: @js($user->active ? 'Deactivate' : 'Reactivate'),
                                    tone: @js($user->active ? 'danger' : 'warning'),
                                })" type="button"
                                class="{{ $user->active ? 'app-action-danger' : 'app-action text-emerald-700' }}">{{ $user->active ? 'Deactivate' : 'Reactivate' }}</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $users->links() }}</div>
    </div>
    <x-confirmation-modal />
</div>
