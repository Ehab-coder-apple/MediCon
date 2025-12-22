# WhatsApp Integration Setup Guide - Dual Mode

## Overview

MediCon now supports two WhatsApp integration modes:
1. **WhatsApp Business Free** - For small to medium pharmacies
2. **WhatsApp Business API** - For medium to large pharmacies

---

## Mode Comparison

| Feature | Business Free | Business API |
|---------|---------------|--------------|
| **Cost** | Free | Pay per message |
| **Setup** | 5 minutes | 30 minutes |
| **Automation** | ❌ Manual | ✅ Automated |
| **Bulk Messaging** | ❌ No | ✅ Yes |
| **Templates** | ❌ No | ✅ Yes |
| **Webhooks** | ❌ No | ✅ Yes |
| **Best For** | Small pharmacies | Growing pharmacies |
| **Scalability** | Limited | Unlimited |

---

## Setup Instructions

### Option 1: WhatsApp Business Free (Recommended for Small Pharmacies)

#### Step 1: Download WhatsApp Business App
1. Go to App Store (iOS) or Google Play (Android)
2. Search for "WhatsApp Business"
3. Download and install the app
4. Create a business account

#### Step 2: Configure in MediCon
1. Go to **Settings → WhatsApp Configuration**
2. Click **"Select Integration Mode"**
3. Choose **"WhatsApp Business Free"**
4. Enter:
   - **Business Phone Number**: Your WhatsApp Business phone number
   - **Business Account Name**: Your pharmacy name
5. Click **"Save & Activate"**

#### Step 3: Start Using
1. Go to **WhatsApp Dashboard**
2. Click **"Send Message"**
3. Select customer and message
4. Click **"Generate WhatsApp Link"**
5. Click the link to open WhatsApp
6. Send the message manually

#### Advantages
✅ No API setup required
✅ Completely free
✅ Simple to use
✅ No technical knowledge needed
✅ Instant activation

#### Limitations
❌ Manual sending only
❌ No automation
❌ No bulk messaging
❌ No message templates
❌ No delivery tracking

---

### Option 2: WhatsApp Business API (Recommended for Growing Pharmacies)

#### Step 1: Create Meta Business Account
1. Go to [business.facebook.com](https://business.facebook.com)
2. Click **"Create Account"**
3. Enter business details:
   - Business name
   - Business email
   - Business phone
   - Country
4. Verify your email

#### Step 2: Set Up WhatsApp Business Account
1. In Meta Business Manager, go to **"Apps"**
2. Click **"Create App"**
3. Choose **"Business"** as app type
4. Fill in app details
5. Add **"WhatsApp"** product

#### Step 3: Get API Credentials
1. Go to **WhatsApp Manager**
2. Click **"API Setup"**
3. Get these credentials:
   - **Business Account ID**
   - **Phone Number ID**
   - **Access Token**
   - **Webhook Secret**

#### Step 4: Configure in MediCon
1. Go to **Settings → WhatsApp Configuration**
2. Click **"Select Integration Mode"**
3. Choose **"WhatsApp Business API"**
4. Enter credentials:
   - **Business Account ID**
   - **Phone Number ID**
   - **Phone Number** (with country code)
   - **Access Token**
   - **Webhook Secret**
5. Click **"Save"**
6. Click **"Verify Credentials"**
7. Click **"Test Connection"**

#### Step 5: Set Up Webhook
1. In Meta Business Manager, go to **Webhooks**
2. Set **Callback URL** to:
   ```
   https://yourdomain.com/api/whatsapp/webhook?tenant_id=YOUR_TENANT_ID
   ```
3. Set **Verify Token** to your webhook secret
4. Subscribe to these events:
   - messages
   - message_status
   - message_template_status_update

#### Step 6: Create Message Templates
1. In WhatsApp Manager, go to **Message Templates**
2. Create templates for:
   - Prescription ready
   - Appointment reminder
   - Order confirmation
   - Delivery notification
3. Get template approval from Meta

#### Step 7: Start Using
1. Go to **WhatsApp Dashboard**
2. Click **"Send Message"**
3. Select customer and template
4. Click **"Send"**
5. Message is sent automatically

#### Advantages
✅ Fully automated
✅ Bulk messaging
✅ Message templates
✅ Delivery tracking
✅ Webhook integration
✅ Professional appearance

#### Costs
- **Conversation-based pricing**: $0.0079 - $0.0256 per message (varies by region)
- **Template messages**: Often cheaper than regular messages
- **Minimum monthly spend**: Usually $1-5

---

## Switching Between Modes

### From Business Free to API
1. Go to **Settings → WhatsApp Configuration**
2. Click **"Switch Mode"**
3. Select **"WhatsApp Business API"**
4. Enter API credentials
5. Click **"Save & Verify"**

### From API to Business Free
1. Go to **Settings → WhatsApp Configuration**
2. Click **"Switch Mode"**
3. Select **"WhatsApp Business Free"**
4. Enter phone number and business name
5. Click **"Save & Activate"**

---

## Troubleshooting

### Business Free Mode

**Problem**: Link not opening WhatsApp
- **Solution**: Make sure WhatsApp Business app is installed
- **Solution**: Check phone number format (include country code)

**Problem**: Message not sending
- **Solution**: Ensure WhatsApp Business app is open
- **Solution**: Check internet connection

### API Mode

**Problem**: "Invalid credentials"
- **Solution**: Verify all credentials are correct
- **Solution**: Check access token hasn't expired
- **Solution**: Ensure phone number is verified

**Problem**: "Webhook verification failed"
- **Solution**: Check webhook URL is correct
- **Solution**: Verify webhook secret matches
- **Solution**: Ensure HTTPS is enabled

**Problem**: "Message sending failed"
- **Solution**: Check customer phone number format
- **Solution**: Verify template is approved
- **Solution**: Check account has sufficient balance

---

## Best Practices

### For Business Free Mode
1. ✅ Use for small pharmacies (< 100 customers)
2. ✅ Send messages during business hours
3. ✅ Keep messages short and clear
4. ✅ Use templates for consistency
5. ✅ Monitor WhatsApp app regularly

### For API Mode
1. ✅ Use approved templates only
2. ✅ Segment customers for targeted messaging
3. ✅ Monitor delivery rates
4. ✅ Set up webhook monitoring
5. ✅ Keep access token secure
6. ✅ Monitor API usage and costs

---

## Support

### For Business Free Issues
- Check WhatsApp Business app settings
- Verify phone number is correct
- Ensure app has necessary permissions

### For API Issues
- Check Meta Business Manager
- Review API documentation
- Contact Meta support

### For MediCon Issues
- Check system logs
- Verify configuration
- Contact MediCon support

---

## Next Steps

1. Choose your integration mode
2. Follow setup instructions
3. Test the connection
4. Start sending messages
5. Monitor usage and costs

---

**Questions?** Contact support@medicon.com

