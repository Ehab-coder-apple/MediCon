# GPS ATTENDANCE SYSTEM - ADMINISTRATION GUIDE

**Version**: 1.0  
**Date**: October 24, 2025  
**Status**: Production Ready

---

## TABLE OF CONTENTS

1. Overview
2. System Architecture
3. Admin Dashboard
4. Viewing Attendance Records
5. Filtering and Searching
6. Viewing Individual Records
7. Statistics and Analytics
8. Exporting Data
9. User Management
10. Troubleshooting
11. FAQ
12. Support

---

## 1. OVERVIEW

### What is the GPS Attendance System?

The GPS Attendance System is a comprehensive employee attendance tracking solution that uses GPS technology and geofencing to ensure accurate, fraud-proof attendance records.

### Key Features

- **GPS Tracking**: Employees check in/out using their mobile phones
- **Geofencing**: 300-meter radius validation around pharmacy location
- **Real-time Monitoring**: View attendance records in real-time
- **Advanced Filtering**: Filter by date, employee, branch, status, geofence
- **Statistics**: View attendance analytics and compliance metrics
- **CSV Export**: Export attendance data for payroll integration
- **Multi-tenant Support**: Manage multiple branches/pharmacies
- **Security**: Role-based access control and audit trails

### Benefits

✅ Prevents time theft and fake check-ins
✅ Accurate attendance records
✅ Reduced payroll errors
✅ Compliance tracking
✅ Employee accountability
✅ Easy reporting and analytics

---

## 2. SYSTEM ARCHITECTURE

### Components

```
┌─────────────────────────────────────────┐
│         Admin Dashboard                 │
│  (Web Interface - Attendance Section)   │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│      AttendanceController               │
│  (index, show, statistics, export)      │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│      Attendance Model                   │
│  (ORM with relationships & scopes)      │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│      AttendanceService                  │
│  (GPS calculations, geofencing)         │
└──────────────┬──────────────────────────┘
               │
┌──────────────▼──────────────────────────┐
│      Database (attendances table)       │
│  (GPS coordinates, geofence data)       │
└─────────────────────────────────────────┘
```

### Database Schema

**Attendance Table Fields**:
- `id`: Unique identifier
- `tenant_id`: Organization identifier
- `user_id`: Employee identifier
- `branch_id`: Branch/pharmacy identifier
- `attendance_date`: Date of attendance
- `check_in_time`: Check-in timestamp
- `check_in_latitude`: Check-in GPS latitude
- `check_in_longitude`: Check-in GPS longitude
- `check_in_within_geofence`: Geofence validation (true/false)
- `check_in_distance_meters`: Distance from branch
- `check_out_time`: Check-out timestamp
- `check_out_latitude`: Check-out GPS latitude
- `check_out_longitude`: Check-out GPS longitude
- `check_out_within_geofence`: Geofence validation (true/false)
- `check_out_distance_meters`: Distance from branch
- `total_minutes_worked`: Total duration
- `status`: Attendance status (pending, checked_in, checked_out, incomplete)
- `check_in_notes`: Employee notes
- `check_out_notes`: Employee notes
- `check_in_device_info`: Device information
- `check_out_device_info`: Device information
- `created_at`: Record creation timestamp
- `updated_at`: Record update timestamp

---

## 3. ADMIN DASHBOARD

### Accessing the Dashboard

**URL**: `http://your-domain.com/admin/attendance`

**Requirements**:
- Admin or Super Admin role
- Active login session
- Same tenant access (for tenant admins)

### Dashboard Layout

The attendance dashboard consists of:

1. **Header Section**
   - Page title: "Attendance"
   - Breadcrumb navigation
   - Quick action buttons

2. **Filter Section**
   - Date range picker
   - Employee selector
   - Branch selector
   - Status filter
   - Geofence filter

3. **Action Buttons**
   - Export CSV
   - View Statistics
   - Refresh data

4. **Attendance Table**
   - Paginated list (15 records per page)
   - Sortable columns
   - Status badges
   - Action buttons

5. **Pagination Controls**
   - Previous/Next buttons
   - Page numbers
   - Records per page selector

---

## 4. VIEWING ATTENDANCE RECORDS

### Step 1: Access the Dashboard

1. Login to admin panel
2. Click "Human Resources" in sidebar
3. Click "Attendance"

### Step 2: View Records

The attendance table displays:

| Column | Description |
|--------|-------------|
| Employee | Employee name |
| Branch | Branch/pharmacy name |
| Date | Attendance date |
| Check-In | Check-in time (HH:MM) |
| Check-Out | Check-out time (HH:MM) |
| Duration | Total hours worked |
| Status | Attendance status badge |
| Geofence | Geofence compliance status |
| Actions | View/Edit buttons |

### Step 3: View Individual Record

1. Click "View" button on any record
2. See complete details:
   - Employee information
   - Check-in details with GPS coordinates
   - Check-out details with GPS coordinates
   - Geofence compliance status
   - Device information
   - Duration summary

---

## 5. FILTERING AND SEARCHING

### Filter Options

#### Date Range Filter
- **Default**: Last 30 days
- **Options**: Custom date range
- **Usage**: Click date picker, select start and end dates

#### Employee Filter
- **Default**: All employees
- **Options**: Dropdown list of employees
- **Usage**: Select employee name to filter

#### Branch Filter
- **Default**: All branches
- **Options**: Dropdown list of branches
- **Usage**: Select branch to filter

#### Status Filter
- **Options**:
  - All (default)
  - Pending
  - Checked In
  - Checked Out
  - Incomplete

#### Geofence Filter
- **Options**:
  - All (default)
  - Within Geofence
  - Outside Geofence

### Applying Filters

1. Select filter criteria
2. Click "Apply Filters" button
3. Table updates with filtered results
4. Click "Reset Filters" to clear all filters

### Search Tips

- Use date range for specific periods
- Combine multiple filters for precise results
- Export filtered results for reporting

---

## 6. VIEWING INDIVIDUAL RECORDS

### Record Details Page

Click "View" button to see complete record details:

#### Employee Information
- Name
- Email
- Branch assigned
- Tenant/Organization

#### Check-In Details
- Date and time
- GPS coordinates (latitude, longitude)
- Distance from branch
- Geofence status (Within/Outside)
- Device information
- Notes

#### Check-Out Details
- Date and time
- GPS coordinates (latitude, longitude)
- Distance from branch
- Geofence status (Within/Outside)
- Device information
- Notes

#### Summary
- Total hours worked
- Total minutes worked
- Attendance status
- Created/Updated timestamps

---

## 7. STATISTICS AND ANALYTICS

### Accessing Statistics

1. Click "Statistics" button on dashboard
2. View analytics dashboard

### Statistics Displayed

#### Overview Metrics
- Total attendance records
- Days present
- Incomplete days
- Geofence violations
- Average hours per day
- Attendance rate percentage

#### Status Breakdown
- Pending records
- Checked in records
- Checked out records
- Incomplete records

#### Geofence Compliance
- Records within geofence
- Records outside geofence
- Compliance percentage

### Using Statistics

- Monitor attendance trends
- Identify compliance issues
- Track employee performance
- Generate reports

---

## 8. EXPORTING DATA

### Export to CSV

#### Step 1: Apply Filters (Optional)
- Select date range
- Select employee
- Select branch
- Select status
- Select geofence status

#### Step 2: Click Export Button
- Click "Export CSV" button
- File downloads automatically

#### Step 3: Use Exported Data
- Open in Excel/Google Sheets
- Import to payroll system
- Create custom reports
- Archive records

### CSV File Format

**Columns**:
- Employee Name
- Email
- Branch
- Date
- Check-In Time
- Check-In Latitude
- Check-In Longitude
- Check-In Distance (meters)
- Check-In Geofence Status
- Check-Out Time
- Check-Out Latitude
- Check-Out Longitude
- Check-Out Distance (meters)
- Check-Out Geofence Status
- Total Minutes Worked
- Status
- Device Info

---

## 9. USER MANAGEMENT

### User Roles

#### Super Admin
- View all attendance records
- View all branches
- View all tenants
- Full system access

#### Tenant Admin
- View attendance for their tenant only
- View their branches
- Cannot view other tenants

#### Employee
- View their own attendance
- Cannot view other employees

### Permissions

| Action | Super Admin | Tenant Admin | Employee |
|--------|------------|-------------|----------|
| View Any | ✅ | ✅ | ❌ |
| View Record | ✅ | ✅ (own tenant) | ✅ (own) |
| Create | ❌ | ❌ | ❌ |
| Update | ✅ | ✅ (own tenant) | ❌ |
| Delete | ✅ | ❌ | ❌ |
| Export | ✅ | ✅ (own tenant) | ❌ |

---

## 10. TROUBLESHOOTING

### Issue 1: Cannot Access Attendance Section

**Cause**: Insufficient permissions

**Solution**:
1. Check user role (must be Admin or Super Admin)
2. Verify tenant assignment
3. Contact system administrator

### Issue 2: No Records Displayed

**Cause**: No attendance data or filters too restrictive

**Solution**:
1. Check date range
2. Reset all filters
3. Verify employees have checked in
4. Check branch assignment

### Issue 3: GPS Coordinates Missing

**Cause**: Employee checked in without GPS

**Solution**:
1. Check employee device GPS status
2. Verify GPS permission granted
3. Contact employee to re-check in

### Issue 4: Geofence Status Incorrect

**Cause**: Branch GPS coordinates incorrect

**Solution**:
1. Verify branch GPS coordinates
2. Check geofence radius setting
3. Update branch location if needed

### Issue 5: Export Not Working

**Cause**: Browser or permission issue

**Solution**:
1. Check browser compatibility
2. Verify export permission
3. Try different browser
4. Clear browser cache

---

## 11. FAQ

### Q: How often is attendance data updated?
**A**: Real-time. Data updates immediately when employee checks in/out.

### Q: Can I edit attendance records?
**A**: Only Super Admins can edit. Tenant Admins cannot edit.

### Q: How long is data retained?
**A**: Indefinitely. All historical data is retained.

### Q: Can I delete attendance records?
**A**: Only Super Admins can delete records.

### Q: How accurate is GPS tracking?
**A**: ±5-15 meters typically. Accuracy depends on device and environment.

### Q: What if employee is outside geofence?
**A**: Check-in is still recorded but marked as "Outside Geofence".

### Q: Can I change geofence radius?
**A**: Yes, contact system administrator to update branch settings.

### Q: How do I integrate with payroll?
**A**: Export CSV and import to payroll system.

### Q: What if employee forgets to check out?
**A**: Record marked as "Incomplete". Admin can manually update.

### Q: Can employees see their own records?
**A**: Yes, employees can view their own attendance.

---

## 12. SUPPORT

### Getting Help

**For Technical Issues**:
- Contact IT Department
- Email: it@company.com
- Phone: +971-XX-XXXXXXX

**For System Questions**:
- Contact HR Department
- Email: hr@company.com
- Phone: +971-XX-XXXXXXX

**For Mobile App Issues**:
- Contact Mobile Support
- Email: mobile@company.com
- Phone: +971-XX-XXXXXXX

### Documentation

- **Admin Guide**: This document
- **Employee Guide**: EMPLOYEE_GPS_CHECKIN_GUIDE.md
- **Mobile App Guide**: MOBILE_APP_GUIDE.md
- **System Guide**: GPS_ATTENDANCE_SYSTEM.md

### System Status

- **Status Page**: http://your-domain.com/status
- **Maintenance**: Scheduled for Sundays 2-4 AM
- **Support Hours**: 24/7

---

## APPENDIX A: KEYBOARD SHORTCUTS

| Shortcut | Action |
|----------|--------|
| Cmd+P / Ctrl+P | Quick search |
| Cmd+F / Ctrl+F | Find on page |
| Cmd+S / Ctrl+S | Save/Export |
| Cmd+P / Ctrl+P | Print |

---

## APPENDIX B: GLOSSARY

**Attendance**: Record of employee check-in and check-out

**Geofence**: Virtual boundary (300m radius) around branch

**GPS**: Global Positioning System for location tracking

**Tenant**: Organization/Company

**Branch**: Individual pharmacy/location

**Status**: Attendance state (pending, checked_in, checked_out, incomplete)

**Device Info**: Mobile phone information

**Coordinates**: Latitude and longitude GPS position

---

## APPENDIX C: CONTACT INFORMATION

**System Administrator**:
- Name: [Administrator Name]
- Email: admin@company.com
- Phone: +971-XX-XXXXXXX

**IT Support**:
- Email: support@company.com
- Phone: +971-XX-XXXXXXX
- Hours: 24/7

**HR Department**:
- Email: hr@company.com
- Phone: +971-XX-XXXXXXX

---

## DOCUMENT INFORMATION

**Title**: GPS Attendance System - Administration Guide

**Version**: 1.0

**Date**: October 24, 2025

**Status**: Production Ready

**Author**: MediCon Development Team

**Last Updated**: October 24, 2025

**Next Review**: January 24, 2026

---

**© 2025 MediCon. All Rights Reserved.**

**This document is confidential and intended for authorized personnel only.**

---

## PRINTING INSTRUCTIONS

### For Best Results:

1. **Page Setup**:
   - Paper Size: A4
   - Orientation: Portrait
   - Margins: 1 inch (2.54 cm)

2. **Print Settings**:
   - Color: Black & White or Color
   - Quality: High
   - Paper Type: Standard

3. **Page Range**:
   - Print all pages
   - Or select specific sections

4. **File Format**:
   - Save as PDF
   - Or print directly to printer

### Recommended Software:

- **macOS**: Preview or Safari
- **Windows**: Microsoft Edge or Chrome
- **Online**: Google Chrome or Firefox

---

**END OF DOCUMENT**

