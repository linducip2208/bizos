<?php

namespace App\Http\Livewire;

use App\Models\EmailAccount;
use App\Services\EmailClientService;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmailInbox extends Component
{
    use WithFileUploads;

    public ?int $selectedAccountId = null;
    public string $currentFolder = 'INBOX';
    public int $currentPage = 1;
    public int $perPage = 50;
    public ?string $selectedMessageUid = null;
    public ?array $selectedMessage = null;
    public array $messages = [];
    public array $folders = [];
    public int $totalMessages = 0;
    public int $lastPage = 1;
    public string $searchQuery = '';
    public bool $showCompose = false;
    public array $composeData = [];
    public bool $showSuggestions = false;
    public array $linkSuggestions = [];
    public $attachmentUploads = [];
    public bool $isLoading = false;

    // Account management
    public ?int $editingAccountId = null;
    public array $accountForm = [
        'email' => '',
        'name' => '',
        'provider' => 'custom_imap',
        'imap_host' => '',
        'imap_port' => 993,
        'imap_encryption' => 'ssl',
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'password' => '',
    ];

    public function mount(): void
    {
        $account = EmailAccount::where('user_id', auth()->id())->where('is_active', true)->first();
        if ($account) {
            $this->selectedAccountId = $account->id;
            $this->loadMessages();
        }
    }

    public function updatedSelectedAccountId(): void
    {
        $this->currentFolder = 'INBOX';
        $this->currentPage = 1;
        $this->selectedMessageUid = null;
        $this->selectedMessage = null;
        $this->searchQuery = '';
        $this->loadMessages();
    }

    public function updatedCurrentFolder(): void
    {
        $this->currentPage = 1;
        $this->selectedMessageUid = null;
        $this->selectedMessage = null;
        $this->loadMessages();
    }

    public function setPage(int $page): void
    {
        $this->currentPage = max(1, $page);
        $this->loadMessages();
    }

    public function loadMessages(): void
    {
        if (!$this->selectedAccountId) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $this->isLoading = true;
        $service = app(EmailClientService::class);

        $result = $service->fetchEmails($account, $this->currentFolder, $this->currentPage, $this->perPage);

        $this->messages = $result['messages'];
        $this->totalMessages = $result['total'] ?? 0;
        $this->lastPage = $result['last_page'] ?? 1;
        $this->folders = $result['folders'] ?? ['INBOX'];
        $this->isLoading = false;
    }

    public function selectMessage(string $uid): void
    {
        if (!$this->selectedAccountId) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $service = app(EmailClientService::class);
        $this->selectedMessage = $service->getEmailDetail($account, $uid);
        $this->selectedMessageUid = $uid;

        if ($this->selectedMessage && !empty($this->selectedMessage['from_email'])) {
            $this->linkSuggestions = $service->suggestLink($this->selectedMessage['from_email']);
        }

        // Mark as read via IMAP
        try {
            $this->markAsRead($account, $uid);
        } catch (\Exception $e) {
            // ignore
        }
    }

    protected function markAsRead(EmailAccount $account, string $uid): void
    {
        $service = app(EmailClientService::class);
        $stream = $this->getImapStream($service, $account);
        if ($stream) {
            imap_setflag_full($stream, $uid, '\\Seen', ST_UID);
        }
    }

    public function searchEmails(): void
    {
        if (!$this->selectedAccountId || empty($this->searchQuery)) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $service = app(EmailClientService::class);
        $result = $service->searchEmails($account, $this->searchQuery, ['folder' => $this->currentFolder]);

        $this->messages = $result['messages'];
        $this->totalMessages = $result['total'] ?? 0;
        $this->currentPage = 1;
    }

    public function openCompose(?string $replyTo = null): void
    {
        $this->composeData = [
            'to' => '',
            'cc' => '',
            'bcc' => '',
            'subject' => '',
            'body_html' => '',
            'reply_to_message_id' => null,
        ];

        if ($replyTo && $this->selectedMessage) {
            $this->composeData['to'] = $this->selectedMessage['from_email'] ?? '';
            $prefix = 'Re: ';
            $subj = $this->selectedMessage['subject'] ?? '';
            if (!str_starts_with(mb_strtolower($subj), 're:')) {
                $subj = $prefix . $subj;
            }
            $this->composeData['subject'] = $subj;
            $this->composeData['reply_to_message_id'] = $this->selectedMessage['message_id'] ?? null;

            $this->composeData['body_html'] = '<br><br><hr style="border:1px solid #e5e7eb">'
                . '<p style="color:#6b7280;font-size:12px">Pada ' . ($this->selectedMessage['date'] ?? '') . ', '
                . e($this->selectedMessage['from'] ?? '') . ' menulis:</p>'
                . '<blockquote style="border-left:2px solid #d1d5db;margin:0;padding-left:12px;color:#6b7280">'
                . ($this->selectedMessage['body_html'] ?? nl2br(e($this->selectedMessage['body_text'] ?? '')))
                . '</blockquote>';
        }

        $this->showCompose = true;
        $this->showSuggestions = false;
    }

    public function closeCompose(): void
    {
        $this->showCompose = false;
        $this->composeData = [];
        $this->attachmentUploads = [];
    }

    public function sendEmail(): void
    {
        if (!$this->selectedAccountId) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $this->validate([
            'composeData.to' => 'required|email',
            'composeData.subject' => 'required|string|max:1000',
        ]);

        $service = app(EmailClientService::class);

        $attachments = [];
        foreach ($this->attachmentUploads as $file) {
            $attachments[] = [
                'path' => $file->getRealPath(),
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ];
        }

        $result = $service->sendEmail($account, [
            'to' => $this->composeData['to'],
            'cc' => $this->composeData['cc'] ?? null,
            'bcc' => $this->composeData['bcc'] ?? null,
            'subject' => $this->composeData['subject'],
            'body_html' => $this->composeData['body_html'],
            'reply_to_message_id' => $this->composeData['reply_to_message_id'] ?? null,
            'attachments' => $attachments,
        ]);

        if ($result['success']) {
            $this->dispatch('notify', ['message' => 'Email berhasil dikirim!', 'type' => 'success']);
            $this->closeCompose();
            $this->attachmentUploads = [];
            $this->loadMessages();
        } else {
            $this->dispatch('notify', ['message' => $result['message'], 'type' => 'error']);
        }
    }

    public function linkEmail(string $linkableType, int $linkableId): void
    {
        if (!$this->selectedMessage) return;

        $service = app(EmailClientService::class);
        $service->linkToRecord(
            $this->selectedMessage['message_id'] ?? $this->selectedMessageUid,
            $linkableType,
            $linkableId
        );

        $this->dispatch('notify', ['message' => 'Email berhasil ditautkan!', 'type' => 'success']);
        $this->showSuggestions = false;
    }

    public function convertToLead(): void
    {
        if (!$this->selectedAccountId || !$this->selectedMessageUid) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $service = app(EmailClientService::class);
        $lead = $service->convertToLead($this->selectedMessageUid, $account);

        if ($lead) {
            $this->dispatch('notify', ['message' => 'Email berhasil dikonversi jadi Lead #' . $lead->id, 'type' => 'success']);
        }
    }

    public function convertToTicket(): void
    {
        if (!$this->selectedAccountId || !$this->selectedMessageUid) return;

        $account = EmailAccount::find($this->selectedAccountId);
        if (!$account) return;

        $service = app(EmailClientService::class);
        $ticket = $service->convertToTicket($this->selectedMessageUid, $account);

        if ($ticket) {
            $this->dispatch('notify', ['message' => 'Email berhasil dikonversi jadi Tiket #' . $ticket->id, 'type' => 'success']);
        }
    }

    // Account CRUD
    public function showAddAccount(): void
    {
        $this->editingAccountId = null;
        $this->accountForm = [
            'email' => '',
            'name' => '',
            'provider' => 'custom_imap',
            'imap_host' => '',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'password' => '',
        ];
    }

    public function saveAccount(): void
    {
        $this->validate([
            'accountForm.email' => 'required|email',
            'accountForm.password' => 'required|string',
            'accountForm.imap_host' => 'required_unless:accountForm.provider,custom_imap',
            'accountForm.smtp_host' => 'required_unless:accountForm.provider,custom_imap',
        ]);

        $service = app(EmailClientService::class);

        $account = $service->connectAccount(auth()->id(), [
            'email' => $this->accountForm['email'],
            'name' => $this->accountForm['name'],
            'provider' => $this->accountForm['provider'],
            'imap_host' => $this->accountForm['imap_host'],
            'imap_port' => $this->accountForm['imap_port'],
            'imap_encryption' => $this->accountForm['imap_encryption'],
            'smtp_host' => $this->accountForm['smtp_host'],
            'smtp_port' => $this->accountForm['smtp_port'],
            'smtp_encryption' => $this->accountForm['smtp_encryption'],
            'password' => $this->accountForm['password'],
        ]);

        $this->selectedAccountId = $account->id;
        $this->dispatch('notify', ['message' => 'Akun email berhasil ditambahkan!', 'type' => 'success']);
        $this->loadMessages();
    }

    public function testAccountConnection(int $accountId): void
    {
        $account = EmailAccount::find($accountId);
        if (!$account) return;

        $service = app(EmailClientService::class);
        if ($service->testConnection($account)) {
            $this->dispatch('notify', ['message' => 'Koneksi berhasil!', 'type' => 'success']);
        } else {
            $this->dispatch('notify', ['message' => 'Koneksi gagal. Periksa kredensial.', 'type' => 'error']);
        }
    }

    public function getImapStream($service, $account)
    {
        $ref = new \ReflectionClass($service);
        $prop = $ref->getProperty('imapConnections');
        $prop->setAccessible(true);
        $connections = $prop->getValue($service);
        return $connections[$account->id] ?? null;
    }

    public function toggleStar(string $uid): void
    {
        // For now, just toggle starred in local state
        foreach ($this->messages as &$msg) {
            if ($msg['uid'] === $uid) {
                $msg['is_flagged'] = !($msg['is_flagged'] ?? false);
                break;
            }
        }
    }

    public function render()
    {
        $accounts = EmailAccount::where('user_id', auth()->id())->get();

        return view('livewire.email-inbox', compact('accounts'));
    }
}
