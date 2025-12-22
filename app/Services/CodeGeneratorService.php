<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Batch;
use App\Models\AccessCode;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CodeGeneratorService
{
    /**
     * Generate a unique product code
     */
    public static function generateProductCode(string $category = null): string
    {
        $categoryPrefix = self::getCategoryPrefix($category);
        $year = date('y'); // 2-digit year
        
        do {
            $sequence = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = "{$categoryPrefix}{$year}{$sequence}";
        } while (Product::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique customer code
     */
    public static function generateCustomerCode(): string
    {
        $prefix = 'CUS';
        $year = date('Y');
        
        do {
            $sequence = str_pad(Customer::count() + rand(1, 100), 5, '0', STR_PAD_LEFT);
            $code = "{$prefix}-{$year}-{$sequence}";
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a unique batch number
     */
    public static function generateBatchNumber(string $productCode): string
    {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        do {
            $sequence = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            $batchNumber = "{$productCode}-{$year}{$month}{$day}-{$sequence}";
        } while (Batch::where('batch_number', $batchNumber)->exists());

        return $batchNumber;
    }

    /**
     * Generate a unique barcode
     */
    public static function generateBarcode(): string
    {
        do {
            // Generate 13-digit EAN-13 barcode
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= rand(0, 9);
            }
            
            // Calculate check digit
            $checkDigit = self::calculateEAN13CheckDigit($code);
            $barcode = $code . $checkDigit;
            
        } while (Product::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Generate a QR code data string
     */
    public static function generateQRCodeData(array $data): string
    {
        $qrData = [
            'type' => $data['type'] ?? 'product',
            'id' => $data['id'] ?? null,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? null,
            'timestamp' => now()->timestamp,
        ];

        return json_encode($qrData);
    }

    /**
     * Generate a unique prescription number
     */
    public static function generatePrescriptionNumber(): string
    {
        $prefix = 'RX';
        $date = now()->format('Ymd');
        
        do {
            $sequence = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $prescriptionNumber = "{$prefix}-{$date}-{$sequence}";
        } while (\App\Models\Prescription::where('prescription_number', $prescriptionNumber)->exists());

        return $prescriptionNumber;
    }

    /**
     * Generate a unique tenant slug
     */
    public static function generateTenantSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (\App\Models\Tenant::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate a secure API key
     */
    public static function generateApiKey(): string
    {
        return 'mk_' . Str::random(32);
    }

    /**
     * Generate a unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(Str::random(6));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Generate a unique reference code for any purpose
     */
    public static function generateReferenceCode(string $prefix = 'REF', int $length = 8): string
    {
        do {
            $code = $prefix . '-' . strtoupper(Str::random($length));
        } while (self::codeExists($code));

        return $code;
    }

    /**
     * Get category prefix for product codes
     */
    private static function getCategoryPrefix(string $category = null): string
    {
        $prefixes = [
            'Pain Relief' => 'PR',
            'Antibiotics' => 'AB',
            'Vitamins' => 'VT',
            'Cold & Flu' => 'CF',
            'Digestive' => 'DG',
            'Allergy' => 'AL',
            'Topical' => 'TP',
            'Prescription' => 'RX',
            'OTC' => 'OTC',
        ];

        return $prefixes[$category] ?? 'GEN'; // Generic prefix
    }

    /**
     * Calculate EAN-13 check digit
     */
    private static function calculateEAN13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $code[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $checkDigit;
    }

    /**
     * Check if a code exists in any table (basic check)
     */
    private static function codeExists(string $code): bool
    {
        // You can expand this to check multiple tables
        return Product::where('code', $code)->exists() ||
               Customer::where('customer_code', $code)->exists() ||
               AccessCode::where('code', $code)->exists();
    }

    /**
     * Generate codes in bulk
     */
    public static function generateBulkCodes(string $type, int $count, array $options = []): array
    {
        $codes = [];
        
        for ($i = 0; $i < $count; $i++) {
            switch ($type) {
                case 'product':
                    $codes[] = self::generateProductCode($options['category'] ?? null);
                    break;
                case 'customer':
                    $codes[] = self::generateCustomerCode();
                    break;
                case 'access':
                    $codes[] = AccessCode::generateUniqueCode();
                    break;
                case 'barcode':
                    $codes[] = self::generateBarcode();
                    break;
                default:
                    $codes[] = self::generateReferenceCode($options['prefix'] ?? 'REF');
            }
        }
        
        return $codes;
    }

    /**
     * Validate code format
     */
    public static function validateCodeFormat(string $code, string $type): bool
    {
        $patterns = [
            'product' => '/^[A-Z]{2,3}\d{6}$/',
            'customer' => '/^CUS-\d{4}-\d{5}$/',
            'purchase' => '/^PO-\d{8}-\d{4}$/',
            'invoice' => '/^INV-\d{8}-\d{4}$/',
            'batch' => '/^[A-Z0-9]+-\d{8}-\d{3}$/',
            'access' => '/^[A-Z0-9]{8}$/',
            'prescription' => '/^RX-\d{8}-\d{4}$/',
        ];

        return isset($patterns[$type]) && preg_match($patterns[$type], $code);
    }
}
