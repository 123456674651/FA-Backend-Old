<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\Customer;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CustomerSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = CustomerSubscription::orderBy('id', 'desc');

        if (request()->ajax()) {
            return DataTables::of($subscriptions)
                ->addIndexColumn()

                ->addColumn('customer', function ($row) {
                    $customer = Customer::find($row->customer_id);
                    return $customer ? $customer->name : 'N/A';
                })

                ->addColumn('plan', function ($row) {
                    $plan = SubscriptionPlan::find($row->subscription_plan_id);
                    return $plan ? $plan->name : 'N/A';
                })

                ->addColumn('status', function ($row) {
                    return $row->is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })

                ->addColumn('actions', function ($row) {
                    return '
                        <a href="' . route('customer-subscriptions.edit', $row->id) . '" class="btn btn-primary btn-sm">Edit</a>
                        <form action="' . route('customer-subscriptions.destroy', $row->id) . '" method="POST" style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button class="btn btn-danger btn-sm" onclick="return confirm(\'Delete this subscription?\')">Delete</button>
                        </form>
                    ';
                })

                ->rawColumns(['status', 'actions'])
                ->toJson();
        }

        return view('admin.customer_subscriptions.index');
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $plans = SubscriptionPlan::orderBy('name')->get();

        return view('admin.customer_subscriptions.create', compact('customers', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required',
            'subscription_plan_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'otp_mode' => 'nullable|in:' . SubscriptionPlan::OTP_WITH . ',' . SubscriptionPlan::OTP_WITHOUT,
            'is_active' => 'nullable|boolean',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        CustomerSubscription::create([
            'customer_id' => $validated['customer_id'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'otp_mode' => $validated['otp_mode'] ?? null,
            'is_active' => $validated['is_active'] ?? 1,
            // Same rule PaymentFulfilmentService::fulfil() applies: a
            // per_agreement plan grants its limit (defaulting to 1), a
            // time-based plan grants unlimited (null), never the column's
            // DEFAULT 0 — which AgreementEntitlementService reads as exhausted.
            'remaining_agreements' => $plan->isPerAgreement() ? ($plan->agreement_limit ?? 1) : null,
        ]);

        return redirect()->route('customer-subscriptions.index')
            ->with('success', 'Subscription added successfully');
    }

    public function edit($id)
    {
        $subscription = CustomerSubscription::findOrFail($id);
        $customers = Customer::orderBy('name')->get();
        $plans = SubscriptionPlan::orderBy('name')->get();

        return view('admin.customer_subscriptions.edit', compact('subscription', 'customers', 'plans'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_id' => 'required',
            'subscription_plan_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'otp_mode' => 'nullable|in:' . SubscriptionPlan::OTP_WITH . ',' . SubscriptionPlan::OTP_WITHOUT,
            'is_active' => 'nullable|boolean',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
        $subscription = CustomerSubscription::findOrFail($id);

        $subscription->update([
            'customer_id' => $validated['customer_id'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'otp_mode' => $validated['otp_mode'] ?? null,
            'is_active' => $validated['is_active'] ?? 1,
            'remaining_agreements' => $plan->isPerAgreement() ? ($plan->agreement_limit ?? 1) : null,
        ]);

        return redirect()->route('customer-subscriptions.index')
            ->with('success', 'Subscription updated successfully');
    }

    public function destroy($id)
    {
        CustomerSubscription::findOrFail($id)->delete();

        return back()->with('success', 'Subscription deleted');
    }
}

