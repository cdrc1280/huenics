<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessUnofficialQuotationAction;
use App\Http\Requests\GenerateUnofficialQuotationRequest;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Services\CustomerDailyInquiryService;
use App\Services\ExportUnofficialQuotationPdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;

class CustomerPortalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('throttle:60,1', only: ['generateUnofficial'])];
    }

    public function __construct(
        protected ExportUnofficialQuotationPdf $pdfExporter,
        protected CustomerDailyInquiryService $inquiryGuard
    ) {}

    public function index()
    {
        return view('customer.home', [
            'featuredProducts' => Product::query()->where('is_active', true)->with(['inventoryItem'])->orderBy('category')->orderBy('canonical_name')->take(16)->get(),
            'categories' => $this->getActiveCategories(),
            'totalProductsCount' => Product::query()->where('is_active', true)->count(),
            'yearsInBusiness' => CompanySetting::getYearsInBusiness(),
        ]);
    }

    public function about()
    {
        return view('customer.about');
    }

    public function products(Request $request)
    {
        $search = $request->query('search');
        $selectedCategory = $request->query('category');

        $products = Product::query()->where('is_active', true)
            ->when($search, fn ($query, $term) => $query->where(fn ($sub) => $sub->where('canonical_name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")->orWhere('product_code', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")))
            ->when($selectedCategory && $selectedCategory !== 'all', fn ($query) => $query->where('category', $selectedCategory))
            ->orderBy('category')->orderBy('canonical_name')->paginate(12)->withQueryString();

        return view('customer.products', [
            'products' => $products,
            'categories' => $this->getActiveCategories(),
            'selectedCategory' => $selectedCategory,
            'search' => $search,
        ]);
    }

    public function quotationBuilder(Request $request)
    {
        return view('customer.quotation-builder', [
            'catalogProducts' => Product::query()->where('is_active', true)
                ->select(['id', 'sku', 'product_code', 'canonical_name', 'unit_default', 'default_price', 'selling_price', 'category'])
                ->orderBy('canonical_name')->get(),
            'hasSentToday' => $this->inquiryGuard->hasSentQuotationToday($request),
            'clientIp' => $request->ip() ?: '127.0.0.1',
            'secondsUntilMidnight' => max(0, now()->diffInSeconds(now()->endOfDay())),
        ]);
    }

    public function generateUnofficial(GenerateUnofficialQuotationRequest $request, ProcessUnofficialQuotationAction $processor)
    {
        if ($this->inquiryGuard->hasSentQuotationToday($request)) {
            return back()->withInput()->with('error', 'Daily Submission Limit Reached: Only 1 quotation inquiry per day is permitted from your IP address / session. Our sales engineering team has already received your previous inquiry and is reviewing it. For urgent project bidding, please call us directly at (02) 8561-6836.');
        }

        $quoteSummary = $processor->execute($request->validated(), $request);

        return $request->input('action') === 'view'
            ? view('customer.quotation-success', ['quote' => $quoteSummary])
            : $this->pdfExporter->downloadResponse($quoteSummary);
    }

    public function downloadLastPdf(Request $request): Response
    {
        $quoteSummary = session('last_unofficial_quote');
        if (! $quoteSummary && $request->has('payload')) {
            $decoded = json_decode(base64_decode($request->query('payload')), true);
            if (is_array($decoded)) {
                $quoteSummary = $decoded;
            }
        }

        if (! $quoteSummary) {
            abort(404, 'No quotation data found to export. Please generate a quotation first.');
        }

        return $this->pdfExporter->downloadResponse($quoteSummary);
    }

    public function fallback(Request $request)
    {
        $requestedPath = trim(strtolower($request->path()), '/');

        $redirectRoute = match (true) {
            in_array($requestedPath, ['quotation-builder', 'quote', 'quote-builder', 'estimator'], true) => 'customer.quotation-builder',
            in_array($requestedPath, ['catalog', 'shop', 'items', 'store', 'product-catalog'], true) => 'customer.products',
            in_array($requestedPath, ['contact', 'contact-us', 'company', 'profile'], true) => 'customer.about',
            default => null,
        };

        if ($redirectRoute) {
            return redirect()->route($redirectRoute);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 404, 'error' => 'Not Found', 'message' => 'The requested endpoint was not found on this server.'], 404);
        }

        return response()->view('errors.404', [], 404);
    }

    private function getActiveCategories()
    {
        return Product::query()->where('is_active', true)->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
    }

    public static function hasSentQuotationToday(Request $request): bool
    {
        return app(CustomerDailyInquiryService::class)->hasSentQuotationToday($request);
    }

    public static function recordQuotationSent(Request $request, string $clientIp): void
    {
        app(CustomerDailyInquiryService::class)->recordQuotationSent($request, $clientIp);
    }
}
