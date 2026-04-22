# AI & Document Processing - Quick Start Guide

## 🚀 Getting Started

### 1. Access the AI Dashboard
Navigate to: `http://127.0.0.1:8000/admin/ai/dashboard`

You'll see:
- Total documents processed
- Pending documents count
- Processed invoices count
- Prescription checks count
- Quick links to all features

### 2. Invoice Processing

#### Upload an Invoice
1. Go to **AI & Documents → Invoice Processing**
2. Click **Upload Invoice**
3. Select a PDF or image file
4. System will automatically extract data

#### Review Extracted Data
1. View the extracted invoice details
2. Check confidence scores for each field
3. Edit any incorrect data
4. Click **Approve** or **Reject**

#### Approved Invoices
- Automatically added to inventory
- Excel file generated
- Linked to supplier records

### 3. Prescription Checking

#### Scan a Prescription
1. Go to **AI & Documents → Prescription Checking**
2. Upload prescription image/PDF
3. System extracts medications

#### Check Availability
- **✓ In Stock** - Medication available
- **⚠️ Low Stock** - Limited quantity
- **✗ Out of Stock** - Not available

#### View Alternatives
- See suggested alternative products
- Check shelf locations
- Compare prices
- View availability at other branches

### 4. Product Information

#### Search Products
1. Go to **AI & Documents → Product Information**
2. Search by name or code
3. View product details

#### Add/Edit Information
1. Click **Edit** on any product
2. Add pharmaceutical data:
   - Active ingredients
   - Side effects
   - Indications
   - Dosage information
   - Contraindications
   - Drug interactions
   - Storage requirements
3. Click **Save Information**

#### View Product Details
- See all pharmaceutical information
- Check current stock levels
- View pricing
- See manufacturer details

## 📱 Mobile App Integration

### API Endpoints for Mobile App

#### Upload Document
```
POST /api/ai/documents/upload
Content-Type: multipart/form-data

Parameters:
- document_type: "invoice" or "prescription"
- file: [PDF/Image file]
- branch_id: [Branch ID]
```

#### Get Processing Status
```
GET /api/ai/documents/{documentId}/status
```

#### Search Products
```
GET /api/ai/products/search?query=paracetamol&branch_id=1
```

#### Get Product Information
```
GET /api/ai/products/{productId}/info
```

#### Get Alternative Products
```
GET /api/ai/products/{productId}/alternatives?branch_id=1
```

## 🔧 Configuration

### OCR Service Setup

To enable actual document processing, configure your OCR service:

1. **Google Cloud Vision** (Recommended)
   - Set up Google Cloud project
   - Enable Vision API
   - Add credentials to `.env`:
     ```
     GOOGLE_CLOUD_PROJECT_ID=your-project-id
     GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
     ```

2. **AWS Textract**
   - Configure AWS credentials
   - Add to `.env`:
     ```
     AWS_ACCESS_KEY_ID=your-key
     AWS_SECRET_ACCESS_KEY=your-secret
     AWS_DEFAULT_REGION=us-east-1
     ```

### File Storage

Configure where uploaded documents are stored:
```
# .env
FILESYSTEM_DISK=private
```

## 📊 Data Flow

### Invoice Processing Flow
```
Upload PDF → Extract Text → Parse Data → Create Invoice Record 
→ Create Line Items → Manual Review → Approve/Reject 
→ Add to Inventory → Generate Excel
```

### Prescription Flow
```
Upload Image → Extract Medications → Check Inventory 
→ Find Alternatives → Display Results → Pharmacist Review
```

## 🔐 Security

- All uploads stored in private storage
- Tenant isolation enforced
- User authentication required
- File size limits: 10MB
- Supported formats: PDF, JPG, PNG

## 📈 Performance Tips

1. **Batch Processing**
   - Process multiple invoices in queue
   - Use background jobs for large files

2. **Caching**
   - Product information cached
   - Alternative suggestions cached

3. **Database**
   - Indexes on frequently queried fields
   - Pagination on list views

## 🐛 Troubleshooting

### Documents Not Processing
- Check file format (PDF, JPG, PNG)
- Verify file size < 10MB
- Check OCR service credentials

### Incorrect Data Extraction
- Ensure document quality
- Check OCR service configuration
- Review confidence scores

### Slow Performance
- Check database indexes
- Clear application cache
- Optimize OCR service

## 📚 Additional Resources

- API Documentation: See `routes/api.php`
- Database Schema: See `database/migrations/`
- Models: See `app/Models/`
- Controllers: See `app/Http/Controllers/`

## 🎯 Next Steps

1. Configure OCR service
2. Test with sample documents
3. Train staff on usage
4. Integrate with mobile app
5. Monitor and optimize performance

