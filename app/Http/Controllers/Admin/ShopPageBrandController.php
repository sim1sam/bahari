<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopPageBrandSchedule;
use App\Models\ShopPageSetting;
use App\Services\ShopPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShopPageBrandController extends Controller
{
    public function __construct(private ShopPageService $shop) {}

    public function edit(): View
    {
        $liveBrands = $this->shop->availableBrandsWithCounts();
        $schedules = ShopPageBrandSchedule::ordered()->get()->keyBy('brand');

        $rows = collect($liveBrands)->map(function (array $item) use ($schedules) {
            $schedule = $schedules->get($item['brand']);

            return [
                'brand' => $item['brand'],
                'product_count' => $item['product_count'],
                'id' => $schedule?->id,
                'selected' => (bool) $schedule,
                'starts_at' => optional($schedule?->starts_at)->format('Y-m-d'),
                'ends_at' => optional($schedule?->ends_at)->format('Y-m-d'),
                'sort_order' => $schedule?->sort_order ?? 0,
                'is_active' => $schedule?->is_active ?? true,
                'is_live_now' => $schedule?->isCurrentlyActive() ?? false,
            ];
        })->values();

        // Keep scheduled brands that no longer have live products (so admin can deactivate/remove).
        foreach ($schedules as $brand => $schedule) {
            if ($rows->contains(fn ($row) => $row['brand'] === $brand)) {
                continue;
            }

            $rows->push([
                'brand' => $brand,
                'product_count' => 0,
                'id' => $schedule->id,
                'selected' => true,
                'starts_at' => optional($schedule->starts_at)->format('Y-m-d'),
                'ends_at' => optional($schedule->ends_at)->format('Y-m-d'),
                'sort_order' => $schedule->sort_order,
                'is_active' => $schedule->is_active,
                'is_live_now' => $schedule->isCurrentlyActive(),
            ]);
        }

        return view('admin.shop-page.brands', [
            'settings' => ShopPageSetting::current(),
            'rows' => $rows,
            'activeBrandCount' => ShopPageBrandSchedule::currentlyActive()->count(),
            'liveBrandCount' => count($liveBrands),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $liveBrands = $this->shop->availableBrands();
        $existingScheduled = ShopPageBrandSchedule::query()->pluck('brand')->all();
        $allowedBrands = array_values(array_unique(array_merge($liveBrands, $existingScheduled)));

        $validated = $request->validate([
            'show_all_when_empty' => 'boolean',
            'brands' => 'nullable|array',
            'brands.*.selected' => 'boolean',
            'brands.*.brand' => ['nullable', 'string', 'max:150', Rule::in($allowedBrands)],
            'brands.*.starts_at' => 'nullable|date',
            'brands.*.ends_at' => 'nullable|date|after_or_equal:brands.*.starts_at',
            'brands.*.sort_order' => 'nullable|integer|min:0',
            'brands.*.is_active' => 'boolean',
            'brands.*.id' => 'nullable|integer|exists:shop_page_brand_schedules,id',
        ]);

        ShopPageSetting::current()->update([
            'show_all_when_empty' => $request->boolean('show_all_when_empty'),
        ]);

        $keptIds = [];
        $index = 0;

        foreach ($validated['brands'] ?? [] as $row) {
            $brand = trim((string) ($row['brand'] ?? ''));
            $selected = filter_var($row['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($brand === '' || ! $selected) {
                continue;
            }

            $payload = [
                'brand' => $brand,
                'starts_at' => $row['starts_at'] ?? null,
                'ends_at' => $row['ends_at'] ?? null,
                'sort_order' => (int) ($row['sort_order'] ?? $index),
                'is_active' => filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if (! empty($row['id'])) {
                $schedule = ShopPageBrandSchedule::query()->find($row['id']);

                if ($schedule) {
                    $schedule->update($payload);
                    $keptIds[] = $schedule->id;
                    $index++;

                    continue;
                }
            }

            $existing = ShopPageBrandSchedule::query()->where('brand', $brand)->first();

            if ($existing) {
                $existing->update($payload);
                $keptIds[] = $existing->id;
            } else {
                $keptIds[] = ShopPageBrandSchedule::create($payload)->id;
            }

            $index++;
        }

        if ($keptIds === []) {
            ShopPageBrandSchedule::query()->delete();
        } else {
            ShopPageBrandSchedule::query()->whereNotIn('id', $keptIds)->delete();
        }

        $this->shop->clearCache();

        return redirect()
            ->route('admin.shop-page.brands.edit')
            ->with('success', 'Shop brands updated.');
    }
}
