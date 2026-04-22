# GPS-Based Employee Attendance Tracking System

## Overview
This document describes the GPS-based employee attendance tracking system implemented in MediCon. The system allows employees to check in/out using mobile applications with GPS location verification and geofencing capabilities.

## Database Schema

### Attendances Table
```sql
CREATE TABLE attendances (
    id BIGINT PRIMARY KEY,
    tenant_id BIGINT (FK to tenants),
    user_id BIGINT (FK to users),
    branch_id BIGINT (FK to branches),
    attendance_date DATE,
    
    -- Check-in Information
    check_in_time TIMESTAMP,
    check_in_latitude DECIMAL(10,8),
    check_in_longitude DECIMAL(11,8),
    check_in_within_geofence BOOLEAN,
    check_in_distance_meters FLOAT,
    
    -- Check-out Information
    check_out_time TIMESTAMP,
    check_out_latitude DECIMAL(10,8),
    check_out_longitude DECIMAL(11,8),
    check_out_within_geofence BOOLEAN,
    check_out_distance_meters FLOAT,
    
    -- Duration and Status
    total_minutes_worked INT,
    status ENUM('pending', 'checked_in', 'checked_out', 'incomplete'),
    
    -- Additional Information
    check_in_notes TEXT,
    check_out_notes TEXT,
    check_in_device_info VARCHAR(255),
    check_out_device_info VARCHAR(255),
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEXES: (tenant_id, attendance_date), (user_id, attendance_date), 
             (branch_id, attendance_date), (status)
);
```

## Core Components

### 1. Attendance Model (`app/Models/Attendance.php`)
- Relationships: User, Branch, Tenant
- Scopes: dateRange(), forUser(), forBranch(), byStatus(), today()
- Methods: calculateTotalMinutesWorked(), getFormattedDurationAttribute()
- Casts: Proper type casting for dates, decimals, and booleans

### 2. Attendance Service (`app/Services/AttendanceService.php`)
Core business logic for attendance operations:

**Key Methods:**
- `calculateDistance()` - Haversine formula for GPS distance calculation
- `isWithinGeofence()` - Validates if coordinates are within branch geofence
- `checkIn()` - Records employee check-in with GPS validation
- `checkOut()` - Records employee check-out with GPS validation
- `getUserAttendanceSummary()` - Generates attendance statistics

### 3. API Endpoints (`app/Http/Controllers/Api/AttendanceApiController.php`)
Mobile app endpoints (require Sanctum authentication):

**Endpoints:**
- `POST /api/attendance/check-in` - Check-in with GPS coordinates
- `POST /api/attendance/check-out` - Check-out with GPS coordinates
- `GET /api/attendance/today` - Get today's attendance status
- `GET /api/attendance/branch` - Get assigned branch details

**Request/Response Examples:**

Check-in Request:
```json
{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "branch_id": 1,
    "device_info": "iPhone 12 Pro",
    "notes": "Arrived early"
}
```

Check-in Response:
```json
{
    "success": true,
    "message": "Check-in successful",
    "attendance": { ... },
    "geofence_check": {
        "is_within": true,
        "distance": 45.5,
        "allowed_radius": 300
    }
}
```

### 4. Admin Controller (`app/Http/Controllers/AttendanceController.php`)
Admin dashboard for attendance management:

**Features:**
- View all attendance records with pagination
- Filter by: date range, user, branch, status, geofence compliance
- Export to CSV
- View detailed attendance records
- Generate attendance statistics and reports

### 5. Authorization Policy (`app/Policies/AttendancePolicy.php`)
- Admins can view/manage attendance for their tenant
- Super admins can view/manage all attendance
- Users can view their own attendance
- Only super admins can delete records

## Geofencing Implementation

### Haversine Formula
Distance calculation between two GPS coordinates:
```
R = 6,371,000 meters (Earth's radius)
Δlat = lat2 - lat1
Δlon = lon2 - lon1
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)
c = 2 × atan2(√a, √(1−a))
distance = R × c
```

### Geofence Validation
- Default radius: 300 meters (configurable per branch)
- Employees can check-in/out outside geofence but it's recorded
- Geofence violations are tracked for reporting

## Branch Configuration

Branches have GPS coordinates and geofencing settings:
```php
$branch->latitude;           // Decimal(10,8)
$branch->longitude;          // Decimal(11,8)
$branch->geofence_radius;    // Integer (meters)
$branch->requires_geofencing; // Boolean
```

## Mobile App Integration

### Authentication
Use Laravel Sanctum tokens:
```bash
POST /api/login
{
    "email": "employee@example.com",
    "password": "password"
}
```

### Check-in Flow
1. Get user's assigned branch: `GET /api/attendance/branch`
2. Get current GPS location from device
3. Submit check-in: `POST /api/attendance/check-in`
4. Display result (success/geofence warning)

### Check-out Flow
1. Get current GPS location from device
2. Submit check-out: `POST /api/attendance/check-out`
3. Display worked hours and summary

## Admin Dashboard Features

### Attendance List
- Paginated list of all attendance records
- Real-time filtering and search
- Status indicators (checked-in, checked-out, incomplete)
- Geofence compliance status

### Statistics View
- Total records in date range
- Days present vs incomplete
- Geofence violations count
- Average hours per day
- Per-user statistics

### Export
- CSV export with all attendance data
- Customizable date range and filters
- Includes GPS coordinates and geofence status

## API Routes

```php
// Web Routes (Admin Dashboard)
Route::prefix('admin/attendance')->name('admin.attendance.')->group(function () {
    Route::get('/', 'AttendanceController@index')->name('index');
    Route::get('/{attendance}', 'AttendanceController@show')->name('show');
    Route::get('/export/csv', 'AttendanceController@export')->name('export');
    Route::get('/statistics/view', 'AttendanceController@statistics')->name('statistics');
});

// API Routes (Mobile App)
Route::prefix('api/attendance')->middleware('auth:sanctum')->group(function () {
    Route::post('/check-in', 'AttendanceApiController@checkIn');
    Route::post('/check-out', 'AttendanceApiController@checkOut');
    Route::get('/today', 'AttendanceApiController@getTodayStatus');
    Route::get('/branch', 'AttendanceApiController@getBranch');
});
```

## Database Migration

Run migration to create attendances table:
```bash
php artisan migrate
```

## Testing

Test the attendance system:
```bash
php artisan test tests/Feature/AttendanceTest.php
```

## Future Enhancements

1. **Attendance Approval Workflow** - Managers approve/reject attendance
2. **Late Arrival Alerts** - Notify managers of late check-ins
3. **Overtime Tracking** - Track and report overtime hours
4. **Attendance Patterns** - Analyze attendance trends
5. **Mobile App** - Native iOS/Android applications
6. **Real-time Notifications** - Push notifications for check-in/out
7. **Biometric Integration** - Fingerprint/face recognition
8. **Offline Support** - Queue check-in/out when offline

