# BizOS Mobile API Documentation

API REST untuk aplikasi Flutter mobile BizOS. Base URL: `https://domain-anda.com/api/v1/mobile`

## Authentication

Semua endpoint kecuali `/login` dan `/register-device` memerlukan token Bearer di header:
```
Authorization: Bearer {token}
```

### POST /login
Login karyawan dengan email + password. Opsional: PIN untuk quick login.

**Request:**
```json
{
    "email": "karyawan@perusahaan.com",
    "password": "password123",
    "device_name": "iPhone 15 Pro",
    "device_token": "fcm_token_abc123",
    "platform": "ios",
    "pin": "123456"
}
```

**Response (200):**
```json
{
    "token": "1|abc123def456...",
    "user": {
        "id": 1,
        "name": "Budi Santoso",
        "email": "budi@perusahaan.com",
        "avatar": "https://domain.com/storage/avatars/1.jpg",
        "has_pin": true,
        "employee": {
            "id": 1,
            "employee_code": "EMP001",
            "first_name": "Budi",
            "last_name": "Santoso",
            "photo": "https://domain.com/storage/employees/photos/1.jpg",
            "department": "Engineering",
            "position": "Software Engineer",
            "branch": "Jakarta",
            "employee_type": "permanent",
            "status": "active"
        },
        "role": "Karyawan"
    }
}
```

**Error (422):**
```json
{
    "message": "Email atau password tidak sesuai.",
    "errors": { "email": ["Email atau password tidak sesuai."] }
}
```

### POST /logout
Revoke token saat ini.

**Response (200):**
```json
{ "message": "Berhasil logout." }
```

### GET /me
Profile user + employee saat ini.

**Response (200):**
```json
{
    "user": {
        "id": 1,
        "name": "Budi Santoso",
        "email": "budi@perusahaan.com",
        "avatar": "...",
        "has_pin": true,
        "employee": { ... },
        "role": "Karyawan"
    }
}
```

### POST /password/change
Ganti password.

**Request:**
```json
{
    "current_password": "oldpass",
    "new_password": "newpass123",
    "new_password_confirmation": "newpass123"
}
```

### POST /pin/setup
Buat atau ubah PIN login cepat.

**Request:**
```json
{
    "pin": "123456",
    "password": "currentpassword"
}
```

### POST /pin/remove
Hapus PIN.

**Request:**
```json
{ "password": "currentpassword" }
```

### POST /register-device
Register device untuk push notification (dengan token auth).

**Request:**
```json
{
    "token": "fcm_token_abc123",
    "platform": "ios",
    "device_name": "iPhone 15 Pro"
}
```

---

## Dashboard

### GET /dashboard
Ringkasan dashboard karyawan.

**Response (200):**
```json
{
    "data": {
        "today_attendance": {
            "clocked_in": true,
            "clocked_out": false,
            "clock_in_time": "08:15:00",
            "clock_out_time": null,
            "status": "late",
            "work_type": "office",
            "late_minutes": 15
        },
        "leave_balances": [
            {
                "leave_type": "Cuti Tahunan",
                "remaining_days": 8,
                "total_days": 12,
                "used_days": 4
            },
            {
                "leave_type": "Cuti Sakit",
                "remaining_days": 5,
                "total_days": 5,
                "used_days": 0
            }
        ],
        "pending": {
            "leaves": 1,
            "overtimes": 0,
            "reimbursements": 2,
            "total": 3
        },
        "monthly_summary": {
            "present_days": 15,
            "late_days": 3,
            "absent_days": 0,
            "total_overtime_hours": 4.5,
            "working_days_elapsed": 18
        },
        "unread_notifications": 3
    }
}
```

---

## Attendance

### POST /attendance/clock-in
Clock-in dengan GPS + selfie photo.

**Request:**
```json
{
    "latitude": -6.2088,
    "longitude": 106.8456,
    "photo_base64": "data:image/jpeg;base64,/9j/4AAQ...",
    "wifi_bssid": "aa:bb:cc:dd:ee:ff",
    "notes": "Lalu lintas macet"
}
```

**Response (200):**
```json
{
    "message": "Clock-in berhasil.",
    "data": {
        "clock_in_time": "08:15:23",
        "status": "late",
        "late_minutes": 15,
        "work_type": "office"
    }
}
```

### POST /attendance/clock-out
Clock-out dengan GPS + selfie photo.

**Request:**
```json
{
    "latitude": -6.2088,
    "longitude": 106.8456,
    "photo_base64": "data:image/jpeg;base64,...",
    "wifi_bssid": "aa:bb:cc:dd:ee:ff",
    "notes": "Selesai meeting"
}
```

**Response (200):**
```json
{
    "message": "Clock-out berhasil.",
    "data": {
        "clock_out_time": "18:15:00",
        "duration": "10j 0m",
        "overtime_minutes": 60
    }
}
```

### GET /attendance/history?month=2026-07
Riwayat absensi bulanan (paginated).

**Response (200):**
```json
{
    "data": [
        {
            "id": 1,
            "date": "2026-07-21",
            "clock_in": "08:15:00",
            "clock_out": "18:00:00",
            "status": "present",
            "work_type": "office",
            "late_minutes": 0,
            "overtime_minutes": 60,
            "shift": "Regular"
        }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 31
}
```

### GET /attendance/today
Status absensi hari ini.

**Response (200):**
```json
{
    "clocked_in": true,
    "clocked_out": false,
    "clock_in_time": "08:15:00",
    "clock_out_time": null,
    "status": "present",
    "work_type": "office",
    "late_minutes": 0,
    "overtime_minutes": 0,
    "shift_name": "Regular"
}
```

---

## Leave (Cuti)

### GET /leave-types
Daftar tipe cuti yang tersedia.

**Response:**
```json
{
    "data": [
        { "id": 1, "code": "annual", "name": "Cuti Tahunan", "default_days": 12, "max_days": 12, "is_paid": true, "require_attachment": false, "require_approval": true },
        { "id": 2, "code": "sick", "name": "Cuti Sakit", "default_days": 14, "max_days": 14, "is_paid": true, "require_attachment": true, "require_approval": true }
    ]
}
```

### GET /leave-balances
Sisa cuti tahun ini.

**Response:**
```json
{
    "data": [
        { "leave_type": "Cuti Tahunan", "total_days": 12, "used_days": 4, "remaining_days": 8, "is_annual": true },
        { "leave_type": "Cuti Sakit", "total_days": 5, "used_days": 0, "remaining_days": 5, "is_annual": false }
    ]
}
```

### GET /leaves?status=pending
Daftar pengajuan cuti (paginated).

### POST /leaves
Ajukan cuti baru.

**Request:**
```json
{
    "leave_type_id": 1,
    "start_date": "2026-08-01",
    "end_date": "2026-08-03",
    "reason": "Liburan keluarga",
    "attachment_base64": "data:application/pdf;base64,..."
}
```

**Response (201):**
```json
{
    "message": "Pengajuan cuti berhasil dibuat.",
    "data": { "id": 5, "status": "pending" }
}
```

### GET /leaves/{id}
Detail pengajuan cuti termasuk timeline approval.

**Response:**
```json
{
    "id": 5,
    "leave_type": "Cuti Tahunan",
    "start_date": "2026-08-01",
    "end_date": "2026-08-03",
    "total_days": 3,
    "reason": "Liburan keluarga",
    "status": "pending",
    "rejection_reason": null,
    "attachment_url": null,
    "approvals": [
        {
            "level": 1,
            "status": "pending",
            "approver": "Ahmad Manager",
            "notes": null,
            "approved_at": null
        }
    ],
    "created_at": "2026-07-21 15:30:00"
}
```

---

## Notifications

### GET /notifications
Notifikasi (paginated).

### POST /notifications/{id}/read
Tandai notifikasi sebagai dibaca.

### POST /notifications/read-all
Tandai semua notifikasi dibaca.

### GET /notifications/unread-count
Jumlah notifikasi belum dibaca.

**Response:**
```json
{ "unread_count": 3 }
```

---

## Profile

### GET /profile
Data lengkap karyawan.

### POST /profile
Update data profil (phone, address, city, province, postal_code, religion, nationality).

### POST /profile/photo
Upload foto profil (base64).

**Request:**
```json
{
    "photo_base64": "data:image/jpeg;base64,..."
}
```

**Response:**
```json
{
    "message": "Foto berhasil diunggah.",
    "photo_url": "https://domain.com/storage/employees/photos/1_1234567890.jpg"
}
```

### GET /payslips
Daftar slip gaji (paginated).

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "period": "JUL-2026",
            "start_date": "2026-07-01",
            "end_date": "2026-07-31",
            "payment_date": "2026-07-28",
            "gross_salary": 10000000.00,
            "net_salary": 8500000.00,
            "status": "paid",
            "has_slip": true,
            "slip_id": 25,
            "viewed_at": "2026-07-28 09:15:00"
        }
    ]
}
```

### GET /payslips/{id}/pdf
Download slip gaji PDF.

---

## Error Codes

| Status | Description |
|--------|-------------|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated |
| 404 | Not Found |
| 422 | Validation Error |

## Flutter Integration

```dart
// Example: Clock-in
final response = await http.post(
  Uri.parse('$baseUrl/api/v1/attendance/clock-in'),
  headers: {
    'Authorization': 'Bearer $token',
    'Content-Type': 'application/json',
  },
  body: jsonEncode({
    'latitude': position.latitude,
    'longitude': position.longitude,
    'photo_base64': base64Photo,
  }),
);

// Example: Dashboard
final response = await http.get(
  Uri.parse('$baseUrl/api/v1/dashboard'),
  headers: {'Authorization': 'Bearer $token'},
);
```

## Push Notification

Untuk menerima push notification, register FCM token via `/register-device`. Server akan mengirim notifikasi untuk:
- Pengingat absensi
- Cuti disetujui/ditolak
- Approval menunggu
- Slip gaji tersedia
