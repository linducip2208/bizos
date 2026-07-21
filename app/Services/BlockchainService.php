<?php

namespace App\Services;

use App\Models\BlockchainBlock;
use App\Models\BlockchainTransaction;
use App\Models\Certificate;
use App\Models\Product;
use App\Models\ProductBlockchainEvent;
use Illuminate\Support\Str;

class BlockchainService
{
    public function notarizeDocument(string $filePath, array $metadata = []): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('File tidak ditemukan: ' . $filePath);
        }

        $documentHash = hash_file('sha256', $filePath);
        $fileName = basename($filePath);

        $existing = BlockchainTransaction::where('document_hash', $documentHash)
            ->where('type', 'document_notarization')
            ->first();

        if ($existing) {
            return [
                'transaction_hash' => $existing->transaction_hash,
                'block_number' => $existing->block?->block_number ?? 0,
                'timestamp' => $existing->timestamped_at->toIso8601String(),
                'document_hash' => $documentHash,
                'already_notarized' => true,
            ];
        }

        $block = $this->generateBlock([
            'type' => 'document_notarization',
            'document_hash' => $documentHash,
            'file_name' => $fileName,
            'metadata' => $metadata,
        ]);

        $transaction = BlockchainTransaction::create([
            'block_id' => $block->id,
            'transaction_hash' => $this->generateTransactionHash($documentHash, $block->block_hash),
            'type' => 'document_notarization',
            'document_hash' => $documentHash,
            'file_name' => $fileName,
            'metadata' => $metadata,
            'timestamped_at' => now(),
            'reference_type' => 'document',
            'reference_id' => 0,
        ]);

        return [
            'transaction_hash' => $transaction->transaction_hash,
            'block_number' => $block->block_number,
            'timestamp' => $transaction->timestamped_at->toIso8601String(),
            'document_hash' => $documentHash,
            'already_notarized' => false,
        ];
    }

    public function verifyDocument(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('File tidak ditemukan: ' . $filePath);
        }

        $currentHash = hash_file('sha256', $filePath);

        $transaction = BlockchainTransaction::where('document_hash', $currentHash)
            ->where('type', 'document_notarization')
            ->with('block')
            ->latest()
            ->first();

        if (!$transaction) {
            $anyTransaction = BlockchainTransaction::where('file_name', basename($filePath))
                ->where('type', 'document_notarization')
                ->with('block')
                ->latest()
                ->first();

            if ($anyTransaction) {
                return [
                    'is_verified' => false,
                    'original_hash' => $anyTransaction->document_hash,
                    'current_hash' => $currentHash,
                    'notarized_at' => $anyTransaction->timestamped_at->toIso8601String(),
                    'tampered' => true,
                    'transaction_hash' => $anyTransaction->transaction_hash,
                ];
            }

            return [
                'is_verified' => false,
                'original_hash' => null,
                'current_hash' => $currentHash,
                'notarized_at' => null,
                'tampered' => false,
                'message' => 'Dokumen belum pernah dinotarisasi di blockchain.',
            ];
        }

        return [
            'is_verified' => true,
            'original_hash' => $transaction->document_hash,
            'current_hash' => $currentHash,
            'notarized_at' => $transaction->timestamped_at->toIso8601String(),
            'tampered' => ($currentHash !== $transaction->document_hash),
            'transaction_hash' => $transaction->transaction_hash,
            'block_number' => $transaction->block?->block_number ?? 0,
        ];
    }

    public function issueBlockchainCertificate(Certificate $certificate): array
    {
        $data = [
            'certificate_number' => $certificate->certificate_number,
            'uuid' => $certificate->uuid,
            'issued_date' => $certificate->issued_date->toDateString(),
            'enrollment_id' => $certificate->enrollment_id,
        ];

        $certHash = hash('sha256', json_encode($data));

        $block = $this->generateBlock([
            'type' => 'certificate_issuance',
            'certificate_hash' => $certHash,
            'certificate_data' => $data,
        ]);

        $transaction = BlockchainTransaction::create([
            'block_id' => $block->id,
            'transaction_hash' => $this->generateTransactionHash($certHash, $block->block_hash),
            'type' => 'certificate_issuance',
            'document_hash' => $certHash,
            'file_name' => $certificate->certificate_number,
            'metadata' => $data,
            'timestamped_at' => now(),
            'reference_type' => Certificate::class,
            'reference_id' => $certificate->id,
        ]);

        return [
            'transaction_hash' => $transaction->transaction_hash,
            'block_number' => $block->block_number,
            'issued_to' => $certificate->enrollment?->employee?->full_name ?? '-',
            'certificate_number' => $certificate->certificate_number,
        ];
    }

    public function verifyCertificate(string $certificateUuid): array
    {
        $certificate = Certificate::where('uuid', $certificateUuid)->first();

        if (!$certificate) {
            return [
                'is_valid' => false,
                'message' => 'Sertifikat tidak ditemukan.',
            ];
        }

        $data = [
            'certificate_number' => $certificate->certificate_number,
            'uuid' => $certificate->uuid,
            'issued_date' => $certificate->issued_date->toDateString(),
            'enrollment_id' => $certificate->enrollment_id,
        ];

        $certHash = hash('sha256', json_encode($data));

        $transaction = BlockchainTransaction::where('document_hash', $certHash)
            ->where('type', 'certificate_issuance')
            ->where('reference_type', Certificate::class)
            ->where('reference_id', $certificate->id)
            ->with('block')
            ->first();

        return [
            'is_valid' => (bool) $transaction,
            'issued_to' => $certificate->enrollment?->employee?->full_name ?? '-',
            'issued_date' => $certificate->issued_date->toDateString(),
            'course' => $certificate->enrollment?->course?->title ?? '-',
            'blockchain_tx' => $transaction?->transaction_hash,
            'block_number' => $transaction?->block?->block_number ?? 0,
            'certificate_number' => $certificate->certificate_number,
        ];
    }

    public function recordProductEvent(Product $product, string $event, array $data = []): array
    {
        $validEvents = ['manufactured', 'qc_passed', 'shipped', 'received', 'sold', 'returned'];

        if (!in_array($event, $validEvents)) {
            throw new \RuntimeException('Event tidak valid. Pilihan: ' . implode(', ', $validEvents));
        }

        $eventHash = hash('sha256', json_encode([
            'product_id' => $product->id,
            'product_code' => $product->code,
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]));

        $block = $this->generateBlock([
            'type' => 'supply_chain_event',
            'product_id' => $product->id,
            'event' => $event,
            'event_hash' => $eventHash,
            'data' => $data,
        ]);

        $transaction = BlockchainTransaction::create([
            'block_id' => $block->id,
            'transaction_hash' => $this->generateTransactionHash($eventHash, $block->block_hash),
            'type' => 'supply_chain_event',
            'document_hash' => $eventHash,
            'metadata' => array_merge($data, ['event' => $event, 'product_code' => $product->code]),
            'timestamped_at' => now(),
            'reference_type' => Product::class,
            'reference_id' => $product->id,
        ]);

        $productEvent = ProductBlockchainEvent::create([
            'company_id' => $product->company_id,
            'product_id' => $product->id,
            'transaction_id' => $transaction->id,
            'event_type' => $event,
            'event_data' => $data,
            'location' => $data['location'] ?? null,
            'actor_name' => $data['actor_name'] ?? auth()->user()?->name ?? 'System',
            'document_hash' => $eventHash,
            'block_number' => $block->block_number,
            'recorded_at' => now(),
        ]);

        return [
            'transaction_hash' => $transaction->transaction_hash,
            'block_number' => $block->block_number,
            'product_code' => $product->code,
            'event' => $event,
            'product_event_id' => $productEvent->id,
        ];
    }

    public function getProductJourney(Product $product): array
    {
        return ProductBlockchainEvent::where('product_id', $product->id)
            ->orderBy('recorded_at')
            ->get()
            ->map(fn($event) => [
                'event_type' => $event->event_type,
                'location' => $event->location,
                'actor' => $event->actor_name,
                'timestamp' => $event->recorded_at->toIso8601String(),
                'block_number' => $event->block_number,
                'transaction_hash' => $event->transaction?->transaction_hash,
                'data' => $event->event_data,
            ])
            ->toArray();
    }

    public function createSmartContract(array $terms): array
    {
        $contractHash = hash('sha256', json_encode($terms));

        $block = $this->generateBlock([
            'type' => 'smart_contract',
            'terms' => $terms,
            'contract_hash' => $contractHash,
        ]);

        $transaction = BlockchainTransaction::create([
            'block_id' => $block->id,
            'transaction_hash' => $this->generateTransactionHash($contractHash, $block->block_hash),
            'type' => 'smart_contract',
            'document_hash' => $contractHash,
            'file_name' => $terms['name'] ?? 'Smart Contract',
            'metadata' => $terms,
            'timestamped_at' => now(),
            'reference_type' => 'smart_contract',
            'reference_id' => 0,
        ]);

        return [
            'contract_id' => $transaction->transaction_hash,
            'transaction_hash' => $transaction->transaction_hash,
            'block_number' => $block->block_number,
            'status' => 'active',
            'parties' => $terms['parties'] ?? [],
        ];
    }

    public function executeSmartContract(string $contractId): array
    {
        $transaction = BlockchainTransaction::where('transaction_hash', $contractId)
            ->where('type', 'smart_contract')
            ->first();

        if (!$transaction) {
            return ['status' => 'not_found', 'message' => 'Smart contract tidak ditemukan.'];
        }

        $terms = $transaction->metadata ?? [];
        $conditions = $terms['conditions'] ?? [];
        $actions = $terms['actions'] ?? [];
        $met = true;

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition)) {
                $met = false;
                break;
            }
        }

        if ($met) {
            $results = [];
            foreach ($actions as $action) {
                $results[] = $this->executeAction($action);
            }

            return [
                'status' => 'executed',
                'contract_id' => $contractId,
                'results' => $results,
                'executed_at' => now()->toIso8601String(),
            ];
        }

        return [
            'status' => 'conditions_not_met',
            'contract_id' => $contractId,
            'message' => 'Kondisi smart contract belum terpenuhi.',
        ];
    }

    protected function evaluateCondition(array $condition): bool
    {
        return true;
    }

    protected function executeAction(array $action): array
    {
        return [
            'action' => $action['type'] ?? 'unknown',
            'status' => 'simulated',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function generateBlock(array $data): BlockchainBlock
    {
        $lastBlock = BlockchainBlock::orderBy('block_number', 'desc')->first();

        $blockNumber = $lastBlock ? $lastBlock->block_number + 1 : 1;
        $previousHash = $lastBlock ? $lastBlock->block_hash : str_repeat('0', 64);
        $nonce = random_int(0, 999999);

        $blockHash = $this->mineBlock($blockNumber, $previousHash, $data, $nonce);

        $block = BlockchainBlock::create([
            'block_number' => $blockNumber,
            'previous_hash' => $previousHash,
            'block_hash' => $blockHash,
            'data' => $data,
            'nonce' => $nonce,
            'mined_at' => now(),
        ]);

        if (!$this->validateChain()) {
            \Log::warning('Blockchain integrity check failed after block ' . $blockNumber);
        }

        return $block;
    }

    protected function mineBlock(int $blockNumber, string $previousHash, array $data, int $nonce): string
    {
        $payload = json_encode([
            'block_number' => $blockNumber,
            'previous_hash' => $previousHash,
            'data' => $data,
            'nonce' => $nonce,
            'timestamp' => now()->toIso8601String(),
        ]);

        return hash('sha256', $payload);
    }

    public function validateChain(): bool
    {
        $blocks = BlockchainBlock::orderBy('block_number')->get();

        for ($i = 1; $i < $blocks->count(); $i++) {
            $current = $blocks[$i];
            $previous = $blocks[$i - 1];

            $expectedHash = $this->mineBlock(
                $current->block_number,
                $previous->block_hash,
                $current->data,
                $current->nonce
            );

            if ($current->previous_hash !== $previous->block_hash) {
                return false;
            }

            if ($current->block_hash !== $expectedHash) {
                return false;
            }
        }

        return true;
    }

    protected function generateTransactionHash(string $documentHash, string $blockHash): string
    {
        return hash('sha256', $documentHash . $blockHash . Str::random(16) . microtime(true));
    }

    public function getBlockchainStats(): array
    {
        return [
            'total_blocks' => BlockchainBlock::count(),
            'total_transactions' => BlockchainTransaction::count(),
            'latest_block' => BlockchainBlock::max('block_number') ?? 0,
            'chain_valid' => $this->validateChain(),
            'transactions_by_type' => [
                'document_notarization' => BlockchainTransaction::where('type', 'document_notarization')->count(),
                'certificate_issuance' => BlockchainTransaction::where('type', 'certificate_issuance')->count(),
                'smart_contract' => BlockchainTransaction::where('type', 'smart_contract')->count(),
                'supply_chain_event' => BlockchainTransaction::where('type', 'supply_chain_event')->count(),
            ],
            'total_product_events' => ProductBlockchainEvent::count(),
        ];
    }
}
