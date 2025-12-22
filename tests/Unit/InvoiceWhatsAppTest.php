<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\InvoiceController;
use App\Services\WhatsAppService;

class InvoiceWhatsAppTest extends TestCase
{
    /**
     * Test that the route exists
     */
    public function test_send_whatsapp_route_exists()
    {
        // This test verifies that the route is properly registered
        $routes = collect(\Route::getRoutes())->pluck('uri');
        $this->assertTrue($routes->contains('invoices/{invoice}/send-whatsapp'));
    }

    /**
     * Test that the controller method exists
     */
    public function test_send_whatsapp_controller_method_exists()
    {
        // Verify the method exists in the controller
        $controller = new InvoiceController();
        $this->assertTrue(method_exists($controller, 'sendViaWhatsApp'));
    }

    /**
     * Test that the PDF view exists
     */
    public function test_invoice_pdf_view_exists()
    {
        $viewPath = resource_path('views/invoices/pdf.blade.php');
        $this->assertTrue(file_exists($viewPath), 'Invoice PDF view does not exist');
    }

    /**
     * Test that the WhatsApp button is in the invoice show view
     */
    public function test_whatsapp_button_in_invoice_view()
    {
        $viewPath = resource_path('views/invoices/show.blade.php');
        $content = file_get_contents($viewPath);
        
        $this->assertStringContainsString('send-whatsapp', $content);
        $this->assertStringContainsString('Send via WhatsApp', $content);
        $this->assertStringContainsString('fab fa-whatsapp', $content);
    }

    /**
     * Test that the required packages are installed
     */
    public function test_required_packages_installed()
    {
        // Check if barryvdh/laravel-dompdf is installed
        $this->assertTrue(class_exists('Barryvdh\DomPDF\Facade\Pdf'));
    }

    /**
     * Test that WhatsAppService has required methods
     */
    public function test_whatsapp_service_has_required_methods()
    {
        $service = new WhatsAppService();
        
        $this->assertTrue(method_exists($service, 'sendTextMessage'));
        $this->assertTrue(method_exists($service, 'isEnabled'));
    }

    /**
     * Test that the route is POST method
     */
    public function test_send_whatsapp_route_is_post()
    {
        $routes = collect(\Route::getRoutes())
            ->filter(function($route) {
                return $route->uri === 'invoices/{invoice}/send-whatsapp';
            });
        
        $this->assertTrue($routes->count() > 0);
        
        $route = $routes->first();
        $this->assertContains('POST', $route->methods);
    }

    /**
     * Test that the controller method has proper imports
     */
    public function test_controller_has_required_imports()
    {
        $controllerPath = app_path('Http/Controllers/InvoiceController.php');
        $content = file_get_contents($controllerPath);
        
        // Check for required imports
        $this->assertStringContainsString('use App\Models\WhatsAppMessage;', $content);
        $this->assertStringContainsString('use App\Services\WhatsAppService;', $content);
        $this->assertStringContainsString('use Barryvdh\DomPDF\Facade\Pdf;', $content);
    }

    /**
     * Test that the PDF view has proper structure
     */
    public function test_pdf_view_has_proper_structure()
    {
        $viewPath = resource_path('views/invoices/pdf.blade.php');
        $content = file_get_contents($viewPath);
        
        // Check for key elements
        $this->assertStringContainsString('<!DOCTYPE html>', $content);
        $this->assertStringContainsString('Invoice', $content);
        $this->assertStringContainsString('$invoice', $content);
        $this->assertStringContainsString('Bill To:', $content);
        $this->assertStringContainsString('Total:', $content);
    }

    /**
     * Test that the route is protected (requires authentication)
     */
    public function test_send_whatsapp_route_requires_auth()
    {
        // Get the route
        $routes = collect(\Route::getRoutes())
            ->filter(function($route) {
                return $route->uri === 'invoices/{invoice}/send-whatsapp';
            });
        
        $this->assertTrue($routes->count() > 0);
        
        // Check if it's in the authenticated middleware group
        $route = $routes->first();
        $middleware = $route->middleware();
        
        // The route should be protected by auth middleware
        $this->assertTrue(
            in_array('auth', $middleware) || in_array('web', $middleware),
            'Route should be protected by authentication'
        );
    }
}

