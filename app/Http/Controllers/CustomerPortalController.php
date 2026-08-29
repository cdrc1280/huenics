<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            ->inRandomOrder()
            ->take(6)
            ->get();

        $categories = $this->getActiveCategories();

        $totalProductsCount = Product::query()->where('is_active', true)->count();
        $yearsInBusiness = date('Y') - 2008;

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
            'customer_company' => 'nullable|string|max:150',
            'email'            => 'nullable|email|max:150',
            'phone_no'         => 'nullable|string|max:50',
            'project_name'     => 'nullable|string|max:150',
            'project_location' => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:1000',
            'items'            => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.unit'        => 'nullable|string|max:20',
            'items.*.item_code'   => 'nullable|string|max:50',
            'action'              => 'nullable|string|in:download_pdf,preview_pdf,view',
        ]);

        $subtotal = 0.0;
        $items = [];

        foreach ($validated['items'] as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $price = (float) ($item['unit_price'] ?? 0);
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $items[] = [
                'item_code'   => $item['item_code'] ?? ('ITM-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)),
                'description' => $item['description'],
                'quantity'    => $qty,
                'unit'        => $item['unit'] ?? 'pcs',
                'unit_price'  => $price,
                'line_total'  => $lineTotal,
            ];
        }

        $vatAmount = round($subtotal * 0.12, 2);
        $grandTotal = round($subtotal + $vatAmount, 2);

        $refNumber = 'UNOFF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $quoteData = [
            'quotation_number' => $refNumber,
            'customer_name'    => $validated['customer_name'] ?? 'Walk-in Client',
            'customer_company' => !empty($validated['customer_company']) ? $validated['customer_company'] : 'Individual / Direct Buyer',
            'email'            => !empty($validated['email']) ? $validated['email'] : 'N/A',
            'phone_no'         => !empty($validated['phone_no']) ? $validated['phone_no'] : 'N/A',
            'project_name'     => !empty($validated['project_name']) ? $validated['project_name'] : 'General Procurement',
            'project_location' => !empty($validated['project_location']) ? $validated['project_location'] : 'Metro Manila',
            'quotation_date'   => now()->format('Y-m-d'),
            'valid_until'      => now()->addDays(30)->format('Y-m-d'),
            'notes'            => $validated['notes'] ?? '',
            'items'            => $items,
            'subtotal'         => $subtotal,
            'vat_amount'       => $vatAmount,
            'grand_total'      => $grandTotal,
        ];

        // Store last generated quotation in session for quick re-downloads
        session(['last_unofficial_quote' => $quoteData]);

        $action = $request->input('action', 'view');

        if ($action === 'download_pdf') {
            return $this->pdfExporter->downloadResponse($quoteData);
        }

        if ($action === 'preview_pdf') {
            return $this->pdfExporter->previewResponse($quoteData);
        }

        return view('customer.quotation-success', [
            'quote' => $quoteData,
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
}
