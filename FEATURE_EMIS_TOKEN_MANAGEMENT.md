# Feature: EMIS API Token Management & NISN Validation

## Overview
Fitur ini memungkinkan admin untuk mengelola token EMIS Kemenag yang digunakan untuk memvalidasi NISN calon siswa pada saat pendaftaran PPDB. Token JWT dari EMIS memiliki masa berlaku terbatas (~4-5 jam), sehingga admin perlu memperbarui token secara berkala.

## Files Created/Modified

### New Files:
1. **Migration:** `database/migrations/2025_12_11_210000_create_api_tokens_table.php`
   - Creates `api_tokens` table for storing API tokens
   - Inserts default empty EMIS token record

2. **Service:** `app/Services/EmisNisnService.php`
   - Service class for EMIS API integration
   - Methods:
     - `cekNisn($nisn)` - Check NISN against EMIS API (Kemdikbud & Kemenag)
     - `validateNisnFormat($nisn)` - Validate NISN format (10 digits)
     - `checkTokenStatus()` - Check if token is configured and valid
     - `extractStudentData($emisData)` - Extract student data for form prefill

3. **Controller:** `app/Http/Controllers/Admin/EmisTokenController.php`
   - Admin controller for managing EMIS token
   - Methods:
     - `index()` - Display token management page
     - `update(Request $request)` - Update EMIS token

4. **View:** `resources/views/admin/pengaturan/update-emis-token.blade.php`
   - Admin page for updating EMIS token
   - Features JWT validation client-side
   - Shows token expiry status

### Modified Files:
1. **Routes:** `routes/ppdb.php`
   - Added routes for EmisTokenController
   - Added API route for NISN check (`ppdb.api.cek-nisn`)

2. **Config:** `config/adminlte.php`
   - Added menu item "Update Token EMIS" under SYSTEM section

3. **Controller:** `app/Http/Controllers/Ppdb/RegisterController.php`
   - Modified `validateNisn()` to use EmisNisnService
   - Added `apiCekNisn()` for AJAX NISN validation
   - Modified `step2()` and `step3()` to pass EMIS data for prefill

4. **Views:**
   - `resources/views/ppdb/step1.blade.php` - Added "Cek NISN" button with AJAX
   - `resources/views/ppdb/step2.blade.php` - Added EMIS data prefill for nama, tempat_lahir, tanggal_lahir, jenis_kelamin, sekolah_asal
   - `resources/views/ppdb/step3.blade.php` - Added EMIS data prefill for nama_ayah, nama_ibu

## Database Schema

### Table: `api_tokens`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(255) | Token identifier (unique) |
| token | text | JWT token value |
| description | varchar(255) | Token description |
| expires_at | timestamp | Token expiry time |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

## Routes

### Admin Routes
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /admin/pengaturan/update-emis-token | admin.pengaturan.update-emis-token.index | Token management page |
| POST | /admin/pengaturan/update-emis-token | admin.pengaturan.update-emis-token.update | Update token |

### Public API Routes
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | /ppdb/api/cek-nisn | ppdb.api.cek-nisn | AJAX NISN validation |

## Usage

### Admin: Update Token
1. Navigate to Admin > SYSTEM > Update Token EMIS
2. Get new token from EMIS Kemenag:
   - Login to EMIS Kemenag
   - Open Developer Tools (F12) > Network tab
   - Make an API request (e.g., search NISN)
   - Find the request and copy Authorization header value (without "Bearer ")
3. Paste token in the form
4. Token info (format, expiry) will be validated automatically
5. Click "Update Token"

### Registration: NISN Check
1. User enters NISN on Step 1
2. Click "Cek NISN" button
3. System validates against EMIS API:
   - **Found:** Shows student data preview
   - **Not Found:** Shows warning but allows to continue
   - **Already Registered:** Shows error
4. On form submit, EMIS data is stored in session
5. Step 2 & 3 forms are prefilled with EMIS data

## API Response Structure

### EmisNisnService::cekNisn()
```php
// Success
[
    'success' => true,
    'message' => 'Data NISN ditemukan',
    'data' => [
        'kemdikbud' => [...], // Kemdikbud data
        'kemenag' => [...]    // Kemenag data
    ]
]

// Not Found
[
    'success' => false,
    'message' => 'NISN tidak ditemukan dalam database EMIS',
    'data' => null
]
```

### API Endpoint Response (ppdb.api.cek-nisn)
```json
{
    "success": true,
    "message": "Data NISN ditemukan",
    "data": {
        "raw": {
            "kemdikbud": {...},
            "kemenag": {...}
        },
        "extracted": {
            "nama": "NAMA SISWA",
            "nisn": "1234567890",
            "tempat_lahir": "JAKARTA",
            "tanggal_lahir": "2008-05-15",
            "jenis_kelamin": "L",
            "nama_ibu": "NAMA IBU",
            "nama_ayah": "NAMA AYAH",
            "asal_sekolah": "SMP NEGERI 1 JAKARTA"
        }
    }
}
```

## Configuration

### .env (Optional)
```env
EMIS_BEARER_TOKEN=your_jwt_token_here
```

Note: Token from database takes precedence over .env config.

### config/services.php (Optional)
```php
'emis' => [
    'api_url' => env('EMIS_API_URL', 'https://api-emis.kemenag.go.id/v1'),
    'bearer_token' => env('EMIS_BEARER_TOKEN'),
],
```

## Testing

### Test Token Update
1. Login as admin
2. Go to `/admin/pengaturan/update-emis-token`
3. Paste a valid JWT token
4. Verify token info is displayed correctly
5. Submit and check success message

### Test NISN Check
1. Go to `/ppdb/register/step1`
2. Enter a valid NISN (10 digits)
3. Click "Cek NISN"
4. Verify response (found/not found)
5. Continue to Step 2 and verify data prefill

## Error Handling

1. **Token Not Configured:** Shows message to set token first
2. **Token Expired:** Shows expiry warning on admin page
3. **API Connection Error:** Shows network error message
4. **Invalid JWT Format:** Shows format error on admin page
5. **NISN Already Registered:** Prevents duplicate registration

## Security Notes

1. Token is partially masked when displayed (first 50 + last 20 chars)
2. Token is stored in database, not in source code
3. CSRF protection on all form submissions
4. Admin authentication required for token management
