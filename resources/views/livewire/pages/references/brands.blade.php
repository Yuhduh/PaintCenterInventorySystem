<?php
use App\Models\AuditLog;
use App\Models\Brand;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;
new #[Layout('layouts.app')] class extends Component {
    use WithPagination;
    public string $search = '';
    public string $name = '';
    public ?int $editingId = null;
    public function save(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:100']]);
        $brand = $this->editingId ? Brand::findOrFail($this->editingId) : new Brand;
        $previousName = $brand->exists ? $brand->name : null;
        $brand->name = trim($this->name);
        $brand->save();
        AuditLog::create(['user_id' => auth()->id(), 'event' => $this->editingId ? 'brand_updated' : 'brand_created', 'auditable_type' => Brand::class, 'auditable_id' => $brand->id, 'context' => ['name_before' => $previousName, 'name_after' => $brand->name]]);
        $this->reset(['name', 'editingId']);
        session()->flash('status', 'Brand saved.');
    }
    public function edit(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $id;
        $this->name = $brand->name;
    }
    public function toggleActive(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['active' => !$brand->active]);
        AuditLog::create(['user_id' => auth()->id(), 'event' => $brand->active ? 'brand_reactivated' : 'brand_deactivated', 'auditable_type' => Brand::class, 'auditable_id' => $brand->id, 'context' => ['name' => $brand->name]]);
        session()->flash('status', 'Brand status updated.');
    }
    public function render(): mixed
    {
        return view('livewire.pages.references.brands', [
            'brands' => Brand::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(15),
        ]);
    }
}; ?>
<div class="app-page max-w-5xl">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Brands</h1>
        <p class="app-page-description">Manage product brand reference data.</p></div>
    </div>
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    <form wire:submit="save" class="app-toolbar">
        <x-text-input wire:model="name" placeholder="Brand name" aria-label="Brand name" class="flex-1"
            required /><x-primary-button>{{ $editingId ? 'Save changes' : 'Add brand' }}</x-primary-button>
    </form>
    <div class="app-panel overflow-x-auto">
        <table class="app-table min-w-full">
            <thead>
                <tr>
                    <th class="px-3 py-2">Name</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($brands as $brand)
                    <tr>
                        <td class="px-3 py-2">{{ $brand->name }}</td>
                        <td class="px-3 py-2"><span
                                class="{{ $brand->active ? 'app-status-success' : 'app-status-neutral' }}">{{ $brand->active ? 'Active' : 'Deactivated' }}</span>
                        </td>
                        <td class="space-x-3 px-3 py-2"><button wire:click="edit({{ $brand->id }})" type="button"
                                class="app-action">Edit</button><button
                                x-on:click="$dispatch('request-confirmation', {
                                    title: @js($brand->active ? 'Deactivate brand?' : 'Reactivate brand?'),
                                    message: @js(($brand->active ? 'Deactivate ' : 'Reactivate ') . $brand->name . '? This affects its availability in reference selections.'),
                                    action: 'toggleActive',
                                    arguments: [{{ $brand->id }}],
                                    confirmText: @js($brand->active ? 'Deactivate' : 'Reactivate'),
                                    tone: @js($brand->active ? 'danger' : 'warning'),
                                })" type="button"
                                class="{{ $brand->active ? 'app-action-danger' : 'app-action text-emerald-700' }}">{{ $brand->active ? 'Deactivate' : 'Reactivate' }}</button>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3" class="app-empty">No brands found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $brands->links() }}</div>
    </div>
    <x-confirmation-modal />
</div>
