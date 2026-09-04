<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\ExportUnofficialQuotationPdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CustomerPortalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('throttle:60,1', only: ['generateUnofficial']),
        ];
    }

    public function __construct(
        protected ExportUnofficialQuotationPdf $pdfExporter
    ) {
    }

    /**
     * Get active product categories
     */
    private function getActiveCategories()
    {
        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
    }

    /**
     * Customer Portal Landing Page.
     */
    public function index()
    {
        $featuredProducts = Product::query()
            ->where('is_active', true)
            ->with(['inventoryItem'])
            ->orderBy('category')
            ->orderBy('canonical_name')
            ->take(16)
            ->get();

        $categories = $this->getActiveCategories();

        $totalProductsCount = Product::query()->where('is_active', true)->count();
        $yearsInBusiness = CompanySetting::getYearsInBusiness();

        return view('customer.home', [
            'featuredProducts'   => $featuredProducts,
            'categories'         => $categories,
            'totalProductsCount' => $totalProductsCount,
            'yearsInBusiness'    => $yearsInBusiness,
        ]);
    }

    /**
     * Customer About Us Page.
     */
    public function about()
    {
        return view('customer.about');
    }

    /**
     * Customer Product Showcase / Catalog Page.
     */
    public function products(Request $request)
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('category');

        $query = Product::query()->where('is_active', true);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('canonical_name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($selectedCategory) && $selectedCategory !== 'all') {
            $query->where('category', $selectedCategory);
        }

        $products = $query->orderBy('category')->orderBy('canonical_name')->paginate(12)->withQueryString();

        $categories = $this->getActiveCategories();

        return view('customer.products', [
            'products'         => $products,
            'categories'       => $categories,
            'selectedCategory' => $selectedCategory,
            'search'           => $search,
        ]);
    }

    /**
     * Interactive Quotation Generator / Cart Page.
     */
    public function quotationBuilder(Request $request)
    {
        $catalogProducts = Product::query()
            ->where('is_active', true)
            ->select(['id', 'sku', 'product_code', 'canonical_name', 'unit_default', 'default_price', 'selling_price', 'category'])
            ->orderBy('canonical_name')
            ->get();

        return view('customer.quotation-builder', [
            'catalogProducts' => $catalogProducts,
        ]);
    }

    /**
     * Process and Generate Unofficial Quotation (View or PDF).
     */
    public function generateUnofficial(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:150',
            'customer_company' => 'required|string|max:150',
            'customer_address' => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:150',
            'phone_no'         => 'required|string|max:50',
            'project_name'     => 'nullable|string|max:150',
            'project_location' => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.product_id'  => 'nullable|integer',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'nullable|numeric|min:0',
            'items.*.unit'        => 'nullable|string|max:20',
            'items.*.item_code'   => 'nullable|string|max:50',
            'action'              => 'nullable|string|in:download_pdf,preview_pdf,print,print_quotation,view',
        ]);

        $subtotal = 0.0;
        $subtotalUndiscounted = 0.0;
        $items = [];

        foreach ($validated['items'] as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $productId = !empty($item['product_id']) ? (int) $item['product_id'] : null;
            $product = $productId ? Product::find($productId) : null;

            $unitPrice = (float) ($item['unit_price'] ?? 0);
            if ($unitPrice <= 0 && $product) {
                $unitPrice = (float) ($product->default_price ?: $product->selling_price ?: 0);
            }

            // Standard trade discount from list price (matches reference PDF 10% volume schedule)
            $discountedPrice = $product && (float) $product->selling_price > 0 && (float) $product->selling_price < $unitPrice
                ? (float) $product->selling_price
                : ($unitPrice > 0 ? round($unitPrice * 0.90, 2) : 0);

            if ($unitPrice <= 0) {
                $unitPrice = 0.0;
                $discountedPrice = 0.0;
            }

            $lineTotal = round($qty * $discountedPrice, 2);
            $undiscountedTotal = round($qty * $unitPrice, 2);

            $subtotal += $lineTotal;
            $subtotalUndiscounted += $undiscountedTotal;

            $itemCode = $item['item_code'] ?? ($product?->sku ?: $product?->product_code ?: ('HISI-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)));
            $desc = $item['description'] ?: ($product?->canonical_name ?? 'Product Line Item');

            $items[] = [
                'product_id'       => $productId,
                'item_code'        => $itemCode,
                'description'      => $desc,
                'quantity'         => $qty,
                'unit'             => $item['unit'] ?? ($product?->unit_default ?: 'pcs'),
                'unit_price'       => $unitPrice,
                'discounted_price' => $discountedPrice,
                'line_total'       => $lineTotal,
                'base64_image'     => $product?->base64_image,
            ];
        }

        $vatAmount = round($subtotal * 0.12, 2);
        $grandTotal = round($subtotal, 2);

        // Reference number format matching Huenics Vendors Agreement Form (e.g. 260904-P)
        $refNumber = date('ymd') . strtoupper(substr(uniqid(), -3)) . ' - P';

        $quoteData = [
            'quotation_number'      => $refNumber,
            'customer_name'         => $validated['customer_name'] ?? 'Walk-in Client',
            'customer_company'      => $validated['customer_company'],
            'customer_address'      => $validated['customer_address'] ?? ($validated['project_location'] ?? 'Metro Manila'),
            'email'                 => !empty($validated['email']) ? $validated['email'] : 'N/A',
            'phone_no'              => $validated['phone_no'],
            'project_name'          => !empty($validated['project_name']) ? $validated['project_name'] : 'General Procurement Project',
            'project_location'      => !empty($validated['project_location']) ? $validated['project_location'] : 'Metro Manila',
            'quotation_date'        => now()->format('Y-m-d'),
            'valid_until'           => now()->addDays(15)->format('Y-m-d'),
            'notes'                 => $validated['notes'] ?? '',
            'items'                 => $items,
            'subtotal'              => $subtotal,
            'subtotal_undiscounted' => $subtotalUndiscounted,
            'total_amount'          => $subtotalUndiscounted,
            'negotiated_amount'     => $subtotal,
            'vat_amount'            => $vatAmount,
            'grand_total'           => $grandTotal,
            'is_encoded'            => false,
        ];

        // Store last generated quotation in session for quick re-downloads
        session(['last_unofficial_quote' => $quoteData]);

        $action = $request->input('action', 'download_pdf');

        if ($action === 'download_pdf') {
            return $this->pdfExporter->downloadResponse($quoteData);
        }

        if (in_array($action, ['print', 'print_quotation', 'preview_pdf'], true)) {
            return response()->view('pdf.unofficial-quotation-template', [
                'quote'        => $quoteData,
                'isPrintView'  => true,
            ]);
        }

        if ($action === 'view') {
            return view('customer.quotation-success', [
                'quote' => $quoteData,
            ]);
        }

        return response()->view('pdf.unofficial-quotation-template', [
            'quote'        => $quoteData,
            'isPrintView'  => true,
        ]);
    }

    /**
     * Download the most recently generated or encoded unofficial quotation PDF.
     */
    public function downloadLastPdf(Request $request): Response
    {
        $quoteData = session('last_unofficial_quote');

        if (!$quoteData && $request->has('payload')) {
            $decoded = json_decode(base64_decode($request->query('payload')), true);
            if (is_array($decoded)) {
                $quoteData = $decoded;
            }
        }

        if (!$quoteData) {
            abort(404, 'No quotation data found to export. Please generate a quotation first.');
        }

        return $this->pdfExporter->downloadResponse($quoteData);
    }

    /**
     * HTTP Fallback for undefined routes with smart aliases.
     */
    public function fallback(Request $request)
    {
        $path = trim(strtolower($request->path()), '/');

        if (in_array($path, ['quotation-builder', 'quote', 'quote-builder', 'estimator'])) {
            return redirect()->route('customer.quotation-builder');
        }

        if (in_array($path, ['catalog', 'shop', 'items', 'store', 'product-catalog'])) {
            return redirect()->route('customer.products');
        }

        if (in_array($path, ['contact', 'contact-us', 'company', 'profile'])) {
            return redirect()->route('customer.about');
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 404,
                'error'  => 'Not Found',
                'message' => 'The requested endpoint was not found on this server.',
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }
}
