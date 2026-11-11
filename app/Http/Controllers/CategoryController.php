<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Controllers\Concerns\ResolvesHousehold;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * EP-04 · C08: categorías de ingreso y gasto del hogar.
 *
 * Se comparten entre integrantes; desactivar una categoría no altera los
 * movimientos que ya la usan (RG-10).
 */
class CategoryController extends Controller
{
    use ResolvesHousehold;

    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $household = $this->household($request);
        $this->authorize('viewAny', [Category::class, $household]);

        $categories = Category::query()
            ->where('household_id', $household->id)
            ->withCount('transactions')
            ->orderBy('kind')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Category $category) => $category->kind->value);

        return view('finance.categories.index', [
            'household' => $household,
            'categories' => $categories,
            'canOperate' => in_array($request->user()->roleIn($household), ['admin', 'member'], true),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $household = $this->household($request);
        $this->authorize('create', [Category::class, $household]);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $household, $validated) {
            $category = Category::create([
                'household_id' => $household->id,
                'name' => $validated['name'],
                'kind' => TransactionType::from($validated['kind']),
                'is_active' => true,
            ]);

            $this->logger->log($household, $request->user(), ActivityLog::CREATED_CATEGORY, $category, [
                'kind' => $validated['kind'],
            ]);
        });

        return redirect()
            ->route('finance.categories.index')
            ->with('status', 'La categoría quedó disponible para tus movimientos.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $category, $validated) {
            $category->update([
                'name' => $validated['name'],
                'is_active' => (bool) $validated['is_active'],
            ]);

            $this->logger->log($category->household, $request->user(), ActivityLog::UPDATED_CATEGORY, $category, [
                'kind' => $category->kind->value,
                'active' => (bool) $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('finance.categories.index')
            ->with('status', 'La categoría quedó actualizada.');
    }
}
