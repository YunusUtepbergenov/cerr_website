<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class UserIndex extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'viewer';

    public string $password = '';

    public bool $showForm = false;

    public ?string $generatedPassword = null;

    public string $roleFilter = '';

    public ?int $currentUserId = null;

    public function mount(): void
    {
        $this->currentUserId = auth()->id();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::in(['admin', 'writer', 'editor', 'viewer', 'accountant'])],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $isSelfCheck = $this->editingId !== null && $this->currentUserId !== null && $this->editingId === $this->currentUserId;
        if ($isSelfCheck && $this->role !== 'admin') {
            $this->addError('role', __('admin.users.cannot_demote_self'));

            return;
        }

        $this->validate();

        $user = $this->editingId ? User::findOrFail($this->editingId) : new User;
        $isNew = ! $user->exists;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->role = $this->role;
        if ($this->password !== '') {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        $changes = array_filter(['name' => $user->getChanges()['name'] ?? null, 'email' => $user->getChanges()['email'] ?? null, 'role' => $user->getChanges()['role'] ?? null]);
        $user->logActivity($isNew ? 'created' : 'updated', $changes);

        session()->flash('status', __('admin.users.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);
        $plain = Str::password(16);
        $user->password = Hash::make($plain);
        $user->save();

        $user->logActivity('reset_password');

        $this->generatedPassword = $plain;
    }

    public function delete(int $id): void
    {
        if ($id === $this->currentUserId) {
            session()->flash('status', __('admin.users.cannot_delete_self'));

            return;
        }

        $user = User::findOrFail($id);
        $user->logActivity('deleted', ['email' => $user->email]);
        $user->delete();
        session()->flash('status', __('admin.users.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->role = 'viewer';
        $this->password = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = User::query()->orderBy('id');
        if ($this->roleFilter !== '') {
            $query->where('role', $this->roleFilter);
        }

        return view('livewire.admin.users.index', [
            'users' => $query->get(),
        ])->title(__('admin.users.title_section'));
    }
}
