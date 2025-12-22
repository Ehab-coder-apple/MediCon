<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->middleware('auth');
    }

    /**
     * Display WhatsApp messaging dashboard
     */
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;

        // Get recent messages
        $recentMessages = WhatsAppMessage::forTenant($tenantId)
            ->with(['customer', 'template'])
            ->latest()
            ->limit(10)
            ->get();

        // Get message statistics
        $stats = [
            'total_messages' => WhatsAppMessage::forTenant($tenantId)->count(),
            'sent_today' => WhatsAppMessage::forTenant($tenantId)
                ->whereDate('sent_at', today())
                ->count(),
            'delivered_today' => WhatsAppMessage::forTenant($tenantId)
                ->whereDate('delivered_at', today())
                ->count(),
            'failed_today' => WhatsAppMessage::forTenant($tenantId)
                ->byStatus(WhatsAppMessage::STATUS_FAILED)
                ->whereDate('created_at', today())
                ->count(),
        ];

        // Get available templates
        $templates = WhatsAppTemplate::forTenant($tenantId)
            ->active()
            ->get();

        return view('whatsapp.index', compact('recentMessages', 'stats', 'templates'));
    }

    /**
     * Show form for sending individual message
     */
    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $customers = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('phone')
            ->orderBy('name')
            ->get();

        $templates = WhatsAppTemplate::forTenant($tenantId)
            ->active()
            ->approved()
            ->get();

        return view('whatsapp.create', compact('customers', 'templates'));
    }

    /**
     * Preview message before sending
     */
    public function preview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'message_type' => 'required|in:text,template',
            'message_content' => 'required_if:message_type,text|nullable|string|max:4096',
            'template_id' => 'required_if:message_type,template|nullable|exists:whats_app_templates,id',
            'template_parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $tenantId = auth()->user()->tenant_id;

            // Validate customer belongs to tenant
            if ($customer->tenant_id !== $tenantId) {
                return response()->json(['success' => false, 'error' => 'Invalid customer selected.'], 403);
            }

            $preview = [
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'message_type' => $request->message_type,
            ];

            if ($request->message_type === 'text') {
                $preview['message_content'] = $request->message_content;
            } else {
                $template = WhatsAppTemplate::findOrFail($request->template_id);
                $parameters = $request->template_parameters ?? [];
                $preview['template_name'] = $template->display_name;
                $preview['message_content'] = $template->replaceParameters($template->body_text, $parameters);
            }

            return response()->json(['success' => true, 'preview' => $preview]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send individual message
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'message_type' => 'required|in:text,template',
            'message_content' => 'required_if:message_type,text|string|max:4096',
            'template_id' => 'required_if:message_type,template|exists:whats_app_templates,id',
            'template_parameters' => 'array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $customer = Customer::findOrFail($request->customer_id);
            $tenantId = auth()->user()->tenant_id;

            // Validate customer belongs to tenant
            if ($customer->tenant_id !== $tenantId) {
                return back()->withErrors(['customer_id' => 'Invalid customer selected.']);
            }

            // Validate phone number
            if (!$this->whatsAppService->validatePhoneNumber($customer->phone)) {
                return back()->withErrors(['customer_id' => 'Customer has invalid phone number.']);
            }

            $messageData = [
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'customer_id' => $customer->id,
                'recipient_phone' => $customer->phone,
                'message_type' => $request->message_type,
                'status' => WhatsAppMessage::STATUS_PENDING,
            ];

            if ($request->message_type === 'text') {
                $messageData['message_content'] = $request->message_content;

                // Send text message
                $result = $this->whatsAppService->sendTextMessage(
                    $customer->phone,
                    $request->message_content
                );

            } else {
                // Template message
                $template = WhatsAppTemplate::findOrFail($request->template_id);
                $parameters = $request->template_parameters ?? [];

                $messageData['template_id'] = $template->id;
                $messageData['template_parameters'] = $parameters;
                $messageData['message_content'] = $template->replaceParameters($template->body_text, $parameters);

                // Send template message
                $result = $this->whatsAppService->sendTemplateMessage(
                    $customer->phone,
                    $template->name,
                    array_values($parameters)
                );

                // Increment template usage
                $template->incrementUsage();
            }

            // Update message with WhatsApp response
            if ($result['success']) {
                $messageData['whatsapp_message_id'] = $result['message_id'];
                $messageData['status'] = WhatsAppMessage::STATUS_SENT;
                $messageData['sent_at'] = now();
            } else {
                $messageData['status'] = WhatsAppMessage::STATUS_FAILED;
                $messageData['error_message'] = $result['error'] ?? 'Unknown error';
            }

            WhatsAppMessage::create($messageData);

            return redirect()->route('whatsapp.index')
                ->with('success', 'Message sent successfully to ' . $customer->name);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send message: ' . $e->getMessage()]);
        }
    }

    /**
     * Show bulk messaging form
     */
    public function bulk(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $customerCount = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('phone')
            ->count();

        $templates = WhatsAppTemplate::forTenant($tenantId)
            ->active()
            ->approved()
            ->get();

        return view('whatsapp.bulk', compact('customerCount', 'templates'));
    }

    /**
     * Send bulk messages
     */
    public function sendBulk(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'message_type' => 'required|in:text,template',
            'message_content' => 'required_if:message_type,text|string|max:4096',
            'template_id' => 'required_if:message_type,template|exists:whats_app_templates,id',
            'template_parameters' => 'array',
            'recipient_filter' => 'required|in:all,active,recent',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $tenantId = auth()->user()->tenant_id;

            // Get recipients based on filter
            $customersQuery = Customer::where('tenant_id', $tenantId)
                ->whereNotNull('phone');

            switch ($request->recipient_filter) {
                case 'active':
                    // Customers with recent activity (purchases)
                    $customersQuery->whereHas('sales', function($sq) {
                        $sq->where('created_at', '>=', now()->subDays(30));
                    });
                    break;
                case 'recent':
                    // Customers created in last 30 days
                    $customersQuery->where('created_at', '>=', now()->subDays(30));
                    break;
                // 'all' - no additional filter
            }

            $customers = $customersQuery->get();
            $recipients = $customers->pluck('phone')->toArray();

            if (empty($recipients)) {
                return back()->withErrors(['error' => 'No customers found with valid phone numbers.']);
            }

            // Validate phone numbers
            $validRecipients = [];
            foreach ($recipients as $phone) {
                if ($this->whatsAppService->validatePhoneNumber($phone)) {
                    $validRecipients[] = $phone;
                }
            }

            if (empty($validRecipients)) {
                return back()->withErrors(['error' => 'No customers have valid phone numbers.']);
            }

            $bulkMessageId = uniqid('bulk_');
            $results = [];

            if ($request->message_type === 'text') {
                // Send bulk text messages
                $results = $this->whatsAppService->sendBulkMessages(
                    $validRecipients,
                    $request->message_content,
                    ['bulk_message_id' => $bulkMessageId]
                );

                // Store messages in database
                foreach ($customers as $customer) {
                    if (in_array($customer->phone, $validRecipients)) {
                        WhatsAppMessage::create([
                            'tenant_id' => $tenantId,
                            'user_id' => auth()->id(),
                            'customer_id' => $customer->id,
                            'recipient_phone' => $customer->phone,
                            'message_type' => 'text',
                            'message_content' => $request->message_content,
                            'bulk_message_id' => $bulkMessageId,
                            'is_bulk_message' => true,
                            'bulk_filters' => ['filter' => $request->recipient_filter],
                            'status' => WhatsAppMessage::STATUS_SENT,
                            'sent_at' => now(),
                        ]);
                    }
                }

            } else {
                // Send bulk template messages
                $template = WhatsAppTemplate::findOrFail($request->template_id);
                $parameters = $request->template_parameters ?? [];

                $results = $this->whatsAppService->sendBulkTemplateMessages(
                    $validRecipients,
                    $template->name,
                    array_values($parameters),
                    ['bulk_message_id' => $bulkMessageId]
                );

                // Store messages in database
                foreach ($customers as $customer) {
                    if (in_array($customer->phone, $validRecipients)) {
                        WhatsAppMessage::create([
                            'tenant_id' => $tenantId,
                            'user_id' => auth()->id(),
                            'customer_id' => $customer->id,
                            'template_id' => $template->id,
                            'recipient_phone' => $customer->phone,
                            'message_type' => 'template',
                            'message_content' => $template->replaceParameters($template->body_text, $parameters),
                            'template_parameters' => $parameters,
                            'bulk_message_id' => $bulkMessageId,
                            'is_bulk_message' => true,
                            'bulk_filters' => ['filter' => $request->recipient_filter],
                            'status' => WhatsAppMessage::STATUS_SENT,
                            'sent_at' => now(),
                        ]);
                    }
                }

                // Increment template usage
                $template->incrementUsage();
            }

            return redirect()->route('whatsapp.index')
                ->with('success', "Bulk message sent to {$results['total_sent']} customers successfully!");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send bulk message: ' . $e->getMessage()]);
        }
    }

    /**
     * Show message history
     */
    public function history(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $messages = WhatsAppMessage::forTenant($tenantId)
            ->with(['customer', 'template', 'user'])
            ->latest()
            ->paginate(20);

        return view('whatsapp.history', compact('messages'));
    }

    /**
     * Get template details via AJAX
     */
    public function getTemplate(WhatsAppTemplate $template): JsonResponse
    {
        // Ensure template belongs to current tenant
        if ($template->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'display_name' => $template->display_name,
            'body_text' => $template->body_text,
            'parameters' => $template->parameters,
            'parameter_placeholders' => $template->getParameterPlaceholders(),
        ]);
    }
}
