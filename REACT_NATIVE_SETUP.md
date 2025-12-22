# React Native Mobile App - Quick Start Guide

## 🚀 Setup Instructions

### Step 1: Create React Native Project

```bash
npx react-native init MediConAttendance
cd MediConAttendance
```

### Step 2: Install Dependencies

```bash
npm install axios
npm install @react-native-camera/camera
npm install react-native-geolocation-service
npm install react-native-maps
npm install @react-navigation/native @react-navigation/bottom-tabs
npm install react-native-screens react-native-safe-area-context
npm install react-native-permissions
npm install @react-native-async-storage/async-storage
```

### Step 3: Link Native Modules

```bash
cd ios && pod install && cd ..
```

---

## 📁 Project Structure

```
MediConAttendance/
├── src/
│   ├── screens/
│   │   ├── LoginScreen.js
│   │   ├── DashboardScreen.js
│   │   ├── MapScreen.js
│   │   └── HistoryScreen.js
│   ├── services/
│   │   ├── api.js
│   │   ├── geolocation.js
│   │   └── storage.js
│   ├── components/
│   │   ├── CheckInButton.js
│   │   ├── CheckOutButton.js
│   │   └── StatusCard.js
│   ├── navigation/
│   │   └── Navigation.js
│   └── App.js
├── package.json
└── app.json
```

---

## 💻 Code Examples

### 1. API Service (src/services/api.js)

```javascript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE_URL = 'http://your-server.com/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
});

// Add token to requests
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const authService = {
  login: (email, password) =>
    api.post('/login', { email, password }),
  logout: () =>
    api.post('/logout'),
};

export const attendanceService = {
  checkIn: (latitude, longitude, branchId, deviceInfo, notes) =>
    api.post('/attendance/check-in', {
      latitude,
      longitude,
      branch_id: branchId,
      device_info: deviceInfo,
      notes,
    }),

  checkOut: (latitude, longitude, deviceInfo, notes) =>
    api.post('/attendance/check-out', {
      latitude,
      longitude,
      device_info: deviceInfo,
      notes,
    }),

  getTodayStatus: () =>
    api.get('/attendance/today'),

  getBranch: () =>
    api.get('/attendance/branch'),
};

export default api;
```

### 2. Geolocation Service (src/services/geolocation.js)

```javascript
import Geolocation from 'react-native-geolocation-service';
import { request, PERMISSIONS, RESULTS } from 'react-native-permissions';
import { Platform } from 'react-native';

export const geolocationService = {
  requestPermission: async () => {
    const permission = Platform.OS === 'ios'
      ? PERMISSIONS.IOS.LOCATION_WHEN_IN_USE
      : PERMISSIONS.ANDROID.ACCESS_FINE_LOCATION;

    const result = await request(permission);
    return result === RESULTS.GRANTED;
  },

  getCurrentLocation: () => {
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
        {
          enableHighAccuracy: true,
          timeout: 15000,
          maximumAge: 10000,
        }
      );
    });
  },

  calculateDistance: (lat1, lon1, lat2, lon2) => {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * (Math.PI / 180);
    const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * (Math.PI / 180)) *
        Math.cos(lat2 * (Math.PI / 180)) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  },

  isWithinGeofence: (userLat, userLon, branchLat, branchLon, radius) => {
    const distance = geolocationService.calculateDistance(
      userLat,
      userLon,
      branchLat,
      branchLon
    );
    return distance <= radius;
  },
};
```

### 3. Storage Service (src/services/storage.js)

```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

export const storageService = {
  setToken: (token) =>
    AsyncStorage.setItem('auth_token', token),

  getToken: () =>
    AsyncStorage.getItem('auth_token'),

  removeToken: () =>
    AsyncStorage.removeItem('auth_token'),

  setUser: (user) =>
    AsyncStorage.setItem('user', JSON.stringify(user)),

  getUser: async () => {
    const user = await AsyncStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  },

  removeUser: () =>
    AsyncStorage.removeItem('user'),

  clear: () =>
    AsyncStorage.clear(),
};
```

### 4. Login Screen (src/screens/LoginScreen.js)

```javascript
import React, { useState } from 'react';
import {
  View,
  TextInput,
  TouchableOpacity,
  Text,
  StyleSheet,
  Alert,
} from 'react-native';
import { authService } from '../services/api';
import { storageService } from '../services/storage';

export const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please enter email and password');
      return;
    }

    setLoading(true);
    try {
      const response = await authService.login(email, password);
      const { token, user } = response.data;

      await storageService.setToken(token);
      await storageService.setUser(user);

      navigation.replace('Dashboard');
    } catch (error) {
      Alert.alert('Login Failed', error.response?.data?.message || 'Unknown error');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>MediCon Attendance</Text>

      <TextInput
        style={styles.input}
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        editable={!loading}
      />

      <TextInput
        style={styles.input}
        placeholder="Password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
        editable={!loading}
      />

      <TouchableOpacity
        style={[styles.button, loading && styles.buttonDisabled]}
        onPress={handleLogin}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Logging in...' : 'Login'}
        </Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    justifyContent: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 30,
    textAlign: 'center',
  },
  input: {
    borderWidth: 1,
    borderColor: '#ddd',
    padding: 12,
    marginBottom: 15,
    borderRadius: 8,
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
});
```

### 5. Dashboard Screen (src/screens/DashboardScreen.js)

```javascript
import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { attendanceService } from '../services/api';
import { geolocationService } from '../services/geolocation';

export const DashboardScreen = ({ navigation }) => {
  const [status, setStatus] = useState(null);
  const [branch, setBranch] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      const [statusRes, branchRes] = await Promise.all([
        attendanceService.getTodayStatus(),
        attendanceService.getBranch(),
      ]);
      setStatus(statusRes.data);
      setBranch(branchRes.data.branch);
    } catch (error) {
      Alert.alert('Error', 'Failed to load data');
    }
  };

  const handleCheckIn = async () => {
    setLoading(true);
    try {
      const hasPermission = await geolocationService.requestPermission();
      if (!hasPermission) {
        Alert.alert('Error', 'Location permission denied');
        return;
      }

      const location = await geolocationService.getCurrentLocation();
      const result = await attendanceService.checkIn(
        location.latitude,
        location.longitude,
        branch.id,
        'iPhone 14 Pro',
        'Morning check-in'
      );

      if (result.data.success) {
        Alert.alert('Success', 'Check-in successful!');
        loadData();
      } else {
        Alert.alert('Error', result.data.message);
      }
    } catch (error) {
      Alert.alert('Error', error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCheckOut = async () => {
    setLoading(true);
    try {
      const location = await geolocationService.getCurrentLocation();
      const result = await attendanceService.checkOut(
        location.latitude,
        location.longitude,
        'iPhone 14 Pro',
        'Evening check-out'
      );

      if (result.data.success) {
        Alert.alert('Success', 'Check-out successful!');
        loadData();
      } else {
        Alert.alert('Error', result.data.message);
      }
    } catch (error) {
      Alert.alert('Error', error.message);
    } finally {
      setLoading(false);
    }
  };

  if (!status || !branch) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color="#007AFF" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Today's Attendance</Text>

      <View style={styles.card}>
        <Text style={styles.label}>Status</Text>
        <Text style={styles.value}>{status.status}</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.label}>Check-In</Text>
        <Text style={styles.value}>{status.check_in_time || '-'}</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.label}>Check-Out</Text>
        <Text style={styles.value}>{status.check_out_time || '-'}</Text>
      </View>

      <View style={styles.card}>
        <Text style={styles.label}>Duration</Text>
        <Text style={styles.value}>{status.duration || '-'}</Text>
      </View>

      <TouchableOpacity
        style={[styles.button, loading && styles.buttonDisabled]}
        onPress={status.status === 'checked_in' ? handleCheckOut : handleCheckIn}
        disabled={loading}
      >
        <Text style={styles.buttonText}>
          {loading ? 'Processing...' : (status.status === 'checked_in' ? 'Check Out' : 'Check In')}
        </Text>
      </TouchableOpacity>

      <TouchableOpacity
        style={styles.secondaryButton}
        onPress={() => navigation.navigate('Map')}
      >
        <Text style={styles.secondaryButtonText}>View Map</Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    padding: 20,
    backgroundColor: '#f5f5f5',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 20,
  },
  card: {
    backgroundColor: 'white',
    padding: 15,
    marginBottom: 10,
    borderRadius: 8,
  },
  label: {
    fontSize: 12,
    color: '#666',
    marginBottom: 5,
  },
  value: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#333',
  },
  button: {
    backgroundColor: '#007AFF',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 20,
  },
  buttonDisabled: {
    opacity: 0.5,
  },
  buttonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
  secondaryButton: {
    backgroundColor: '#f0f0f0',
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginTop: 10,
  },
  secondaryButtonText: {
    color: '#007AFF',
    fontSize: 16,
    fontWeight: 'bold',
  },
});
```

---

## 🏃 Running the App

### iOS
```bash
npm run ios
```

### Android
```bash
npm run android
```

---

## 📱 Permissions Required

### iOS (Info.plist)
```xml
<key>NSLocationWhenInUseUsageDescription</key>
<string>We need your location to track attendance</string>
```

### Android (AndroidManifest.xml)
```xml
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
```

---

**Status**: ✅ READY TO BUILD
**Last Updated**: 2025-10-24

