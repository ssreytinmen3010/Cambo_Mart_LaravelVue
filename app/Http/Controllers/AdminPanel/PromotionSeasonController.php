<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use App\Models\PromotionSeason;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PromotionSeasonController extends Controller
{
    public function index(Request $request)
    {
        PromotionSeason::query()
            ->where('status', '!=', PromotionSeason::STATUS_EXPIRE)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->update(['status' => PromotionSeason::STATUS_EXPIRE]);

        $query = PromotionSeason::query()->latest();

        if ($request->search) {
            $query->where('code', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $seasonsPaginator = $query->paginate(10);
        $seasonsData = collect($seasonsPaginator->items())->map(fn ($season) => [
            'id' => $season->id,
            'image' => $season->image,
            'code' => $season->code,
            'start_date' => $season->start_date?->format('Y-m-d'),
            'end_date' => $season->end_date?->format('Y-m-d'),
            'promotion_type' => $season->promotion_type,
            'promotion_value' => $season->promotion_value,
            'status' => $season->status,
            'created_at' => $season->created_at->format('Y-m-d H:i'),
        ])->all();

        $seasons = [
            'data' => $seasonsData,
            'current_page' => $seasonsPaginator->currentPage(),
            'last_page' => $seasonsPaginator->lastPage(),
            'per_page' => $seasonsPaginator->perPage(),
            'total' => $seasonsPaginator->total(),
            'links' => $seasonsPaginator->linkCollection()->toArray(),
        ];

        return Inertia::render('Admin/PromotionSeason/Index', [
            'seasons' => $seasons,
            'filters' => $request->only(['search', 'from_date', 'to_date']),
            'statuses' => PromotionSeason::statuses(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSeason($request);
        $validated['promotion_value'] = $this->normalizePromotionValue($validated);

        PromotionSeason::create($validated);

        return redirect()->route('admin.promotion-seasons.index')
            ->with('success', 'Promotion season created successfully.');
    }

    public function update(Request $request, PromotionSeason $promotionSeason)
    {
        $validated = $this->validateSeason($request, $promotionSeason->id);
        $validated['promotion_value'] = $this->normalizePromotionValue($validated);

        $promotionSeason->update($validated);

        return redirect()->route('admin.promotion-seasons.index')
            ->with('success', 'Promotion season updated successfully.');
    }

    public function destroy(PromotionSeason $promotionSeason)
    {
        $promotionSeason->delete();

        return redirect()->route('admin.promotion-seasons.index')
            ->with('success', 'Promotion season deleted.');
    }

    private function validateSeason(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'image' => ['nullable', 'string', 'max:2048'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('promotion_seasons', 'code')->ignore($ignoreId),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'promotion_type' => ['nullable', Rule::in(PromotionSeason::promotionTypes())],
            'promotion_value' => [
                Rule::requiredIf(fn () => in_array(
                    $request->input('promotion_type'),
                    [PromotionSeason::PROMOTION_FIX, PromotionSeason::PROMOTION_PERCENTAGE],
                    true
                )),
                'nullable',
                'numeric',
                'min:0',
            ],
            'cart_id' => ['nullable', 'integer', 'exists:cart,id'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'status' => ['required', Rule::in(PromotionSeason::statuses())],
        ]);
    }

    private function normalizePromotionValue(array $validated): float
    {
        if (($validated['promotion_type'] ?? null) === PromotionSeason::PROMOTION_FREE_DELIVERY) {
            return 0;
        }

        return (float) ($validated['promotion_value'] ?? 0);
    }
}
