<?php

namespace App\Services;

use App\Models\RecurringInvoice;

class RecurringBillingService
{
    public function runScheduledGeneration(): int
    {
        $count = 0;

        $dueInvoices = RecurringInvoice::dueForGeneration()->get();

        foreach ($dueInvoices as $recurring) {
            try {
                $recurring->generateInvoice();
                $count++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $count;
    }

    public function createRecurringInvoice(array $data): RecurringInvoice
    {
        $data['next_run_date'] = $data['next_run_date'] ?? $data['start_date'] ?? now()->toDateString();
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        return RecurringInvoice::create($data);
    }

    public function pause(int $id): void
    {
        RecurringInvoice::findOrFail($id)->update(['status' => 'paused']);
    }

    public function resume(int $id): void
    {
        $recurring = RecurringInvoice::findOrFail($id);

        $nextRunDate = $recurring->next_run_date;

        if (!$nextRunDate || $nextRunDate->isPast()) {
            $nextRunDate = now()->toDateString();
        }

        $recurring->update([
            'status' => 'active',
            'next_run_date' => $nextRunDate,
        ]);
    }

    public function cancel(int $id): void
    {
        RecurringInvoice::findOrFail($id)->update(['status' => 'cancelled']);
    }
}
