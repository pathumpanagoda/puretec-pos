<?php
namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $query = Income::where('store_id', $storeId)->with('category', 'user');

        // Filters
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('received_from', 'like', "%{$request->search}%")
                  ->orWhere('reference_number', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('category')) {
            $query->where('income_category_id', $request->category);
        }
        if ($request->filled('from')) {
            $query->whereDate('income_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('income_date', '<=', $request->to);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $incomes = $query->latest('income_date')->paginate(20)->withQueryString();
        $categories = IncomeCategory::where('store_id', $storeId)->where('is_active', true)->get();

        // Stats
        $totalIncome = Income::where('store_id', $storeId)
            ->when($request->from, fn($q) => $q->whereDate('income_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('income_date', '<=', $request->to))
            ->sum('amount');

        $thisMonth = Income::where('store_id', $storeId)
            ->whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->sum('amount');

        return view('incomes.index', compact('incomes', 'categories', 'totalIncome', 'thisMonth'));
    }

    public function create()
    {
        $categories = IncomeCategory::where('store_id', Auth::user()->store_id)
            ->where('is_active', true)->get();
        return view('incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'income_category_id' => 'nullable|exists:income_categories,id',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,other',
            'received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only([
            'title', 'amount', 'income_date', 'income_category_id', 'payment_method',
            'received_from', 'reference_number', 'notes', 'is_recurring', 'recurring_interval'
        ]);

        $data['store_id'] = Auth::user()->store_id;
        $data['user_id'] = Auth::id();
        $data['is_recurring'] = $request->has('is_recurring');

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('incomes', 'public');
        }

        Income::create($data);

        return redirect()->route('incomes.index')->with('success', 'Income recorded successfully!');
    }

    public function show(Income $income)
    {
        $income->load('category', 'user');
        return view('incomes.show', compact('income'));
    }

    public function edit(Income $income)
    {
        $categories = IncomeCategory::where('store_id', Auth::user()->store_id)
            ->where('is_active', true)->get();
        return view('incomes.edit', compact('income', 'categories'));
    }

    public function update(Request $request, Income $income)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'income_date' => 'required|date',
            'income_category_id' => 'nullable|exists:income_categories,id',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,other',
            'received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only([
            'title', 'amount', 'income_date', 'income_category_id', 'payment_method',
            'received_from', 'reference_number', 'notes', 'is_recurring', 'recurring_interval'
        ]);

        $data['is_recurring'] = $request->has('is_recurring');

        if ($request->hasFile('attachment')) {
            if ($income->attachment) {
                Storage::disk('public')->delete($income->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('incomes', 'public');
        }

        if ($request->has('remove_attachment') && $income->attachment) {
            Storage::disk('public')->delete($income->attachment);
            $data['attachment'] = null;
        }

        $income->update($data);

        return redirect()->route('incomes.index')->with('success', 'Income updated successfully!');
    }

    public function destroy(Income $income)
    {
        if ($income->attachment) {
            Storage::disk('public')->delete($income->attachment);
        }
        $income->delete();

        return redirect()->route('incomes.index')->with('success', 'Income deleted successfully!');
    }

    // Quick add category
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $category = IncomeCategory::create([
            'store_id' => Auth::user()->store_id,
            'name' => $request->name,
            'color' => $request->color ?? '#4CAF50',
        ]);

        return response()->json(['success' => true, 'category' => $category]);
    }
}
