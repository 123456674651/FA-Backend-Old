<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SubscriptionPlanController extends Controller
{
    // LIST
   public function index()
{
    $plans = SubscriptionPlan::query()
        ->select('subscription_plans.*')
        ->orderBy('id', 'desc');

    if (request()->ajax()) {

        return DataTables::of($plans)
            ->addIndexColumn()

            ->addColumn('price', function ($plan) {
                return '₹ ' . number_format($plan->price, 2);
            })

            ->addColumn('duration', function ($plan) {
                return $plan->duration_value . ' ' . ucfirst($plan->duration_type);
            })

            ->addColumn('agreement_limit', function ($plan) {
                return $plan->agreement_limit ?? 'N/A';
            })
            ->addColumn('created_at', function ($plan) {
                return $plan->created_at ? $plan->created_at->format('Y-m-d') : 'N/A';
            })
            ->addColumn('status', function ($plan) {
                return $plan->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })

            ->addColumn('actions', function ($plan) {
                return '
                    <a href="' . route('subscription-plans.show', $plan->id) . '" class="btn btn-info btn-sm me-1">View</a>
                    <a href="' . route('subscription-plans.edit', $plan->id) . '" class="btn btn-primary btn-sm me-1">Edit</a>

                    <form action="' . route('subscription-plans.destroy', $plan->id) . '" 
                          method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" 
                            class="btn btn-danger btn-sm"
                            onclick="return confirm(\'Are you sure you want to delete this plan?\')">
                            Delete
                        </button>
                    </form>
                ';
            })

            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    return view('admin.subscription_plans.index');
}

    // CREATE VIEW
    public function create()
    {
        return view('admin.subscription_plans.create');
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric',
            'duration_type'   => 'required|in:monthly,yearly,days,per_agreement,lifetime',
            'duration_value'  => 'required|integer|min:1',
            'agreement_limit' => 'nullable|integer|min:0',
            'validity_days'   => 'nullable|integer|min:0',
            'is_active'       => 'nullable|boolean',
        ]);

        SubscriptionPlan::create([
            'name'            => $request->name,
            'price'           => $request->price,
            'duration_type'   => $request->duration_type,
            'duration_value'  => $request->duration_value,
            'agreement_limit' => $request->agreement_limit,
            'validity_days'   => $request->validity_days,
            'features'        => $request->features,
            'is_active'       => $request->is_active ?? 0,
        ]);

        return redirect()->route('subscription-plans.index')
            ->with('success', 'Subscription plan created successfully');
    }

    // EDIT VIEW
    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plans.edit', compact('plan'));
    }

    public function show($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        return view('admin.subscription_plans.show', compact('plan'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'price'           => 'required|numeric',
            'duration_type'   => 'required|in:monthly,yearly,days,per_agreement,lifetime',
            'duration_value'  => 'required|integer|min:1',
            'agreement_limit' => 'nullable|integer|min:0',
            'validity_days'   => 'nullable|integer|min:0',
            'is_active'       => 'nullable|boolean',
        ]);

        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update([
            'name'            => $request->name,
            'price'           => $request->price,
            'duration_type'   => $request->duration_type,
            'duration_value'  => $request->duration_value,
            'agreement_limit' => $request->agreement_limit,
            'validity_days'   => $request->validity_days,
            'features'        => $request->features,
            'is_active'       => $request->is_active ?? 0,
        ]);

        return redirect()->route('subscription-plans.index')
            ->with('success', 'Subscription plan updated successfully');
    }

    // DELETE
    public function destroy($id)
    {
        SubscriptionPlan::findOrFail($id)->delete();

        return redirect()->route('subscription-plans.index')
            ->with('success', 'Subscription plan deleted');
    }
}

