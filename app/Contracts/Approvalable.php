<?php

namespace App\Contracts;

interface Approvalable
{
    public function getApprovalModule(): string;

    public function getApprovalTitle(): string;

    public function getApprovalRequesterId(): int;

    public function getApprovalWorkflowName(): string;

    public function onApproved(): void;

    public function onRejected(string $reason): void;
}
