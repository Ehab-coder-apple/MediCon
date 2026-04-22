# Multi-Step Invoice Approval Workflow - Testing Guide

## 🧪 Manual Testing Steps

### Test 1: View Invoice List with Workflow Stages
1. Navigate to `/admin/ai/invoices`
2. Verify invoices display with workflow stage badges
3. Test filter dropdown shows all 6 workflow stages
4. Filter by "Uploaded" stage
5. Verify only uploaded invoices appear

### Test 2: View Invoice Detail Page
1. Click on an invoice in the list
2. Verify workflow progress bar displays all 5 stages
3. Verify current stage is highlighted in blue
4. Verify PDF preview section appears for "Uploaded" stage
5. Verify approval history section is empty initially

### Test 3: First Approval (Approve for Processing)
1. On an "Uploaded" invoice, click "Approve for OCR Processing" button
2. Verify page refreshes
3. Verify workflow stage changes to "Approved for Processing"
4. Verify approval history shows:
   - "Approved for Processing" entry
   - Current user name
   - Current timestamp
5. Verify "Processing in Progress" message appears

### Test 4: Processing Stage
1. Wait for OCR processing to complete (or manually update in database)
2. Refresh the page
3. Verify workflow stage shows "Processed"
4. Verify extracted invoice items table appears
5. Verify "Approve for Inventory Upload" button appears

### Test 5: Second Approval (Approve for Inventory)
1. On a "Processed" invoice, click "Approve for Inventory Upload" button
2. Verify page refreshes
3. Verify workflow stage changes to "Completed"
4. Verify approval history shows both approvals
5. Verify completion message shows items added count

### Test 6: Filtering by Workflow Stage
1. Go to invoice list
2. Filter by "Processing" stage
3. Verify only processing invoices appear
4. Filter by "Completed" stage
5. Verify only completed invoices appear

### Test 7: Search and Filter Combined
1. Go to invoice list
2. Enter invoice number in search box
3. Select workflow stage from dropdown
4. Click Filter
5. Verify results match both criteria

## 🔌 API Testing

### Test API: Approve for Processing
```bash
curl -X POST http://localhost:8000/api/ai/invoices/{invoiceId}/approve-for-processing \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```
Expected: 200 OK with workflow_stage = "approved_for_processing"

### Test API: Approve for Inventory
```bash
curl -X POST http://localhost:8000/api/ai/invoices/{invoiceId}/approve-for-inventory \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```
Expected: 200 OK with workflow_stage = "completed"

### Test API: Get Invoice Details
```bash
curl -X GET http://localhost:8000/api/ai/invoices/{invoiceId} \
  -H "Authorization: Bearer {token}"
```
Expected: 200 OK with all workflow fields populated

## ✅ Verification Checklist

- [ ] All 6 workflow stages display correctly
- [ ] Progress bar updates as workflow progresses
- [ ] PDF preview shows for uploaded stage only
- [ ] Extracted items show after processing
- [ ] Approval buttons appear at correct stages
- [ ] Approval history tracks both approvals
- [ ] User names appear in approval history
- [ ] Timestamps are accurate
- [ ] Filtering works for all stages
- [ ] Search and filter work together
- [ ] API endpoints return correct data
- [ ] Backward compatibility maintained
- [ ] No console errors in browser
- [ ] No database errors in logs

## 🐛 Troubleshooting

**Issue**: Workflow stage not updating
- Check database migration ran: `php artisan migrate:status`
- Verify workflow_stage column exists: `php artisan tinker`

**Issue**: Approval buttons not showing
- Check workflow_stage value in database
- Verify view conditions are correct
- Check browser console for JavaScript errors

**Issue**: Approval history not showing
- Verify approved_for_processing_by is set
- Check timestamps are not null
- Verify user relationships load correctly

## 📝 Notes

- All timestamps are in UTC
- User IDs are tracked for audit trail
- Workflow is one-directional (cannot go backward)
- Items count is recorded for inventory tracking

