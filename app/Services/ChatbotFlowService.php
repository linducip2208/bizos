<?php

namespace App\Services;

use App\Models\ChatbotFlow;
use App\Models\ChatbotFlowNode;
use App\Models\ChatbotFlowEdge;
use App\Models\WaConversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChatbotFlowService
{
    protected ?ChatbotFlow $flow = null;
    protected array $context = [];
    protected const INTENT_PATTERNS = [
        'salam_pembuka' => '/\b(halo|hai|hallo|assalam|selamat\s?(pagi|siang|sore|malam)|hi|bro|gan)\b/i',
        'harga' => '/\b(harga|biaya|berapa|mahal|murah|tarif|ongkos|fee)\b/i',
        'produk' => '/\b(produk|barang|beli|pesan|order|stok|katalog|list|daftar)\b/i',
        'keluhan' => '/\b(komplain|keluhan|rusak|jelek|kecewa|buruk|masalah|error|gangguan)\b/i',
        'order_status' => '/\b(status|tracking|lacak|dimana|sampai|kirim|pengiriman|resi)\b/i',
        'jam_operasional' => '/\b(jam|buka|tutup|operasional|jadwal|hari|libur)\b/i',
        'alamat' => '/\b(alamat|lokasi|maps|google\s?maps|tempat|dimana|arah|petunjuk)\b/i',
        'pembayaran' => '/\b(bayar|transfer|pembayaran|dp|dp|angsuran|cicil|bank|rekening|qris)\b/i',
        'bantuan' => '/\b(bantuan|help|tolong|butuh|help|info|informasi|tanya|nanya)\b/i',
        'terima_kasih' => '/\b(terima\s?kasih|makasih|thanks|thx|tq)\b/i',
    ];

    protected const NODE_TYPES = [
        'send_message' => 'Kirim Pesan',
        'wait_for_reply' => 'Tunggu Balasan',
        'check_keyword' => 'Cek Kata Kunci',
        'check_intent' => 'Cek Intent',
        'update_lead' => 'Update Lead',
        'create_ticket' => 'Buat Tiket',
        'send_template' => 'Kirim Template WA',
        'transfer_to_agent' => 'Transfer ke Agent',
        'end_conversation' => 'Akhiri Percakapan',
    ];

    public function __construct(?int $flowId = null)
    {
        if ($flowId) {
            $this->flow = ChatbotFlow::with(['nodes', 'edges'])->find($flowId);
        }
    }

    // ─── Flow CRUD ────────────────────────────────────────────────────

    public function createFlow(string $name, array $nodes, array $edges, ?int $companyId = null): ChatbotFlow
    {
        $flow = ChatbotFlow::create([
            'company_id' => $companyId ?? auth()->user()?->company_id,
            'name' => $name,
            'welcome_message' => 'Halo! Selamat datang di layanan chat kami. Ada yang bisa kami bantu?',
            'fallback_message' => 'Maaf, kami tidak mengerti. Ketik "bantuan" untuk opsi yang tersedia.',
            'is_active' => false,
            'is_published' => false,
        ]);

        $nodeIdMap = [];
        foreach ($nodes as $nodeData) {
            $node = ChatbotFlowNode::create([
                'flow_id' => $flow->id,
                'type' => $nodeData['type'] ?? 'send_message',
                'label' => $nodeData['label'] ?? null,
                'config' => $nodeData['config'] ?? [],
                'position_x' => $nodeData['position_x'] ?? 0,
                'position_y' => $nodeData['position_y'] ?? 0,
            ]);
            $nodeIdMap[$nodeData['id'] ?? $nodeData['temp_id']] = $node->id;
        }

        foreach ($edges as $edgeData) {
            ChatbotFlowEdge::create([
                'flow_id' => $flow->id,
                'source_node_id' => $nodeIdMap[$edgeData['source']] ?? $edgeData['source'],
                'target_node_id' => $nodeIdMap[$edgeData['target']] ?? $edgeData['target'],
                'condition' => $edgeData['condition'] ?? null,
                'label' => $edgeData['label'] ?? null,
            ]);
        }

        return $flow->fresh(['nodes', 'edges']);
    }

    public function updateFlow(int $flowId, array $data): ChatbotFlow
    {
        $flow = ChatbotFlow::findOrFail($flowId);

        if (isset($data['name'])) {
            $flow->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $flow->description,
                'trigger_keywords' => $data['trigger_keywords'] ?? $flow->trigger_keywords,
                'welcome_message' => $data['welcome_message'] ?? $flow->welcome_message,
                'fallback_message' => $data['fallback_message'] ?? $flow->fallback_message,
            ]);
        }

        if (isset($data['nodes'])) {
            $flow->nodes()->delete();
            $flow->edges()->delete();

            return $this->createFlow($data['name'] ?? $flow->name, $data['nodes'], $data['edges'] ?? []);
        }

        return $flow->fresh(['nodes', 'edges']);
    }

    public function deleteFlow(int $flowId): void
    {
        $flow = ChatbotFlow::findOrFail($flowId);
        $flow->nodes()->delete();
        $flow->edges()->delete();
        $flow->delete();
    }

    // ─── Message Processing ───────────────────────────────────────────

    public function processMessage(WaConversation $conversation, string $message): array
    {
        $this->flow = $conversation->flow;

        if (!$this->flow) {
            $matchingFlow = $this->findMatchingFlow($conversation, $message);
            if ($matchingFlow) {
                $this->flow = $matchingFlow;
                $conversation->activateBot($this->flow->id);
            } else {
                return ['reply' => '', 'action' => 'no_flow', 'updateState' => []];
            }
        }

        $currentNodeId = $conversation->getCurrentFlowNodeId();
        $currentNode = $currentNodeId
            ? ChatbotFlowNode::find($currentNodeId)
            : $this->getStartNode();

        if (!$currentNode) {
            $reply = $this->flow->welcome_message ?? 'Halo! Ada yang bisa kami bantu?';
            return $this->buildReply($reply, 'send_message', [], $conversation);
        }

        $result = $this->executeNode($currentNode, $message, $conversation);

        return $result;
    }

    protected function findMatchingFlow(WaConversation $conversation, string $message): ?ChatbotFlow
    {
        $flows = ChatbotFlow::where('is_active', true)
            ->whereNotNull('trigger_keywords')
            ->get();

        foreach ($flows as $flow) {
            $keywords = $flow->trigger_keywords ?? [];
            foreach ($keywords as $keyword) {
                if (stripos($message, $keyword) !== false) {
                    return $flow;
                }
            }
        }

        $intent = $this->detectIntent($message);
        if ($intent) {
            $conversation->update([
                'chatbot_intent' => $intent['intent'],
                'chatbot_confidence' => $intent['confidence'],
            ]);
        }

        return null;
    }

    protected function getStartNode(): ?ChatbotFlowNode
    {
        if (!$this->flow) return null;

        $startNodes = $this->flow->nodes()
            ->where('type', 'send_message')
            ->orderBy('id')
            ->first();

        return $startNodes ?: $this->flow->nodes()->first();
    }

    protected function executeNode(ChatbotFlowNode $node, string $message, WaConversation $conversation): array
    {
        $config = $node->config ?? [];
        $reply = '';
        $action = $node->type;
        $updateState = [];

        switch ($node->type) {
            case 'send_message':
                $reply = $config['text'] ?? 'Ada yang bisa kami bantu?';
                if (!empty($config['delay_seconds'])) {
                    sleep(min((int) $config['delay_seconds'], 3));
                }
                break;

            case 'wait_for_reply':
                $conversation->setFlowState('current_node_id', $node->id);
                $conversation->setFlowState('waiting_for', $config['variable'] ?? 'input');
                $reply = $config['prompt'] ?? 'Silakan ketik jawaban Anda.';
                break;

            case 'check_keyword':
                $keywords = $config['keywords'] ?? [];
                foreach ($keywords as $keyword) {
                    if (stripos($message, $keyword) !== false) {
                        $nextNode = $node->getNextNodeForCondition($keyword);
                        if ($nextNode) {
                            $conversation->setFlowState('current_node_id', $nextNode->id);
                            return $this->executeNode($nextNode, $message, $conversation);
                        }
                    }
                }
                $fallbackNode = $node->getNextNodeForCondition('_default');
                if ($fallbackNode) {
                    $conversation->setFlowState('current_node_id', $fallbackNode->id);
                    return $this->executeNode($fallbackNode, $message, $conversation);
                }
                $reply = $this->flow->fallback_message ?? 'Maaf, pilihan tidak dikenali.';
                break;

            case 'check_intent':
                $intent = $this->detectIntent($message);
                if ($intent) {
                    $conversation->update([
                        'chatbot_intent' => $intent['intent'],
                        'chatbot_confidence' => $intent['confidence'],
                    ]);

                    $nextNode = $node->getNextNodeForCondition($intent['intent']);
                    if ($nextNode) {
                        $conversation->setFlowState('current_node_id', $nextNode->id);
                        return $this->executeNode($nextNode, $message, $conversation);
                    }
                }
                $defaultNode = $node->getNextNodeForCondition('_default');
                if ($defaultNode) {
                    $conversation->setFlowState('current_node_id', $defaultNode->id);
                    return $this->executeNode($defaultNode, $message, $conversation);
                }
                $reply = $this->flow->fallback_message ?? 'Maaf, maksud Anda kurang jelas. Bisa dijelaskan lebih detail?';
                break;

            case 'update_lead':
                try {
                    $leadData = [];
                    $flowState = $conversation->flow_state ?? [];
                    $savedData = $flowState['data'] ?? [];

                    if (!empty($config['fields'])) {
                        foreach ($config['fields'] as $field) {
                            if (isset($savedData[$field])) {
                                $leadData[$field] = $savedData[$field];
                            }
                        }
                    }

                    if (!empty($config['source_phone'])) {
                        $leadData['phone'] = $conversation->contact_phone;
                    }
                    if (!empty($config['source_name'])) {
                        $leadData['name'] = $conversation->contact_name;
                    }

                    if (!empty($leadData)) {
                        $lead = \App\Models\Lead::updateOrCreate(
                            ['phone' => $conversation->contact_phone, 'company_id' => $conversation->company_id],
                            $leadData
                        );
                        $updateState['lead_id'] = $lead->id;
                    }

                    $reply = $config['success_message'] ?? 'Data berhasil disimpan.';
                    $action = 'update_lead';
                } catch (\Exception $e) {
                    Log::error('Chatbot update_lead error', ['error' => $e->getMessage()]);
                    $reply = 'Maaf, terjadi kesalahan saat menyimpan data.';
                }
                break;

            case 'create_ticket':
                try {
                    $ticket = \App\Models\Ticket::create([
                        'company_id' => $conversation->company_id,
                        'title' => $config['title_prefix'] ?? 'Pertanyaan via WA',
                        'subject' => $conversation->contact_phone . ' - ' . ($conversation->contact_name ?? 'Unknown'),
                        'description' => $message,
                        'status' => 'open',
                        'priority' => $config['priority'] ?? 'medium',
                        'source' => 'whatsapp',
                        'contact_phone' => $conversation->contact_phone,
                        'contact_name' => $conversation->contact_name,
                    ]);
                    $updateState['ticket_id'] = $ticket->id;
                    $reply = $config['success_message'] ?? 'Tiket support telah dibuat. Tim kami akan segera menghubungi Anda. Nomor tiket: #' . $ticket->id;
                    $action = 'create_ticket';
                } catch (\Exception $e) {
                    Log::error('Chatbot create_ticket error', ['error' => $e->getMessage()]);
                    $reply = 'Maaf, gagal membuat tiket support.';
                }
                break;

            case 'send_template':
                $templateName = $config['template_name'] ?? '';
                $language = $config['language'] ?? 'id';
                $parameters = $config['parameters'] ?? [];

                if ($templateName) {
                    try {
                        $waService = app(WhatsappBusinessService::class);
                        $waService->sendTemplateMessage($conversation->contact_phone, $templateName, $language, $parameters);
                    } catch (\Exception $e) {
                        Log::error('Chatbot send_template error', ['error' => $e->getMessage()]);
                    }
                }
                $reply = $config['fallback_message'] ?? 'Terima kasih, informasi sudah kami kirimkan.';
                break;

            case 'transfer_to_agent':
                $conversation->deactivateBot();
                $reply = $config['message'] ?? 'Mohon tunggu sebentar, Anda akan segera terhubung dengan tim kami.';
                $action = 'transfer_to_agent';
                $updateState['agent_transferred'] = true;
                break;

            case 'end_conversation':
                $conversation->deactivateBot();
                $conversation->update(['status' => 'selesai']);
                $reply = $config['message'] ?? 'Terima kasih telah menghubungi kami. Percakapan ini telah selesai.';
                $action = 'end_conversation';
                $updateState['conversation_ended'] = true;
                break;

            default:
                $reply = 'Pesan diterima. Tim kami akan segera merespon.';
                break;
        }

        // Find and transition to next node if available
        $nextNodes = $node->getNextNodes();
        if (!empty($nextNodes) && $node->type !== 'check_keyword' && $node->type !== 'check_intent'
            && $node->type !== 'wait_for_reply' && $node->type !== 'end_conversation') {
            $nextNode = $nextNodes[0] ?? null;
            if ($nextNode) {
                $conversation->setFlowState('current_node_id', $nextNode->id);
            }
        }

        if ($reply && !empty($reply)) {
            try {
                $waService = app(WhatsappBusinessService::class);
                $waService->sendTextMessage($conversation->contact_phone, $reply);
                $conversation->addToFlowHistory('bot', $reply);
            } catch (\Exception $e) {
                Log::error('ChatbotFlow send reply error', ['error' => $e->getMessage()]);
            }
        }

        return $this->buildReply($reply, $action, $updateState, $conversation);
    }

    protected function buildReply(string $reply, string $action, array $updateState, WaConversation $conversation): array
    {
        return [
            'reply' => $reply,
            'action' => $action,
            'updateState' => array_merge($updateState, [
                'current_node_id' => $conversation->getCurrentFlowNodeId(),
                'flow_id' => $this->flow?->id,
            ]),
        ];
    }

    // ─── NLP Intent Detection ─────────────────────────────────────────

    public function detectIntent(string $message): ?array
    {
        foreach (self::INTENT_PATTERNS as $intent => $pattern) {
            if (preg_match($pattern, $message)) {
                $confidence = 0.80;
                $matches = [];
                preg_match_all($pattern, $message, $matches);
                $matchCount = count($matches[0] ?? []);
                $confidence = min(0.95, 0.80 + ($matchCount * 0.05));

                return ['intent' => $intent, 'confidence' => round($confidence, 2)];
            }
        }

        return null;
    }

    public function getNodeTypes(): array
    {
        return self::NODE_TYPES;
    }

    // ─── Flow Testing ─────────────────────────────────────────────────

    public function simulateFlow(int $flowId, string $message, array $state = []): array
    {
        $this->flow = ChatbotFlow::with(['nodes', 'edges'])->findOrFail($flowId);

        $conversation = new WaConversation([
            'flow_id' => $flowId,
            'flow_state' => $state,
            'is_bot_active' => true,
            'contact_phone' => '6280000000000',
            'contact_name' => 'Simulator',
        ]);

        $result = $this->processMessage($conversation, $message);

        return array_merge($result, [
            'flow_name' => $this->flow->name,
            'total_nodes' => $this->flow->nodes->count(),
            'total_edges' => $this->flow->edges->count(),
        ]);
    }

    // ─── Quick Setup ───────────────────────────────────────────────────

    public function createQuickReplyFlow(int $companyId, string $name, array $replies): ChatbotFlow
    {
        $nodes = [['temp_id' => 'start', 'type' => 'check_keyword', 'config' => ['keywords' => array_keys($replies)], 'position_x' => 250, 'position_y' => 50]];
        $edges = [];

        foreach ($replies as $keyword => $replyText) {
            $nodeId = 'reply_' . crc32($keyword);
            $nodes[] = ['temp_id' => $nodeId, 'type' => 'send_message', 'config' => ['text' => $replyText], 'position_x' => 250, 'position_y' => 50 + (count($nodes) * 130)];
            $edges[] = ['source' => 'start', 'target' => $nodeId, 'condition' => ['match' => $keyword]];
        }

        return $this->createFlow($name, $nodes, $edges, $companyId);
    }
}
