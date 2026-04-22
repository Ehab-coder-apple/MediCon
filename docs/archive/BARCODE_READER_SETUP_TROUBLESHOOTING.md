# 🔧 Barcode Reader Setup & Troubleshooting Guide

## Pre-Setup Checklist

Before connecting your barcode reader, verify:

- [ ] Barcode reader is charged (battery indicator shows green)
- [ ] Computer has Bluetooth capability
- [ ] MediCon web app is installed and running
- [ ] Browser is up to date (Chrome, Firefox, Safari, Edge)
- [ ] You have admin access to MediCon
- [ ] Products have barcodes entered in system

---

## Hardware Setup

### Bluetooth Scanner Setup

**1. Charge the Scanner**
- Connect to USB charger
- Wait for green indicator
- Usually takes 2-4 hours

**2. Enable Bluetooth on Computer**

**macOS:**
```
Apple Menu → System Preferences → Bluetooth → Turn On
```

**Windows:**
```
Settings → Devices → Bluetooth & other devices → Toggle On
```

**3. Put Scanner in Pairing Mode**
- Hold power button 3-5 seconds
- Look for blue/red flashing LED
- Scanner is now discoverable

**4. Pair the Scanner**

**macOS:**
- System Preferences → Bluetooth
- Click "Pair" next to scanner
- Wait for "Connected"

**Windows:**
- Settings → Bluetooth & other devices
- Click "Add device"
- Select "Bluetooth"
- Click scanner name
- Click "Pair"

**5. Test Connection**
- Open Notepad/Text Editor
- Click in text field
- Scan a barcode
- Text should appear

---

## Software Setup

### MediCon Configuration

**1. Add Barcodes to Products**

**Via Admin Panel:**
1. Go to Admin → Products
2. Click on product
3. Enter barcode in "Barcode" field
4. Save product

**Example Barcodes:**
- EAN-13: `5901234123457`
- Code128: `CODE123456`
- UPC: `012345678905`

**2. Verify API Endpoints**

Check that these endpoints are working:

```bash
# Test barcode lookup
curl -X GET "http://localhost:8000/api/products/by-barcode/5901234123457" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Test Medicine",
    "barcode": "5901234123457",
    "price": 100.00,
    "batches": [...]
  }
}
```

**3. Test in Browser**

1. Open MediCon web app
2. Go to Sales → Create Invoice
3. Look for barcode scanner icon
4. Click in barcode field
5. Scan a test barcode
6. Product should appear

---

## Troubleshooting Guide

### Issue 1: Scanner Won't Pair

**Symptoms:**
- Scanner doesn't appear in Bluetooth list
- "Device not found" error

**Solutions:**

1. **Restart Scanner**
   - Turn off (hold power 5 seconds)
   - Wait 10 seconds
   - Turn on again
   - Try pairing

2. **Restart Bluetooth**
   - Turn off Bluetooth on computer
   - Wait 10 seconds
   - Turn on Bluetooth
   - Try pairing

3. **Check Battery**
   - Ensure scanner is charged
   - Look for battery indicator
   - Charge if needed

4. **Check Manual**
   - Find exact pairing instructions
   - Look for pairing button location
   - Check LED indicator meanings

5. **Factory Reset**
   - Hold power button 10+ seconds
   - Scanner will reset
   - Try pairing again

---

### Issue 2: Scanner Paired But Not Working

**Symptoms:**
- Scanner shows "Connected"
- No text appears when scanning
- Works in some apps but not others

**Solutions:**

1. **Test in Text Editor**
   - Open Notepad
   - Click in text field
   - Scan barcode
   - If text appears, issue is with MediCon

2. **Check Scanner Mode**
   - Some scanners have multiple modes
   - Check manual for "keyboard mode"
   - May need to switch modes
   - Scan mode-switching barcode

3. **Reconnect Scanner**
   - Unpair scanner
   - Restart scanner
   - Pair again
   - Test

4. **Check Browser**
   - Try different browser
   - Clear browser cache
   - Disable extensions
   - Try incognito mode

5. **Check MediCon**
   - Refresh page (F5)
   - Check browser console (F12)
   - Look for JavaScript errors
   - Contact support if errors

---

### Issue 3: Barcode Scans But Product Not Found

**Symptoms:**
- Scanner works (text appears)
- "Product not found" message
- Barcode is correct

**Solutions:**

1. **Verify Barcode in System**
   - Go to Admin → Products
   - Search for product
   - Check if barcode is entered
   - Add barcode if missing

2. **Check Barcode Format**
   - Ensure barcode matches exactly
   - Check for spaces or special characters
   - Verify barcode type (EAN-13, Code128, etc.)

3. **Check Product Status**
   - Verify product is active
   - Check if product is assigned to tenant
   - Ensure product has stock

4. **Try Product Code**
   - System falls back to product code
   - If barcode not found, searches by code
   - Verify product code is correct

5. **Rescan Barcode**
   - Clean scanner lens
   - Scan from 2-6 inches away
   - Try different angle
   - Try different barcode

---

### Issue 4: Slow Response

**Symptoms:**
- Barcode scans but takes time to appear
- Product details load slowly
- System feels sluggish

**Solutions:**

1. **Check Internet Connection**
   - Test speed: speedtest.net
   - Ensure stable connection
   - Move closer to WiFi router
   - Check WiFi signal strength

2. **Check Server Status**
   - Verify MediCon server is running
   - Check server logs for errors
   - Restart server if needed
   - Check server resources (CPU, RAM)

3. **Clear Browser Cache**
   - Chrome: Ctrl+Shift+Delete
   - Firefox: Ctrl+Shift+Delete
   - Safari: Develop → Empty Caches
   - Refresh page

4. **Reduce Load**
   - Close other browser tabs
   - Close other applications
   - Restart browser
   - Restart computer

5. **Check Database**
   - Verify database is responsive
   - Check for slow queries
   - Optimize database if needed
   - Contact support if persists

---

### Issue 5: Multiple Scanners Conflict

**Symptoms:**
- Two scanners paired to same computer
- Text appears in wrong application
- Unpredictable behavior

**Solutions:**

1. **Use One Scanner at a Time**
   - Unpair unused scanner
   - Keep only active scanner paired
   - Pair new scanner when needed

2. **Rename Scanners**
   - Give each scanner unique name
   - Makes it easier to identify
   - Prevents confusion

3. **Use Different Computers**
   - Pair each scanner to different computer
   - Reduces conflicts
   - Better for multi-station setup

---

## Performance Optimization

### For Faster Scanning:

1. **Optimize Network**
   - Use wired connection if possible
   - Ensure good WiFi signal
   - Reduce network congestion

2. **Optimize Browser**
   - Use Chrome (fastest)
   - Disable unnecessary extensions
   - Clear cache regularly
   - Update browser

3. **Optimize Server**
   - Ensure server has adequate resources
   - Monitor CPU and RAM usage
   - Optimize database queries
   - Use caching where possible

4. **Optimize Scanner**
   - Keep scanner firmware updated
   - Clean scanner lens
   - Ensure good battery
   - Use optimal scanning distance

---

## Testing Checklist

Before going live, verify:

- [ ] Scanner pairs successfully
- [ ] Scanner works in text editor
- [ ] MediCon web app loads
- [ ] Barcode field is visible
- [ ] Scanning triggers product lookup
- [ ] Product details display correctly
- [ ] Batch selection works
- [ ] Add to cart works
- [ ] Checkout completes successfully
- [ ] Inventory updates correctly
- [ ] Invoice is generated
- [ ] Multiple products can be scanned
- [ ] System handles errors gracefully

---

## Support Resources

### Documentation:
- `BARCODE_READER_CONNECTION_GUIDE.md` - Connection steps
- `BARCODE_READER_MEDICON_WORKFLOW.md` - Usage workflow
- `BARCODE_SCANNER_TECHNICAL_SPEC.md` - API specifications

### Scanner Manuals:
- Zebra DS3678: [Link to manual]
- Honeywell 1911i: [Link to manual]
- Symbol LS2208: [Link to manual]

### Contact Support:
- Email: support@medicon.com
- Phone: +1-XXX-XXX-XXXX
- Chat: support.medicon.com

---

**Need help? Check the troubleshooting guide above! 🔧**

