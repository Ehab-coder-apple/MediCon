# GPS ATTENDANCE SYSTEM - TROUBLESHOOTING & FAQ

**Version**: 1.0  
**Date**: October 24, 2025  
**Format**: PDF Ready

---

## TABLE OF CONTENTS

1. Troubleshooting Guide
2. Frequently Asked Questions
3. Error Messages
4. Performance Issues
5. Data Issues
6. GPS Issues
7. Geofence Issues
8. Export Issues
9. Access Issues
10. Mobile App Issues

---

## 1. TROUBLESHOOTING GUIDE

### Issue: Cannot Access Attendance Section

**Symptoms**:
- 403 Forbidden error
- "Access Denied" message
- Attendance menu not visible

**Causes**:
- Insufficient user permissions
- Not assigned to correct role
- Tenant mismatch
- Session expired

**Solutions**:

**Step 1**: Check User Role
```
1. Go to Admin → Users
2. Find your user account
3. Check assigned role
4. Must be "Admin" or "Super Admin"
```

**Step 2**: Verify Tenant Assignment
```
1. Check user tenant_id
2. Must match your organization
3. Contact administrator if incorrect
```

**Step 3**: Refresh Session
```
1. Logout completely
2. Clear browser cache
3. Login again
4. Try accessing attendance
```

**Step 4**: Contact Support
```
If still not working:
- Email: support@company.com
- Phone: +971-XX-XXXXXXX
- Provide: Username, error message, timestamp
```

---

### Issue: No Attendance Records Displayed

**Symptoms**:
- Empty attendance table
- "No records found" message
- Table shows 0 records

**Causes**:
- Filters too restrictive
- No data in database
- Date range incorrect
- Employee hasn't checked in

**Solutions**:

**Step 1**: Reset Filters
```
1. Click "Reset Filters" button
2. All filters cleared
3. View all records
```

**Step 2**: Check Date Range
```
1. Verify date range is correct
2. Ensure start date < end date
3. Check if data exists for period
```

**Step 3**: Verify Employee Data
```
1. Ask employee if they checked in
2. Check employee's mobile app
3. Verify employee is assigned to branch
```

**Step 4**: Check Database
```
1. Contact IT support
2. Verify database connection
3. Check data integrity
```

---

### Issue: GPS Coordinates Missing

**Symptoms**:
- GPS fields show "N/A" or blank
- Latitude/Longitude empty
- Distance shows "-"

**Causes**:
- Employee GPS disabled
- GPS permission not granted
- Mobile app error
- Network issue during check-in

**Solutions**:

**Step 1**: Verify Employee Device
```
1. Contact employee
2. Ask to check GPS status
3. Verify GPS is enabled
4. Check location permission
```

**Step 2**: Ask Employee to Re-Check In
```
1. Employee opens mobile app
2. Grants GPS permission
3. Performs check-in again
4. GPS data should now appear
```

**Step 3**: Check Mobile App
```
1. Verify app version is latest
2. Check app permissions
3. Reinstall if necessary
4. Test on another device
```

**Step 4**: Review Network
```
1. Check internet connection
2. Verify API connectivity
3. Check server logs
4. Contact IT support
```

---

### Issue: Geofence Status Incorrect

**Symptoms**:
- Shows "Outside" when should be "Inside"
- Shows "Inside" when should be "Outside"
- Distance calculation wrong

**Causes**:
- Branch GPS coordinates incorrect
- Geofence radius wrong
- GPS accuracy issue
- Calculation error

**Solutions**:

**Step 1**: Verify Branch Coordinates
```
1. Go to Admin → Branches
2. Find branch
3. Check GPS coordinates:
   - Latitude: Should be between -90 and 90
   - Longitude: Should be between -180 and 180
4. Verify coordinates are correct
```

**Step 2**: Check Geofence Radius
```
1. Go to Admin → Branches
2. Find branch
3. Check geofence_radius field
4. Default: 300 meters
5. Adjust if needed
```

**Step 3**: Verify GPS Accuracy
```
1. Check employee GPS accuracy
2. GPS accuracy typically ±5-15 meters
3. May vary by location
4. Outdoor areas more accurate
```

**Step 4**: Recalculate Distance
```
1. Use Haversine formula
2. Distance = 2R × arcsin(√(sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)))
3. R = 6,371,000 meters (Earth radius)
4. If calculation correct, issue is elsewhere
```

---

### Issue: Export Not Working

**Symptoms**:
- Export button doesn't respond
- File doesn't download
- Error message appears

**Causes**:
- Browser compatibility
- JavaScript disabled
- Permission issue
- Server error

**Solutions**:

**Step 1**: Try Different Browser
```
1. Try Chrome, Firefox, Safari, or Edge
2. Some browsers work better than others
3. Update browser to latest version
```

**Step 2**: Enable JavaScript
```
1. Check browser settings
2. Ensure JavaScript is enabled
3. Disable browser extensions
4. Try incognito/private mode
```

**Step 3**: Check Permissions
```
1. Verify user has export permission
2. Check user role
3. Verify tenant assignment
4. Contact administrator if needed
```

**Step 4**: Check Server
```
1. Verify server is running
2. Check server logs
3. Verify disk space available
4. Contact IT support
```

---

### Issue: Slow Loading

**Symptoms**:
- Page takes long time to load
- Table loads slowly
- Filters take time to apply

**Causes**:
- Large dataset
- Slow internet connection
- Server overload
- Browser cache issues

**Solutions**:

**Step 1**: Clear Browser Cache
```
1. Press Cmd+Shift+Delete (Mac) or Ctrl+Shift+Delete (Windows)
2. Select "All time"
3. Check "Cookies and other site data"
4. Click "Clear data"
5. Refresh page
```

**Step 2**: Reduce Data
```
1. Apply filters to reduce records
2. Use smaller date range
3. Filter by specific employee
4. Filter by specific branch
```

**Step 3**: Check Internet
```
1. Test internet speed
2. Verify connection is stable
3. Try wired connection if possible
4. Restart router if needed
```

**Step 4**: Contact IT
```
1. If still slow, contact IT
2. May be server issue
3. May need optimization
4. Provide: Browser, OS, timestamp
```

---

### Issue: Data Not Updating

**Symptoms**:
- New records not appearing
- Changes not reflected
- Stale data displayed

**Causes**:
- Browser cache
- Page not refreshed
- Server issue
- Database sync issue

**Solutions**:

**Step 1**: Refresh Page
```
1. Press Cmd+R (Mac) or Ctrl+R (Windows)
2. Or click refresh button
3. Wait for page to load
4. Check if data updated
```

**Step 2**: Hard Refresh
```
1. Press Cmd+Shift+R (Mac) or Ctrl+Shift+R (Windows)
2. Clears cache and refreshes
3. May take longer
4. Check if data updated
```

**Step 3**: Clear Cache
```
1. Clear browser cache (see above)
2. Logout and login again
3. Try different browser
4. Check if data updated
```

**Step 4**: Check Server
```
1. Verify server is running
2. Check database connection
3. Verify API is responding
4. Contact IT support
```

---

## 2. FREQUENTLY ASKED QUESTIONS

### Q1: How often is attendance data updated?

**A**: Real-time. Data updates immediately when:
- Employee checks in
- Employee checks out
- Admin makes changes

**Refresh**: Page may need refresh to see updates

---

### Q2: Can I edit attendance records?

**A**: 
- **Super Admin**: Yes, can edit any record
- **Tenant Admin**: No, cannot edit
- **Employee**: No, cannot edit

**Note**: Editing is not recommended. Better to delete and re-create if needed.

---

### Q3: How long is data retained?

**A**: Indefinitely. All historical data is retained permanently.

**Backup**: Daily backups are performed automatically.

---

### Q4: Can I delete attendance records?

**A**: 
- **Super Admin**: Yes, can delete
- **Tenant Admin**: No, cannot delete
- **Employee**: No, cannot delete

**Warning**: Deletion is permanent. Backup first if needed.

---

### Q5: How accurate is GPS tracking?

**A**: Typical accuracy: ±5-15 meters

**Factors affecting accuracy**:
- Device GPS quality
- Satellite availability
- Weather conditions
- Urban vs rural area
- Building obstruction

---

### Q6: What if employee is outside geofence?

**A**: 
- Check-in is still recorded
- Marked as "Outside Geofence"
- Distance is recorded
- Admin can review

**Note**: Geofence is for monitoring, not blocking.

---

### Q7: Can I change geofence radius?

**A**: Yes, but only Super Admin can change.

**Steps**:
1. Go to Admin → Branches
2. Find branch
3. Edit geofence_radius field
4. Default: 300 meters
5. Save changes

---

### Q8: How do I integrate with payroll?

**A**: 
1. Export attendance data as CSV
2. Open in Excel/Google Sheets
3. Format as needed
4. Import to payroll system

**Fields exported**:
- Employee name
- Date
- Check-in time
- Check-out time
- Total minutes worked
- Status

---

### Q9: What if employee forgets to check out?

**A**: 
- Record marked as "Incomplete"
- Admin can manually update
- Or employee can check out later

**Steps to fix**:
1. Find incomplete record
2. Click "View"
3. Contact employee
4. Ask to check out
5. Or manually update if needed

---

### Q10: Can employees see their own records?

**A**: Yes, employees can:
- View their own attendance
- See check-in/out times
- See duration worked
- Cannot see other employees

---

### Q11: What is the geofence radius?

**A**: 300 meters (default)

**Meaning**: Employee must be within 300 meters of branch to check in.

**Calculation**: Using Haversine formula for accurate distance.

---

### Q12: How do I generate reports?

**A**: 
1. Go to Attendance section
2. Apply filters for period
3. Click "Export CSV"
4. Open in Excel
5. Create pivot table/chart
6. Generate report

---

### Q13: What if GPS is not working?

**A**: 
1. Check device GPS is enabled
2. Verify GPS permission granted
3. Go outside for better signal
4. Wait 30 seconds for GPS fix
5. Try again

---

### Q14: Can I view attendance on mobile?

**A**: 
- **Employees**: Yes, via mobile app
- **Admins**: No, only via web dashboard

**Mobile app features**:
- Check-in/out
- View today's status
- View history
- See GPS location

---

### Q15: What if I lose access?

**A**: 
1. Contact administrator
2. Provide: Username, email, reason
3. Admin will restore access
4. May take 24 hours

---

## 3. ERROR MESSAGES

### Error: "403 Forbidden"

**Meaning**: Access denied

**Cause**: Insufficient permissions

**Solution**: Contact administrator

---

### Error: "404 Not Found"

**Meaning**: Page not found

**Cause**: URL incorrect or page deleted

**Solution**: Check URL, navigate from menu

---

### Error: "500 Internal Server Error"

**Meaning**: Server error

**Cause**: Server issue

**Solution**: Contact IT support

---

### Error: "Database Connection Failed"

**Meaning**: Cannot connect to database

**Cause**: Database down or unreachable

**Solution**: Contact IT support

---

### Error: "Invalid GPS Coordinates"

**Meaning**: GPS data invalid

**Cause**: GPS error or data corruption

**Solution**: Ask employee to re-check in

---

## 4. PERFORMANCE ISSUES

### Slow Page Load

**Cause**: Large dataset or slow connection

**Solution**: 
- Apply filters
- Use smaller date range
- Clear cache
- Check internet speed

---

### Slow Export

**Cause**: Large dataset

**Solution**:
- Apply filters first
- Export smaller date range
- Try again later
- Contact IT if persistent

---

### Slow Search

**Cause**: Large dataset

**Solution**:
- Use filters
- Narrow search criteria
- Try again later

---

## 5. DATA ISSUES

### Missing Records

**Cause**: 
- Filters too restrictive
- Data not entered
- Employee didn't check in

**Solution**: Reset filters, check date range

---

### Duplicate Records

**Cause**: System error or manual entry

**Solution**: Contact IT support

---

### Incorrect Data

**Cause**: 
- GPS error
- Manual entry error
- System error

**Solution**: Verify, correct if needed

---

## 6. GPS ISSUES

### GPS Not Available

**Cause**: GPS disabled or no signal

**Solution**: Enable GPS, go outside

---

### GPS Inaccurate

**Cause**: Poor signal, urban canyon

**Solution**: Move to open area, wait for fix

---

### GPS Coordinates Wrong

**Cause**: GPS error or data corruption

**Solution**: Ask employee to re-check in

---

## 7. GEOFENCE ISSUES

### Geofence Status Wrong

**Cause**: 
- Branch coordinates incorrect
- GPS accuracy issue
- Calculation error

**Solution**: Verify branch coordinates

---

### Geofence Violations

**Cause**: Employee outside 300m radius

**Solution**: Review, investigate, document

---

## 8. EXPORT ISSUES

### Export Button Not Working

**Cause**: 
- Browser issue
- Permission issue
- Server error

**Solution**: Try different browser, check permissions

---

### Export File Corrupted

**Cause**: Download interrupted

**Solution**: Try again, use different browser

---

### Export Missing Data

**Cause**: Filters applied

**Solution**: Reset filters, export again

---

## 9. ACCESS ISSUES

### Cannot Login

**Cause**: 
- Wrong credentials
- Account locked
- Session expired

**Solution**: Reset password, contact support

---

### Lost Access

**Cause**: 
- Role changed
- Account disabled
- Tenant changed

**Solution**: Contact administrator

---

## 10. MOBILE APP ISSUES

### App Crashes

**Cause**: 
- App bug
- Device issue
- Memory issue

**Solution**: Restart app, restart device, reinstall

---

### GPS Permission Denied

**Cause**: Permission not granted

**Solution**: Go to Settings, enable location

---

### Cannot Check In

**Cause**: 
- GPS not available
- Outside geofence
- Network issue

**Solution**: Enable GPS, move closer, check internet

---

## SUPPORT CONTACT

**For Technical Issues**:
- Email: support@company.com
- Phone: +971-XX-XXXXXXX
- Hours: 24/7

**For System Questions**:
- Email: admin@company.com
- Phone: +971-XX-XXXXXXX

**For Mobile App Issues**:
- Email: mobile@company.com
- Phone: +971-XX-XXXXXXX

---

## DOCUMENT INFORMATION

**Title**: GPS Attendance System - Troubleshooting & FAQ

**Version**: 1.0

**Date**: October 24, 2025

**Format**: PDF Ready

**Pages**: 4-6 (when printed)

---

**© 2025 MediCon. All Rights Reserved.**

**END OF TROUBLESHOOTING & FAQ GUIDE**

