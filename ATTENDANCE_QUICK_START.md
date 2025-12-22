# GPS-Based Attendance System - Quick Start Guide

## ✅ What's Been Implemented

### 1. Database
- ✅ Attendances table with GPS tracking fields
- ✅ Check-in/check-out coordinates and geofence validation
- ✅ Device information and notes tracking
- ✅ Performance indexes

### 2. Backend Components
- ✅ **Attendance Model** - Full ORM with relationships and scopes
- ✅ **AttendanceService** - GPS calculations, geofencing, check-in/out logic
- ✅ **AttendanceApiController** - Mobile app API endpoints
- ✅ **AttendanceController** - Admin dashboard controller
- ✅ **AttendancePolicy** - Authorization rules

### 3. Routes
- ✅ **API Routes** (Mobile App)
  - `POST /api/attendance/check-in`
  - `POST /api/attendance/check-out`
  - `GET /api/attendance/today`
  - `GET /api/attendance/branch`

- ✅ **Web Routes** (Admin Dashboard)
  - `GET /admin/attendance/` - List all attendance
  - `GET /admin/attendance/{attendance}` - View details
  - `GET /admin/attendance/export/csv` - Export to CSV
  - `GET /admin/attendance/statistics/view` - View statistics

### 4. Navigation
- ✅ Added "Attendance" menu item under "Human Resources" section in sidebar

## 🚀 How to Use

### For Mobile App Developers

#### 1. Authenticate
```bash
POST /api/login
{
    "email": "employee@example.com",
    "password": "password"
}
```

#### 2. Get Branch Information
```bash
GET /api/attendance/branch
Authorization: Bearer {token}
```

Response includes branch GPS coordinates and geofence radius.

#### 3. Check In
```bash
POST /api/attendance/check-in
Authorization: Bearer {token}
Content-Type: application/json

{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "branch_id": 1,
    "device_info": "iPhone 12 Pro iOS 15.0",
    "notes": "Arrived early"
}
```

#### 4. Check Out
```bash
POST /api/attendance/check-out
Authorization: Bearer {token}
Content-Type: application/json

{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "device_info": "iPhone 12 Pro iOS 15.0",
    "notes": "Leaving for the day"
}
```

#### 5. Get Today's Status
```bash
GET /api/attendance/today
Authorization: Bearer {token}
```

### For Admin Users

1. **Access Attendance Dashboard**
   - Click "Attendance" in sidebar under "Human Resources"
   - View all employee attendance records

2. **Filter Records**
   - By date range
   - By employee
   - By branch
   - By status (checked-in, checked-out, incomplete)
   - By geofence compliance

3. **View Details**
   - Click on any record to see full details
   - View GPS coordinates and geofence status
   - See device information

4. **Export Data**
   - Click "Export" to download CSV
   - Includes all GPS and geofence data

5. **View Statistics**
   - Click "Statistics" to see analytics
   - Total records, days present, violations, average hours

## 📱 GPS Geofencing

### How It Works
- Each branch has GPS coordinates (latitude, longitude)
- Each branch has a geofence radius (default: 300 meters)
- When employee checks in/out, distance is calculated using Haversine formula
- If distance ≤ radius: "Within Geofence" ✅
- If distance > radius: "Outside Geofence" ⚠️

### Haversine Formula
Calculates great-circle distance between two GPS points on Earth's surface.

```
Distance = 2 × R × arcsin(√(sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)))
R = 6,371,000 meters (Earth's radius)
```

## 🔐 Security

- **Sanctum Authentication** - Token-based API authentication
- **Policy-Based Authorization** - Role-based access control
- **Tenant Isolation** - Multi-tenant data separation
- **GPS Validation** - Coordinates validated on server
- **Audit Trail** - All timestamps recorded

## 📊 Data Tracked

For each check-in/check-out:
- Employee ID
- Branch/Location
- Timestamp (with timezone)
- GPS Coordinates (latitude, longitude)
- Distance from branch (meters)
- Geofence compliance status
- Device information
- Optional notes

## 🧪 Testing the System

### 1. Test API Endpoints
```bash
# Using Postman or curl
curl -X POST http://localhost:8000/api/attendance/check-in \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 40.7128,
    "longitude": -74.0060,
    "branch_id": 1,
    "device_info": "Test Device"
  }'
```

### 2. Test Admin Dashboard
- Login as admin
- Navigate to Attendance in sidebar
- View records, apply filters, export data

### 3. Test Geofencing
- Use different GPS coordinates
- Some within 300m of branch
- Some outside 300m
- Verify geofence status is recorded correctly

## 📋 Remaining Tasks

- [ ] **Create Attendance Views** - Build admin dashboard UI templates
- [ ] **Write Tests** - Unit and feature tests for attendance system

## 🔧 Configuration

### Geofence Radius
Edit in `app/Models/Branch.php`:
```php
$branch->geofence_radius = 300; // meters
```

### Timezone
Set in `.env`:
```
APP_TIMEZONE=UTC
```

## 📚 Documentation Files

- `GPS_ATTENDANCE_SYSTEM.md` - Detailed technical documentation
- `ATTENDANCE_IMPLEMENTATION.md` - Implementation details
- `ATTENDANCE_QUICK_START.md` - This file

## 🎯 Next Steps

1. **Create Views** - Build admin dashboard templates
2. **Write Tests** - Test geofencing, API endpoints, authorization
3. **Deploy** - Push to production
4. **Mobile App** - Develop iOS/Android apps using API

## 💡 Tips

- Always validate GPS coordinates on the server
- Use HTTPS for API endpoints in production
- Store tokens securely on mobile devices
- Implement token refresh mechanism
- Log all attendance changes for audit trail
- Consider offline support for mobile app

## 🆘 Troubleshooting

### API Returns 401 Unauthorized
- Check token is valid
- Verify token is included in Authorization header
- Token may have expired

### Geofence Always Shows False
- Check branch GPS coordinates are correct
- Verify geofence_radius is set
- Test with known coordinates

### Distance Calculation Seems Wrong
- Verify latitude/longitude format (decimal degrees)
- Check Earth's radius constant (6,371,000 meters)
- Ensure coordinates are in correct order (lat, lon)

## 📞 Support

For issues or questions, refer to:
- `GPS_ATTENDANCE_SYSTEM.md` for technical details
- `ATTENDANCE_IMPLEMENTATION.md` for implementation info
- Laravel documentation for framework-specific questions

