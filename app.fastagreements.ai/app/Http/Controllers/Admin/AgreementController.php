<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aggriment;
use App\Models\Customer;
use App\Models\DealCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class AgreementController extends Controller
{
    /**
     * Display a listing of agreements.
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {
            // Select only the columns the table renders. The default SELECT *
            // pulls party_1_signature / party_2_signature (longtext base64 image
            // blobs), which MySQL then has to materialise while filesorting the
            // whole table on the unindexed created_at — that was the 60s hang.
            $agreements = Aggriment::query()
                ->select([
                    'id',
                    'party_1_id',
                    'party_2_id',
                    'category_id',
                    'sub_category',
                    'created_at',
                ])
                ->with([
                    'party1:id,name',
                    'party2:id,name',
                    'category:id,category_name',
                    'subCategory:id,category_name',
                ])
                ->latest();

            // Category Filter
            if ($request->filled('category_id')) {
                $agreements->where('category_id', $request->category_id);
            }

            // Party Filter
            if ($request->filled('party_id')) {
                $partyId = $request->party_id;
                $agreements->where(function ($query) use ($partyId) {
                    $query->where('party_1_id', $partyId)
                        ->orWhere('party_2_id', $partyId);
                });
            }

            // Date Filters (Optimized bounds for indices compatibility)
            if ($request->filled('date_from')) {
                $agreements->where('created_at', '>=', $request->date_from . ' 00:00:00');
            }
            if ($request->filled('date_to')) {
                $agreements->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            return DataTables::of($agreements)
                ->addIndexColumn()
                ->addColumn('party_1_name', function ($row) {
                    if ($row->party1) {
                        return '<a href="#" class="view-customer-trigger fw-bold text-primary text-decoration-none" data-id="' . $row->party_1_id . '">' . htmlspecialchars($row->party1->name) . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('party_2_name', function ($row) {
                    if ($row->party2) {
                        return '<a href="#" class="view-customer-trigger fw-bold text-primary text-decoration-none" data-id="' . $row->party_2_id . '">' . htmlspecialchars($row->party2->name) . '</a>';
                    }
                    return 'N/A';
                })
                ->addColumn('agreement_name', function ($row) {
                    $catName = $row->category ? $row->category->category_name : null;
                    $subCatName = $row->subCategory ? $row->subCategory->category_name : null;

                    if ($catName) {
                        return $catName . ' - ' . $subCatName;
                    } elseif ($catName) {
                        return $catName;
                    } else {
                        return 'N/A';
                    }
                })
                ->addColumn('plan_name', function ($row) {
                    $plan = $this->latestPlanFor($row);

                    return $plan ? $plan->name : 'N/A';
                })
                ->addColumn('plan_price', function ($row) {
                    $plan = $this->latestPlanFor($row);

                    return $plan ? '₹' . number_format($plan->price, 2) : 'N/A';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $viewUrl = route('agreements.show', $row->id);
                    return '<div class="text-center">
                        <a href="' . $viewUrl . '" class="btn btn-dark btn-sm"><i class="bi bi-eye"></i> View</a>
                    </div>';
                })
                ->rawColumns(['action', 'party_1_name', 'party_2_name'])
                ->make(true);
        }

        $categories = DealCategory::where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('category_name')
            ->get();

        $parties = Customer::where('is_active', 1)->orderBy('name')->get();

        return view('admin.agreements.index', compact('categories', 'parties'));
    }

    /**
     * Resolve party 1's latest subscription plan once per row, memoised so the
     * plan_name and plan_price columns don't each fire the same query.
     */
    private $planCache = [];

    private function latestPlanFor($row)
    {
        if (! $row->party_1_id) {
            return null;
        }

        if (! array_key_exists($row->party_1_id, $this->planCache)) {
            $subscription = \App\Models\CustomerSubscription::with('plan')
                ->where('customer_id', $row->party_1_id)
                ->orderBy('id', 'desc')
                ->first();

            $this->planCache[$row->party_1_id] = $subscription ? $subscription->plan : null;
        }

        return $this->planCache[$row->party_1_id];
    }

    /**
     * Display the specified agreement.
     */
    public function show(Aggriment $agreement)
    {
        $agreement->load(['party1', 'party2', 'category', 'language']);

        return view('admin.agreements.show', compact('agreement'));
    }
}
