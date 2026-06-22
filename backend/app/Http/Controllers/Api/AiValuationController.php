<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiValuationController extends Controller
{
    /**
     * Estimate the value of a gemstone using the 4 Cs criteria.
     */
    public function estimate(Request $request, int $productId): JsonResponse
    {
        $product = Product::with('category')->findOrFail($productId);

        // Extract weight (default to product weight cast, or 1.0 if not set)
        $carats = (float) ($product->weight ?? 1.0);
        if ($carats <= 0) {
            $carats = 1.0;
        }

        // Determine base price per carat based on category
        $categoryName = optional($product->category)->name ?? '';
        $basePricePerCarat = 1000.00; // default

        if (stripos($categoryName, 'ألماس') !== false || stripos($categoryName, 'diamond') !== false) {
            $basePricePerCarat = 5000.00;
        } elseif (stripos($categoryName, 'زمرد') !== false || stripos($categoryName, 'emerald') !== false) {
            $basePricePerCarat = 3500.00;
        } elseif (stripos($categoryName, 'ياقوت') !== false || stripos($categoryName, 'ruby') !== false || stripos($categoryName, 'sapphire') !== false) {
            $basePricePerCarat = 2500.00;
        } elseif (stripos($categoryName, 'عقيق') !== false || stripos($categoryName, 'agate') !== false) {
            $basePricePerCarat = 300.00;
        }

        // Simulating the 4 Cs factors using properties parsed from description or name
        // Cut Multiplier (Excellent: 1.5, Very Good: 1.2, Good: 1.0, Fair: 0.8)
        $cut = 'ممتاز (Excellent)';
        $cutMultiplier = 1.5;
        if (stripos($product->description, 'very good') !== false || stripos($product->description, 'جيد جدا') !== false) {
            $cut = 'جيد جداً (Very Good)';
            $cutMultiplier = 1.25;
        } elseif (stripos($product->description, 'good') !== false || stripos($product->description, 'جيد') !== false) {
            $cut = 'جيد (Good)';
            $cutMultiplier = 1.0;
        }

        // Clarity Multiplier (FL/IF: 2.0, VVS: 1.6, VS: 1.3, SI: 1.0)
        $clarity = 'نقي جداً (VVS1)';
        $clarityMultiplier = 1.6;
        if (stripos($product->description, 'flawless') !== false || stripos($product->description, 'نقي تماما') !== false || stripos($product->description, 'IF') !== false) {
            $clarity = 'خالٍ من العيوب (Flawless/IF)';
            $clarityMultiplier = 2.0;
        } elseif (stripos($product->description, 'VS') !== false || stripos($product->description, 'شبه نقي') !== false) {
            $clarity = 'نقي قليلاً (VS2)';
            $clarityMultiplier = 1.25;
        } elseif (stripos($product->description, 'SI') !== false) {
            $clarity = 'تضمينات طفيفة (SI1)';
            $clarityMultiplier = 1.0;
        }

        // Color Multiplier (D-F colorless: 1.5, G-J near colorless: 1.2, K-M: 1.0)
        $color = 'ممتاز (D-Colorless)';
        $colorMultiplier = 1.5;
        if (stripos($product->description, 'G-Color') !== false || stripos($product->description, 'شبه عديم اللون') !== false) {
            $color = 'شبه عديم اللون (G-Near Colorless)';
            $colorMultiplier = 1.2;
        }

        // Origin Multiplier (e.g. Kashmir Sapphire, Colombian Emerald, Burmese Ruby have premium)
        $originMultiplier = 1.0;
        $origin = $product->origin_country ?? 'غير محدد';
        if (stripos($origin, 'ميانمار') !== false || stripos($origin, 'بورما') !== false || stripos($origin, 'burma') !== false) {
            $originMultiplier = 1.8; // Burmese premium
        } elseif (stripos($origin, 'كولومبيا') !== false || stripos($origin, 'colombia') !== false) {
            $originMultiplier = 1.5; // Colombian premium
        }

        // Calculate final estimate
        $calculatedEstimate = $carats * $basePricePerCarat * $cutMultiplier * $clarityMultiplier * $colorMultiplier * $originMultiplier;

        // Apply a small fluctuation range for low/high estimates (+/- 10%)
        $lowEstimate = $calculatedEstimate * 0.9;
        $highEstimate = $calculatedEstimate * 1.1;

        return response()->json([
            'success' => true,
            'data' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'category' => $categoryName,
                'carats' => $carats,
                'valuation' => [
                    'low_estimate' => round($lowEstimate, 2),
                    'high_estimate' => round($highEstimate, 2),
                    'currency' => 'USD',
                ],
                'factors' => [
                    'base_price_per_carat' => $basePricePerCarat,
                    'cut' => [
                        'grade' => $cut,
                        'multiplier' => $cutMultiplier,
                    ],
                    'clarity' => [
                        'grade' => $clarity,
                        'multiplier' => $clarityMultiplier,
                    ],
                    'color' => [
                        'grade' => $color,
                        'multiplier' => $colorMultiplier,
                    ],
                    'origin' => [
                        'country' => $origin,
                        'multiplier' => $originMultiplier,
                    ],
                ],
                'note' => 'تم حساب التقييم باستخدام محاكاة خوارزمية الذكاء الاصطناعي للفحص الفني للأحجار الكريمة وفقاً للمعايير العالمية (4 Cs).'
            ]
        ]);
    }

    /**
     * Get personalized product recommendations.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = $request->user();

        // If authenticated and has favorites, recommend other products in same categories
        if ($user && $user->favorites()->exists()) {
            $favoriteCategoryIds = $user->favorites()
                ->pluck('category_id')
                ->unique()
                ->toArray();

            $recommendations = Product::active()
                ->whereIn('category_id', $favoriteCategoryIds)
                ->whereNotIn('id', $user->favorites()->pluck('products.id'))
                ->limit(6)
                ->get();
        } else {
            // Fallback: return featured or high views count products
            $recommendations = Product::active()
                ->orderBy('is_featured', 'desc')
                ->orderBy('views_count', 'desc')
                ->limit(6)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }
}
