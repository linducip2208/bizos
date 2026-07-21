# BizOS — Architecture Documentation

## System Overview

BizOS adalah Business Operating System all-in-one: HRM, Accounting, CRM, Project Management, POS, AI Assistant, Collaboration — 150+ fitur dalam satu platform SaaS multi-company.

---

## 1. Entity Relationship Diagram (ERD)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              MASTER DATA                                         │
│  ┌──────────┐  ┌───────────┐  ┌──────────┐  ┌───────────┐  ┌──────────────┐   │
│  │companies │  │departments│  │positions │  │  shifts   │  │  holidays    │   │
│  └────┬─────┘  └─────┬─────┘  └────┬─────┘  └─────┬─────┘  └──────────────┘   │
│       │              │             │              │                             │
│  ┌────┴──────┐ ┌─────┴──────┐ ┌───┴──────┐  ┌────┴──────┐                     │
│  │ branches  │ │ designations│ │  grades  │  │ shift_emps│                     │
│  └────┬──────┘ └────────────┘ └──────────┘  └───────────┘                     │
│       │                                                                         │
│  ┌────┴───────────────────────────────────────────────────────────┐            │
│  │                        employees                               │            │
│  │  (belongsTo: company, branch, department, position, grade,     │            │
│  │   designation, shift; hasOne: user; hasMany: attendances,     │            │
│  │   leaves, overtimes, reimbursements, payrolls, assets,...)     │            │
│  └────┬───────────────────────────────────────────────────────────┘            │
│       │                                                                         │
│  ┌────┴──────┐  ┌────────────┐  ┌────────────┐  ┌─────────────┐               │
│  │  users    │  │ bank_accounts│  │family_members│ │ employee_docs│              │
│  └───────────┘  └────────────┘  └────────────┘  └─────────────┘               │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              HRM — ATTENDANCE                                    │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐         │
│  │attendances│  │attendance_logs│  │attendance_rules│  │attendance_config│        │
│  └────┬─────┘  └──────┬───────┘  └───────┬───────┘  └────────────────┘         │
│       │               │                  │                                       │
│  ┌────┴────┐    ┌─────┴──────┐    ┌─────┴──────┐                               │
│  │wifi_aps │    │gps_locations│   │late_config │                               │
│  └─────────┘    └────────────┘    └────────────┘                               │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                              HRM — LEAVE & OVERTIME                             │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐         │
│  │  leaves  │  │ leave_types   │  │ leave_balances │  │ leave_approvals│         │
│  └──────────┘  └──────────────┘  └───────────────┘  └────────────────┘         │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐                              │
│  │overtimes │  │ overtime_rates│  │overtime_approvals│                           │
│  └──────────┘  └──────────────┘  └───────────────┘                              │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         HRM — REIMBURSEMENT & VISIT                             │
│  ┌───────────────┐  ┌──────────────┐  ┌──────────────────┐                     │
│  │reimbursements │  │reimb_categories│  │reimb_attachments │                     │
│  └───────┬───────┘  └──────────────┘  └──────────────────┘                     │
│          │                                                                       │
│  ┌───────┴──────┐   ┌───────────────┐                                           │
│  │reimb_approvals│  │    visits      │                                           │
│  └──────────────┘   └───────────────┘                                           │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         HRM — PAYROLL                                           │
│  ┌──────────┐  ┌─────────────┐  ┌──────────────┐  ┌───────────────┐            │
│  │ payrolls │  │payroll_items │  │salary_components│ │payroll_periods│           │
│  └────┬─────┘  └──────┬──────┘  └──────┬───────┘  └───────────────┘            │
│       │               │                │                                         │
│  ┌────┴────┐    ┌─────┴──────┐   ┌────┴────────┐                               │
│  │pay_slips│    │pph21_config │   │bpjs_config   │                              │
│  └─────────┘    └────────────┘   └──────────────┘                              │
│  ┌────────────┐  ┌────────────┐   ┌──────────────┐                             │
│  │thr_config  │  │bonus_config│   │payroll_banks  │                             │
│  └────────────┘  └────────────┘   └──────────────┘                             │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                      HRM — RECRUITMENT & PERFORMANCE                            │
│  ┌──────────────┐  ┌────────────┐  ┌──────────────┐  ┌──────────────────┐      │
│  │recruitments  │  │candidates  │  │interviews     │  │interview_results │      │
│  └──────┬───────┘  └─────┬──────┘  └──────┬───────┘  └──────────────────┘      │
│         │                │                │                                       │
│  ┌──────┴──────┐   ┌────┴──────┐   ┌─────┴───────┐                             │
│  │job_postings │   │candidate_docs│ │interviewers │                             │
│  └─────────────┘   └───────────┘   └─────────────┘                             │
│                                                                                  │
│  ┌──────────────┐  ┌───────────────┐  ┌───────────────┐                        │
│  │feedbacks_360 │  │feedback_questions│ │feedback_answers│                      │
│  └──────┬───────┘  └───────────────┘  └───────────────┘                        │
│         │                                                                        │
│  ┌──────┴──────────┐                                                            │
│  │feedback_reviewers│                                                           │
│  └─────────────────┘                                                            │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         HRM — CANTEEN & ANNOUNCEMENTS                           │
│  ┌──────────┐  ┌──────────────┐  ┌────────────────┐  ┌────────────────┐        │
│  │canteen_menu│ │canteen_orders│  │canteen_order_items│ │canteen_balances│       │
│  └──────────┘  └──────────────┘  └────────────────┘  └────────────────┘        │
│                                                                                  │
│  ┌──────────────┐  ┌─────────────────┐                                          │
│  │announcements │  │announcement_reads│                                         │
│  └──────┬───────┘  └─────────────────┘                                          │
│         │                                                                        │
│  ┌──────┴──────────┐                                                            │
│  │announcement_tags│                                                            │
│  └─────────────────┘                                                            │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         ACCOUNTING (FINANCE)                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐                          │
│  │     coa      │  │coa_categories│  │    journals    │                          │
│  └──────┬───────┘  └──────────────┘  └───────┬───────┘                          │
│         │                                    │                                    │
│  ┌──────┴──────┐                       ┌─────┴──────────┐                       │
│  │coa_balances │                       │journal_entries  │                       │
│  └─────────────┘                       └─────┬──────────┘                       │
│                                             │                                     │
│  ┌───────────┐  ┌───────────┐  ┌───────────┴──────────┐                        │
│  │ invoices  │  │invoice_items│ │  general_ledger      │                        │
│  └─────┬─────┘  └───────────┘  └──────────────────────┘                        │
│        │                                                                         │
│  ┌─────┴──────┐  ┌─────────────┐  ┌──────────────┐                              │
│  │payments    │  │payment_methods│ │payment_terms │                              │
│  └────────────┘  └─────────────┘  └──────────────┘                              │
│                                                                                  │
│  ┌───────────┐  ┌──────────────┐  ┌───────────────┐                             │
│  │ budgets   │  │budget_items  │  │budget_realizations│                          │
│  └───────────┘  └──────────────┘  └───────────────┘                             │
│                                                                                  │
│  ┌───────────┐  ┌──────────────┐  ┌───────────────┐                             │
│  │   taxes   │  │tax_configs   │  │ tax_transactions│                            │
│  └───────────┘  └──────────────┘  └───────────────┘                             │
│                                                                                  │
│  ┌───────────┐  ┌───────────────┐  ┌────────────────┐                           │
│  │  assets   │  │asset_categories│  │asset_depreciations│                         │
│  └─────┬─────┘  └───────────────┘  └────────────────┘                           │
│        │                                                                         │
│  ┌─────┴──────┐  ┌───────────────┐  ┌──────────────────┐                       │
│  │asset_mutations│ │asset_maintenances│ │asset_assignments │                      │
│  └────────────┘  └───────────────┘  └──────────────────┘                       │
│                                                                                  │
│  ┌──────────────┐  ┌─────────────────┐                                         │
│  │approval_flows│  │approval_flow_steps│                                        │
│  └──────────────┘  └─────────────────┘                                         │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         CRM                                                     │
│  ┌───────────┐  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐        │
│  │leads      │  │lead_sources  │  │  lead_scores  │  │lead_activities │        │
│  └─────┬─────┘  └──────────────┘  └───────────────┘  └────────────────┘        │
│        │                                                                         │
│  ┌─────┴──────┐  ┌──────────────┐  ┌───────────────┐                            │
│  │  clients   │  │client_contacts│  │client_histories│                          │
│  └─────┬─────┘  └──────────────┘  └───────────────┘                            │
│        │                                                                         │
│  ┌─────┴──────┐  ┌──────────────┐  ┌───────────────┐                            │
│  │   deals    │  │deal_stages   │  │ deal_products  │                            │
│  └─────┬─────┘  └──────────────┘  └───────────────┘                            │
│        │                                                                         │
│  ┌─────┴──────┐  ┌──────────────┐  ┌───────────────┐                            │
│  │activities  │  │activity_types│  │client_segments │                            │
│  └────────────┘  └──────────────┘  └───────────────┘                            │
│                                                                                  │
│  ┌───────────────┐  ┌─────────────────┐  ┌──────────────┐                       │
│  │wa_templates   │  │wa_blast_campaigns│  │wa_blast_logs │                       │
│  └───────────────┘  └─────────────────┘  └──────────────┘                       │
│  ┌───────────────┐  ┌──────────────────┐                                        │
│  │wa_auto_replies│  │wa_conversations  │                                        │
│  └───────────────┘  └──────────────────┘                                        │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                    PROJECT MANAGEMENT                                            │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────┐  ┌────────────────┐          │
│  │ projects │  │project_phases│  │project_members│  │project_clients │          │
│  └────┬─────┘  └──────────────┘  └──────────────┘  └────────────────┘          │
│       │                                                                          │
│  ┌────┴────┐  ┌──────────┐  ┌───────────────┐  ┌─────────────────┐             │
│  │  tasks  │  │task_labels│  │task_assignees │  │task_dependencies│             │
│  └────┬────┘  └──────────┘  └───────────────┘  └─────────────────┘             │
│       │                                                                          │
│  ┌────┴──────┐  ┌─────────────┐  ┌───────────────┐                              │
│  │task_comments│ │task_attachments│ │  task_activities│                            │
│  └───────────┘  └─────────────┘  └───────────────┘                              │
│                                                                                  │
│  ┌──────────────┐  ┌───────────────┐  ┌───────────────┐                         │
│  │  timesheets  │  │timesheet_tasks│  │  milestones   │                         │
│  └──────┬───────┘  └───────────────┘  └───────┬───────┘                         │
│         │                                     │                                   │
│  ┌──────┴──────┐                        ┌─────┴────────┐                        │
│  │timesheet_approvals│                   │milestone_tasks│                       │
│  └─────────────┘                        └──────────────┘                        │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                         POS (POINT OF SALES)                                     │
│  ┌───────────┐  ┌────────────────┐  ┌───────────────┐  ┌──────────────────┐     │
│  │ products  │  │product_categories│ │product_variants│  │product_discounts │     │
│  └─────┬─────┘  └────────────────┘  └───────────────┘  └──────────────────┘     │
│        │                                                                          │
│  ┌─────┴──────┐  ┌──────────────┐  ┌───────────────┐                             │
│  │cashier_shifts│ │pos_transactions│ │pos_transaction_items│                      │
│  └─────┬──────┘  └──────┬───────┘  └───────────────┘                             │
│        │                │                                                         │
│  ┌─────┴──────┐   ┌─────┴──────┐  ┌───────────────┐                              │
│  │pos_payments│   │pos_refunds │  │pos_discounts_applied│                        │
│  └────────────┘   └────────────┘  └───────────────┘                              │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────────┐                        │
│  │pos_members   │  │pos_member_points│ │pos_vouchers     │                       │
│  └──────────────┘  └──────────────┘  └─────────────────┘                        │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                     COLLABORATION                                                │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐          │
│  │  chats   │  │chat_messages │  │chat_attachments│  │chat_reactions  │          │
│  └────┬─────┘  └──────┬───────┘  └───────────────┘  └────────────────┘          │
│       │               │                                                          │
│  ┌────┴────┐    ┌─────┴──────┐                                                  │
│  │chat_participants│ │chat_message_reads│                                         │
│  └─────────┘    └────────────┘                                                  │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐                               │
│  │meetings  │  │meeting_attendees│ │meeting_minutes │                             │
│  └────┬─────┘  └──────────────┘  └───────────────┘                               │
│       │                                                                          │
│  ┌────┴──────┐  ┌───────────────┐                                               │
│  │meeting_recaps│ │meeting_action_items│                                          │
│  └───────────┘  └───────────────┘                                               │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────┐                                │
│  │calendars │  │calendar_events│  │event_reminders│                              │
│  └──────────┘  └──────────────┘  └──────────────┘                                │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────┐                                │
│  │   forms  │  │ form_fields  │  │form_submissions│                              │
│  └────┬─────┘  └──────────────┘  └──────────────┘                                │
│       │                                                                          │
│  ┌────┴──────┐  ┌──────────────┐  ┌──────────────┐                               │
│  │form_field_vals│ │form_submission_files│                                          │
│  └───────────┘  └──────────────┘  └──────────────┘                               │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌──────────────┐                                │
│  │  files   │  │file_folders  │  │file_versions │                                │
│  └──────────┘  └──────────────┘  └──────────────┘                                │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                     AI ASSISTANT & LMS                                           │
│  ┌──────────────┐  ┌───────────────┐  ┌───────────────────┐                     │
│  │ai_conversations│ │ai_conversation_msgs│ │ai_knowledge_base │                  │
│  └──────────────┘  └───────────────┘  └───────────────────┘                     │
│                                                                                  │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐         │
│  │ courses  │  │course_modules│  │course_lessons │  │course_enrollments│        │
│  └────┬─────┘  └──────────────┘  └───────────────┘  └────────────────┘         │
│       │                                                                          │
│  ┌────┴──────┐  ┌──────────────┐  ┌───────────────┐                             │
│  │  quizzes  │  │quiz_questions│  │quiz_answers    │                             │
│  └─────┬─────┘  └──────────────┘  └───────────────┘                             │
│        │                                                                         │
│  ┌─────┴──────┐  ┌──────────────┐                                               │
│  │quiz_attempts│ │quiz_attempt_answers│                                           │
│  └────────────┘  └──────────────┘                                               │
│  ┌──────────────┐  ┌─────────────────┐                                         │
│  │certificates  │  │certificate_issued│                                        │
│  └──────────────┘  └─────────────────┘                                         │
└─────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────┐
│                     PLATFORM & SYSTEM                                            │
│  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐                        │
│  │role_permissions│ │user_roles     │  │notifications   │                        │
│  └──────────────┘  └───────────────┘  └────────┬───────┘                        │
│                                                │                                  │
│  ┌──────────────┐  ┌───────────────┐  ┌────────┴──────┐                         │
│  │ audit_logs   │  │system_settings│  │notification_templates│                   │
│  └──────────────┘  └───────────────┘  └───────────────┘                         │
│  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐                        │
│  │ integrations │  │webhooks       │  │  api_tokens    │                        │
│  └──────────────┘  └───────────────┘  └────────────────┘                        │
│  ┌──────────────┐  ┌───────────────┐  ┌────────────────┐                        │
│  │import_exports│  │backup_logs    │  │scheduled_jobs  │                        │
│  └──────────────┘  └───────────────┘  └────────────────┘                        │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Complete Database Schema

### 2.1 MASTER DATA

#### companies
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| code | varchar(50) | UNIQUE | Kode perusahaan |
| name | varchar(255) | | Nama perusahaan |
| slug | varchar(255) | UNIQUE | |
| logo | varchar(255) | nullable | |
| address | text | nullable | |
| phone | varchar(30) | nullable | |
| email | varchar(255) | nullable | |
| website | varchar(255) | nullable | |
| tax_id | varchar(50) | nullable | NPWP |
| is_active | boolean | | Default true |
| subscription_start | date | nullable | |
| subscription_end | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

#### branches
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | | |
| name | varchar(255) | | |
| address | text | nullable | |
| phone | varchar(30) | nullable | |
| timezone | varchar(100) | | Default 'Asia/Jakarta' |
| is_headquarters | boolean | | Default false |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### departments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| parent_id | bigint unsigned | nullable, FK→departments | Self-referencing hierarchy |
| code | varchar(50) | | |
| name | varchar(255) | | |
| description | text | nullable | |
| sort_order | integer | | Default 0 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### positions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| department_id | bigint unsigned | nullable, FK→departments | |
| code | varchar(50) | | |
| name | varchar(255) | | |
| description | text | nullable | |
| sort_order | integer | | Default 0 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### designations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | Contoh: Staff, Supervisor, Manager, Director |
| level | integer | | Level hierarki (1=terendah) |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### grades
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | | |
| name | varchar(255) | | |
| min_salary | decimal(20,2) | nullable | |
| max_salary | decimal(20,2) | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.2 EMPLOYEES & USERS

#### employees
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| branch_id | bigint unsigned | nullable, FK→branches | |
| department_id | bigint unsigned | nullable, FK→departments | |
| position_id | bigint unsigned | nullable, FK→positions | |
| designation_id | bigint unsigned | nullable, FK→designations | |
| grade_id | bigint unsigned | nullable, FK→grades | |
| employee_code | varchar(50) | UNIQUE | NIP / ID karyawan |
| first_name | varchar(100) | | |
| last_name | varchar(100) | nullable | |
| email | varchar(255) | | |
| phone | varchar(30) | nullable | |
| gender | enum('male','female','other') | | |
| birth_date | date | nullable | |
| birth_place | varchar(100) | nullable | |
| religion | varchar(50) | nullable | |
| marital_status | enum('single','married','divorced','widowed') | nullable | |
| nationality | varchar(50) | | Default 'Indonesia' |
| id_number | varchar(50) | nullable | NIK KTP |
| tax_number | varchar(50) | nullable | NPWP |
| bpjs_kesehatan | varchar(50) | nullable | |
| bpjs_ketenagakerjaan | varchar(50) | nullable | |
| address | text | nullable | |
| city | varchar(100) | nullable | |
| province | varchar(100) | nullable | |
| postal_code | varchar(10) | nullable | |
| photo | varchar(255) | nullable | |
| join_date | date | | Tanggal bergabung |
| contract_start | date | nullable | |
| contract_end | date | nullable | |
| employee_type | enum('permanent','contract','probation','intern','freelance','part_time') | | |
| status | enum('active','inactive','terminated','resigned','retired') | | Default 'active' |
| termination_date | date | nullable | |
| termination_reason | text | nullable | |
| basic_salary | decimal(20,2) | | Gaji pokok |
| hourly_rate | decimal(15,2) | nullable | |
| overtime_rate | decimal(15,2) | nullable | |
| bank_name | varchar(100) | nullable | |
| bank_account_number | varchar(50) | nullable | |
| bank_account_name | varchar(255) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

#### users
(laravel default + extension)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | nullable, FK→employees | Link ke data karyawan |
| company_id | bigint unsigned | nullable, FK→companies | Multi-tenant |
| role_id | bigint unsigned | nullable, FK→roles | |
| name | varchar(255) | | |
| email | varchar(255) | UNIQUE | |
| email_verified_at | timestamp | nullable | |
| password | varchar(255) | | |
| avatar | varchar(255) | nullable | |
| is_active | boolean | | Default true |
| last_login_at | timestamp | nullable | |
| last_login_ip | varchar(45) | nullable | |
| two_factor_secret | text | nullable | |
| two_factor_recovery_codes | text | nullable | |
| remember_token | varchar(100) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

#### family_members
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| name | varchar(255) | | |
| relationship | enum('spouse','child','parent','sibling') | | |
| gender | enum('male','female','other') | nullable | |
| birth_date | date | nullable | |
| occupation | varchar(255) | nullable | |
| phone | varchar(30) | nullable | |
| is_emergency_contact | boolean | | Default false |
| is_dependent | boolean | | Default false |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### employee_documents
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| document_type | varchar(100) | | KTP, KK, Ijazah, CV, SKCK, dll |
| document_name | varchar(255) | | |
| file_path | varchar(255) | | |
| file_size | bigint | nullable | |
| issue_date | date | nullable | |
| expiry_date | date | nullable | |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.3 HRM — ATTENDANCE

#### shifts
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(100) | | 'Shift Pagi', 'Shift Malam', etc |
| start_time | time | | |
| end_time | time | | |
| grace_period_minutes | integer | | Default 15 (toleransi keterlambatan) |
| break_start | time | nullable | |
| break_end | time | nullable | |
| is_overnight | boolean | | Default false (shift lewat midnight) |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### shift_employees
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| shift_id | bigint unsigned | FK→shifts cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| effective_date | date | | |
| end_date | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### attendance_configs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| method | enum('gps','wifi','selfie','manual','qrcode','nfc') | | Multi-select disimpan JSON |
| gps_radius_meters | integer | nullable | |
| gps_latitude | decimal(12,8) | nullable | |
| gps_longitude | decimal(12,8) | nullable | |
| require_selfie | boolean | | Default true |
| require_wfh_photo | boolean | | Default true |
| auto_clock_out | boolean | | Default false |
| auto_clock_out_time | time | nullable | |
| weekend_days | json | | Default '[6,7]' (Sabtu,Minggu) |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### wifi_access_points
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| branch_id | bigint unsigned | nullable, FK→branches | |
| ssid | varchar(255) | | Nama WiFi |
| bssid | varchar(50) | | MAC address |
| ip_address | varchar(45) | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### attendances
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees | |
| shift_id | bigint unsigned | nullable, FK→shifts | |
| date | date | | |
| clock_in | timestamp | nullable | |
| clock_out | timestamp | nullable | |
| clock_in_lat | decimal(12,8) | nullable | |
| clock_in_lng | decimal(12,8) | nullable | |
| clock_out_lat | decimal(12,8) | nullable | |
| clock_out_lng | decimal(12,8) | nullable | |
| clock_in_photo | varchar(255) | nullable | |
| clock_out_photo | varchar(255) | nullable | |
| clock_in_wifi_bssid | varchar(50) | nullable | |
| clock_out_wifi_bssid | varchar(50) | nullable | |
| status | enum('present','late','absent','half_day','leave','holiday','weekend') | | Default 'present' |
| late_minutes | integer | | Default 0 |
| early_departure_minutes | integer | | Default 0 |
| overtime_minutes | integer | | Default 0 |
| work_type | enum('office','wfh','wfa','field') | | Default 'office' |
| notes | text | nullable | |
| approved_by | bigint unsigned | nullable, FK→employees | |
| approved_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### attendance_logs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| attendance_id | bigint unsigned | FK→attendances cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees | |
| action | enum('clock_in','clock_out','request_attendance','approve','reject') | | |
| latitude | decimal(12,8) | nullable | |
| longitude | decimal(12,8) | nullable | |
| photo | varchar(255) | nullable | |
| wifi_bssid | varchar(50) | nullable | |
| device_info | varchar(255) | nullable | User agent / device |
| ip_address | varchar(45) | nullable | |
| notes | text | nullable | |
| created_at | timestamp | | |

---

### 2.4 HRM — LEAVE & OVERTIME

#### leave_types
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | | |
| name | varchar(255) | | Cuti Tahunan, Sakit, Melahirkan, Menikah, Duka, dll |
| description | text | nullable | |
| default_days | integer | | Default alokasi per tahun |
| max_days | integer | nullable | Maksimal hari per pengajuan |
| is_annual | boolean | | Default true |
| is_paid | boolean | | Default true |
| require_attachment | boolean | | Default false |
| require_approval | boolean | | Default true |
| min_approval_level | integer | | Default 1 |
| applicable_gender | enum('all','male','female') | | Default 'all' |
| applicable_marital | enum('all','single','married') | | Default 'all' |
| color | varchar(20) | nullable | Warna di calendar |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### leave_balances
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| leave_type_id | bigint unsigned | FK→leave_types cascadeOnDelete | |
| year | smallint | | |
| total_days | integer | | |
| used_days | integer | | Default 0 |
| remaining_days | integer | | Generated column |
| carry_forward | integer | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### leaves
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| leave_type_id | bigint unsigned | FK→leave_types | |
| start_date | date | | |
| end_date | date | | |
| total_days | integer | | |
| reason | text | nullable | |
| attachment | varchar(255) | nullable | Surat dokter, dll |
| status | enum('draft','pending','approved','rejected','cancelled') | | Default 'pending' |
| rejection_reason | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### leave_approvals
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| leave_id | bigint unsigned | FK→leaves cascadeOnDelete | |
| approver_id | bigint unsigned | FK→employees | |
| level | integer | | Level approval (1,2,3...) |
| status | enum('pending','approved','rejected') | | Default 'pending' |
| notes | text | nullable | |
| approved_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### overtimes
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| date | date | | |
| start_time | time | | |
| end_time | time | | |
| duration_minutes | integer | | |
| rate_multiplier | decimal(5,2) | | Default 1.5 |
| reason | text | | |
| status | enum('draft','pending','approved','rejected','cancelled') | | Default 'pending' |
| approved_by | bigint unsigned | nullable, FK→employees | |
| approved_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.5 HRM — REIMBURSEMENT & VISIT

#### reimbursement_categories
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | Transport, Makan, Hotel, Medis, dll |
| description | text | nullable | |
| max_amount | decimal(20,2) | nullable | |
| require_receipt | boolean | | Default true |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### reimbursements
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| category_id | bigint unsigned | FK→reimbursement_categories | |
| date | date | | |
| amount | decimal(20,2) | | |
| description | text | | |
| status | enum('draft','pending','approved','rejected','paid') | | Default 'pending' |
| rejection_reason | text | nullable | |
| paid_date | date | nullable | |
| paid_amount | decimal(20,2) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### reimbursement_attachments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| reimbursement_id | bigint unsigned | FK→reimbursements cascadeOnDelete | |
| file_name | varchar(255) | | |
| file_path | varchar(255) | | |
| file_size | bigint | nullable | |
| file_type | varchar(50) | | |
| created_at | timestamp | | |

#### visits
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees | |
| date | date | | |
| visit_type | enum('customer','vendor','field','other') | | |
| location | varchar(255) | | |
| purpose | text | | |
| start_time | time | | |
| end_time | time | nullable | |
| check_in_lat | decimal(12,8) | nullable | |
| check_in_lng | decimal(12,8) | nullable | |
| check_out_lat | decimal(12,8) | nullable | |
| check_out_lng | decimal(12,8) | nullable | |
| status | enum('planned','in_progress','completed','cancelled') | | Default 'planned' |
| report | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.6 HRM — PAYROLL

#### salary_components
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | | |
| name | varchar(255) | | Gaji Pokok, Tunjangan Jabatan, Transport, dll |
| type | enum('income','deduction') | | |
| calculation_type | enum('fixed','percentage','formula','per_day','per_hour','per_attendance') | | |
| amount | decimal(20,2) | nullable | |
| formula | text | nullable | |
| is_taxable | boolean | | Default false |
| is_mandatory | boolean | | Default false |
| sort_order | integer | | Default 0 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### employee_salary_components
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| salary_component_id | bigint unsigned | FK→salary_components cascadeOnDelete | |
| amount | decimal(20,2) | | Override per employee |
| effective_date | date | | |
| end_date | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### payroll_periods
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| period_code | varchar(50) | UNIQUE | '2026-05' |
| start_date | date | | |
| end_date | date | | |
| payment_date | date | | |
| status | enum('draft','processing','completed','cancelled') | | Default 'draft' |
| total_gross | decimal(20,2) | | Default 0 |
| total_deductions | decimal(20,2) | | Default 0 |
| total_net | decimal(20,2) | | Default 0 |
| total_employees | integer | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### payrolls
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| period_id | bigint unsigned | FK→payroll_periods | |
| employee_id | bigint unsigned | FK→employees | |
| gross_salary | decimal(20,2) | | |
| total_income_components | decimal(20,2) | | Default 0 |
| total_deduction_components | decimal(20,2) | | Default 0 |
| pph21_amount | decimal(20,2) | | Default 0 |
| bpjs_tk_jht | decimal(20,2) | | Default 0 |
| bpjs_tk_jp | decimal(20,2) | | Default 0 |
| bpjs_tk_jkk | decimal(20,2) | | Default 0 |
| bpjs_tk_jkm | decimal(20,2) | | Default 0 |
| bpjs_kes | decimal(20,2) | | Default 0 |
| net_salary | decimal(20,2) | | |
| attendance_days | integer | | Default 0 |
| leave_days | integer | | Default 0 |
| overtime_hours | decimal(8,2) | | Default 0 |
| overtime_pay | decimal(20,2) | | Default 0 |
| status | enum('draft','finalized','paid') | | Default 'draft' |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### payroll_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| payroll_id | bigint unsigned | FK→payrolls cascadeOnDelete | |
| salary_component_id | bigint unsigned | FK→salary_components | |
| name | varchar(255) | | |
| type | enum('income','deduction') | | |
| amount | decimal(20,2) | | |
| created_at | timestamp | | |

#### pay_slips
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| payroll_id | bigint unsigned | FK→payrolls cascadeOnDelete | |
| slip_number | varchar(50) | UNIQUE | |
| file_path | varchar(255) | nullable | PDF path |
| sent_at | timestamp | nullable | |
| viewed_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### pph21_configs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| ptkp_category | enum('tk0','tk1','tk2','tk3','k0','k1','k2','k3') | | Status PTKP |
| ptkp_amount | decimal(20,2) | | |
| threshold_low | decimal(20,2) | | Layer 1 batas |
| rate_low | decimal(5,4) | | Layer 1 rate |
| threshold_mid | decimal(20,2) | | Layer 2 batas |
| rate_mid | decimal(5,4) | | Layer 2 rate |
| threshold_high | decimal(20,2) | | Layer 3 batas |
| rate_high | decimal(5,4) | | Layer 3 rate |
| rate_top | decimal(5,4) | | Top bracket rate |
| effective_year | smallint | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### bpjs_configs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| bpjs_type | enum('tk_jht','tk_jp','tk_jkk','tk_jkm','kes') | | |
| company_rate | decimal(5,4) | | |
| employee_rate | decimal(5,4) | | |
| max_salary_cap | decimal(20,2) | nullable | |
| effective_year | smallint | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### thr_configs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| religious_holiday | varchar(100) | | |
| min_months_service | integer | | Default 1 (bulan minimum kerja) |
| formula | enum('1x_salary','prorated','custom') | | |
| custom_formula | text | nullable | |
| payment_deadline_days | integer | | Default 7 (H-7 before holiday) |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.7 HRM — RECRUITMENT

#### job_postings
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| department_id | bigint unsigned | nullable, FK→departments | |
| position_id | bigint unsigned | nullable, FK→positions | |
| title | varchar(255) | | |
| description | text | | |
| requirements | text | nullable | |
| responsibilities | text | nullable | |
| employee_type | enum('permanent','contract','probation','intern','freelance') | nullable | |
| min_salary | decimal(20,2) | nullable | |
| max_salary | decimal(20,2) | nullable | |
| location | varchar(255) | nullable | |
| is_remote | boolean | | Default false |
| quota | integer | | Default 1 |
| status | enum('draft','published','closed','cancelled') | | Default 'draft' |
| published_at | timestamp | nullable | |
| closed_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### candidates
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| job_posting_id | bigint unsigned | FK→job_postings | |
| first_name | varchar(100) | | |
| last_name | varchar(100) | nullable | |
| email | varchar(255) | | |
| phone | varchar(30) | nullable | |
| photo | varchar(255) | nullable | |
| resume_path | varchar(255) | nullable | |
| portfolio_url | varchar(255) | nullable | |
| linkedin_url | varchar(255) | nullable | |
| source | varchar(100) | nullable | LinkedIn, JobStreet, Referral, dll |
| expected_salary | decimal(20,2) | nullable | |
| available_date | date | nullable | |
| pipeline_stage | enum('applied','screening','hr_interview','user_interview','technical_test','offering','hired','rejected','withdrawn') | | Default 'applied' |
| notes | text | nullable | |
| rejection_reason | text | nullable | |
| hired_employee_id | bigint unsigned | nullable, FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### interviews
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| candidate_id | bigint unsigned | FK→candidates cascadeOnDelete | |
| interview_type | enum('phone','video','onsite','technical_test','psychological_test','medical') | | |
| scheduled_at | timestamp | | |
| duration_minutes | integer | | Default 60 |
| location | varchar(255) | nullable | |
| meeting_link | varchar(255) | nullable | |
| notes | text | nullable | |
| status | enum('scheduled','in_progress','completed','cancelled','rescheduled') | | Default 'scheduled' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### interviewers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| interview_id | bigint unsigned | FK→interviews cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees | |
| role | enum('lead','panel','observer') | | Default 'lead' |
| created_at | timestamp | | |

#### interview_results
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| interview_id | bigint unsigned | FK→interviews cascadeOnDelete | |
| interviewer_id | bigint unsigned | FK→interviewers cascadeOnDelete | |
| rating | decimal(3,1) | | 1-5 |
| comments | text | nullable | |
| recommendation | enum('strong_hire','hire','maybe','reject','strong_reject') | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.8 HRM — 360 FEEDBACK & PERFORMANCE

#### feedback_cycles
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | 'Performance Review H1 2026' |
| start_date | date | | |
| end_date | date | | |
| status | enum('draft','active','completed') | | Default 'draft' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### feedback_questions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| cycle_id | bigint unsigned | FK→feedback_cycles cascadeOnDelete | |
| question | text | | |
| category | enum('technical','soft_skill','leadership','communication','teamwork','initiative') | | |
| question_type | enum('rating','text','multiple_choice') | | Default 'rating' |
| options | json | nullable | Untuk multiple choice |
| sort_order | integer | | Default 0 |
| created_at | timestamp | | |

#### feedback_reviewers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| cycle_id | bigint unsigned | FK→feedback_cycles cascadeOnDelete | |
| reviewee_id | bigint unsigned | FK→employees cascadeOnDelete | Yang dinilai |
| reviewer_id | bigint unsigned | FK→employees | Yang menilai |
| reviewer_type | enum('self','supervisor','peer','subordinate') | | |
| status | enum('pending','in_progress','completed') | | Default 'pending' |
| completed_at | timestamp | nullable | |
| created_at | timestamp | | |

#### feedback_answers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| reviewer_id | bigint unsigned | FK→feedback_reviewers cascadeOnDelete | |
| question_id | bigint unsigned | FK→feedback_questions cascadeOnDelete | |
| rating | decimal(3,1) | nullable | |
| text_answer | text | nullable | |
| selected_options | json | nullable | Untuk multiple choice |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.9 HRM — CANTEEN

#### canteen_menus
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| description | text | nullable | |
| category | varchar(100) | nullable | Makanan, Minuman, Snack |
| price | decimal(15,2) | | |
| photo | varchar(255) | nullable | |
| stock | integer | | Default 0 |
| is_available | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### canteen_orders
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| order_date | date | | |
| status | enum('pending','preparing','ready','served','cancelled') | | Default 'pending' |
| total_amount | decimal(15,2) | | Default 0 |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### canteen_order_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| order_id | bigint unsigned | FK→canteen_orders cascadeOnDelete | |
| menu_id | bigint unsigned | FK→canteen_menus | |
| quantity | integer | | Default 1 |
| unit_price | decimal(15,2) | | |
| subtotal | decimal(15,2) | | |
| created_at | timestamp | | |

---

### 2.10 HRM — ANNOUNCEMENTS

#### announcements
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| title | varchar(500) | | |
| content | text | | |
| priority | enum('low','normal','high','urgent') | | Default 'normal' |
| target_type | enum('all','department','position','designation','specific') | | Default 'all' |
| target_department_ids | json | nullable | |
| target_position_ids | json | nullable | |
| expires_at | timestamp | nullable | |
| published_at | timestamp | nullable | |
| published_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### announcement_reads
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| announcement_id | bigint unsigned | FK→announcements cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| read_at | timestamp | | |
| created_at | timestamp | | |

---

### 2.11 ACCOUNTING — CHART OF ACCOUNTS

#### coa_categories
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(20) | | 1=Asset, 2=Liability, 3=Equity, 4=Revenue, 5=Expense |
| name | varchar(255) | | |
| normal_balance | enum('debit','credit') | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### coa
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| category_id | bigint unsigned | FK→coa_categories | |
| parent_id | bigint unsigned | nullable, FK→coa | Self-referencing hierarchy |
| code | varchar(30) | | |
| name | varchar(255) | | |
| description | text | nullable | |
| is_header | boolean | | Default false (heading account) |
| opening_balance | decimal(20,2) | | Default 0 |
| balance_type | enum('debit','credit') | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### coa_balances
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| coa_id | bigint unsigned | FK→coa cascadeOnDelete | |
| year | smallint | | |
| month | tinyint | | |
| opening_balance | decimal(20,2) | | Default 0 |
| debit_total | decimal(20,2) | | Default 0 |
| credit_total | decimal(20,2) | | Default 0 |
| closing_balance | decimal(20,2) | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.12 ACCOUNTING — JOURNALS & ENTRIES

#### journals
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| journal_number | varchar(50) | UNIQUE | |
| journal_date | date | | |
| journal_type | enum('general','sales','purchase','cash_receipt','cash_payment','bank','adjustment','opening') | | Default 'general' |
| description | text | nullable | |
| total_debit | decimal(20,2) | | Default 0 |
| total_credit | decimal(20,2) | | Default 0 |
| reference_type | varchar(100) | nullable | invoice, payment, etc |
| reference_id | bigint unsigned | nullable | polymorphic |
| status | enum('draft','posted','voided') | | Default 'draft' |
| posted_by | bigint unsigned | nullable, FK→users | |
| posted_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### journal_entries
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| journal_id | bigint unsigned | FK→journals cascadeOnDelete | |
| coa_id | bigint unsigned | FK→coa | |
| description | varchar(255) | nullable | |
| debit | decimal(20,2) | | Default 0 |
| credit | decimal(20,2) | | Default 0 |
| created_at | timestamp | | |

---

### 2.13 ACCOUNTING — INVOICES & PAYMENTS

#### invoices
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| invoice_number | varchar(50) | UNIQUE | |
| invoice_type | enum('sales','purchase','credit_note','debit_note') | | |
| invoice_date | date | | |
| due_date | date | | |
| reference_entity | varchar(100) | | 'client', 'vendor', 'employee' |
| reference_id | bigint unsigned | | polymorphic |
| subtotal | decimal(20,2) | | Default 0 |
| discount_amount | decimal(20,2) | | Default 0 |
| tax_amount | decimal(20,2) | | Default 0 |
| total | decimal(20,2) | | Default 0 |
| paid_amount | decimal(20,2) | | Default 0 |
| remaining_amount | decimal(20,2) | | Generated column (total - paid_amount) |
| status | enum('draft','sent','partial','paid','overdue','cancelled') | | Default 'draft' |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### invoice_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| invoice_id | bigint unsigned | FK→invoices cascadeOnDelete | |
| description | varchar(500) | | |
| quantity | decimal(15,4) | | Default 1 |
| unit_price | decimal(20,2) | | |
| tax_rate | decimal(5,2) | | Default 0 |
| amount | decimal(20,2) | | |
| created_at | timestamp | | |

#### payment_methods
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(100) | | Cash, Transfer Bank, QRIS, Kartu Kredit, dll |
| code | varchar(50) | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### payments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| payment_number | varchar(50) | UNIQUE | |
| payment_date | date | | |
| payment_method_id | bigint unsigned | FK→payment_methods | |
| amount | decimal(20,2) | | |
| reference_number | varchar(100) | nullable | Nomor referensi bank |
| notes | text | nullable | |
| status | enum('pending','confirmed','rejected') | | Default 'pending' |
| confirmed_by | bigint unsigned | nullable, FK→users | |
| confirmed_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### invoice_payments (pivot)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| invoice_id | bigint unsigned | FK→invoices cascadeOnDelete | |
| payment_id | bigint unsigned | FK→payments cascadeOnDelete | |
| amount | decimal(20,2) | | |
| created_at | timestamp | | |

---

### 2.14 ACCOUNTING — BUDGETS

#### budgets
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| fiscal_year | smallint | | |
| start_date | date | | |
| end_date | date | | |
| department_id | bigint unsigned | nullable, FK→departments | |
| project_id | bigint unsigned | nullable, FK→projects | |
| status | enum('draft','approved','active','closed') | | Default 'draft' |
| approved_by | bigint unsigned | nullable, FK→users | |
| approved_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### budget_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| budget_id | bigint unsigned | FK→budgets cascadeOnDelete | |
| coa_id | bigint unsigned | FK→coa | |
| description | varchar(500) | | |
| planned_amount | decimal(20,2) | | |
| actual_amount | decimal(20,2) | | Default 0 |
| variance | decimal(20,2) | | Generated (planned - actual) |
| period_start | date | nullable | |
| period_end | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.15 ACCOUNTING — TAXES

#### tax_configs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| tax_type | enum('ppn','pph21','pph22','pph23','pph25','pph29','pph_final') | | |
| name | varchar(255) | | |
| rate | decimal(5,4) | | |
| effective_year | smallint | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### tax_transactions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| tax_config_id | bigint unsigned | FK→tax_configs | |
| reference_type | varchar(100) | | invoice, payment, payroll |
| reference_id | bigint unsigned | | |
| base_amount | decimal(20,2) | | |
| tax_amount | decimal(20,2) | | |
| tax_date | date | | |
| payment_status | enum('unpaid','paid','deferred') | | Default 'unpaid' |
| paid_date | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.16 ACCOUNTING — ASSET MANAGEMENT

#### asset_categories
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | | |
| name | varchar(255) | | |
| depreciation_method | enum('straight_line','declining_balance','sum_of_years','units_of_production','none') | | Default 'straight_line' |
| useful_life_years | integer | | |
| salvage_value_percent | decimal(5,2) | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### assets
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| category_id | bigint unsigned | FK→asset_categories | |
| asset_code | varchar(50) | UNIQUE | |
| name | varchar(255) | | |
| description | text | nullable | |
| acquisition_date | date | | |
| acquisition_cost | decimal(20,2) | | |
| useful_life_years | integer | | |
| salvage_value | decimal(20,2) | | Default 0 |
| current_value | decimal(20,2) | | |
| accumulated_depreciation | decimal(20,2) | | Default 0 |
| location | varchar(255) | nullable | |
| current_employee_id | bigint unsigned | nullable, FK→employees | PIC |
| status | enum('active','maintenance','disposed','sold','written_off') | | Default 'active' |
| purchase_invoice_id | bigint unsigned | nullable, FK→invoices | |
| warranty_expiry | date | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### asset_depreciations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| asset_id | bigint unsigned | FK→assets cascadeOnDelete | |
| year | smallint | | |
| month | tinyint | | |
| depreciation_amount | decimal(20,2) | | |
| accumulated_before | decimal(20,2) | | |
| accumulated_after | decimal(20,2) | | |
| book_value_after | decimal(20,2) | | |
| journal_id | bigint unsigned | nullable, FK→journals | |
| created_at | timestamp | | |

#### asset_mutations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| asset_id | bigint unsigned | FK→assets cascadeOnDelete | |
| mutation_type | enum('transfer_location','assign_employee','return','disposal') | | |
| from_location | varchar(255) | nullable | |
| to_location | varchar(255) | nullable | |
| from_employee_id | bigint unsigned | nullable, FK→employees | |
| to_employee_id | bigint unsigned | nullable, FK→employees | |
| mutation_date | date | | |
| notes | text | nullable | |
| created_at | timestamp | | |

#### asset_maintenances
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| asset_id | bigint unsigned | FK→assets cascadeOnDelete | |
| maintenance_type | enum('preventive','corrective','inspection','calibration') | | |
| description | text | | |
| cost | decimal(20,2) | | Default 0 |
| scheduled_date | date | | |
| completed_date | date | nullable | |
| vendor_name | varchar(255) | nullable | |
| status | enum('scheduled','in_progress','completed','cancelled') | | Default 'scheduled' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.17 CRM

#### lead_sources
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | Website, Referral, LinkedIn, Iklan, dll |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### leads
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| source_id | bigint unsigned | nullable, FK→lead_sources | |
| assigned_to | bigint unsigned | nullable, FK→employees | Sales person |
| first_name | varchar(100) | | |
| last_name | varchar(100) | nullable | |
| email | varchar(255) | nullable | |
| phone | varchar(30) | nullable | |
| company_name | varchar(255) | nullable | |
| industry | varchar(100) | nullable | |
| address | text | nullable | |
| score | integer | | Default 0 |
| status | enum('new','contacted','qualified','proposal','negotiation','won','lost','disqualified') | | Default 'new' |
| lost_reason | text | nullable | |
| converted_client_id | bigint unsigned | nullable, FK→clients | |
| notes | text | nullable | |
| next_follow_up | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### lead_activities
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| lead_id | bigint unsigned | FK→leads cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees | |
| activity_type | enum('call','email','meeting','whatsapp','note','task') | | |
| subject | varchar(255) | | |
| description | text | nullable | |
| scheduled_at | timestamp | nullable | |
| completed_at | timestamp | nullable | |
| status | enum('planned','completed','cancelled') | | Default 'planned' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### clients
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| client_code | varchar(50) | UNIQUE | |
| name | varchar(255) | | Perusahaan / individu |
| client_type | enum('individual','company','government','nonprofit') | | Default 'company' |
| industry | varchar(100) | nullable | |
| tax_id | varchar(50) | nullable | NPWP |
| website | varchar(255) | nullable | |
| address | text | nullable | |
| city | varchar(100) | nullable | |
| province | varchar(100) | nullable | |
| postal_code | varchar(10) | nullable | |
| phone | varchar(30) | nullable | |
| email | varchar(255) | nullable | |
| logo | varchar(255) | nullable | |
| status | enum('active','inactive','blocked') | | Default 'active' |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | |

#### client_contacts
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| client_id | bigint unsigned | FK→clients cascadeOnDelete | |
| first_name | varchar(100) | | |
| last_name | varchar(100) | nullable | |
| position | varchar(100) | nullable | |
| email | varchar(255) | nullable | |
| phone | varchar(30) | nullable | |
| is_primary | boolean | | Default false |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### client_segments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | VIP, Regular, New, At Risk |
| description | text | nullable | |
| color | varchar(20) | nullable | |
| criteria_json | json | nullable | Rules untuk segmentasi |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### client_segment_members (pivot)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| segment_id | bigint unsigned | FK→client_segments cascadeOnDelete | |
| client_id | bigint unsigned | FK→clients cascadeOnDelete | |
| added_at | timestamp | | |
| created_at | timestamp | | |

#### pipeline_stages
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(100) | | Prospecting, Qualification, Proposal, dll |
| probability_percent | integer | | Default 0 |
| color | varchar(20) | nullable | |
| sort_order | integer | | Default 0 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |

#### deals
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| lead_id | bigint unsigned | nullable, FK→leads | |
| client_id | bigint unsigned | nullable, FK→clients | |
| stage_id | bigint unsigned | FK→pipeline_stages | |
| assigned_to | bigint unsigned | nullable, FK→employees | |
| title | varchar(500) | | |
| expected_value | decimal(20,2) | | |
| expected_close_date | date | nullable | |
| actual_close_date | date | nullable | |
| status | enum('open','won','lost','on_hold') | | Default 'open' |
| lost_reason | text | nullable | |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.18 CRM — WHATSAPP

#### wa_templates
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| content | text | | Variabel pakai {{nama}} |
| category | varchar(100) | nullable | Marketing, Utility, Authentication |
| language | varchar(10) | | Default 'id' |
| status | enum('draft','pending','approved','rejected','active') | | Default 'draft' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### wa_blast_campaigns
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| template_id | bigint unsigned | FK→wa_templates | |
| name | varchar(255) | | |
| target_type | enum('all_clients','segment','specific','leads') | | |
| target_segment_id | bigint unsigned | nullable, FK→client_segments | |
| target_clients | json | nullable | Array of client IDs |
| scheduled_at | timestamp | nullable | |
| sent_at | timestamp | nullable | |
| total_targets | integer | | Default 0 |
| total_sent | integer | | Default 0 |
| total_failed | integer | | Default 0 |
| status | enum('draft','scheduled','sending','sent','failed') | | Default 'draft' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### wa_blast_logs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| campaign_id | bigint unsigned | FK→wa_blast_campaigns cascadeOnDelete | |
| contact_phone | varchar(30) | | |
| contact_name | varchar(255) | nullable | |
| message | text | | |
| status | enum('queued','sent','delivered','read','failed') | | Default 'queued' |
| error_message | text | nullable | |
| sent_at | timestamp | nullable | |
| delivered_at | timestamp | nullable | |
| read_at | timestamp | nullable | |
| created_at | timestamp | | |

#### wa_auto_replies
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| keyword | varchar(255) | | Kata kunci trigger |
| match_type | enum('exact','contains','starts_with','regex') | | Default 'contains' |
| reply_text | text | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### wa_conversations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| contact_phone | varchar(30) | | |
| contact_name | varchar(255) | nullable | |
| last_message | text | nullable | |
| last_message_at | timestamp | nullable | |
| unread_count | integer | | Default 0 |
| assigned_to | bigint unsigned | nullable, FK→employees | |
| status | enum('active','resolved','archived') | | Default 'active' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.19 PROJECT MANAGEMENT

#### projects
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| department_id | bigint unsigned | nullable, FK→departments | |
| client_id | bigint unsigned | nullable, FK→clients | |
| manager_id | bigint unsigned | nullable, FK→employees | Project manager |
| code | varchar(50) | UNIQUE | |
| name | varchar(500) | | |
| description | text | nullable | |
| start_date | date | | |
| end_date | date | nullable | |
| budget | decimal(20,2) | nullable | |
| actual_cost | decimal(20,2) | | Default 0 |
| status | enum('planning','active','on_hold','completed','cancelled','archived') | | Default 'planning' |
| priority | enum('low','medium','high','urgent') | | Default 'medium' |
| progress_percent | decimal(5,2) | | Default 0 |
| color | varchar(20) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | |

#### project_phases
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| project_id | bigint unsigned | FK→projects cascadeOnDelete | |
| name | varchar(255) | | |
| description | text | nullable | |
| start_date | date | | |
| end_date | date | nullable | |
| sort_order | integer | | Default 0 |
| status | enum('pending','active','completed') | | Default 'pending' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### project_members
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| project_id | bigint unsigned | FK→projects cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| role | enum('manager','member','observer') | | Default 'member' |
| joined_at | timestamp | | |
| created_at | timestamp | | |

#### tasks
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| project_id | bigint unsigned | FK→projects cascadeOnDelete | |
| phase_id | bigint unsigned | nullable, FK→project_phases | |
| parent_id | bigint unsigned | nullable, FK→tasks | Sub-task |
| milestone_id | bigint unsigned | nullable, FK→milestones | |
| title | varchar(500) | | |
| description | text | nullable | |
| status | enum('backlog','todo','in_progress','review','done','cancelled') | | Default 'todo' |
| priority | enum('low','medium','high','urgent') | | Default 'medium' |
| type | enum('task','bug','feature','improvement','documentation') | | Default 'task' |
| estimated_hours | decimal(8,2) | nullable | |
| actual_hours | decimal(8,2) | | Default 0 |
| start_date | date | nullable | |
| due_date | date | nullable | |
| completed_at | timestamp | nullable | |
| sort_order | integer | | Default 0 |
| created_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | |

#### task_labels
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(100) | | |
| color | varchar(20) | nullable | |
| created_at | timestamp | | |

#### task_label_task (pivot)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| label_id | bigint unsigned | FK→task_labels cascadeOnDelete | |

#### task_assignees
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| created_at | timestamp | | |

#### task_dependencies
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| depends_on_task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| dependency_type | enum('blocks','requires','relates_to') | | Default 'blocks' |
| created_at | timestamp | | |

#### task_comments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees | |
| comment | text | | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### task_attachments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| file_name | varchar(255) | | |
| file_path | varchar(255) | | |
| file_size | bigint | nullable | |
| file_type | varchar(50) | | |
| uploaded_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |

#### task_activities
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| task_id | bigint unsigned | FK→tasks cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees | |
| activity_type | varchar(50) | | created, status_changed, assigned, commented, etc |
| old_value | text | nullable | |
| new_value | text | nullable | |
| description | text | nullable | |
| created_at | timestamp | | |

#### milestones
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| project_id | bigint unsigned | FK→projects cascadeOnDelete | |
| name | varchar(255) | | |
| description | text | nullable | |
| target_date | date | | |
| completed_date | date | nullable | |
| status | enum('pending','in_progress','completed','delayed') | | Default 'pending' |
| sort_order | integer | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### timesheets
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| date | date | | |
| total_hours | decimal(5,2) | | Default 0 |
| status | enum('draft','submitted','approved','rejected') | | Default 'draft' |
| submitted_at | timestamp | nullable | |
| approved_by | bigint unsigned | nullable, FK→employees | |
| approved_at | timestamp | nullable | |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### timesheet_entries
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| timesheet_id | bigint unsigned | FK→timesheets cascadeOnDelete | |
| task_id | bigint unsigned | nullable, FK→tasks | |
| start_time | time | | |
| end_time | time | | |
| hours | decimal(5,2) | | |
| description | text | | |
| is_billable | boolean | | Default true |
| created_at | timestamp | | |

---

### 2.20 POS (POINT OF SALES)

#### product_categories
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| parent_id | bigint unsigned | nullable, FK→product_categories | |
| name | varchar(255) | | |
| description | text | nullable | |
| image | varchar(255) | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### products
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| category_id | bigint unsigned | nullable, FK→product_categories | |
| code | varchar(50) | UNIQUE | SKU / barcode |
| name | varchar(500) | | |
| description | text | nullable | |
| unit | varchar(50) | | pcs, kg, liter, box |
| purchase_price | decimal(20,2) | | Default 0 |
| selling_price | decimal(20,2) | | |
| stock | decimal(15,4) | | Default 0 |
| min_stock | decimal(15,4) | | Default 0 |
| max_stock | decimal(15,4) | nullable | |
| photo | varchar(255) | nullable | |
| is_taxable | boolean | | Default true |
| tax_rate | decimal(5,2) | | Default 11 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | |

#### product_variants
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| product_id | bigint unsigned | FK→products cascadeOnDelete | |
| name | varchar(255) | | Ukuran M, Warna Merah, dll |
| sku | varchar(50) | | |
| price_adjustment | decimal(15,2) | | Default 0 |
| stock | decimal(15,4) | | Default 0 |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### product_discounts
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| type | enum('percentage','fixed') | | |
| value | decimal(15,2) | | |
| min_purchase | decimal(20,2) | nullable | |
| start_date | date | | |
| end_date | date | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### pos_members
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| member_code | varchar(50) | UNIQUE | |
| name | varchar(255) | | |
| phone | varchar(30) | nullable | |
| email | varchar(255) | nullable | |
| points | integer | | Default 0 |
| total_spent | decimal(20,2) | | Default 0 |
| join_date | date | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### pos_vouchers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| code | varchar(50) | UNIQUE | |
| name | varchar(255) | | |
| type | enum('percentage','fixed') | | |
| value | decimal(15,2) | | |
| min_purchase | decimal(20,2) | | Default 0 |
| max_discount | decimal(20,2) | nullable | |
| usage_limit | integer | nullable | |
| used_count | integer | | Default 0 |
| start_date | date | | |
| end_date | date | | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### cashier_shifts
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees | |
| branch_id | bigint unsigned | nullable, FK→branches | |
| shift_date | date | | |
| opening_time | timestamp | | |
| opening_balance | decimal(20,2) | | Default 0 |
| closing_time | timestamp | nullable | |
| closing_balance | decimal(20,2) | nullable | |
| expected_cash | decimal(20,2) | nullable | |
| actual_cash | decimal(20,2) | nullable | |
| difference | decimal(20,2) | nullable | |
| total_transactions | integer | | Default 0 |
| total_sales | decimal(20,2) | | Default 0 |
| status | enum('open','closed','reconciled') | | Default 'open' |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### pos_transactions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| shift_id | bigint unsigned | FK→cashier_shifts | |
| receipt_number | varchar(50) | UNIQUE | |
| member_id | bigint unsigned | nullable, FK→pos_members | |
| cashier_id | bigint unsigned | FK→employees | |
| transaction_date | timestamp | | |
| subtotal | decimal(20,2) | | |
| discount_total | decimal(20,2) | | Default 0 |
| tax_total | decimal(20,2) | | Default 0 |
| grand_total | decimal(20,2) | | |
| payment_status | enum('pending','paid','partial','refunded') | | Default 'pending' |
| notes | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### pos_transaction_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| transaction_id | bigint unsigned | FK→pos_transactions cascadeOnDelete | |
| product_id | bigint unsigned | FK→products | |
| variant_id | bigint unsigned | nullable, FK→product_variants | |
| quantity | decimal(15,4) | | |
| unit_price | decimal(20,2) | | |
| discount_amount | decimal(20,2) | | Default 0 |
| tax_amount | decimal(20,2) | | Default 0 |
| subtotal | decimal(20,2) | | |
| created_at | timestamp | | |

#### pos_payments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| transaction_id | bigint unsigned | FK→pos_transactions cascadeOnDelete | |
| payment_method | varchar(50) | | cash, debit, credit, qris, transfer |
| amount | decimal(20,2) | | |
| reference_number | varchar(100) | nullable | |
| paid_at | timestamp | | |
| created_at | timestamp | | |

#### pos_refunds
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| transaction_id | bigint unsigned | FK→pos_transactions cascadeOnDelete | |
| refund_number | varchar(50) | UNIQUE | |
| amount | decimal(20,2) | | |
| reason | text | | |
| refund_date | timestamp | | |
| refunded_by | bigint unsigned | FK→employees | |
| approved_by | bigint unsigned | nullable, FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.21 COLLABORATION — CHAT

#### chats
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| chat_type | enum('personal','group','department') | | |
| name | varchar(255) | nullable | Nama grup |
| department_id | bigint unsigned | nullable, FK→departments | |
| created_by | bigint unsigned | FK→employees | |
| last_message | text | nullable | |
| last_message_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### chat_participants
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| chat_id | bigint unsigned | FK→chats cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| role | enum('admin','member') | | Default 'member' |
| last_read_at | timestamp | nullable | |
| joined_at | timestamp | | |
| created_at | timestamp | | |

#### chat_messages
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| chat_id | bigint unsigned | FK→chats cascadeOnDelete | |
| sender_id | bigint unsigned | FK→employees | |
| message_type | enum('text','image','file','voice','video','system') | | Default 'text' |
| message | text | nullable | |
| file_path | varchar(255) | nullable | |
| file_name | varchar(255) | nullable | |
| file_size | bigint | nullable | |
| reply_to_id | bigint unsigned | nullable, FK→chat_messages | |
| is_edited | boolean | | Default false |
| edited_at | timestamp | nullable | |
| created_at | timestamp | | |

#### chat_message_reads
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| message_id | bigint unsigned | FK→chat_messages cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| read_at | timestamp | | |
| created_at | timestamp | | |

#### chat_reactions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| message_id | bigint unsigned | FK→chat_messages cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| reaction | varchar(50) | | Emoji |
| created_at | timestamp | | |

---

### 2.22 COLLABORATION — MEETINGS & CALENDAR

#### meetings
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| organized_by | bigint unsigned | FK→employees | |
| title | varchar(500) | | |
| description | text | nullable | |
| start_time | timestamp | | |
| end_time | timestamp | | |
| location | varchar(255) | nullable | |
| meeting_link | varchar(255) | nullable | Zoom, Google Meet, Kolabo Meet |
| meeting_type | enum('online','onsite','hybrid') | | Default 'online' |
| status | enum('scheduled','in_progress','completed','cancelled') | | Default 'scheduled' |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### meeting_attendees
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| meeting_id | bigint unsigned | FK→meetings cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| response | enum('pending','accepted','declined','tentative') | | Default 'pending' |
| attended_at | timestamp | nullable | |
| left_at | timestamp | nullable | |
| created_at | timestamp | | |

#### meeting_minutes
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| meeting_id | bigint unsigned | FK→meetings cascadeOnDelete | |
| recorded_by | bigint unsigned | FK→employees | |
| content | text | | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### meeting_recaps (AI Generated)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| meeting_id | bigint unsigned | FK→meetings cascadeOnDelete | |
| summary | text | | Ringkasan oleh AI |
| key_points | json | | Array poin penting |
| sentiment | enum('positive','neutral','negative') | nullable | |
| transcript_path | varchar(255) | nullable | Path file transkrip |
| status | enum('pending','processing','completed','failed') | | Default 'pending' |
| ai_provider | varchar(100) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### meeting_action_items
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| meeting_id | bigint unsigned | FK→meetings cascadeOnDelete | |
| assigned_to | bigint unsigned | FK→employees | |
| title | varchar(500) | | |
| description | text | nullable | |
| due_date | date | nullable | |
| status | enum('pending','in_progress','completed','cancelled') | | Default 'pending' |
| completed_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### calendars
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | 'Libur Nasional', 'Event Kantor', dll |
| color | varchar(20) | nullable | |
| is_public | boolean | | Default false |
| created_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### calendar_events
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| calendar_id | bigint unsigned | FK→calendars cascadeOnDelete | |
| title | varchar(500) | | |
| description | text | nullable | |
| start_time | timestamp | | |
| end_time | timestamp | | |
| is_all_day | boolean | | Default false |
| location | varchar(255) | nullable | |
| color | varchar(20) | nullable | |
| eventable_type | varchar(100) | nullable | polymorphic: meeting, leave, task, etc |
| eventable_id | bigint unsigned | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.23 COLLABORATION — FORMS & CLOUD STORAGE

#### forms
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| description | text | nullable | |
| status | enum('draft','published','closed') | | Default 'draft' |
| collect_email | boolean | | Default false |
| max_submissions | integer | nullable | |
| current_submissions | integer | | Default 0 |
| expiration_date | timestamp | nullable | |
| created_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### form_fields
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| form_id | bigint unsigned | FK→forms cascadeOnDelete | |
| label | varchar(255) | | |
| field_type | enum('text','textarea','number','email','phone','date','time','file','select','multiselect','checkbox','radio','rating','signature') | | |
| placeholder | varchar(255) | nullable | |
| options | json | nullable | Untuk select/radio/checkbox |
| is_required | boolean | | Default false |
| validation_rules | varchar(500) | nullable | |
| sort_order | integer | | Default 0 |
| created_at | timestamp | | |

#### form_submissions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| form_id | bigint unsigned | FK→forms cascadeOnDelete | |
| submitter_email | varchar(255) | nullable | |
| submitted_by | bigint unsigned | nullable, FK→employees | |
| submitted_at | timestamp | | |
| created_at | timestamp | | |

#### form_field_values
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| submission_id | bigint unsigned | FK→form_submissions cascadeOnDelete | |
| field_id | bigint unsigned | FK→form_fields cascadeOnDelete | |
| value | text | nullable | |
| created_at | timestamp | | |

#### file_folders
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| parent_id | bigint unsigned | nullable, FK→file_folders | Self-referencing |
| name | varchar(255) | | |
| created_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### files
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| folder_id | bigint unsigned | nullable, FK→file_folders | |
| uploaded_by | bigint unsigned | FK→employees | |
| file_name | varchar(255) | | |
| original_name | varchar(500) | | |
| file_path | varchar(500) | | |
| file_size | bigint | | |
| mime_type | varchar(100) | | |
| extension | varchar(20) | | |
| is_public | boolean | | Default false |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | nullable | Soft delete |

#### file_versions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| file_id | bigint unsigned | FK→files cascadeOnDelete | |
| version_number | integer | | |
| file_path | varchar(500) | | |
| file_size | bigint | | |
| uploaded_by | bigint unsigned | FK→employees | |
| notes | text | nullable | |
| created_at | timestamp | | |

---

### 2.24 AI ASSISTANT

#### ai_providers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | User-defined name |
| api_format | enum('openai_compatible','anthropic','gemini') | | |
| base_url | varchar(500) | | |
| api_key_encrypted | text | | Encrypted |
| default_model | varchar(100) | nullable | |
| extra_headers | json | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### ai_conversations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| provider_id | bigint unsigned | FK→ai_providers | |
| title | varchar(500) | nullable | |
| model | varchar(100) | | |
| context_type | varchar(100) | nullable | 'hrm','finance','general', etc |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### ai_conversation_messages
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| conversation_id | bigint unsigned | FK→ai_conversations cascadeOnDelete | |
| role | enum('user','assistant','system') | | |
| content | text | | |
| tokens_used | integer | nullable | |
| created_at | timestamp | | |

#### ai_knowledge_base
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| title | varchar(500) | | |
| content | text | | |
| source_type | varchar(100) | | 'sop','policy','manual','faq' |
| embedding_vector | json | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

### 2.25 LMS (LEARNING MANAGEMENT SYSTEM)

#### courses
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| category | varchar(100) | nullable | |
| title | varchar(500) | | |
| description | text | nullable | |
| cover_image | varchar(255) | nullable | |
| duration_minutes | integer | nullable | |
| is_published | boolean | | Default false |
| enrollment_start | date | nullable | |
| enrollment_end | date | nullable | |
| created_by | bigint unsigned | FK→employees | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### course_modules
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| course_id | bigint unsigned | FK→courses cascadeOnDelete | |
| title | varchar(500) | | |
| description | text | nullable | |
| sort_order | integer | | Default 0 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### course_lessons
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| module_id | bigint unsigned | FK→course_modules cascadeOnDelete | |
| title | varchar(500) | | |
| content_type | enum('text','video','pdf','link','quiz') | | |
| content | text | nullable | |
| file_path | varchar(255) | nullable | |
| external_url | varchar(500) | nullable | |
| duration_minutes | integer | nullable | |
| sort_order | integer | | Default 0 |
| is_preview | boolean | | Default false |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### course_enrollments
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| course_id | bigint unsigned | FK→courses cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| enrolled_at | timestamp | | |
| started_at | timestamp | nullable | |
| completed_at | timestamp | nullable | |
| progress_percent | decimal(5,2) | | Default 0 |
| status | enum('enrolled','in_progress','completed','dropped') | | Default 'enrolled' |
| certificate_issued | boolean | | Default false |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### quizzes
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| lesson_id | bigint unsigned | FK→course_lessons cascadeOnDelete | |
| title | varchar(500) | | |
| description | text | nullable | |
| passing_score | decimal(5,2) | | Default 70 |
| time_limit_minutes | integer | nullable | |
| max_attempts | integer | nullable | |
| is_required | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### quiz_questions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| quiz_id | bigint unsigned | FK→quizzes cascadeOnDelete | |
| question | text | | |
| question_type | enum('multiple_choice','true_false','essay','matching','fill_blank') | | |
| options | json | nullable | |
| correct_answer | text | nullable | |
| points | integer | | Default 1 |
| sort_order | integer | | Default 0 |
| created_at | timestamp | | |

#### quiz_attempts
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| quiz_id | bigint unsigned | FK→quizzes cascadeOnDelete | |
| employee_id | bigint unsigned | FK→employees cascadeOnDelete | |
| started_at | timestamp | | |
| submitted_at | timestamp | nullable | |
| score | decimal(5,2) | nullable | |
| total_points | integer | | Default 0 |
| earned_points | integer | | Default 0 |
| is_passed | boolean | | Default false |
| attempt_number | integer | | Default 1 |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### quiz_attempt_answers
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| attempt_id | bigint unsigned | FK→quiz_attempts cascadeOnDelete | |
| question_id | bigint unsigned | FK→quiz_questions cascadeOnDelete | |
| answer | text | nullable | |
| is_correct | boolean | | Default false |
| points_earned | integer | | Default 0 |
| created_at | timestamp | | |

#### certificates
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | Template nama |
| template_html | text | | |
| template_css | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### certificate_issued
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| certificate_id | bigint unsigned | FK→certificates | |
| enrollment_id | bigint unsigned | FK→course_enrollments cascadeOnDelete | |
| certificate_number | varchar(50) | UNIQUE | |
| issued_date | date | | |
| file_path | varchar(255) | nullable | PDF path |
| created_at | timestamp | | |

---

### 2.26 PLATFORM — ROLES, NOTIFICATIONS, AUDIT

#### roles
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | Super Admin, HR Manager, Finance, Manager, Staff, Kasir, dll |
| slug | varchar(100) | | |
| description | text | nullable | |
| is_system | boolean | | Default false |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### permissions
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| name | varchar(255) | | 'view-employees', 'edit-payroll', dll |
| slug | varchar(100) | | |
| group | varchar(100) | | HRM, Accounting, CRM, System |
| created_at | timestamp | | |

#### role_permissions (pivot)
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| role_id | bigint unsigned | FK→roles cascadeOnDelete | |
| permission_id | bigint unsigned | FK→permissions cascadeOnDelete | |

#### notification_templates
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | |
| slug | varchar(100) | | |
| channel | enum('in_app','email','whatsapp','push') | | |
| subject | varchar(500) | nullable | |
| body | text | | Variable {{variable_name}} |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### notifications
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | FK→users cascadeOnDelete | |
| notification_type | varchar(100) | | leave_approval, payroll_published, task_assigned, dll |
| title | varchar(500) | | |
| body | text | | |
| data | json | nullable | |
| channel | enum('in_app','email','whatsapp') | | Default 'in_app' |
| is_read | boolean | | Default false |
| read_at | timestamp | nullable | |
| sent_at | timestamp | | |
| created_at | timestamp | | |

#### audit_logs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| user_id | bigint unsigned | nullable, FK→users | |
| company_id | bigint unsigned | FK→companies | |
| action | varchar(50) | | created, updated, deleted, login, logout |
| entity_type | varchar(100) | | Model class |
| entity_id | bigint unsigned | nullable | |
| old_values | json | nullable | |
| new_values | json | nullable | |
| ip_address | varchar(45) | nullable | |
| user_agent | text | nullable | |
| created_at | timestamp | | |

#### system_settings
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| key | varchar(100) | | |
| value | text | | |
| type | enum('string','integer','boolean','json','image') | | Default 'string' |
| group | varchar(50) | | general, attendance, payroll, notification, approval |
| description | varchar(500) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### integrations
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| name | varchar(255) | | User-defined |
| integration_type | varchar(50) | | whatsapp, email, sms, payment, ai, storage, google_docs |
| api_format | varchar(50) | | Format-based: openai_compatible, smtp, midtrans_format, dll |
| base_url | varchar(500) | nullable | |
| api_key_encrypted | text | nullable | |
| extra_config | json | nullable | |
| is_active | boolean | | Default true |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### webhooks
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| url | varchar(500) | | |
| event | varchar(100) | | employee.created, invoice.paid, etc |
| secret_encrypted | text | nullable | |
| is_active | boolean | | Default true |
| last_sent_at | timestamp | nullable | |
| last_response_code | integer | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### import_exports
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| user_id | bigint unsigned | FK→users | |
| type | enum('import','export') | | |
| entity | varchar(100) | | employees, products, clients |
| file_path | varchar(500) | | |
| file_type | enum('csv','xlsx','pdf') | | |
| status | enum('pending','processing','completed','failed') | | Default 'pending' |
| total_rows | integer | nullable | |
| processed_rows | integer | | Default 0 |
| error_rows | integer | | Default 0 |
| error_file_path | varchar(500) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

#### backup_logs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| company_id | bigint unsigned | FK→companies | |
| file_path | varchar(500) | | |
| file_size | bigint | | |
| database_size | bigint | | |
| status | enum('success','failed') | | Default 'success' |
| error_message | text | nullable | |
| created_at | timestamp | | |

#### scheduled_jobs
| Column | Type | Key | Description |
|---|---|---|---|
| id | bigint unsigned | PK | |
| job_name | varchar(255) | | |
| last_run_at | timestamp | nullable | |
| next_run_at | timestamp | nullable | |
| status | enum('idle','running','completed','failed') | | Default 'idle' |
| execution_time_ms | integer | nullable | |
| error_message | text | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

---

## 3. Summary

| Module | Tables Count | Features |
|---|---|---|
| Master Data | 7 | companies, branches, departments, positions, designations, grades, shifts_employees |
| Employees | 5 | employees, users, family_members, employee_documents, bank_accounts |
| Attendance | 7 | shifts, shift_employees, attendance_configs, wifi_aps, attendances, attendance_logs, late_config |
| Leave & Overtime | 6 | leave_types, leave_balances, leaves, leave_approvals, overtimes, overtime_rates |
| Reimbursement & Visit | 5 | reimbursement_categories, reimbursements, reimb_attachments, reimb_approvals, visits |
| Payroll | 10 | salary_components, employee_salary_components, payroll_periods, payrolls, payroll_items, pay_slips, pph21_configs, bpjs_configs, thr_configs, bonus_configs |
| Recruitment | 5 | job_postings, candidates, interviews, interviewers, interview_results |
| 360 Feedback | 4 | feedback_cycles, feedback_questions, feedback_reviewers, feedback_answers |
| Canteen & Announcements | 6 | canteen_menus, canteen_orders, canteen_order_items, announcements, announcement_reads, announcement_tags |
| Accounting Core | 7 | coa_categories, coa, coa_balances, journals, journal_entries, general_ledger, approval_flows |
| Invoices & Payments | 7 | invoices, invoice_items, payment_methods, payments, invoice_payments, payment_terms, payment_approvals |
| Budgets & Taxes | 6 | budgets, budget_items, budget_realizations, tax_configs, tax_transactions, tax_reports |
| Asset Management | 6 | asset_categories, assets, asset_depreciations, asset_mutations, asset_maintenances, asset_assignments |
| CRM Core | 9 | lead_sources, leads, lead_activities, clients, client_contacts, client_histories, pipeline_stages, deals, activities |
| CRM Segments | 3 | client_segments, client_segment_members, activity_types |
| CRM WhatsApp | 5 | wa_templates, wa_blast_campaigns, wa_blast_logs, wa_auto_replies, wa_conversations |
| Project Mgmt | 12 | projects, project_phases, project_members, tasks, task_labels, task_label_task, task_assignees, task_dependencies, task_comments, task_attachments, task_activities, milestones |
| Timesheet | 3 | timesheets, timesheet_entries, timesheet_approvals |
| POS | 12 | product_categories, products, product_variants, product_discounts, pos_members, pos_vouchers, cashier_shifts, pos_transactions, pos_transaction_items, pos_payments, pos_refunds, pos_discounts_applied |
| Chat | 6 | chats, chat_participants, chat_messages, chat_message_reads, chat_reactions, chat_attachments |
| Meetings & Calendar | 7 | meetings, meeting_attendees, meeting_minutes, meeting_recaps, meeting_action_items, calendars, calendar_events |
| Forms & Storage | 7 | forms, form_fields, form_submissions, form_field_values, files, file_folders, file_versions |
| AI Assistant | 4 | ai_providers, ai_conversations, ai_conversation_messages, ai_knowledge_base |
| LMS | 8 | courses, course_modules, course_lessons, course_enrollments, quizzes, quiz_questions, quiz_attempts, quiz_attempt_answers |
| Certificates | 2 | certificates, certificate_issued |
| Platform | 11 | roles, permissions, role_permissions, notification_templates, notifications, audit_logs, system_settings, integrations, webhooks, import_exports, backup_logs, scheduled_jobs |
| **TOTAL** | **~163 tables** | **150+ fitur/modul** |

---

## 4. Key Relationships Map

```
companies (1) ──< (many) branches
companies (1) ──< departments
companies (1) ──< positions
companies (1) ──< designations
companies (1) ──< grades
companies (1) ──< employees
companies (1) ──< shifts
companies (1) ──< leave_types
companies (1) ──< salary_components
companies (1) ──< coa
companies (1) ──< leads
companies (1) ──< clients
companies (1) ──< projects
companies (1) ──< products
companies (1) ──< courses
companies (1) ──< roles

employee ──< attendances
employee ──< leaves
employee ──< overtimes
employee ──< reimbursements
employee ──< payrolls
employee ──< family_members
employee ──< employee_documents
employee ──< task_assignees
employee ──< timesheets
employee ──< meetings (via attendees)
employee ──< chats (via participants)
employee ──< canteen_orders
employee ──< course_enrollments

project ──< tasks
project ──< phases
project ──< milestones
project ──< project_members

client ──< client_contacts
client ──< deals
lead ──< lead_activities

journal ──< journal_entries
journal_entry ──< coa
invoice ──< invoice_items
invoice ──< payments (via pivot)
asset ──< asset_depreciations
asset ──< asset_mutations
asset ──< asset_maintenances
```

---
*Generated: 2026-05-30 | BizOS v1.0 Architecture*
