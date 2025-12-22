# Testing Guide - New Admin Features

This guide covers testing the three new admin modules:
1. Leave Management
2. Branch Management
3. Geofence Configuration

---

## Prerequisites

- Web app running at `http://localhost:8000`
- Mobile app running on iPhone
- Admin user logged in to web dashboard
- Test employee account for mobile app

---

## Test Credentials

### Web Admin
- Email: `michael.admin@medicon.com`
- Password: `password`

### Mobile Employee
- Email: `maria.pharmacist@medicon.com`
- Password: `medicon123`

---

## Module 1: Leave Management Testing

### Test 1.1: View Leave Requests
1. Login to web dashboard as admin
2. Navigate to **Admin → Leave Requests**
3. Verify you see a list of leave requests
4. Check that columns show: Employee, Leave Type, Dates, Days, Status

**Expected Result:** ✅ Leave list displays with all pending/approved/rejected requests

### Test 1.2: Filter Leave Requests
1. On Leave Requests page, use filters:
   - Filter by Status: "Pending"
   - Filter by Employee: Select an employee
   - Filter by Leave Type: Select a type
   - Filter by Date Range: Set start and end dates
2. Click "Filter" button
3. Verify results are filtered correctly

**Expected Result:** ✅ Filters work and show only matching records

### Test 1.3: View Leave Details
1. Click "View" on any leave request
2. Verify you see:
   - Employee name and leave type
   - Start and end dates
   - Number of days
   - Reason (if provided)
   - Current status
   - Half-day information (if applicable)

**Expected Result:** ✅ All leave details display correctly

### Test 1.4: Approve Leave Request
1. On leave details page, scroll to "Approve Leave" section
2. Enter optional approval notes
3. Click "✓ Approve" button
4. Verify success message appears
5. Check status changed to "Approved"

**Expected Result:** ✅ Leave approved and status updated

### Test 1.5: Reject Leave Request
1. Go back to leave list
2. Click "View" on a pending leave request
3. Scroll to "Reject Leave" section
4. Enter rejection reason (required)
5. Click "✗ Reject" button
6. Verify success message appears
7. Check status changed to "Rejected"

**Expected Result:** ✅ Leave rejected with reason recorded

### Test 1.6: Export Leave Requests
1. On Leave Requests page, click "📥 Export CSV"
2. Verify CSV file downloads
3. Open CSV and verify it contains:
   - Employee names
   - Leave types
   - Dates
   - Number of days
   - Status
   - Reasons

**Expected Result:** ✅ CSV exports with all leave data

---

## Module 2: Branch Management Testing

### Test 2.1: View All Branches
1. Login to web dashboard as admin
2. Navigate to **Admin → Branches**
3. Verify you see a grid of branch cards
4. Each card should show:
   - Branch name and code
   - City
   - GPS coordinates
   - Geofence radius
   - Geofencing status (Required/Optional)
   - Active/Inactive status

**Expected Result:** ✅ All branches display in grid view

### Test 2.2: Filter Branches
1. Use filters:
   - Filter by Status: "Active" or "Inactive"
   - Filter by Geofencing: "Required" or "Not Required"
2. Click "Filter" button
3. Verify results are filtered

**Expected Result:** ✅ Filters work correctly

### Test 2.3: Create New Branch
1. Click "+ Add Branch" button
2. Fill in form:
   - Branch Name: "Test Pharmacy"
   - Branch Code: "TEST-001"
   - Address: "123 Test St"
   - City: "Test City"
   - State: "Test State"
   - Country: "Test Country"
   - Postal Code: "12345"
   - Latitude: 40.7128
   - Longitude: -74.0060
   - Geofence Radius: 300
   - Check "Require Geofencing"
   - Check "Active"
3. Click "Create Branch"
4. Verify success message

**Expected Result:** ✅ New branch created and appears in list

### Test 2.4: View Branch Details
1. Click "View" on any branch card
2. Verify you see:
   - Branch name and code
   - Full address
   - GPS coordinates
   - Geofence radius
   - Geofencing status
   - Contact information
   - Manager name
   - Active/Inactive status

**Expected Result:** ✅ All branch details display

### Test 2.5: Edit Branch
1. On branch details page, click "✎ Edit Branch"
2. Modify:
   - Geofence Radius: Change from 300 to 500
   - Uncheck "Require Geofencing"
3. Click "Save Changes"
4. Verify success message
5. Go back to details and confirm changes saved

**Expected Result:** ✅ Branch updated with new settings

### Test 2.6: Update GPS Coordinates
1. Edit a branch
2. Change Latitude and Longitude to new values
3. Save changes
4. Verify coordinates updated

**Expected Result:** ✅ GPS coordinates updated

---

## Module 3: Geofence Configuration Testing

### Test 3.1: Verify Geofence Settings in Mobile App
1. On iPhone, open MediCon app
2. Login with: `maria.pharmacist@medicon.com` / `medicon123`
3. Select a branch
4. App should fetch geofence settings from backend
5. Check app console logs for geofence radius value

**Expected Result:** ✅ Mobile app fetches geofence radius from backend

### Test 3.2: Change Geofence Radius and Test Mobile
1. In web admin, edit a branch
2. Change geofence radius from 300m to 500m
3. Save changes
4. On mobile app, force restart or logout/login
5. Verify app uses new 500m radius

**Expected Result:** ✅ Mobile app uses updated geofence radius

### Test 3.3: Disable Geofencing
1. In web admin, edit a branch
2. Uncheck "Require Geofencing"
3. Save changes
4. On mobile app, try to check-in from outside the geofence
5. Verify check-in is allowed (no geofence warning)

**Expected Result:** ✅ Check-in allowed when geofencing disabled

### Test 3.4: Enable Geofencing
1. In web admin, edit a branch
2. Check "Require Geofencing"
3. Save changes
4. On mobile app, try to check-in from outside the geofence
5. Verify check-in shows geofence warning

**Expected Result:** ✅ Geofence warning shown when outside radius

### Test 3.5: Test Multiple Branches
1. Create 2 branches with different geofence radii:
   - Branch A: 300m
   - Branch B: 500m
2. Assign user to both branches
3. On mobile app, select Branch A
4. Verify app uses 300m radius
5. Switch to Branch B
6. Verify app uses 500m radius

**Expected Result:** ✅ Each branch uses correct geofence radius

---

## Integration Testing

### Test 4.1: Leave Request → Attendance Sync
1. Employee submits leave request via mobile app
2. Admin approves leave in web dashboard
3. Verify leave status updates to "Approved"
4. Check that employee's attendance records reflect the leave

**Expected Result:** ✅ Leave approval syncs with attendance

### Test 4.2: Branch Update → Mobile App
1. Admin updates branch GPS coordinates
2. Mobile app fetches updated coordinates
3. Verify geofence validation uses new coordinates

**Expected Result:** ✅ Mobile app uses updated branch data

### Test 4.3: Multi-tenant Isolation
1. Create 2 tenants with different branches
2. Login as admin from Tenant 1
3. Verify you only see Tenant 1 branches
4. Verify you cannot access Tenant 2 branches

**Expected Result:** ✅ Tenants isolated from each other

---

## Performance Testing

### Test 5.1: Large Dataset
1. Create 100+ leave requests
2. Navigate to Leave Requests page
3. Verify page loads within 2 seconds
4. Verify pagination works

**Expected Result:** ✅ Page loads quickly with pagination

### Test 5.2: Filter Performance
1. Apply multiple filters on Leave Requests
2. Verify results load within 1 second

**Expected Result:** ✅ Filters respond quickly

---

## Error Handling Testing

### Test 6.1: Invalid GPS Coordinates
1. Try to create branch with invalid latitude (>90)
2. Verify error message appears

**Expected Result:** ✅ Validation error shown

### Test 6.2: Invalid Geofence Radius
1. Try to create branch with radius < 50m
2. Verify error message appears

**Expected Result:** ✅ Validation error shown

### Test 6.3: Unauthorized Access
1. Try to access `/admin/leaves` as non-admin user
2. Verify access denied

**Expected Result:** ✅ Access denied for non-admins

---

## Checklist

- [ ] All leave management features working
- [ ] All branch management features working
- [ ] Geofence configuration working
- [ ] Mobile app fetches geofence settings
- [ ] Multi-tenant isolation working
- [ ] Error handling working
- [ ] Performance acceptable
- [ ] CSV exports working
- [ ] Filters working
- [ ] Pagination working

---

## Known Issues / Notes

(Add any issues found during testing here)

---

## Sign-off

- Tested by: _______________
- Date: _______________
- Status: ✅ PASSED / ❌ FAILED

