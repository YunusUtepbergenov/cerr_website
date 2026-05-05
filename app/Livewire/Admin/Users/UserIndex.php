<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Пользователи')]
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
            'role' => ['required', Rule::in(['admin', 'writer', 'editor', 'viewer'])],
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
        $user->name = $this->name;
        $user->email = $this->email;
        $user->role = $this->role;
        if ($this->password !== '') {
            $user->password = Hash::make($this->password);
        }
        $user->save();

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

        $this->generatedPassword = $plain;
    }

    public function delete(int $id): void
    {
        if ($id === $this->currentUserId) {
            session()->flash('status', __('admin.users.cannot_delete_self'));

            return;
        }

        User::findOrFail($id)->delete();
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
        ]);
    }
}
