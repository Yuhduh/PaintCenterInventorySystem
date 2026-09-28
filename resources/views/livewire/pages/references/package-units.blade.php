<?php
use App\Models\AuditLog;
use App\Models\PackageUnit;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public string $search = '';
    public string $name = '';
    public string $abbreviation = '';
    public ?int $editingId = null;
    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:100'], 'abbreviation' => ['nullable', 'string', 'max:20']]);
        $unit = $this->editingId ? PackageUnit::findOrFail($this->editingId) : new PackageUnit;
        $before = $unit->exists ? ['name' => $unit->name, 'abbreviation' => $unit->abbreviation] : null;
        $unit->name = trim($this->name);
        $unit->abbreviation = $this->abbreviation ?: null;
        $unit->save();
        AuditLog::create(['user_id' => auth()->id(), 'event' => $this->editingId ? 'package_unit_updated' : 'package_unit_created', 'auditable_type' => PackageUnit::class, 'auditable_id' => $unit->id, 'context' => ['before' => $before, 'after' => ['name' => $unit->name, 'abbreviation' => $unit->abbreviation]]]);
        $this->reset(['name', 'abbreviation', 'editingId']);
        session()->flash('status', 'Package unit saved.');
    }
    public function edit(int $id): void
    {
        $unit = PackageUnit::findOrFail($id);
        $this->editingId = $id;
        $this->name = $unit->name;
        $this->abbreviation = $unit->abbreviation ?? '';
    }
    public function toggleActive(int $id): void
    {
        $unit = PackageUnit::findOrFail($id);
        $unit->update(['active' => !$unit->active]);
        AuditLog::create(['user_id' => auth()->id(), 'event' => $unit->active ? 'package_unit_reactivated' : 'package_unit_deactivated', 'auditable_type' => PackageUnit::class, 'auditable_id' => $unit->id, 'context' => ['name' => $unit->name]]);
        session()->flash('status', 'Package unit status updated.');
    }
    public function render(): mixed
    {
        return view('livewire.pages.references.package-units', [
            'packageUnits' => PackageUnit::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(15),
        ]);
    }
}; ?>
<div class="app-page max-w-5xl">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Package units</h1>
        <p class="app-page-description">Manage the approved packaging units used by products.</p></div>
    </div>
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    <form wire:submit="save" class="app-toolbar grid sm:grid-cols-[1fr_180px_auto]">
        <x-text-input wire:model="name" placeholder="Display name" aria-label="Package unit display name" required /><x-text-input wire:model="abbreviation"
            placeholder="Abbreviation" aria-label="Package unit abbreviation" /><x-primary-button>{{ $editingId ? 'Save changes' : 'Add unit' }}</x-primary-button>
    </form>
    <div class="app-panel overflow-x-auto">
        <table class="app-table min-w-full">
            <thead>
                <tr>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Abbreviation</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($packageUnits as $unit)
                    <tr>
                        <td class="px-3 py-2">{{ $unit->name }}</td>
                        <td class="px-3 py-2">{{ $unit->abbreviation }}</td>
                        <td class="px-3 py-2"><span
                                class="{{ $unit->active ? 'app-status-success' : 'app-status-neutral' }}">{{ $unit->active ? 'Active' : 'Deactivated' }}</span>
                        </td>
                        <td class="space-x-3 px-3 py-2"><button wire:click="edit({{ $unit->id }})" type="button"
                                class="app-action">Edit</button><button
                                x-on:click="$dispatch('request-confirmation', {
                                    title: @js($unit->active ? 'Deactivate package unit?' : 'Reactivate package unit?'),
                                    message: @js(($unit->active ? 'Deactivate ' : 'Reactivate ') . $unit->name . '? This affects its availability for products.'),
                                    action: 'toggleActive',
                                    arguments: [{{ $unit->id }}],
                                    confirmText: @js($unit->active ? 'Deactivate' : 'Reactivate'),
                                    tone: @js($unit->active ? 'danger' : 'warning'),
                                })" type="button"
                                class="{{ $unit->active ? 'app-action-danger' : 'app-action text-emerald-700' }}">{{ $unit->active ? 'Deactivate' : 'Reactivate' }}</button>
                        </td>
                </tr>@empty<tr>
                        <td colspan="4" class="app-empty">No package units found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $packageUnits->links() }}</div>
    </div>
    <x-confirmation-modal />
</div>
