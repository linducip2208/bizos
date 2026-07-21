<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketActivity;
use Illuminate\Support\Facades\DB;

class TicketMergeService
{
    public function findDuplicates(Ticket $ticket, int $limit = 5): array
    {
        $openTickets = Ticket::where('company_id', $ticket->company_id)
            ->where('id', '!=', $ticket->id)
            ->whereIn('status', ['open', 'in_progress', 'waiting_on_customer'])
            ->select('id', 'ticket_number', 'subject', 'description', 'status', 'created_at')
            ->get();

        $sourceText = $this->normalizeText($ticket->subject . ' ' . $ticket->description);
        $sourceTerms = $this->tokenize($sourceText);

        $scores = [];
        foreach ($openTickets as $candidate) {
            $candidateText = $this->normalizeText($candidate->subject . ' ' . $candidate->description);
            $candidateTerms = $this->tokenize($candidateText);

            $similarity = $this->cosineSimilarity($sourceTerms, $candidateTerms);

            if ($similarity > 0.3) {
                $scores[] = [
                    'ticket' => $candidate,
                    'similarity' => round($similarity * 100, 1),
                ];
            }
        }

        usort($scores, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice(array_map(function ($item) {
            return [
                'ticket_id' => $item['ticket']->id,
                'ticket_number' => $item['ticket']->ticket_number,
                'subject' => $item['ticket']->subject,
                'status' => $item['ticket']->status,
                'created_at' => $item['ticket']->created_at?->format('Y-m-d H:i'),
                'similarity_percent' => $item['similarity'],
            ];
        }, $scores), 0, $limit);
    }

    public function merge(Ticket $source, Ticket $target, string $reason): Ticket
    {
        if ($source->id === $target->id) {
            throw new \InvalidArgumentException('Tidak dapat menggabungkan tiket dengan dirinya sendiri');
        }

        DB::transaction(function () use ($source, $target, $reason) {
            $source->replies()->update(['ticket_id' => $target->id]);
            $source->attachments()->update(['ticket_id' => $target->id]);
            $source->activities()->update(['ticket_id' => $target->id]);

            $sourceTagIds = $source->tags()->pluck('ticket_tags.id')->toArray();
            $target->tags()->syncWithoutDetaching($sourceTagIds);

            TicketActivity::create([
                'ticket_id' => $target->id,
                'employee_id' => auth()->user()?->employee_id,
                'activity_type' => 'merged',
                'new_value' => "Tiket #{$source->ticket_number} digabungkan ke tiket ini. Alasan: {$reason}",
                'created_at' => now(),
            ]);

            $source->update([
                'status' => 'closed',
                'closed_at' => now(),
                'parent_id' => $target->id,
            ]);
        });

        $target->refresh();
        return $target;
    }

    protected function normalizeText(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/i', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        $stopWords = [
            'yang', 'dan', 'di', 'ke', 'dari', 'pada', 'ini', 'itu',
            'adalah', 'akan', 'juga', 'sudah', 'telah', 'atau', 'untuk',
            'dengan', 'tidak', 'dalam', 'seperti', 'bahwa', 'oleh', 'tentang',
            'jika', 'saya', 'kami', 'anda', 'mereka', 'bisa', 'dapat',
            'ada', 'hal', 'masalah', 'the', 'is', 'at', 'which', 'on',
            'in', 'to', 'of', 'and', 'a', 'an', 'for', 'with', 'this',
            'that', 'it', 'but', 'not', 'or', 'as', 'be', 'been',
        ];

        $words = explode(' ', $text);
        $words = array_filter($words, function ($w) use ($stopWords) {
            return !empty(trim($w)) && !in_array(trim($w), $stopWords);
        });

        return implode(' ', $words);
    }

    protected function tokenize(string $text): array
    {
        $words = explode(' ', $text);
        $terms = [];

        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;

            if (!isset($terms[$word])) {
                $terms[$word] = 0;
            }
            $terms[$word]++;

            for ($i = 2; $i <= 3; $i++) {
                if (strlen($word) >= $i) {
                    for ($j = 0; $j <= strlen($word) - $i; $j++) {
                        $ngram = substr($word, $j, $i);
                        if (!isset($terms[$ngram])) {
                            $terms[$ngram] = 0;
                        }
                        $terms[$ngram] += 0.5;
                    }
                }
            }
        }

        return $terms;
    }

    protected function cosineSimilarity(array $vec1, array $vec2): float
    {
        $allTerms = array_unique(array_merge(array_keys($vec1), array_keys($vec2)));

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($allTerms as $term) {
            $val1 = $vec1[$term] ?? 0;
            $val2 = $vec2[$term] ?? 0;

            $dotProduct += $val1 * $val2;
            $magnitude1 += $val1 * $val1;
            $magnitude2 += $val2 * $val2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}
