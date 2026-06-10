<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $storeId = Auth::user()->store_id;
        $query   = Expense::where('store_id', $storeId)->with('category','user');
        if ($request->filled('from'))     $query->whereDate('expense_date', '>=', $request->from);
        if ($request->filled('to'))       $query->whereDate('expense_date', '<=', $request->to);
        if ($request->filled('category')) $query->where('expense_category_id', $request->category);
        $expenses   = $query->latest('expense_date')->paginate(20)->withQueryString();
        $categories = ExpenseCategory::where('store_id', $storeId)->get();
        $total      = $query->sum('amount');
        return view('expenses.index', compact('expenses','categories','total'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('store_id', Auth::user()->store_id)->where('is_active', true)->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string', 'amount' => 'required|numeric|min:0.01', 'expense_date' => 'required|date']);
        $data             = $request->except('_token');
        $data['store_id'] = Auth::user()->store_id;
        $data['user_id']  = Auth::id();
        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('store_id', Auth::user()->store_id)->get();
        return view('expenses.edit', compact('expense','categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate(['title' => 'required|string', 'amount' => 'required|numeric|min:0.01', 'expense_date' => 'required|date']);
        $expense->update($request->except('_token','_method'));
        return redirect()->route('expenses.index')->with('success', 'Expense updated!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Expense deleted!');
    }
}
