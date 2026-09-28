<?php
use App\Models\AuditLog;
use App\Models\Category;
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
        $category = $this->editingId ? Category::findOrFail($this->editingId) : new Category;
        $previousName = $category->exists ? $category->name : null;
        $category->name = trim($this->name);
        $category->save();
        AuditLog::create(['user_id' => auth()->id(), 'event' => $this->editingId ? 'category_updated' : 'category_created', 'auditable_type' => Category::class, 'auditable_id' => $category->id, 'context' => ['name_before' => $previousName, 'name_after' => $category->name]]);
        $this->reset(['name', 'editingId']);
        session()->flash('status', 'Category saved.');
    }
    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $id;
        $this->name = $category->name;
    }
    public function toggleActive(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['active' => !$category->active]);
        AuditLog::create(['user_id' => auth()->id(), 'event' => $category->active ? 'category_reactivated' : 'category_deactivated', 'auditable_type' => Category::class, 'auditable_id' => $category->id, 'context' => ['name' => $category->name]]);
        session()->flash('status', 'Category status updated.');
    }
    public function render(): mixed
    {
        return view('livewire.pages.references.categories', [
            'categories' => Category::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(15),
        ]);
    }
}; ?>
<div class="app-page max-w-5xl">
    <div class="app-page-header">
        <div><h1 class="app-page-title">Categories</h1>
        <p class="app-page-description">Manage product category reference data.</p></div>
    </div>
    @if (session('status'))
        <div class="app-flash-success">{{ session('status') }}</div>
    @endif
    <form wire:submit="save" class="app-toolbar">
        <x-text-input wire:model="name" placeholder="Category name" aria-label="Category name" class="flex-1"
            required /><x-primary-button>{{ $editingId ? 'Save changes' : 'Add category' }}</x-primary-button>
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
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-3 py-2">{{ $category->name }}</td>
                        <td class="px-3 py-2"><span
                                class="{{ $category->active ? 'app-status-success' : 'app-status-neutral' }}">{{ $category->active ? 'Active' : 'Deactivated' }}</span>
                        </td>
                        <td class="space-x-3 px-3 py-2"><button wire:click="edit({{ $category->id }})" type="button"
                                class="app-action">Edit</button><button
                                x-on:click="$dispatch('request-confirmation', {
                                    title: @js($category->active ? 'Deactivate category?' : 'Reactivate category?'),
                                    message: @js(($category->active ? 'Deactivate ' : 'Reactivate ') . $category->name . '? This affects its availability in reference selections.'),
                                    action: 'toggleActive',
                                    arguments: [{{ $category->id }}],
                                    confirmText: @js($category->active ? 'Deactivate' : 'Reactivate'),
                                    tone: @js($category->active ? 'danger' : 'warning'),
                                })" type="button"
                                class="{{ $category->active ? 'app-action-danger' : 'app-action text-emerald-700' }}">{{ $category->active ? 'Deactivate' : 'Reactivate' }}</button>
                        </td>
                </tr>@empty<tr>
                        <td colspan="3" class="app-empty">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $categories->links() }}</div>
    </div>
    <x-confirmation-modal />
</div>
