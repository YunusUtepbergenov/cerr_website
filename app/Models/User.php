<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\LogsActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, LogsActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isWriter(): bool
    {
        return $this->role === 'writer';
    }

    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['admin', 'editor', 'writer'], true);
    }

    public function canManageContent(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canViewActivity(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    public function canPublishNews(): bool
    {
        return $this->isAdmin() || $this->isEditor();
    }

    public function canEditNews(News $news): bool
    {
        if ($this->isAdmin() || $this->isEditor()) {
            return true;
        }

        return $this->isWriter() && $news->user_id === $this->id;
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
