<?php

namespace App\Http\Livewire;

use App\Services\EmailClientService;
use Livewire\Component;

class EmailTimeline extends Component
{
    public string $emailAddress = '';
    public int $limit = 50;
    public array $emails = [];
    public bool $isLoading = false;
    public string $contextType = '';
    public string $contextName = '';

    protected $listeners = [
        'loadEmailTimeline' => 'load',
        'refreshEmailTimeline' => '$refresh',
    ];

    public function mount(string $email = '', int $limit = 50): void
    {
        $this->emailAddress = $email;
        $this->limit = $limit;

        if (!empty($this->emailAddress)) {
            $this->load($this->emailAddress);
        }
    }

    public function load(string $email = ''): void
    {
        if (!empty($email)) {
            $this->emailAddress = $email;
        }

        if (empty($this->emailAddress)) return;

        $this->isLoading = true;

        $service = app(EmailClientService::class);
        $this->emails = $service->getTimeline($this->emailAddress, $this->limit);

        $this->isLoading = false;
    }

    public function render()
    {
        return view('livewire.email-timeline');
    }
}
