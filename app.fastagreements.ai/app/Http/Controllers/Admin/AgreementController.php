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
            $agreements = Aggriment::with(['party1', 'party2', 'category', 'subCategory'])->latest();

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
                    if ($row->party1) {
                        $subscription = \App\Models\CustomerSubscription::with('plan')
                            ->where('customer_id', $row->party_1_id)
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($subscription && $subscription->plan) {
                            return $subscription->plan->name;
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('plan_price', function ($row) {
                    if ($row->party1) {
                        $subscription = \App\Models\CustomerSubscription::with('plan')
                            ->where('customer_id', $row->party_1_id)
                            ->orderBy('id', 'desc')
                            ->first();

                        if ($subscription && $subscription->plan) {
                            return '₹' . number_format($subscription->plan->price, 2);
                        }
                    }
                    return 'N/A';
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
     * Display the specified agreement.
     */
    public function show(Aggriment $agreement)
    {
        $agreement->load(['party1', 'party2', 'category', 'language']);

        return view('admin.agreements.show', compact('agreement'));
    }
}
