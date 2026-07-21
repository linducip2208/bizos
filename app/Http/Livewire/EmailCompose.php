<?php

namespace App\Http\Livewire;

use App\Models\EmailAccount;
use App\Models\EmailTemplate;
use App\Services\EmailClientService;
use Livewire\Component;

class EmailCompose extends Component
{
    public bool $isOpen = false;
    public ?int $accountId = null;
    public array $form = [
        'to' => '',
        'cc' => '',
        'bcc' => '',
        'subject' => '',
        'body' => '',
    ];
    public ?int $templateId = null;
    public array $templateVariables = [];
    public array $attachments = [];
    public bool $sending = false;

    protected $listeners = [
        'openEmailCompose' => 'open',
        'closeEmailCompose' => 'close',
    ];

    public function open(array $data = []): void
    {
        $this->isOpen = true;
        $this->sending = false;
        $this->attachments = [];

        $account = EmailAccount::where('user_id', auth()->id())->where('is_active', true)->first();
        $this->accountId = $account?->id;

        $this->form = array_merge([
            'to' => '',
            'cc' => '',
            'bcc' => '',
            'subject' => '',
            'body' => '',
        ], $data['form'] ?? $data);
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->form = ['to' => '', 'cc' => '', 'bcc' => '', 'subject' => '', 'body' => ''];
        $this->templateId = null;
        $this->templateVariables = [];
        $this->attachments = [];
    }

    public function removeAttachment(int $index): void
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments);
        }
    }

    public function send(): void
    {
        if (!$this->accountId) {
            $this->dispatch('notify', ['message' => 'Tidak ada akun email yang dikonfigurasi.', 'type' => 'error']);
            return;
        }

        $this->validate([
            'form.to' => 'required|email',
            'form.subject' => 'required|string|max:1000',
        ]);

        $this->sending = true;

        $account = EmailAccount::find($this->accountId);
        if (!$account) {
            $this->dispatch('notify', ['message' => 'Akun email tidak ditemukan.', 'type' => 'error']);
            $this->sending = false;
            return;
        }

        $service = app(EmailClientService::class);

        $attachments = [];
        foreach ($this->attachments as $f) {
            $attachments[] = [
                'path' => $f->getRealPath(),
                'name' => $f->getClientOriginalName(),
                'mime' => $f->getMimeType(),
            ];
        }

        $result = $service->sendEmail($account, [
            'to' => $this->form['to'],
            'cc' => $this->form['cc'] ?? null,
            'bcc' => $this->form['bcc'] ?? null,
            'subject' => $this->form['subject'],
            'body_html' => nl2br(e($this->form['body'])),
            'attachments' => $attachments,
        ]);

        $this->sending = false;

        if ($result['success']) {
            $this->dispatch('notify', ['message' => 'Email berhasil dikirim!', 'type' => 'success']);
            $this->close();
            $this->dispatch('emailSent');
        } else {
            $this->dispatch('notify', ['message' => $result['message'], 'type' => 'error']);
        }
    }

    public function render()
    {
        $accounts = EmailAccount::where('user_id', auth()->id())->where('is_active', true)->get();
        return view('livewire.email-compose', compact('accounts'));
    }
}
