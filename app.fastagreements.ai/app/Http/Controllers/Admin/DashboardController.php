<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Deal;
use App\Models\DealCategory;
use App\Models\Advocate;
use App\Models\Customer;
use App\Models\Aggriment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
      
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Normalize dates when both provided
        if ($fromDate && $toDate) {
            try {
                $from = Carbon::parse($fromDate)->startOfDay();
                $to = Carbon::parse($toDate)->endOfDay();
            } catch (\Exception $e) {
                $from = null;
                $to = null;
            }
        } else {
            $from = null;
            $to = null;
        }
        // Optimized count queries using Eloquent count() only
        // Total Registered Users and Total Agreements: apply date filter when both dates provided
        if ($from && $to) {
            $totalRegisteredUsers = User::whereBetween('created_at', [$from, $to])->count();
            $totalAgreements = Aggriment::whereBetween('created_at', [$from, $to])->count();
        } else {
            // No date filter: keep existing behaviour (all time / active-based where previously used)
            if (Schema::hasColumn('users', 'status')) {
                $totalRegisteredUsers = User::where('status', 1)->count();
            } elseif (Schema::hasColumn('users', 'is_active')) {
                $totalRegisteredUsers = User::where('is_active', 1)->count();
            } else {
                $totalRegisteredUsers = User::count();
            }

            $totalAgreements = Deal::count();
        }
        // Today's agreements and monthly agreements ignore the date filter
        $todaysAgreements = Aggriment::whereDate('created_at', Carbon::today())->count();
        $monthlyAgreements = Aggriment::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        // Deal categories may use `is_active` or `status`
        if (Schema::hasColumn('deal_categories', 'is_active')) {
            $activeCategories = DealCategory::where('is_active', 1)->count();
        } elseif (Schema::hasColumn('deal_categories', 'status')) {
            $activeCategories = DealCategory::where('status', 1)->count();
        } else {
            $activeCategories = DealCategory::count();
        }
        $activeAdvocates = Advocate::where('status', 1)->count();
        // Customers use `is_active` in migrations
        if (Schema::hasColumn('customers', 'is_active')) {
            $activeCustomers = Customer::where('is_active', 1)->count();
        } elseif (Schema::hasColumn('customers', 'status')) {
            $activeCustomers = Customer::where('status', 1)->count();
        } else {
            $activeCustomers = Customer::count();
        }

        // Keep compatibility with previous compact vars used in the view
        $dealsCount = $totalAgreements;
        $customersCount = $activeCustomers;
      

        return view('admin.dashboard.index', compact(
            'totalRegisteredUsers',
            'totalAgreements',
            'todaysAgreements',
            'monthlyAgreements',
            'activeCategories',
            'activeAdvocates',
            'activeCustomers',
            'dealsCount',
            'customersCount'
        ));
    }
}
