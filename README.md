# BizOS — Enterprise Business Operating System

ERP all-in-one buatan Indonesia: HRM, Payroll, Finance, CRM, POS, Project, LMS, AI, dan 9+ industry verticals.

## Quick Start

```bash
git clone https://github.com/linducip2208/bizos.git
cd bizos
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Buka `http://127.0.0.1:8000/admin`

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| **Super Admin** (Direktur Utama) | `budi@maju.test` | `password` |
| **Admin HR** | `siti@maju.test` | `password` |
| **Admin Finance** | `ahmad@maju.test` | `password` |
| **Manager Sales** | `andi@maju.test` | `password` |
| **Manager IT** | `donni@maju.test` | `password` |
| **Staff** | `dewi@maju.test` | `password` |
| **Staff** | `rudi@maju.test` | `password` |

Semua karyawan: `firstname@maju.test / password`

## Tech Stack

- **Backend:** Laravel 11 + Filament v3
- **Database:** MySQL
- **UI:** TailwindCSS + Chart.js + Leaflet

## Modules

| Module | Fitur |
|--------|-------|
| Master Data | Perusahaan, Cabang, Departemen, Jabatan, Karyawan |
| HRM | Absensi GPS/WiFi, Cuti, Lembur Kepmen 102/2004, Reimbursement, Rekrutmen, Feedback 360° |
| Payroll | PPh21 TER PMK 168/2023, BPJS PP 2024, THR Pro-rata, Slip Gaji |
| Finance | COA, Jurnal, Invoice, Pembayaran, Budget, Aset, Pajak, Bank Reconciliation, Multi-Currency |
| CRM | Lead, Pipeline, Deal, WA Blast, Auto-Reply, Chatbot |
| POS | Produk, Transaksi, Member, Loyalty, QRIS, Split Payment |
| Project | Gantt, Kanban, EVM, Sprint, Timesheet |
| Procurement | PR→RFQ→PO→GRN, QC, ABC, FEFO, Landed Cost |
| LMS | Kursus, Kuis, Sertifikat, Anti-Cheating |
| AI Assistant | BYOK, RAG Q&A, AI-Write, OCR, Document Classifier |
| + 9 Industry Verticals | Manufaktur, Kesehatan, Konstruksi, Perhotelan, Properti, Logistik, E-Commerce, Field Service, ESG |
