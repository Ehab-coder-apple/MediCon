<?php

namespace App\Traits;

trait HasRoleBasedRouting
{
    /**
     * Get the route prefix based on user role
     */
    protected function getRoutePrefix(): string
    {
        if (auth()->user()->hasRole('admin')) {
            return 'admin.';
        } elseif (auth()->user()->hasRole('pharmacist')) {
            return 'pharmacist.';
        } elseif (auth()->user()->hasRole('sales_staff')) {
            return 'sales-staff.';
        }
        
        return '';
    }

    /**
     * Redirect to index route with role prefix
     */
    protected function redirectToIndex(string $resource, ?string $message = null, string $type = 'success')
    {
        $redirect = redirect()->route($this->getRoutePrefix() . $resource . '.index');
        
        if ($message) {
            $redirect->with($type, $message);
        }
        
        return $redirect;
    }
}
