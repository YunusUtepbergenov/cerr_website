<?php

namespace App\Livewire\Admin\Activity;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Журнал действий')]
class ActivityIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $action = '';

    #[Url(except: '')]
    public string $subjectType = '';

    public function render()
    {
        $query = Activity::with('user')->latest('created_at');

        if ($this->action !== '') {
            $query->where('action', $this->action);
        }

        if ($this->subjectType !== '') {
            $query->where('subject_type', $this->subjectType);
        }

        return view('livewire.admin.activity.index', [
            'activities' => $query->paginate(30),
        ]);
    }
}
