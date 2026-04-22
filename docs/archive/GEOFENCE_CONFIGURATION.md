# Geofence Configuration System

## Overview

The MediCon system now supports **dynamic geofence radius configuration**. Admins can configure geofence settings per branch through the web dashboard, and the mobile app automatically fetches these settings from the backend.

## Features

✅ **Dynamic Geofence Radius** - Configure per branch (50m - 5000m)
✅ **Enable/Disable Geofencing** - Toggle geofencing requirement per branch
✅ **Real-time Updates** - Mobile app fetches latest settings on startup
✅ **Multi-tenant Support** - Each tenant has independent geofence settings
✅ **API Endpoints** - Full REST API for geofence management

---

## Admin Dashboard - Branch Management

### Access Branch Management
1. Login to web dashboard as admin
2. Navigate to **Admin → Branches**
3. Click **"+ Add Branch"** or **"Edit"** on existing branch

### Configure Geofence Settings

**Location Settings:**
- **Latitude** - GPS latitude coordinate (required)
- **Longitude** - GPS longitude coordinate (required)
- **Geofence Radius** - Distance in meters (50m - 5000m, default: 300m)
- **Require Geofencing** - Toggle to enforce geofence validation

### Example Configuration
```
Branch Name: Downtown Pharmacy
GPS: 40.7128, -74.0060
Geofence Radius: 300 meters
Require Geofencing: ✓ Enabled
```

---

## Mobile App - Automatic Geofence Fetching

### How It Works

1. **On Login** - App fetches branch configuration from backend
2. **Geofence Radius** - Automatically loaded from `geofence_radius` field
3. **Real-time Monitoring** - App continuously monitors GPS location
4. **Check-in Validation** - Validates location against geofence before check-in

### Code Example (App.tsx)

```typescript
// Fetch branch configuration with geofence settings
const response = await fetch(`${ATTENDANCE_API_URL}/branch`, {
  method: 'GET',
  headers: {
    Authorization: `Bearer ${authToken}`,
  },
});

const json = await response.json();
const branch = {
  id: json.branch.id,
  name: json.branch.name,
  latitude: json.branch.latitude,
  longitude: json.branch.longitude,
  geofenceRadiusMeters: json.branch.geofence_radius ?? 300, // Dynamic!
};
```

---

## API Endpoints

### 1. Get All Branches
```
GET /api/branches
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Downtown Pharmacy",
      "latitude": 40.7128,
      "longitude": -74.0060,
      "geofence_radius": 300,
      "requires_geofencing": true,
      "is_active": true
    }
  ]
}
```

### 2. Get Single Branch
```
GET /api/branches/{branch_id}
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Downtown Pharmacy",
    "code": "DT-001",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "geofence_radius": 300,
    "requires_geofencing": true,
    "is_active": true,
    "address": "123 Main St, New York, NY 10001"
  }
}
```

### 3. Update Branch Geofence Settings
```
POST /api/branches/{branch_id}/set-location
Authorization: Bearer {token}
Content-Type: application/json

Request Body:
{
  "latitude": 40.7128,
  "longitude": -74.0060,
  "geofence_radius": 500,
  "requires_geofencing": true,
  "name": "Downtown Pharmacy"
}

Response:
{
  "success": true,
  "message": "Branch location updated successfully.",
  "data": {
    "id": 1,
    "name": "Downtown Pharmacy",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "geofence_radius": 500,
    "requires_geofencing": true
  }
}
```

---

## Geofence Validation Logic

### Distance Calculation (Haversine Formula)

```typescript
function distanceInMeters(
  lat1: number, lon1: number,
  lat2: number, lon2: number
): number {
  const R = 6371000; // Earth radius in meters
  const φ1 = (lat1 * Math.PI) / 180;
  const φ2 = (lat2 * Math.PI) / 180;
  const Δφ = ((lat2 - lat1) * Math.PI) / 180;
  const Δλ = ((lon2 - lon1) * Math.PI) / 180;

  const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
            Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  
  return R * c;
}
```

### Check-in Validation

```typescript
const distance = distanceInMeters(
  userLat, userLon,
  branchLat, branchLon
);

const isWithinGeofence = distance <= branchConfig.geofenceRadiusMeters;

if (!isWithinGeofence && branchConfig.requires_geofencing) {
  // Show warning or prevent check-in
  Alert.alert('Outside Geofence', 
    `You are ${Math.round(distance)}m away from the pharmacy`);
}
```

---

## Testing Geofence Configuration

### Test Scenario 1: Update Geofence Radius
1. Go to Admin → Branches
2. Edit a branch
3. Change geofence radius from 300m to 500m
4. Save changes
5. Mobile app automatically uses new radius on next location check

### Test Scenario 2: Disable Geofencing
1. Go to Admin → Branches
2. Edit a branch
3. Uncheck "Require Geofencing"
4. Save changes
5. Mobile app allows check-in from anywhere

### Test Scenario 3: Multiple Branches
1. Create 2 branches with different geofence radii
2. Assign user to both branches
3. Mobile app shows correct radius for selected branch

---

## Database Schema

### Branches Table
```sql
CREATE TABLE branches (
  id BIGINT PRIMARY KEY,
  tenant_id BIGINT,
  name VARCHAR(255),
  code VARCHAR(50),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  geofence_radius INT DEFAULT 300,
  requires_geofencing BOOLEAN DEFAULT true,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  ...
);
```

---

## Security Considerations

✅ **Tenant Isolation** - Each tenant only sees their own branches
✅ **Authentication** - All endpoints require Sanctum token
✅ **Authorization** - Only admins can update geofence settings
✅ **Validation** - Server-side validation of GPS coordinates
✅ **Audit Trail** - All changes tracked with timestamps

---

## Troubleshooting

### Issue: Mobile app shows old geofence radius
**Solution:** Force app restart or clear cache

### Issue: Geofence validation not working
**Solution:** Ensure GPS coordinates are accurate and device has location permission

### Issue: Cannot update branch location
**Solution:** Verify you have admin permissions and branch belongs to your tenant

---

## Next Steps

- [ ] Add geofence violation reports
- [ ] Implement geofence alerts/notifications
- [ ] Add geofence history tracking
- [ ] Create geofence analytics dashboard

