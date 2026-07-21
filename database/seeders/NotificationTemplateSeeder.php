<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = 1;

        $templates = [
            [
                'name' => 'Cuti - Permohonan Baru',
                'slug' => 'leave_submitted',
                'channel' => 'in_app',
                'subject' => 'Cuti Baru Diajukan',
                'body' => 'Cuti baru dari {employee_name} perlu persetujuan Anda. Periode: {start_date} s/d {end_date} ({total_days} hari). Tipe: {leave_type}. Alasan: {reason}',
            ],
            [
                'name' => 'Cuti - Disetujui',
                'slug' => 'leave_approved',
                'channel' => 'in_app',
                'subject' => 'Cuti Anda Disetujui',
                'body' => 'Pengajuan cuti Anda untuk periode {start_date} s/d {end_date} ({total_days} hari) telah disetujui oleh {approver_name}.',
            ],
            [
                'name' => 'Cuti - Ditolak',
                'slug' => 'leave_rejected',
                'channel' => 'in_app',
                'subject' => 'Cuti Anda Ditolak',
                'body' => 'Pengajuan cuti Anda untuk periode {start_date} s/d {end_date} ditolak. Alasan: {rejection_reason}',
            ],
            [
                'name' => 'Reimbursement - Pengajuan Baru',
                'slug' => 'reimbursement_submitted',
                'channel' => 'in_app',
                'subject' => 'Reimbursement Baru Diajukan',
                'body' => 'Reimbursement baru dari {employee_name} sebesar Rp {amount} untuk {category}. Diajukan pada {date}.',
            ],
            [
                'name' => 'Reimbursement - Disetujui',
                'slug' => 'reimbursement_approved',
                'channel' => 'in_app',
                'subject' => 'Reimbursement Disetujui',
                'body' => 'Reimbursement Anda sebesar Rp {amount} untuk {category} telah disetujui pada {approved_date}.',
            ],
            [
                'name' => 'Reimbursement - Ditolak',
                'slug' => 'reimbursement_rejected',
                'channel' => 'in_app',
                'subject' => 'Reimbursement Ditolak',
                'body' => 'Reimbursement Anda sebesar Rp {amount} ditolak. Alasan: {rejection_reason}',
            ],
            [
                'name' => 'Lembur - Pengajuan Baru',
                'slug' => 'overtime_submitted',
                'channel' => 'in_app',
                'subject' => 'Lembur Baru Diajukan',
                'body' => 'Pengajuan lembur dari {employee_name} pada {date}. Jam: {start_time} - {end_time} ({duration} menit). Alasan: {reason}',
            ],
            [
                'name' => 'Lembur - Disetujui',
                'slug' => 'overtime_approved',
                'channel' => 'in_app',
                'subject' => 'Lembur Disetujui',
                'body' => 'Pengajuan lembur Anda pada {date} ({duration} menit) telah disetujui.',
            ],
            [
                'name' => 'Lembur - Ditolak',
                'slug' => 'overtime_rejected',
                'channel' => 'in_app',
                'subject' => 'Lembur Ditolak',
                'body' => 'Pengajuan lembur Anda pada {date} ditolak.',
            ],
            [
                'name' => 'PR - Permintaan Baru',
                'slug' => 'pr_submitted',
                'channel' => 'in_app',
                'subject' => 'Purchase Requisition Baru',
                'body' => 'PR #{pr_number} dari departemen {department} membutuhkan persetujuan Anda. Dibutuhkan tanggal: {date_required}. Total item: {item_count}.',
            ],
            [
                'name' => 'PR - Disetujui',
                'slug' => 'pr_approved',
                'channel' => 'in_app',
                'subject' => 'PR Disetujui',
                'body' => 'PR #{pr_number} telah disetujui dan siap diproses menjadi Purchase Order.',
            ],
            [
                'name' => 'PR - Ditolak',
                'slug' => 'pr_rejected',
                'channel' => 'in_app',
                'subject' => 'PR Ditolak',
                'body' => 'PR #{pr_number} ditolak. Alasan: {rejection_reason}',
            ],
            [
                'name' => 'PO - Pesanan Baru',
                'slug' => 'po_submitted',
                'channel' => 'in_app',
                'subject' => 'Purchase Order Baru',
                'body' => 'PO #{po_number} ke supplier {supplier} membutuhkan persetujuan. Total: Rp {total}. Tanggal pengiriman: {expected_date}.',
            ],
            [
                'name' => 'PO - Disetujui',
                'slug' => 'po_approved',
                'channel' => 'in_app',
                'subject' => 'PO Disetujui',
                'body' => 'PO #{po_number} telah disetujui dan siap dikirim ke supplier {supplier}.',
            ],
            [
                'name' => 'PO - Ditolak',
                'slug' => 'po_rejected',
                'channel' => 'in_app',
                'subject' => 'PO Ditolak',
                'body' => 'PO #{po_number} ditolak.',
            ],
            [
                'name' => 'Anggaran - Pengajuan Baru',
                'slug' => 'budget_submitted',
                'channel' => 'in_app',
                'subject' => 'Anggaran Baru Diajukan',
                'body' => 'Anggaran "{name}" untuk tahun fiskal {fiscal_year} dari departemen {department} membutuhkan persetujuan.',
            ],
            [
                'name' => 'Anggaran - Disetujui',
                'slug' => 'budget_approved',
                'channel' => 'in_app',
                'subject' => 'Anggaran Disetujui',
                'body' => 'Anggaran "{name}" untuk tahun fiskal {fiscal_year} telah disetujui.',
            ],
            [
                'name' => 'Invoice - Jatuh Tempo',
                'slug' => 'invoice_overdue',
                'channel' => 'in_app',
                'subject' => 'Invoice Jatuh Tempo',
                'body' => 'Invoice #{invoice_number} jatuh tempo pada {due_date} ({days_overdue} hari lalu). Total: Rp {total}. Segera lakukan pembayaran.',
            ],
            [
                'name' => 'Invoice - Pembayaran Diterima',
                'slug' => 'invoice_paid',
                'channel' => 'in_app',
                'subject' => 'Pembayaran Invoice Diterima',
                'body' => 'Pembayaran untuk invoice #{invoice_number} sebesar Rp {paid_amount} telah diterima. Sisa: Rp {remaining_amount}.',
            ],
            [
                'name' => 'Tiket - Tiket Baru',
                'slug' => 'ticket_created',
                'channel' => 'in_app',
                'subject' => 'Tiket Baru Dibuat',
                'body' => 'Tiket #{ticket_number} dibuat: {subject}. Kategori: {category}. Prioritas: {priority}. Deadline: {due_date}.',
            ],
            [
                'name' => 'Tiket - Ditugaskan',
                'slug' => 'ticket_assigned',
                'channel' => 'in_app',
                'subject' => 'Tiket Ditugaskan ke Anda',
                'body' => 'Tiket #{ticket_number}: {subject} telah ditugaskan kepada Anda. Prioritas: {priority}. Mohon segera ditindaklanjuti.',
            ],
            [
                'name' => 'Tiket - SLA Terlampaui',
                'slug' => 'ticket_sla_breach',
                'channel' => 'in_app',
                'subject' => 'SLA Tiket Terlampaui',
                'body' => 'Tiket #{ticket_number}: {subject} telah melampaui SLA. Segera ditindaklanjuti.',
            ],
            [
                'name' => 'Tiket - Diselesaikan',
                'slug' => 'ticket_resolved',
                'channel' => 'in_app',
                'subject' => 'Tiket Diselesaikan',
                'body' => 'Tiket #{ticket_number}: {subject} telah diselesaikan. Rating kepuasan: {satisfaction_rating}/5.',
            ],
            [
                'name' => 'Approval - Pengingat SLA',
                'slug' => 'approval_sla_reminder',
                'channel' => 'in_app',
                'subject' => 'Pengingat Approval - SLA Mendekati',
                'body' => 'Permintaan approval "{title}" menunggu tindakan Anda. SLA akan berakhir dalam {remaining_hours} jam.',
            ],
            [
                'name' => 'Approval - SLA Terlampaui',
                'slug' => 'approval_sla_breach',
                'channel' => 'in_app',
                'subject' => 'SLA Approval Terlampaui',
                'body' => 'Permintaan approval "{title}" telah melampaui SLA ({sla_hours} jam). Mohon segera ditindaklanjuti.',
            ],
        ];

        foreach ($templates as $template) {
            $template['company_id'] = $companyId;
            $template['is_active'] = true;
            NotificationTemplate::updateOrCreate(
                ['slug' => $template['slug'], 'company_id' => $companyId],
                $template
            );
        }

        $this->command?->info('  Notification Templates: ' . count($templates) . ' seeded.');
    }
}
