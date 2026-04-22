# Mobile App Implementation Guide - GPS Attendance ✅

## 📱 Overview

This guide explains how to build a mobile app that enables employees to check in/out using GPS tracking with geofencing validation.

---

## 🎯 How It Works

### 1. **Employee Opens Mobile App**
```
┌─────────────────────────────────┐
│   Mobile App Starts             │
│   - Requests GPS Permission     │
│   - Gets Device Location        │
│   - Shows Branch Location       │
│   - Shows Distance to Branch    │
└─────────────────────────────────┘
```

### 2. **GPS Location Tracking**
```
┌─────────────────────────────────┐
│   Device GPS Module             │
│   - Latitude: 25.2048           │
│   - Longitude: 55.2708          │
│   - Accuracy: ±10 meters        │
│   - Timestamp: 2025-10-24 08:00 │
└─────────────────────────────────┘
```

### 3. **Geofence Validation**
```
┌─────────────────────────────────┐
│   Branch Location               │
│   - Latitude: 25.2050           │
│   - Longitude: 55.2710          │
│   - Geofence Radius: 300m       │
│                                 │
│   Distance Calculation          │
│   - Using Haversine Formula     │
│   - Result: 45 meters           │
│   - Status: ✅ WITHIN GEOFENCE  │
└─────────────────────────────────┘
```

### 4. **Check-In/Out Request**
```
POST /api/attendance/check-in
{
    "latitude": 25.2048,
    "longitude": 55.2708,
    "branch_id": 1,
    "device_info": "iPhone 14 Pro",
    "notes": "Morning check-in"
}
```

### 5. **Server Response**
```
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

## 🛠️ Technology Stack

### Recommended Frameworks

#### **Option 1: React Native** (Best for iOS + Android)
```bash
npx react-native init MediConAttendance
npm install @react-native-camera/camera
npm install react-native-geolocation-service
npm install axios
npm install @react-navigation/native
```

#### **Option 2: Flutter** (Best for Performance)
```bash
flutter create medicon_attendance
flutter pub add geolocator
flutter pub add http
flutter pub add google_maps_flutter
```

#### **Option 3: Native iOS** (Swift)
```swift
import CoreLocation
import MapKit
```

#### **Option 4: Native Android** (Kotlin)
```kotlin
import android.location.Location
import com.google.android.gms.location.FusedLocationProviderClient
```

---

## 📍 GPS Implementation

### Step 1: Request GPS Permission

**React Native Example:**
```javascript
import { request, PERMISSIONS, RESULTS } from 'react-native-permissions';

const requestGPSPermission = async () => {
  const result = await request(PERMISSIONS.IOS.LOCATION_WHEN_IN_USE);
  return result === RESULTS.GRANTED;
};
```

**Flutter Example:**
```dart
import 'package:geolocator/geolocator.dart';

Future<bool> requestLocationPermission() async {
  final permission = await Geolocator.requestPermission();
  return permission == LocationPermission.whileInUse;
}
```

### Step 2: Get Current Location

**React Native Example:**
```javascript
import Geolocation from 'react-native-geolocation-service';

const getCurrentLocation = () => {
  return new Promise((resolve, reject) => {
    Geolocation.getCurrentPosition(
      (position) => {
        resolve({
          latitude: position.coords.latitude,
          longitude: position.coords.longitude,
          accuracy: position.coords.accuracy,
        });
      },
      (error) => reject(error),
      { enableHighAccuracy: true, timeout: 15000, maximumAge: 10000 }
    );
  });
};
```

**Flutter Example:**
```dart
import 'package:geolocator/geolocator.dart';

Future<Position> getCurrentLocation() async {
  return await Geolocator.getCurrentPosition(
    desiredAccuracy: LocationAccuracy.best,
  );
}
```

### Step 3: Display Branch Location

**React Native Example:**
```javascript
import MapView, { Marker, Circle } from 'react-native-maps';

const BranchMap = ({ branch, userLocation }) => {
  return (
    <MapView
      initialRegion={{
        latitude: branch.latitude,
        longitude: branch.longitude,
        latitudeDelta: 0.01,
        longitudeDelta: 0.01,
      }}
    >
      {/* Branch Location */}
      <Marker
        coordinate={{
          latitude: branch.latitude,
          longitude: branch.longitude,
        }}
        title={branch.name}
        pinColor="blue"
      />

      {/* Geofence Circle */}
      <Circle
        center={{
          latitude: branch.latitude,
          longitude: branch.longitude,
        }}
        radius={branch.geofence_radius}
        fillColor="rgba(0, 150, 255, 0.1)"
        strokeColor="rgba(0, 150, 255, 0.5)"
      />

      {/* User Location */}
      <Marker
        coordinate={{
          latitude: userLocation.latitude,
          longitude: userLocation.longitude,
        }}
        title="Your Location"
        pinColor="red"
      />
    </MapView>
  );
};
```

---

## ✅ Check-In/Out Implementation

### React Native Example

```javascript
import axios from 'axios';

const API_BASE_URL = 'http://your-server.com/api';

const checkIn = async (latitude, longitude, branchId, token) => {
  try {
    const response = await axios.post(
      `${API_BASE_URL}/attendance/check-in`,
      {
        latitude,
        longitude,
        branch_id: branchId,
        device_info: 'iPhone 14 Pro',
        notes: 'Morning check-in',
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      }
    );

    return response.data;
  } catch (error) {
    throw error.response?.data || error.message;
  }
};

const checkOut = async (latitude, longitude, token) => {
  try {
    const response = await axios.post(
      `${API_BASE_URL}/attendance/check-out`,
      {
        latitude,
        longitude,
        device_info: 'iPhone 14 Pro',
        notes: 'Evening check-out',
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      }
    );

    return response.data;
  } catch (error) {
    throw error.response?.data || error.message;
  }
};
```

### Flutter Example

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class AttendanceService {
  static const String baseUrl = 'http://your-server.com/api';

  static Future<Map<String, dynamic>> checkIn(
    double latitude,
    double longitude,
    int branchId,
    String token,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/attendance/check-in'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'latitude': latitude,
        'longitude': longitude,
        'branch_id': branchId,
        'device_info': 'Android Device',
        'notes': 'Morning check-in',
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Check-in failed');
    }
  }

  static Future<Map<String, dynamic>> checkOut(
    double latitude,
    double longitude,
    String token,
  ) async {
    final response = await http.post(
      Uri.parse('$baseUrl/attendance/check-out'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'latitude': latitude,
        'longitude': longitude,
        'device_info': 'Android Device',
        'notes': 'Evening check-out',
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Check-out failed');
    }
  }
}
```

---

## 🔐 Authentication

### Get API Token

```javascript
// Login to get token
const login = async (email, password) => {
  const response = await axios.post(
    `${API_BASE_URL}/login`,
    { email, password }
  );
  
  const token = response.data.token;
  // Store token securely
  await SecureStore.setItem('auth_token', token);
  return token;
};
```

---

## 📊 API Endpoints

### 1. Check-In
```
POST /api/attendance/check-in
Authorization: Bearer {token}

Request:
{
    "latitude": 25.2048,
    "longitude": 55.2708,
    "branch_id": 1,
    "device_info": "iPhone 14 Pro",
    "notes": "Morning check-in"
}

Response:
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

### 2. Check-Out
```
POST /api/attendance/check-out
Authorization: Bearer {token}

Request:
{
    "latitude": 25.2048,
    "longitude": 55.2708,
    "device_info": "iPhone 14 Pro",
    "notes": "Evening check-out"
}

Response:
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

### 3. Get Today's Status
```
GET /api/attendance/today
Authorization: Bearer {token}

Response:
{
    "status": "checked_in",
    "check_in_time": "2025-10-24 08:00:00",
    "check_out_time": null,
    "check_in_within_geofence": true,
    "check_out_within_geofence": null,
    "duration": "9 hours 30 minutes"
}
```

### 4. Get Branch Info
```
GET /api/attendance/branch
Authorization: Bearer {token}

Response:
{
    "success": true,
    "branch": {
        "id": 1,
        "name": "Downtown Pharmacy",
        "latitude": 25.2050,
        "longitude": 55.2710,
        "geofence_radius": 300,
        "address": "123 Main Street, Dubai"
    }
}
```

---

## 🎨 UI/UX Flow

### Screen 1: Login
```
┌─────────────────────────────┐
│   MediCon Attendance        │
│                             │
│   Email: [_____________]    │
│   Password: [_____________] │
│                             │
│   [Login Button]            │
└─────────────────────────────┘
```

### Screen 2: Dashboard
```
┌─────────────────────────────┐
│   Today's Attendance        │
│                             │
│   Status: ✅ Checked In     │
│   Check-In: 08:00 AM        │
│   Check-Out: -              │
│   Duration: 9h 30m          │
│                             │
│   [Check Out Button]        │
│   [View Map]                │
│   [View History]            │
└─────────────────────────────┘
```

### Screen 3: Map View
```
┌─────────────────────────────┐
│   [Map with Branch & User]  │
│                             │
│   Distance: 45 meters       │
│   Status: ✅ Within Zone    │
│                             │
│   [Check In] [Check Out]    │
└─────────────────────────────┘
```

---

## ⚠️ Error Handling

```javascript
const handleCheckIn = async () => {
  try {
    // Get location
    const location = await getCurrentLocation();
    
    // Validate location
    if (!location) {
      showError('Unable to get GPS location');
      return;
    }

    // Check-in
    const result = await checkIn(
      location.latitude,
      location.longitude,
      branchId,
      token
    );

    if (result.success) {
      showSuccess('Check-in successful!');
    } else {
      showError(result.message);
    }
  } catch (error) {
    showError(error.message);
  }
};
```

---

## 🚀 Deployment

### iOS
1. Build with Xcode
2. Submit to App Store
3. Requires location permission in Info.plist

### Android
1. Build with Android Studio
2. Submit to Google Play
3. Requires location permission in AndroidManifest.xml

---

## 📚 Resources

- React Native: https://reactnative.dev
- Flutter: https://flutter.dev
- Geolocator: https://pub.dev/packages/geolocator
- React Native Geolocation: https://github.com/react-native-camera/react-native-geolocation-service

---

**Status**: ✅ READY FOR DEVELOPMENT
**Last Updated**: 2025-10-24

