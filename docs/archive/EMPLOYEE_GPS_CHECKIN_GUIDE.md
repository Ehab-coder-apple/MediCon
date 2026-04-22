# Employee GPS Check-In Guide - How It Works ✅

## 🎯 Overview

This guide explains how employees use their mobile phones to check in/out with GPS tracking and geofencing validation.

---

## 📱 Step-by-Step Process

### Step 1: Employee Opens Mobile App
```
┌─────────────────────────────────┐
│   MediCon Attendance App        │
│                                 │
│   [Login Screen]                │
│   Email: employee@medicon.com   │
│   Password: ••••••••            │
│                                 │
│   [Login Button]                │
└─────────────────────────────────┘
```

**What Happens:**
- Employee enters credentials
- App authenticates with server
- Gets API token for future requests

---

### Step 2: App Requests GPS Permission
```
┌─────────────────────────────────┐
│   Location Permission           │
│                                 │
│   "MediCon needs access to      │
│    your location"               │
│                                 │
│   [Allow]  [Don't Allow]        │
└─────────────────────────────────┘
```

**What Happens:**
- iOS: Requests "While Using" permission
- Android: Requests "Fine Location" permission
- User must grant permission to proceed

---

### Step 3: App Gets Current GPS Location
```
Device GPS Module Activates:
├── Connects to GPS satellites
├── Gets Latitude: 25.2048°N
├── Gets Longitude: 55.2708°E
├── Gets Accuracy: ±10 meters
└── Gets Timestamp: 08:00:00 AM
```

**Technical Details:**
- Uses device's built-in GPS
- Accuracy typically ±5-15 meters
- Takes 5-30 seconds to get fix
- Works even without internet (for location)

---

### Step 4: App Displays Branch Location on Map
```
┌─────────────────────────────────┐
│   [Map View]                    │
│                                 │
│   🔵 Branch Location            │
│      Downtown Pharmacy          │
│      Latitude: 25.2050°N        │
│      Longitude: 55.2710°E       │
│                                 │
│   🔴 Your Location              │
│      Latitude: 25.2048°N        │
│      Longitude: 55.2708°E       │
│                                 │
│   ⭕ Geofence Circle (300m)     │
│                                 │
│   Distance: 45 meters           │
│   Status: ✅ WITHIN ZONE        │
└─────────────────────────────────┘
```

**What Happens:**
- Shows branch location (blue pin)
- Shows employee location (red pin)
- Shows geofence circle (300m radius)
- Calculates distance between them

---

### Step 5: Distance Calculation (Haversine Formula)
```
Formula: d = 2R × arcsin(√(sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)))

Where:
- R = Earth's radius (6,371 km)
- Δlat = Latitude difference
- Δlon = Longitude difference
- lat1, lat2 = Latitudes

Example:
- Branch: 25.2050°N, 55.2710°E
- Employee: 25.2048°N, 55.2708°E
- Distance: 45 meters ✅ WITHIN 300m
```

---

### Step 6: Employee Taps Check-In Button
```
┌─────────────────────────────────┐
│   Dashboard                     │
│                                 │
│   Status: Not Checked In        │
│   Check-In: -                   │
│   Check-Out: -                  │
│   Duration: -                   │
│                                 │
│   [CHECK IN BUTTON]             │
│   [View Map]                    │
│   [View History]                │
└─────────────────────────────────┘
```

**What Happens:**
- App collects GPS data
- Validates geofence
- Sends to server

---

### Step 7: Server Validates and Stores
```
Server Processing:
1. Receives check-in request
   {
     "latitude": 25.2048,
     "longitude": 55.2708,
     "branch_id": 1,
     "device_info": "iPhone 14 Pro",
     "notes": "Morning check-in"
   }

2. Validates GPS coordinates
   - Latitude: -90 to 90 ✅
   - Longitude: -180 to 180 ✅

3. Calculates distance
   - Distance: 45 meters ✅
   - Geofence radius: 300 meters ✅
   - Status: WITHIN GEOFENCE ✅

4. Creates attendance record
   - user_id: 5
   - branch_id: 1
   - check_in_time: 2025-10-24 08:00:00
   - check_in_latitude: 25.2048
   - check_in_longitude: 55.2708
   - check_in_within_geofence: true
   - check_in_distance_meters: 45
   - status: checked_in

5. Returns success response
   {
     "success": true,
     "message": "Check-in successful",
     "data": {
       "check_in_time": "2025-10-24 08:00:00",
       "within_geofence": true,
       "distance_meters": 45,
       "status": "checked_in"
     }
   }
```

---

### Step 8: App Shows Confirmation
```
┌─────────────────────────────────┐
│   ✅ Check-In Successful!       │
│                                 │
│   Time: 08:00 AM                │
│   Location: Within Geofence     │
│   Distance: 45 meters           │
│                                 │
│   [OK]                          │
└─────────────────────────────────┘
```

**Dashboard Updates:**
```
┌─────────────────────────────────┐
│   Today's Attendance            │
│                                 │
│   Status: ✅ Checked In         │
│   Check-In: 08:00 AM            │
│   Check-Out: -                  │
│   Duration: 0h 0m               │
│                                 │
│   [CHECK OUT BUTTON]            │
│   [View Map]                    │
│   [View History]                │
└─────────────────────────────────┘
```

---

### Step 9: Employee Works During Day
```
App Continues Running:
- Tracks location in background
- Updates duration in real-time
- Shows current status
- Allows check-out anytime
```

---

### Step 10: Employee Taps Check-Out Button
```
Same process as check-in:
1. Gets current GPS location
2. Validates geofence
3. Sends to server
4. Server stores check-out data
5. Calculates total hours worked
6. Shows confirmation
```

**Server Response:**
```json
{
  "success": true,
  "message": "Check-out successful",
  "data": {
    "check_out_time": "2025-10-24 17:00:00",
    "within_geofence": true,
    "distance_meters": 50,
    "total_minutes_worked": 540,
    "status": "checked_out"
  }
}
```

---

## 🗺️ Geofencing Explained

### What is Geofencing?
```
A virtual boundary around a physical location (300 meters radius)

┌─────────────────────────────────┐
│                                 │
│      ⭕ Geofence Circle         │
│     /                 \         │
│    /                   \        │
│   |    🏪 Branch       |        │
│   |   Downtown Pharm   |        │
│    \                   /        │
│     \                 /         │
│      ⭕ 300m Radius   ⭕        │
│                                 │
│  ✅ Inside = Can Check In       │
│  ❌ Outside = Cannot Check In   │
│                                 │
└─────────────────────────────────┘
```

### Why Geofencing?
- ✅ Prevents fake check-ins from home
- ✅ Ensures employee is at work location
- ✅ Tracks actual attendance
- ✅ Prevents time theft

---

## 📊 Admin Dashboard View

After check-in, admin can see:

```
Attendance Record:
├── Employee: John Doe
├── Date: Oct 24, 2025
├── Check-In Time: 08:00 AM
├── Check-In Location: 25.2048°N, 55.2708°E
├── Check-In Distance: 45 meters
├── Check-In Status: ✅ Within Geofence
├── Check-Out Time: 05:00 PM
├── Check-Out Location: 25.2049°N, 55.2709°E
├── Check-Out Distance: 50 meters
├── Check-Out Status: ✅ Within Geofence
├── Total Hours: 9 hours
└── Device: iPhone 14 Pro
```

---

## ⚠️ Common Issues & Solutions

### Issue 1: "GPS Not Available"
**Cause:** GPS disabled or no satellite signal
**Solution:**
- Enable GPS in phone settings
- Go outside for better signal
- Wait 30 seconds for GPS fix

### Issue 2: "Outside Geofence"
**Cause:** Employee is >300m from branch
**Solution:**
- Move closer to branch
- Check if branch location is correct
- Contact admin if location is wrong

### Issue 3: "No Internet Connection"
**Cause:** WiFi/Mobile data not available
**Solution:**
- Connect to WiFi
- Enable mobile data
- Move to area with signal

### Issue 4: "Permission Denied"
**Cause:** GPS permission not granted
**Solution:**
- Go to Settings > Privacy > Location
- Enable location for MediCon app
- Restart app

---

## 🔐 Security Features

- ✅ GPS coordinates verified
- ✅ Geofence validation
- ✅ Timestamp recorded
- ✅ Device info tracked
- ✅ Token-based authentication
- ✅ Server-side validation

---

## 📱 Supported Platforms

- ✅ iOS 12+
- ✅ Android 8+
- ✅ Requires GPS hardware
- ✅ Requires internet connection

---

## 🚀 Getting Started

### For Employees:
1. Download MediCon Attendance app
2. Login with credentials
3. Grant GPS permission
4. Tap "Check In" when at work
5. Tap "Check Out" when leaving

### For Admins:
1. Access admin dashboard
2. Go to Attendance section
3. View all check-ins/outs
4. See GPS coordinates
5. Verify geofence status

---

## 📞 Support

**Issues?**
- Contact IT department
- Check GPS is enabled
- Ensure location permission granted
- Verify internet connection

---

**Status**: ✅ READY FOR EMPLOYEES
**Last Updated**: 2025-10-24
**Version**: 1.0

