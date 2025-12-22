<?php

namespace Database\Seeders;

use App\Models\WhatsAppTemplate;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WhatsAppTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants to create templates for each
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            // Get the first user for this tenant (assuming they have admin privileges)
            $adminUser = User::where('tenant_id', $tenant->id)->first();

            if (!$adminUser) {
                continue; // Skip if no user found
            }

            $templates = [
                [
                    'name' => 'prescription_ready',
                    'display_name' => 'Prescription Ready for Pickup',
                    'description' => 'Notify customer that their prescription is ready for pickup',
                    'category' => 'UTILITY',
                    'body_text' => "Hello {{customer_name}}, your prescription #{{prescription_id}} is ready for pickup at {{pharmacy_name}}. Please bring a valid ID. Thank you!",
                    'parameters' => ['customer_name', 'prescription_id', 'pharmacy_name'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'appointment_reminder',
                    'display_name' => 'Appointment Reminder',
                    'description' => 'Remind customer about upcoming appointment',
                    'category' => 'UTILITY',
                    'body_text' => "Hi {{customer_name}}, this is a reminder about your appointment on {{appointment_date}} at {{appointment_time}}. Please arrive 15 minutes early. Thank you!",
                    'parameters' => ['customer_name', 'appointment_date', 'appointment_time'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'medication_reminder',
                    'display_name' => 'Medication Reminder',
                    'description' => 'Remind customer to take their medication',
                    'category' => 'UTILITY',
                    'body_text' => "Hello {{customer_name}}, this is a friendly reminder to take your {{medication_name}} - {{dosage}}. Stay healthy!",
                    'parameters' => ['customer_name', 'medication_name', 'dosage'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'order_confirmation',
                    'display_name' => 'Order Confirmation',
                    'description' => 'Confirm customer order details',
                    'category' => 'UTILITY',
                    'body_text' => "Thank you {{customer_name}}! Your order #{{order_id}} has been confirmed. Total: \${{total_amount}}. Estimated delivery: {{delivery_date}}.",
                    'parameters' => ['customer_name', 'order_id', 'total_amount', 'delivery_date'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'promotional_offer',
                    'display_name' => 'Promotional Offer',
                    'description' => 'Send promotional offers to customers',
                    'category' => 'MARKETING',
                    'body_text' => "Hi {{customer_name}}! 🎉 Special offer: {{offer_details}} Valid until {{expiry_date}}. Visit {{pharmacy_name}} or call us to avail this offer!",
                    'parameters' => ['customer_name', 'offer_details', 'expiry_date', 'pharmacy_name'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'health_checkup_reminder',
                    'display_name' => 'Health Checkup Reminder',
                    'description' => 'Remind customers about regular health checkups',
                    'category' => 'UTILITY',
                    'body_text' => "Hello {{customer_name}}, it's time for your regular health checkup! Book your appointment at {{pharmacy_name}} for {{service_type}}. Call us at {{phone_number}}.",
                    'parameters' => ['customer_name', 'pharmacy_name', 'service_type', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'birthday_wishes',
                    'display_name' => 'Birthday Wishes',
                    'description' => 'Send birthday wishes to customers',
                    'category' => 'MARKETING',
                    'body_text' => "🎂 Happy Birthday {{customer_name}}! 🎉 Wishing you good health and happiness. Enjoy a special {{discount_percentage}}% discount on your next purchase at {{pharmacy_name}}!",
                    'parameters' => ['customer_name', 'discount_percentage', 'pharmacy_name'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'payment_reminder',
                    'display_name' => 'Payment Reminder',
                    'description' => 'Remind customers about pending payments',
                    'category' => 'UTILITY',
                    'body_text' => "Dear {{customer_name}}, you have a pending payment of \${{amount}} for invoice #{{invoice_id}}. Please settle your account at your earliest convenience. Thank you!",
                    'parameters' => ['customer_name', 'amount', 'invoice_id'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'new_product_announcement',
                    'display_name' => 'New Product Announcement',
                    'description' => 'Announce new products or services',
                    'category' => 'MARKETING',
                    'body_text' => "🆕 Exciting news {{customer_name}}! We now have {{product_name}} available at {{pharmacy_name}}. {{product_description}} Visit us today!",
                    'parameters' => ['customer_name', 'product_name', 'pharmacy_name', 'product_description'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'thank_you_message',
                    'display_name' => 'Thank You Message',
                    'description' => 'Thank customers for their business',
                    'category' => 'UTILITY',
                    'body_text' => "Thank you {{customer_name}} for choosing {{pharmacy_name}}! We appreciate your trust in our services. Your health is our priority. 💚",
                    'parameters' => ['customer_name', 'pharmacy_name'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'item_available',
                    'display_name' => 'Item Now Available',
                    'description' => 'Notify customer that a previously unavailable item is now in stock',
                    'category' => 'UTILITY',
                    'body_text' => "Great news {{customer_name}}! 📦 {{product_name}} is now available at {{pharmacy_name}}. Price: {{price}}. Visit us or call {{phone_number}} to order. Limited stock available!",
                    'parameters' => ['customer_name', 'product_name', 'pharmacy_name', 'price', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'delivery_notification',
                    'display_name' => 'Delivery Notification',
                    'description' => 'Notify customer about delivery status',
                    'category' => 'UTILITY',
                    'body_text' => "Hi {{customer_name}}, your order #{{order_id}} is on its way! 🚚 Expected delivery: {{delivery_date}}. Track your package or contact us at {{phone_number}}.",
                    'parameters' => ['customer_name', 'order_id', 'delivery_date', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'refill_reminder',
                    'display_name' => 'Prescription Refill Reminder',
                    'description' => 'Remind customer to refill their prescription',
                    'category' => 'UTILITY',
                    'body_text' => "Hello {{customer_name}}, it's time to refill your {{medication_name}} prescription! 💊 Visit {{pharmacy_name}} or call {{phone_number}} to refill. We're here to help!",
                    'parameters' => ['customer_name', 'medication_name', 'pharmacy_name', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'loyalty_reward',
                    'display_name' => 'Loyalty Reward Notification',
                    'description' => 'Notify customer about loyalty rewards or points earned',
                    'category' => 'MARKETING',
                    'body_text' => "🎁 Congratulations {{customer_name}}! You've earned {{reward_points}} loyalty points! Redeem them for {{reward_description}} at {{pharmacy_name}}. Valid until {{expiry_date}}.",
                    'parameters' => ['customer_name', 'reward_points', 'reward_description', 'pharmacy_name', 'expiry_date'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'seasonal_offer',
                    'display_name' => 'Seasonal Offer',
                    'description' => 'Send seasonal promotions and special offers',
                    'category' => 'MARKETING',
                    'body_text' => "🌟 {{season}} Special {{customer_name}}! {{offer_details}} Get {{discount_percentage}}% off on {{product_category}} at {{pharmacy_name}}. Offer valid: {{start_date}} - {{end_date}}.",
                    'parameters' => ['season', 'customer_name', 'offer_details', 'discount_percentage', 'product_category', 'pharmacy_name', 'start_date', 'end_date'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'survey_feedback',
                    'display_name' => 'Survey & Feedback Request',
                    'description' => 'Request customer feedback and survey responses',
                    'category' => 'UTILITY',
                    'body_text' => "Hi {{customer_name}}, we'd love to hear from you! 📝 Please take 2 minutes to share your feedback about your recent visit to {{pharmacy_name}}. Your opinion matters! {{survey_link}}",
                    'parameters' => ['customer_name', 'pharmacy_name', 'survey_link'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'vaccination_reminder',
                    'display_name' => 'Vaccination Reminder',
                    'description' => 'Remind customers about upcoming vaccinations',
                    'category' => 'UTILITY',
                    'body_text' => "Hello {{customer_name}}, it's time for your {{vaccine_name}} vaccination! 💉 Schedule your appointment at {{pharmacy_name}} by calling {{phone_number}}. Protect your health today!",
                    'parameters' => ['customer_name', 'vaccine_name', 'pharmacy_name', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'special_discount',
                    'display_name' => 'Special Discount Code',
                    'description' => 'Send exclusive discount codes to customers',
                    'category' => 'MARKETING',
                    'body_text' => "🎉 Exclusive for you {{customer_name}}! Use code {{discount_code}} to get {{discount_percentage}}% off on {{product_category}} at {{pharmacy_name}}. Valid until {{expiry_date}}. Shop now!",
                    'parameters' => ['customer_name', 'discount_code', 'discount_percentage', 'product_category', 'pharmacy_name', 'expiry_date'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'welcome_new_customer',
                    'display_name' => 'Welcome New Customer',
                    'description' => 'Welcome message for new customers',
                    'category' => 'MARKETING',
                    'body_text' => "👋 Welcome {{customer_name}}! Thank you for choosing {{pharmacy_name}}. We're excited to serve you! Get {{welcome_discount}}% off your first purchase. Visit us at {{address}} or call {{phone_number}}.",
                    'parameters' => ['customer_name', 'pharmacy_name', 'welcome_discount', 'address', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
                [
                    'name' => 'stock_alert',
                    'display_name' => 'Low Stock Alert',
                    'description' => 'Alert customers about low stock items they may want',
                    'category' => 'UTILITY',
                    'body_text' => "⚠️ Alert {{customer_name}}: {{product_name}} is running low on stock at {{pharmacy_name}}! Only {{quantity}} units left. Order now at {{phone_number}} to avoid missing out!",
                    'parameters' => ['customer_name', 'product_name', 'pharmacy_name', 'quantity', 'phone_number'],
                    'is_default' => true,
                    'status' => 'approved',
                ],
            ];

            foreach ($templates as $templateData) {
                WhatsAppTemplate::create(array_merge($templateData, [
                    'tenant_id' => $tenant->id,
                    'created_by' => $adminUser->id,
                    'language' => 'en',
                    'is_active' => true,
                ]));
            }
        }
    }
}
