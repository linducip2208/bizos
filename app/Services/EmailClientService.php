<?php

namespace App\Services;

use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailLink;
use App\Models\EmailMessage;
use App\Models\Lead;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailClientService
{
    protected array $imapConnections = [];

    // ─── ACCOUNT MANAGEMENT ───

    public function connectAccount(int $userId, array $config): EmailAccount
    {
        $account = EmailAccount::create([
            'user_id' => $userId,
            'email' => $config['email'],
            'name' => $config['name'] ?? null,
            'provider' => $config['provider'] ?? 'custom_imap',
            'imap_host' => $config['imap_host'] ?? null,
            'imap_port' => $config['imap_port'] ?? 993,
            'imap_encryption' => $config['imap_encryption'] ?? 'ssl',
            'smtp_host' => $config['smtp_host'] ?? null,
            'smtp_port' => $config['smtp_port'] ?? 587,
            'smtp_encryption' => $config['smtp_encryption'] ?? 'tls',
            'password_encrypted' => Crypt::encryptString($config['password']),
            'signature' => $config['signature'] ?? null,
            'is_active' => true,
            'auto_fetch' => $config['auto_fetch'] ?? true,
            'fetch_interval_minutes' => $config['fetch_interval_minutes'] ?? 5,
        ]);

        if (!empty($config['provider']) && $config['provider'] !== 'custom_imap') {
            $this->applyProviderDefaults($account);
            $account->save();
        }

        return $account;
    }

    protected function applyProviderDefaults(EmailAccount $account): void
    {
        switch ($account->provider) {
            case 'gmail':
                $account->imap_host = $account->imap_host ?: 'imap.gmail.com';
                $account->imap_port = $account->imap_port ?: 993;
                $account->imap_encryption = 'ssl';
                $account->smtp_host = $account->smtp_host ?: 'smtp.gmail.com';
                $account->smtp_port = $account->smtp_port ?: 587;
                $account->smtp_encryption = 'tls';
                break;

            case 'outlook':
                $account->imap_host = $account->imap_host ?: 'outlook.office365.com';
                $account->imap_port = $account->imap_port ?: 993;
                $account->imap_encryption = 'ssl';
                $account->smtp_host = $account->smtp_host ?: 'smtp.office365.com';
                $account->smtp_port = $account->smtp_port ?: 587;
                $account->smtp_encryption = 'tls';
                break;

            case 'yahoo':
                $account->imap_host = $account->imap_host ?: 'imap.mail.yahoo.com';
                $account->imap_port = $account->imap_port ?: 993;
                $account->imap_encryption = 'ssl';
                $account->smtp_host = $account->smtp_host ?: 'smtp.mail.yahoo.com';
                $account->smtp_port = $account->smtp_port ?: 587;
                $account->smtp_encryption = 'tls';
                break;
        }
    }

    public function testConnection(EmailAccount $account): bool
    {
        try {
            $stream = $this->connectImap($account);
            if ($stream) {
                imap_close($stream);
                unset($this->imapConnections[$account->id]);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::warning("IMAP connection test failed for {$account->email}: " . $e->getMessage());
            $account->update(['last_error' => mb_substr($e->getMessage(), 0, 500)]);
            return false;
        }
    }

    public function disconnectAccount(EmailAccount $account): void
    {
        if (isset($this->imapConnections[$account->id])) {
            try {
                imap_close($this->imapConnections[$account->id]);
            } catch (\Exception $e) {
                // ignore
            }
            unset($this->imapConnections[$account->id]);
        }
    }

    // ─── INBOX ───

    public function fetchEmails(EmailAccount $account, string $folder = 'INBOX', int $page = 1, int $perPage = 50): array
    {
        $stream = $this->connectImap($account);
        if (!$stream) {
            return ['messages' => [], 'total' => 0, 'current_page' => $page, 'folders' => []];
        }

        $cleanFolder = $this->cleanImapFolder($folder);
        $folders = $this->listFolders($stream);

        imap_reopen($stream, $this->buildImapRef($account, $cleanFolder));

        $total = imap_num_msg($stream);
        $start = max(($page - 1) * $perPage + 1, 1);
        $end = min($start + $perPage - 1, $total);

        if ($start > $total) {
            return ['messages' => [], 'total' => $total, 'current_page' => $page, 'folders' => $folders];
        }

        $messages = [];
        for ($i = $end; $i >= $start; $i--) {
            try {
                $msg = $this->parseEmailHeader($stream, $i, $account->id);
                if ($msg) {
                    $messages[] = $msg;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse email #{$i}: " . $e->getMessage());
            }
        }

        return [
            'messages' => $messages,
            'total' => $total,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'folders' => $folders,
        ];
    }

    public function getEmailDetail(EmailAccount $account, string $msgnumOrUid): ?array
    {
        $stream = $this->connectImap($account);
        if (!$stream) {
            return null;
        }

        try {
            $overview = imap_fetch_overview($stream, $msgnumOrUid, FT_UID);
            if (empty($overview)) {
                return null;
            }

            $overview = $overview[0];
            $bodyHtml = '';
            $bodyText = '';
            $attachments = [];

            $structure = imap_fetchstructure($stream, $msgnumOrUid, FT_UID);

            if (!empty($structure->parts)) {
                foreach ($structure->parts as $partNum => $part) {
                    $section = $partNum + 1;
                    $this->parseEmailPart($stream, $msgnumOrUid, $section, $part, $bodyHtml, $bodyText, $attachments, true);
                }
            } else {
                $body = imap_body($stream, $msgnumOrUid, FT_UID);
                if ($structure->subtype === 'HTML') {
                    $bodyHtml = $body;
                } else {
                    $bodyText = $body;
                }
            }

            if (empty($bodyHtml) && !empty($bodyText)) {
                $bodyHtml = nl2br(e($bodyText));
            }
            if (empty($bodyText) && !empty($bodyHtml)) {
                $bodyText = strip_tags($bodyHtml);
            }

            return [
                'uid' => $msgnumOrUid,
                'message_id' => $overview->message_id ?? null,
                'from' => $overview->from ?? '',
                'from_email' => $this->extractEmail($overview->from ?? ''),
                'from_name' => $this->extractName($overview->from ?? ''),
                'to' => $overview->to ?? '',
                'cc' => $overview->cc ?? '',
                'subject' => $overview->subject ?? '(Tanpa Subjek)',
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
                'attachments' => $attachments,
                'date' => $overview->date ?? '',
                'is_read' => !($overview->seen ?? false),
            ];
        } catch (\Exception $e) {
            Log::warning("Failed to fetch email detail: " . $e->getMessage());
            return null;
        }
    }

    protected function parseEmailHeader($stream, int $msgNum, int $accountId): ?array
    {
        $overview = imap_fetch_overview($stream, $msgNum);
        if (empty($overview)) {
            return null;
        }

        $overview = $overview[0];

        return [
            'uid' => $overview->uid,
            'msg_no' => $overview->msgno,
            'message_id' => $overview->message_id ?? null,
            'from' => $overview->from ?? '',
            'from_email' => $this->extractEmail($overview->from ?? ''),
            'from_name' => $this->extractName($overview->from ?? ''),
            'to' => $overview->to ?? '',
            'subject' => mb_decode_mimeheader($overview->subject ?? '(Tanpa Subjek)'),
            'date' => $overview->date ?? '',
            'date_ts' => strtotime($overview->date ?? ''),
            'is_read' => ($overview->seen ?? false),
            'is_flagged' => ($overview->flagged ?? false),
            'has_attachments' => ($overview->size ?? 0) > 0 && !($overview->seen ?? false),
            'size' => $overview->size ?? 0,
            'account_id' => $accountId,
        ];
    }

    protected function parseEmailPart($stream, string $uid, string $section, $part, &$bodyHtml, &$bodyText, &$attachments, bool $isUid = true): void
    {
        $options = $isUid ? FT_UID : 0;

        if (!empty($part->parts)) {
            foreach ($part->parts as $subNum => $subPart) {
                $subSection = $section . '.' . ($subNum + 1);
                $this->parseEmailPart($stream, $uid, $subSection, $subPart, $bodyHtml, $bodyText, $attachments, $isUid);
            }
            return;
        }

        $isAttachment = false;
        if ($part->ifdparameters) {
            foreach ($part->dparameters as $param) {
                if (strtoupper($param->attribute) === 'FILENAME') {
                    $isAttachment = true;
                    break;
                }
            }
        }
        if ($part->ifparameters && !$isAttachment) {
            foreach ($part->parameters as $param) {
                if (strtoupper($param->attribute) === 'NAME') {
                    $isAttachment = true;
                    break;
                }
            }
        }

        $encoding = $part->encoding ?? 0;

        if ($isAttachment) {
            $filename = '';
            if ($part->ifdparameters) {
                foreach ($part->dparameters as $param) {
                    if (strtoupper($param->attribute) === 'FILENAME') {
                        $filename = $param->value;
                        break;
                    }
                }
            }
            if (empty($filename) && $part->ifparameters) {
                foreach ($part->parameters as $param) {
                    if (strtoupper($param->attribute) === 'NAME') {
                        $filename = $param->value;
                        break;
                    }
                }
            }

            $content = imap_fetchbody($stream, $uid, $section, $options);
            if ($content) {
                $decoded = $this->decodeContent($content, $encoding);
                $attachments[] = [
                    'filename' => $filename ?: 'attachment',
                    'mime_type' => $this->partMimeType($part),
                    'size' => strlen($decoded),
                    'data' => base64_encode($decoded),
                ];
            }
        } else {
            $content = imap_fetchbody($stream, $uid, $section, $options);
            if ($content) {
                $decoded = $this->decodeContent($content, $encoding);
                $subtype = strtoupper($part->subtype ?? 'PLAIN');
                if ($subtype === 'HTML' && empty($bodyHtml)) {
                    $bodyHtml = $decoded;
                } elseif (empty($bodyText)) {
                    $bodyText = $decoded;
                }
            }
        }
    }

    protected function decodeContent(string $content, int $encoding): string
    {
        return match ($encoding) {
            1 => imap_8bit($content),       // 8BIT
            2 => imap_binary($content),     // BINARY
            3 => base64_decode($content),   // BASE64
            4 => quoted_printable_decode($content), // QUOTED-PRINTABLE
            default => $content,
        };
    }

    protected function partMimeType($part): string
    {
        $type = $part->type ?? 0;
        $subtype = $part->subtype ?? 'octet-stream';

        $types = ['text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other'];
        return ($types[$type] ?? 'application') . '/' . $subtype;
    }

    // ─── SEND ───

    public function sendEmail(EmailAccount $account, array $data): array
    {
        $password = Crypt::decryptString($account->password_encrypted);

        $config = [
            'transport' => 'smtp',
            'host' => $account->smtp_host,
            'port' => $account->smtp_port,
            'encryption' => $account->smtp_encryption,
            'username' => $account->email,
            'password' => $password,
            'timeout' => 30,
            'auth_mode' => null,
        ];

        config(['mail.mailers.embedded' => $config]);

        try {
            $body = $data['body_html'] ?? '';

            if (!empty($account->signature)) {
                $body .= '<br><br><div style="color:#666;font-size:12px">' . nl2br(e($account->signature)) . '</div>';
            }

            $mail = Mail::mailer('embedded');

            $message = $mail->to($data['to'])
                ->subject($data['subject'] ?? '(Tanpa Subjek)');

            if (!empty($data['cc'])) {
                $message->cc($data['cc']);
            }
            if (!empty($data['bcc'])) {
                $message->bcc($data['bcc']);
            }

            if (!empty($data['reply_to_message_id'])) {
                $message->getHeaders()->addTextHeader('In-Reply-To', $data['reply_to_message_id']);
                $message->getHeaders()->addTextHeader('References', $data['reply_to_message_id']);
            }

            $message->html($body);

            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (is_array($attachment) && !empty($attachment['path'])) {
                        $message->attach($attachment['path'], [
                            'as' => $attachment['name'] ?? basename($attachment['path']),
                            'mime' => $attachment['mime'] ?? null,
                        ]);
                    }
                }
            }

            $message->send();

            // Save to sent folder in local DB
            EmailMessage::create([
                'email_account_id' => $account->id,
                'message_uid' => 'sent_' . time() . '_' . Str::random(6),
                'message_id' => $data['reply_to_message_id'] ? null : ('<sent.' . time() . '@' . $account->email . '>'),
                'from_email' => $account->email,
                'from_name' => $account->name ?: $account->email,
                'to_email' => is_array($data['to']) ? implode(',', $data['to']) : $data['to'],
                'cc' => $data['cc'] ?? null,
                'bcc' => $data['bcc'] ?? null,
                'subject' => $data['subject'] ?? '(Tanpa Subjek)',
                'body_html' => $body,
                'folder' => 'INBOX.Sent',
                'is_sent' => true,
                'email_date' => now(),
            ]);

            Log::info("Email sent from {$account->email} to " . json_encode($data['to']));

            return ['success' => true, 'message' => 'Email berhasil dikirim'];

        } catch (\Exception $e) {
            Log::error("Failed to send email from {$account->email}: " . $e->getMessage());
            $account->update(['last_error' => mb_substr($e->getMessage(), 0, 500)]);
            return ['success' => false, 'message' => 'Gagal mengirim email: ' . $e->getMessage()];
        }
    }

    // ─── SMART FEATURES ───

    public function linkToRecord(string $messageId, string $modelClass, int $modelId): void
    {
        EmailLink::updateOrCreate(
            [
                'message_id' => $messageId,
                'linkable_type' => $modelClass,
                'linkable_id' => $modelId,
            ],
            [
                'linked_by' => auth()->id(),
                'link_reason' => class_basename($modelClass),
            ]
        );
    }

    public function suggestLink(string $fromEmail): array
    {
        if (empty($fromEmail)) {
            return [];
        }

        $suggestions = [];

        $clients = \App\Models\Client::where('email', $fromEmail)
            ->orWhereHas('clientContacts', fn($q) => $q->where('email', $fromEmail))
            ->limit(3)
            ->get();

        foreach ($clients as $client) {
            $suggestions[] = [
                'model' => 'Client',
                'name' => $client->name,
                'id' => $client->id,
                'url' => url('/admin/clients/' . $client->id),
            ];
        }

        $leads = \App\Models\Lead::where('email', $fromEmail)
            ->limit(3)
            ->get();

        foreach ($leads as $lead) {
            $suggestions[] = [
                'model' => 'Lead',
                'name' => trim($lead->first_name . ' ' . $lead->last_name),
                'id' => $lead->id,
                'url' => url('/admin/leads/' . $lead->id),
            ];
        }

        $employees = \App\Models\Employee::where('email', $fromEmail)
            ->limit(3)
            ->get();

        foreach ($employees as $emp) {
            $suggestions[] = [
                'model' => 'Employee',
                'name' => trim($emp->first_name . ' ' . $emp->last_name),
                'id' => $emp->id,
                'url' => url('/admin/employees/' . $emp->id),
            ];
        }

        $suppliers = \App\Models\Supplier::where('email', $fromEmail)
            ->limit(3)
            ->get();

        foreach ($suppliers as $sup) {
            $suggestions[] = [
                'model' => 'Supplier',
                'name' => $sup->name,
                'id' => $sup->id,
                'url' => url('/admin/suppliers/' . $sup->id),
            ];
        }

        return $suggestions;
    }

    public function convertToLead(string $messageId, EmailAccount $account): ?Lead
    {
        $email = $this->getEmailDetail($account, $messageId);
        if (!$email) {
            return null;
        }

        $lead = Lead::create([
            'first_name' => $email['from_name'] ?: explode('@', $email['from_email'])[0],
            'last_name' => '',
            'email' => $email['from_email'],
            'status' => 'baru',
            'notes' => 'Dikonversi dari email: ' . ($email['subject'] ?? '(Tanpa Subjek)'),
        ]);

        $this->linkToRecord($email['message_id'] ?? $messageId, Lead::class, $lead->id);

        return $lead;
    }

    public function convertToTicket(string $messageId, EmailAccount $account): ?Ticket
    {
        $email = $this->getEmailDetail($account, $messageId);
        if (!$email) {
            return null;
        }

        $ticket = Ticket::create([
            'subject' => $email['subject'] ?? 'Dari email',
            'description' => $email['body_text'] ?? 'Dikonversi dari email oleh ' . $email['from_email'],
            'priority' => 'medium',
            'status' => 'open',
            'source' => 'email',
        ]);

        $this->linkToRecord($email['message_id'] ?? $messageId, Ticket::class, $ticket->id);

        return $ticket;
    }

    // ─── SEARCH EMAILS ───

    public function searchEmails(EmailAccount $account, string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $stream = $this->connectImap($account);
        if (!$stream) {
            return ['messages' => [], 'total' => 0];
        }

        $folder = $filters['folder'] ?? 'INBOX';

        try {
            $criteria = 'SUBJECT "' . addcslashes($query, '"\\') . '"';
            if (!empty($filters['from'])) {
                $criteria .= ' FROM "' . addcslashes($filters['from'], '"\\') . '"';
            }
            if (!empty($filters['since'])) {
                $criteria .= ' SINCE "' . date('d-M-Y', strtotime($filters['since'])) . '"';
            }
            if (!empty($filters['before'])) {
                $criteria .= ' BEFORE "' . date('d-M-Y', strtotime($filters['before'])) . '"';
            }
            if (empty($filters['unread'])) {
                // search all
            } else {
                $criteria = 'UNSEEN ' . $criteria;
            }

            $matches = imap_search($stream, $criteria, SE_UID);
            if (empty($matches)) {
                return ['messages' => [], 'total' => 0, 'current_page' => 1];
            }

            $matches = array_reverse($matches);
            $total = count($matches);
            $slice = array_slice($matches, ($page - 1) * $perPage, $perPage);

            $messages = [];
            foreach ($slice as $uid) {
                $overview = imap_fetch_overview($stream, $uid, FT_UID);
                if (!empty($overview)) {
                    $messages[] = [
                        'uid' => $uid,
                        'from' => $overview[0]->from ?? '',
                        'from_email' => $this->extractEmail($overview[0]->from ?? ''),
                        'subject' => mb_decode_mimeheader($overview[0]->subject ?? ''),
                        'date' => $overview[0]->date ?? '',
                        'is_read' => $overview[0]->seen ?? false,
                    ];
                }
            }

            return [
                'messages' => $messages,
                'total' => $total,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ];
        } catch (\Exception $e) {
            Log::warning("Email search failed: " . $e->getMessage());
            return ['messages' => [], 'total' => 0, 'current_page' => 1];
        }
    }

    // ─── EMAIL TIMELINE ───

    public function getTimeline(string $emailAddress, int $limit = 50): array
    {
        return EmailMessage::where(function ($q) use ($emailAddress) {
            $q->where('from_email', $emailAddress)
                ->orWhere('to_email', 'LIKE', '%' . $emailAddress . '%')
                ->orWhere('cc', 'LIKE', '%' . $emailAddress . '%');
        })
        ->orderByDesc('email_date')
        ->limit($limit)
        ->with('account')
        ->get()
        ->map(function ($msg) {
            return [
                'id' => $msg->id,
                'account_email' => $msg->account?->email,
                'from_email' => $msg->from_email,
                'from_name' => $msg->from_name,
                'to_email' => $msg->to_email,
                'subject' => $msg->subject,
                'body_preview' => mb_substr(strip_tags($msg->body_html ?? $msg->body_text ?? ''), 0, 150),
                'email_date' => $msg->email_date?->format('Y-m-d H:i'),
                'is_sent' => $msg->is_sent,
                'is_read' => $msg->is_read,
                'has_attachments' => $msg->has_attachments,
                'linked_records' => $msg->links->map(fn($l) => [
                    'model' => class_basename($l->linkable_type),
                    'name' => $l->linkable?->name ?? $l->linkable?->title ?? '#' . $l->linkable_id,
                    'url' => $this->getRecordUrl($l->linkable_type, $l->linkable_id),
                ])->toArray(),
            ];
        })->toArray();
    }

    protected function getRecordUrl(string $modelClass, int $id): string
    {
        $base = match ($modelClass) {
            \App\Models\Client::class => '/admin/clients',
            \App\Models\Lead::class => '/admin/leads',
            \App\Models\Deal::class => '/admin/deals',
            \App\Models\Employee::class => '/admin/employees',
            \App\Models\Ticket::class => '/admin/tickets',
            \App\Models\Invoice::class => '/admin/invoices',
            default => '/admin',
        };
        return url($base . '/' . $id);
    }

    // ─── TEMPLATE SENDING ───

    public function sendWithTemplate(EmailAccount $account, string $to, $template, array $data): array
    {
        $subject = $template->subject ?? $data['subject'] ?? '(Tanpa Subjek)';
        $body = $template->content ?? $template->body ?? '';

        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return $this->sendEmail($account, [
            'to' => $to,
            'cc' => $data['cc'] ?? null,
            'subject' => $subject,
            'body_html' => $body,
            'attachments' => $data['attachments'] ?? [],
        ]);
    }

    // ─── IMAP HELPERS ───

    protected function connectImap(EmailAccount $account)
    {
        if (isset($this->imapConnections[$account->id])) {
            return $this->imapConnections[$account->id];
        }

        if (!$account->is_active || empty($account->imap_host)) {
            return null;
        }

        try {
            $mailbox = $this->buildImapRef($account);
            $password = Crypt::decryptString($account->password_encrypted);
            $stream = imap_open($mailbox, $account->email, $password, 0, 1, [
                'DISABLE_AUTHENTICATOR' => 'GSSAPI',
            ]);

            if ($stream) {
                $this->imapConnections[$account->id] = $stream;
                return $stream;
            }
        } catch (\Exception $e) {
            Log::warning("IMAP connection failed for {$account->email}: " . $e->getMessage());
            $account->update(['last_error' => mb_substr($e->getMessage(), 0, 500), 'is_active' => false]);
            return null;
        }

        return null;
    }

    protected function buildImapRef(EmailAccount $account, string $folder = null): string
    {
        $encryption = $account->imap_encryption;
        $prefix = match ($encryption) {
            'ssl' => '/ssl',
            'tls' => '/tls',
            default => '/notls',
        };

        $ref = '{' . $account->imap_host . ':' . $account->imap_port . '/imap' . $prefix . '/novalidate-cert}';
        if ($folder) {
            $ref .= $folder;
        }

        return $ref;
    }

    protected function cleanImapFolder(string $folder): string
    {
        $folder = str_replace('{', '', $folder);
        $folder = str_replace('}', '', $folder);
        return $folder;
    }

    protected function listFolders($stream): array
    {
        try {
            $folders = imap_list($stream, $this->buildImapRefForCurrent($stream), '*');
            if (!$folders) {
                return ['INBOX'];
            }

            return array_map(function ($f) {
                $name = str_replace($this->buildImapRefForCurrent($stream), '', $f);
                return trim($name);
            }, $folders);
        } catch (\Exception $e) {
            return ['INBOX'];
        }
    }

    protected function buildImapRefForCurrent($stream): string
    {
        $mailboxInfo = imap_mailboxmsginfo($stream);
        $mailbox = $mailboxInfo->Mailbox ?? '';
        $pos = strrpos($mailbox, '}');
        return $pos !== false ? substr($mailbox, 0, $pos + 1) : $mailbox;
    }

    // ─── PARSING HELPERS ───

    protected function extractEmail(string $header): string
    {
        if (preg_match('/<([^>]+)>/', $header, $matches)) {
            return $matches[1];
        }
        return trim($header);
    }

    protected function extractName(string $header): string
    {
        if (preg_match('/^([^<]+)</', $header, $matches)) {
            return trim(trim($matches[1]), '"\' ');
        }
        return trim($header);
    }

    // ─── SYNC (called by scheduler) ───

    public function syncAllAccounts(): void
    {
        $accounts = EmailAccount::where('is_active', true)
            ->where('auto_fetch', true)
            ->where(function ($q) {
                $q->whereNull('last_fetched_at')
                    ->orWhere('last_fetched_at', '<=', now()->subMinutes(5));
            })
            ->get();

        foreach ($accounts as $account) {
            try {
                $this->syncAccount($account);
                $account->update([
                    'last_fetched_at' => now(),
                    'last_error' => null,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to sync email account {$account->email}: " . $e->getMessage());
                $account->update(['last_error' => mb_substr($e->getMessage(), 0, 500)]);
            }
        }
    }

    public function syncAccount(EmailAccount $account): int
    {
        $stream = $this->connectImap($account);
        if (!$stream) {
            return 0;
        }

        $synced = 0;
        $maxToFetch = 100;

        for ($i = 1; $i <= $maxToFetch; $i++) {
            $overview = imap_fetch_overview($stream, $i);
            if (empty($overview)) {
                break;
            }

            $overview = $overview[0];

            $exists = EmailMessage::where('email_account_id', $account->id)
                ->where('message_uid', $overview->uid)
                ->where('folder', 'INBOX')
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                $structure = imap_fetchstructure($stream, $i);
                $bodyHtml = '';
                $bodyText = '';

                if (!empty($structure->parts)) {
                    $this->parseFullBody($stream, $i, $structure, $bodyHtml, $bodyText);
                } else {
                    $body = imap_body($stream, $i);
                    if ($structure->subtype === 'HTML') {
                        $bodyHtml = $body;
                    } else {
                        $bodyText = $body;
                    }
                }

                if (empty($bodyHtml) && !empty($bodyText)) {
                    $bodyHtml = nl2br(e($bodyText));
                }

                EmailMessage::create([
                    'email_account_id' => $account->id,
                    'message_uid' => $overview->uid,
                    'message_id' => $overview->message_id ?? null,
                    'from_email' => $this->extractEmail($overview->from ?? ''),
                    'from_name' => $this->extractName($overview->from ?? ''),
                    'to_email' => $overview->to ?? '',
                    'cc' => $overview->cc ?? '',
                    'subject' => mb_decode_mimeheader($overview->subject ?? '(Tanpa Subjek)'),
                    'body_html' => $bodyHtml,
                    'body_text' => $bodyText,
                    'folder' => 'INBOX',
                    'is_read' => $overview->seen ?? false,
                    'has_attachments' => ($overview->size ?? 0) > 0,
                    'email_date' => date('Y-m-d H:i:s', strtotime($overview->date ?? 'now')),
                    'in_reply_to' => $overview->in_reply_to ?? null,
                ]);

                $synced++;
            } catch (\Exception $e) {
                Log::warning("Failed to sync email UID {$overview->uid}: " . $e->getMessage());
            }
        }

        return $synced;
    }

    protected function parseFullBody($stream, int $msgNum, $structure, &$bodyHtml, &$bodyText): void
    {
        foreach ($structure->parts as $partNum => $part) {
            $section = $partNum + 1;
            if (!empty($part->parts)) {
                $this->parseFullBodyPart($stream, $msgNum, $section, $part, $bodyHtml, $bodyText);
            } else {
                $this->parseFullBodyPart($stream, $msgNum, $section, $part, $bodyHtml, $bodyText);
            }
        }
    }

    protected function parseFullBodyPart($stream, int $msgNum, string $section, $part, &$bodyHtml, &$bodyText): void
    {
        if (!empty($part->parts)) {
            foreach ($part->parts as $subNum => $subPart) {
                $subSection = $section . '.' . ($subNum + 1);
                $this->parseFullBodyPart($stream, $msgNum, $subSection, $subPart, $bodyHtml, $bodyText);
            }
            return;
        }

        $isAttachment = false;
        if ($part->ifdparameters) {
            foreach ($part->dparameters as $param) {
                if (strtoupper($param->attribute) === 'FILENAME') {
                    $isAttachment = true;
                    break;
                }
            }
        }

        if ($isAttachment) {
            return;
        }

        $content = imap_fetchbody($stream, $msgNum, $section);
        if ($content) {
            $encoding = $part->encoding ?? 0;
            $decoded = $this->decodeContent($content, $encoding);
            $subtype = strtoupper($part->subtype ?? 'PLAIN');
            if ($subtype === 'HTML' && empty($bodyHtml)) {
                $bodyHtml = $decoded;
            } elseif (empty($bodyText)) {
                $bodyText = $decoded;
            }
        }
    }

    public function disconnectAll(): void
    {
        foreach ($this->imapConnections as $id => $conn) {
            try {
                imap_close($conn);
            } catch (\Exception $e) {
                // ignore
            }
        }
        $this->imapConnections = [];
    }

    public function __destruct()
    {
        $this->disconnectAll();
    }
}
