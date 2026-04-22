# WhatsApp Dual-Mode Integration Architecture

## Overview
This document outlines the implementation of dual WhatsApp integration modes for MediCon, allowing tenants to choose between:
1. **WhatsApp Business Free** - Manual messaging via WhatsApp Business app
2. **WhatsApp Business API** - Automated messaging via Meta's Cloud API

---

## Architecture Design

### 1. Database Schema Changes

#### New Columns in `whatsapp_credentials` Table:
```sql
ALTER TABLE whatsapp_credentials ADD COLUMN (
    integration_type ENUM('api', 'business_free') DEFAULT 'api',
    business_phone_number VARCHAR(20),
    business_account_name VARCHAR(255),
    api_status VARCHAR(50) DEFAULT 'inactive',
    business_free_status VARCHAR(50) DEFAULT 'inactive',
    last_sync_at TIMESTAMP NULL,
    sync_method VARCHAR(50) DEFAULT 'manual'
);
```

### 2. Integration Types

#### Mode 1: WhatsApp Business Free
- **Use Case**: Small to medium pharmacies
- **Setup**: Manual - no API credentials needed
- **Features**:
  - Store phone number and business name
  - Manual message templates
  - No automated sending
  - No webhook integration
  - Cost: Free
  - Limitations: Manual only, no bulk automation

#### Mode 2: WhatsApp Business API
- **Use Case**: Medium to large pharmacies
- **Setup**: Requires Meta API credentials
- **Features**:
  - Automated message sending
  - Template management
  - Webhook integration
  - Bulk messaging
  - Cost: Per message (varies by region)
  - Capabilities: Full automation

### 3. Model Updates

#### WhatsAppCredential Model
```php
// New properties
protected $fillable = [
    // ... existing fields
    'integration_type',      // 'api' or 'business_free'
    'business_phone_number', // For Business Free mode
    'business_account_name', // For Business Free mode
    'api_status',           // 'active', 'inactive', 'error'
    'business_free_status', // 'active', 'inactive'
    'last_sync_at',
    'sync_method',
];

// New methods
public function isBusinessFreeMode(): bool
public function isApiMode(): bool
public function getActiveMode(): string
public function canSendAutomated(): bool
```

### 4. Service Layer Architecture

#### WhatsAppService - Dual Mode Support
```php
class WhatsAppService {
    private $credential;
    private $mode; // 'api' or 'business_free'
    
    public function __construct(WhatsAppCredential $credential) {
        $this->credential = $credential;
        $this->mode = $credential->integration_type;
    }
    
    public function sendTextMessage($to, $message, $options = []) {
        if ($this->mode === 'api') {
            return $this->sendViaAPI($to, $message, $options);
        } else {
            return $this->sendViaBusinessFree($to, $message, $options);
        }
    }
    
    private function sendViaAPI($to, $message, $options) {
        // Existing API implementation
    }
    
    private function sendViaBusinessFree($to, $message, $options) {
        // Generate WhatsApp link for manual sending
        // Return link and instructions
    }
}
```

### 5. Controller Flow

#### TenantWhatsAppSettingsController
```
1. Show Settings Page
   ├─ Check if credential exists
   ├─ If exists, show current mode
   └─ Display mode selection

2. Select Mode
   ├─ User chooses API or Business Free
   └─ Show appropriate form

3. Configure Mode
   ├─ API Mode: Show API credential form
   └─ Business Free: Show phone number form

4. Save & Verify
   ├─ API: Test API connection
   └─ Business Free: Verify phone number
```

### 6. UI/UX Flow

#### Settings Page Structure
```
┌─────────────────────────────────────┐
│ WhatsApp Configuration              │
├─────────────────────────────────────┤
│                                     │
│ Current Mode: [API / Business Free] │
│                                     │
│ ┌─ Change Integration Mode ────────┐│
│ │ ○ WhatsApp Business API          ││
│ │   (Automated, requires credentials)
│ │                                  ││
│ │ ○ WhatsApp Business Free         ││
│ │   (Manual, no API needed)        ││
│ └──────────────────────────────────┘│
│                                     │
│ ┌─ Configuration ──────────────────┐│
│ │ [Mode-specific form here]        ││
│ └──────────────────────────────────┘│
│                                     │
│ [Save] [Test] [Cancel]             │
└─────────────────────────────────────┘
```

### 7. Message Sending Flow

#### API Mode
```
User sends message
    ↓
WhatsAppService (API mode)
    ↓
Meta Cloud API
    ↓
WhatsApp Server
    ↓
Customer receives message
    ↓
Webhook updates status
```

#### Business Free Mode
```
User sends message
    ↓
WhatsAppService (Business Free mode)
    ↓
Generate WhatsApp link
    ↓
Display link to user
    ↓
User clicks link (opens WhatsApp)
    ↓
User manually sends message
    ↓
Manual status update
```

### 8. Configuration Options

#### Environment Variables
```env
# Existing
WHATSAPP_API_ENABLED=true
WHATSAPP_TEST_MODE=false

# New
WHATSAPP_DUAL_MODE_ENABLED=true
WHATSAPP_DEFAULT_MODE=api
WHATSAPP_ALLOW_MODE_SWITCHING=true
```

### 9. Migration Path

#### For Existing Tenants
1. Existing API credentials → Automatically set to 'api' mode
2. Option to switch to Business Free
3. Can switch back to API anytime

#### For New Tenants
1. Choose mode during setup
2. Configure based on selected mode
3. Can change mode later

### 10. Benefits

#### Business Free Mode
✓ No API costs
✓ Simple setup
✓ Good for small pharmacies
✓ No technical requirements
✗ Manual process
✗ No automation
✗ No bulk messaging

#### API Mode
✓ Full automation
✓ Bulk messaging
✓ Template management
✓ Webhook integration
✗ Requires API credentials
✗ Monthly costs
✗ More complex setup

---

## Implementation Steps

1. **Database Migration** - Add new columns
2. **Model Update** - Add integration_type and methods
3. **Service Update** - Implement dual-mode logic
4. **Controller Update** - Handle mode selection
5. **Views Update** - Create mode-specific forms
6. **Testing** - Test both modes
7. **Documentation** - Setup guides for both modes

---

## Next Steps

See implementation files for detailed code changes.

