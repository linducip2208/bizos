<?php

namespace App\Services;

use App\Models\DocumentTemplate;

class EmailTemplateService
{
    public function render(DocumentTemplate $template, array $data): string
    {
        $service = app(DocumentTemplateService::class);
        return $service->replacePlaceholders($template->content, $data);
    }

    public function sendEmail(string $to, string $subject, string $htmlBody, ?array $attachments = []): void
    {
        \Illuminate\Support\Facades\Mail::html($htmlBody, function ($message) use ($to, $subject, $attachments) {
            $message->to($to)
                ->subject($subject);

            if ($attachments) {
                foreach ($attachments as $attachment) {
                    if (is_string($attachment) && file_exists($attachment)) {
                        $message->attach($attachment);
                    } elseif (is_array($attachment)) {
                        $message->attach(
                            $attachment['path'] ?? $attachment[0],
                            $attachment['options'] ?? []
                        );
                    }
                }
            }
        });
    }

    public function sendWithTemplate(string $to, DocumentTemplate $template, array $data): void
    {
        $htmlBody = $this->render($template, $data);

        $subject = $data['subject'] ?? $template->name;
        $attachments = $data['attachments'] ?? [];

        $this->sendEmail($to, $subject, $htmlBody, $attachments);
    }

    public function sendDocumentEmail(string $to, DocumentTemplate $template, array $data): void
    {
        $service = app(DocumentTemplateService::class);
        $htmlBody = $service->preview($template);

        $this->sendEmail($to, $template->name, $htmlBody);
    }
}
