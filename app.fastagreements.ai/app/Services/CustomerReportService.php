<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerReportService
{
    /**
     * Build the Customer Report query based on provided filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getCustomerReportQuery(array $filters)
    {
        $reportType = $filters['report_type'] ?? 'new_users';
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;
        $search = $filters['search'] ?? null;
        $state = $filters['state'] ?? null;
        $city = $filters['city'] ?? null;
        $status = $filters['status'] ?? null;
        $sortBy = $filters['sort_by'] ?? null;
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $query = Customer::query()
            ->leftJoin('cities', 'customers.city_id', '=', 'cities.id')
            ->leftJoin('states', 'customers.state_id', '=', 'states.id')
            ->select([
                'customers.id',
                'customers.name',
                'customers.mobile',
                'customers.email',
                'states.name as state',
                'cities.city as city',
                'customers.is_active as status',
                'customers.created_at as registration_date'
            ]);

        // Add dynamically calculated N+1 safe subqueries
        $query->selectSub(function ($q) {
            $q->from('agreements')
              ->selectRaw('count(*)')
              ->whereRaw('agreements.party_1_id = customers.id or agreements.party_2_id = customers.id');
        }, 'total_agreements');

        $query->selectSub(function ($q) {
            $q->from('subscription_invoices')
              ->selectRaw('COALESCE(sum(amount), 0)')
              ->whereColumn('subscription_invoices.customer_id', 'customers.id');
        }, 'total_spending');

        $query->selectSub(function ($q) {
            $q->from('subscription_invoices')
              ->select('invoice_date')
              ->whereColumn('subscription_invoices.customer_id', 'customers.id')
              ->orderBy('invoice_date', 'desc')
              ->limit(1);
        }, 'last_payment_date');

        // Apply Report Type filters
        if ($reportType === 'new_users') {
            if ($fromDate && $toDate) {
                $query->whereBetween('customers.created_at', [
                    Carbon::parse($fromDate)->startOfDay(),
                    Carbon::parse($toDate)->endOfDay()
                ]);
            }
        } elseif ($reportType === 'active_users') {
            $query->where('customers.is_active', 1);
        } elseif ($reportType === 'inactive_users') {
            $query->where('customers.is_active', 0);
        }

        // Apply general date filters for other report types
        if ($reportType !== 'new_users' && $fromDate && $toDate) {
            $query->whereBetween('customers.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ]);
        }

        // Apply specific Status filter if provided (0 or 1)
        if ($status !== null && $status !== '') {
            $query->where('customers.is_active', $status);
        }

        // Apply Search Filter (Name, Mobile, Email)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.name', 'like', "%{$search}%")
                  ->orWhere('customers.mobile', 'like', "%{$search}%")
                  ->orWhere('customers.email', 'like', "%{$search}%");
            });
        }

        // Apply State filter (either name or state_id)
        if (!empty($state)) {
            if (is_numeric($state)) {
                $query->where('customers.state_id', $state);
            } else {
                $query->where('states.name', 'like', "%{$state}%");
            }
        }

        // Apply City filter (either name or city_id)
        if (!empty($city)) {
            if (is_numeric($city)) {
                $query->where('customers.city_id', $city);
            } else {
                $query->where('cities.city', 'like', "%{$city}%");
            }
        }

        // Apply Sorting
        $allowedSorts = ['name', 'registration_date', 'total_spending'];
        if (!empty($sortBy) && in_array($sortBy, $allowedSorts)) {
            $direction = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            if ($sortBy === 'name') {
                $query->orderBy('customers.name', $direction);
            } elseif ($sortBy === 'registration_date') {
                $query->orderBy('customers.created_at', $direction);
            } elseif ($sortBy === 'total_spending') {
                $query->orderBy('total_spending', $direction);
            }
        } else {
            // Default Sorting per report type
            if ($reportType === 'high_spending_users') {
                $query->orderBy('total_spending', 'desc');
            } elseif ($reportType === 'new_users') {
                $query->orderBy('customers.created_at', 'desc');
            } else {
                $query->orderBy('customers.name', 'asc');
            }
        }

        return $query;
    }
}
