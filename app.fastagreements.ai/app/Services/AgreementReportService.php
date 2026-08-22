<?php

namespace App\Services;

use App\Models\Aggriment;
use App\Models\Advocate;
use App\Models\DealCategory;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AgreementReportService
{
    /**
     * Build the Agreement Report query based on provided filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAgreementReportQuery(array $filters)
    {
        $reportType = $filters['report_type'] ?? 'daily';
        $fromDate = $filters['from_date'] ?? null;
        $toDate = $filters['to_date'] ?? null;
        $categoryId = $filters['category_id'] ?? null;
        $languageId = $filters['language_id'] ?? null;
        $stateId = $filters['state_id'] ?? null;
        $cityId = $filters['city_id'] ?? null;
        $customerId = $filters['customer_id'] ?? null;
        $advocateId = $filters['advocate_id'] ?? null;
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $sortBy = $filters['sort_by'] ?? null;
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $revenueGroupBy = $filters['revenue_group_by'] ?? 'day'; // day, month, year, category, advocate

        $hasAdvocateColumn = Schema::hasColumn('agreements', 'advocate_id');

        // Check if report is grouped
        $isGrouped = in_array($reportType, [
            'category_wise', 'language_wise', 'state_wise', 'city_wise', 'user_wise', 'advocate_wise', 'revenue_wise'
        ]);

        // If advocate_wise and advocate_id column does not exist, return a mock listing from advocates table
        if ($reportType === 'advocate_wise' && !$hasAdvocateColumn) {
            $query = Advocate::query()
                ->select([
                    'advocates.name as group_name',
                    'advocates.mobile_number as mobile',
                    DB::raw("'N/A' as email"),
                    DB::raw('0 as total_agreements')
                ]);

            if (!empty($search)) {
                $query->where('advocates.name', 'like', "%{$search}%")
                      ->orWhere('advocates.mobile_number', 'like', "%{$search}%");
            }

            $direction = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
            if ($sortBy === 'total_agreements') {
                $query->orderBy('total_agreements', $direction);
            } else {
                $query->orderBy('advocates.name', $direction);
            }

            return $query;
        }

        // Base Query Configuration
        $query = Aggriment::query();

        // 1. Configure Select & Joins for Grouped Reports
        if ($reportType === 'category_wise') {
            $query->select([
                'deal_categories.category_name as group_name',
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('deal_categories', 'agreements.category_id', '=', 'deal_categories.id')
            ->groupBy('deal_categories.id', 'deal_categories.category_name');

        } elseif ($reportType === 'language_wise') {
            $query->select([
                'languages.language_name as group_name',
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('languages', 'agreements.aggriment_language_id', '=', 'languages.id')
            ->groupBy('languages.id', 'languages.language_name');

        } elseif ($reportType === 'state_wise') {
            $query->select([
                'states.name as group_name',
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('customers', 'agreements.party_1_id', '=', 'customers.id')
            ->join('states', 'customers.state_id', '=', 'states.id')
            ->groupBy('states.id', 'states.name');

        } elseif ($reportType === 'city_wise') {
            $query->select([
                'cities.city as group_name',
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('customers', 'agreements.party_1_id', '=', 'customers.id')
            ->join('cities', 'customers.city_id', '=', 'cities.id')
            ->groupBy('cities.id', 'cities.city');

        } elseif ($reportType === 'user_wise') {
            $query->select([
                'customers.name as group_name',
                'customers.mobile',
                'customers.email',
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('customers', 'agreements.party_1_id', '=', 'customers.id')
            ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.email');

        } elseif ($reportType === 'advocate_wise') {
            // Here hasAdvocateColumn is true
            $query->select([
                'advocates.name as group_name',
                'advocates.mobile_number as mobile',
                DB::raw("'N/A' as email"), // email field doesn't exist on advocates table
                DB::raw('COUNT(agreements.id) as total_agreements')
            ])
            ->join('advocates', 'agreements.advocate_id', '=', 'advocates.id')
            ->groupBy('advocates.id', 'advocates.name', 'advocates.mobile_number');

        } elseif ($reportType === 'revenue_wise') {
            if ($revenueGroupBy === 'day') {
                $query->select([
                    DB::raw('DATE(agreements.created_at) as group_name'),
                    DB::raw('COUNT(agreements.id) as total_agreements'),
                    DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                    DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                    DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                ])
                ->groupBy(DB::raw('DATE(agreements.created_at)'));

            } elseif ($revenueGroupBy === 'month') {
                $query->select([
                    DB::raw('DATE_FORMAT(agreements.created_at, "%Y-%m") as group_name'),
                    DB::raw('COUNT(agreements.id) as total_agreements'),
                    DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                    DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                    DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                ])
                ->groupBy(DB::raw('DATE_FORMAT(agreements.created_at, "%Y-%m")'));

            } elseif ($revenueGroupBy === 'year') {
                $query->select([
                    DB::raw('YEAR(agreements.created_at) as group_name'),
                    DB::raw('COUNT(agreements.id) as total_agreements'),
                    DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                    DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                    DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                ])
                ->groupBy(DB::raw('YEAR(agreements.created_at)'));

            } elseif ($revenueGroupBy === 'category') {
                $query->select([
                    'deal_categories.category_name as group_name',
                    DB::raw('COUNT(agreements.id) as total_agreements'),
                    DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                    DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                    DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                    DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                ])
                ->join('deal_categories', 'agreements.category_id', '=', 'deal_categories.id')
                ->groupBy('deal_categories.id', 'deal_categories.category_name');

            } elseif ($revenueGroupBy === 'advocate') {
                if ($hasAdvocateColumn) {
                    $query->select([
                        'advocates.name as group_name',
                        DB::raw('COUNT(agreements.id) as total_agreements'),
                        DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                        DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                        DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                        DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                    ])
                    ->join('advocates', 'agreements.advocate_id', '=', 'advocates.id')
                    ->groupBy('advocates.id', 'advocates.name');
                } else {
                    $query->select([
                        DB::raw("'N/A' as group_name"),
                        DB::raw('COUNT(agreements.id) as total_agreements'),
                        DB::raw('COALESCE(SUM(agreements.amount), 0) as total_revenue'),
                        DB::raw('COALESCE(AVG(agreements.amount), 0) as average_revenue'),
                        DB::raw('COALESCE(MAX(agreements.amount), 0) as highest_revenue'),
                        DB::raw('COALESCE(MIN(agreements.amount), 0) as lowest_revenue')
                    ])
                    ->groupBy(DB::raw("'N/A'"));
                }
            }
        } else {
            // Eager load relations for standard reports
            $query->with(['party1', 'party2', 'category', 'language'])
                  ->leftJoin('customers as p1', 'agreements.party_1_id', '=', 'p1.id')
                  ->leftJoin('states as s1', 'p1.state_id', '=', 's1.id')
                  ->leftJoin('cities as c1', 'p1.city_id', '=', 'c1.id')
                  ->select([
                      'agreements.*',
                      's1.name as state_name',
                      'c1.city as city_name'
                  ]);

            // Filter by report type date parameters
            if ($reportType === 'daily') {
                $date = $fromDate ?: Carbon::today()->toDateString();
                $query->whereDate('agreements.created_at', $date);
            } elseif ($reportType === 'monthly') {
                $month = $filters['month'] ?? Carbon::now()->month;
                $year = $filters['year'] ?? Carbon::now()->year;
                if ($fromDate) {
                    $dt = Carbon::parse($fromDate);
                    $month = $dt->month;
                    $year = $dt->year;
                }
                $query->whereMonth('agreements.created_at', $month)
                      ->whereYear('agreements.created_at', $year);
            } elseif ($reportType === 'yearly') {
                $year = $filters['year'] ?? Carbon::now()->year;
                if ($fromDate) {
                    $year = Carbon::parse($fromDate)->year;
                }
                $query->whereYear('agreements.created_at', $year);
            } elseif ($reportType === 'cancelled') {
                $query->where('agreements.agreement_status', 0);
            } elseif ($reportType === 'failed') {
                $query->where('agreements.agreement_status', 2);
            }
        }

        // 2. Apply Filters (Where Conditions)
        // General From/To Dates (Applicable if not daily, monthly, yearly reports)
        if (!in_array($reportType, ['daily', 'monthly', 'yearly']) && $fromDate && $toDate) {
            $query->whereBetween('agreements.created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ]);
        }

        if ($categoryId) {
            $query->where('agreements.category_id', $categoryId);
        }

        if ($languageId) {
            $query->where('agreements.aggriment_language_id', $languageId);
        }

        if ($stateId) {
            $query->where(function($q) use ($stateId) {
                $q->whereHas('party1', function($q2) use ($stateId) {
                    $q2->where('state_id', $stateId);
                })->orWhereHas('party2', function($q2) use ($stateId) {
                    $q2->where('state_id', $stateId);
                });
            });
        }

        if ($cityId) {
            $query->where(function($q) use ($cityId) {
                $q->whereHas('party1', function($q2) use ($cityId) {
                    $q2->where('city_id', $cityId);
                })->orWhereHas('party2', function($q2) use ($cityId) {
                    $q2->where('city_id', $cityId);
                });
            });
        }

        if ($customerId) {
            $query->where(function($q) use ($customerId) {
                $q->where('agreements.party_1_id', $customerId)
                  ->orWhere('agreements.party_2_id', $customerId);
            });
        }

        if ($hasAdvocateColumn && $advocateId) {
            $query->where('agreements.advocate_id', $advocateId);
        }

        if ($status !== null && $status !== '') {
            $query->where('agreements.agreement_status', $status);
        }

        // Search Filter (Agreement Number, Customer Name, Mobile)
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('agreements.reference_no', 'like', "%{$search}%")
                  ->orWhereHas('party1', function($q2) use ($search) {
                      $q2->where('customers.name', 'like', "%{$search}%")
                         ->orWhere('customers.mobile', 'like', "%{$search}%");
                  })
                  ->orWhereHas('party2', function($q2) use ($search) {
                      $q2->where('customers.name', 'like', "%{$search}%")
                         ->orWhere('customers.mobile', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Apply Sorting
        $direction = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';

        if ($sortBy) {
            if ($isGrouped) {
                if ($sortBy === 'total_agreements') {
                    $query->orderBy(DB::raw('COUNT(agreements.id)'), $direction);
                } elseif ($sortBy === 'revenue' && $reportType === 'revenue_wise') {
                    $query->orderBy(DB::raw('SUM(agreements.amount)'), $direction);
                } else {
                    $query->orderBy('group_name', $direction);
                }
            } else {
                if ($sortBy === 'created_date' || $sortBy === 'created_at') {
                    $query->orderBy('agreements.created_at', $direction);
                } elseif ($sortBy === 'agreement_number') {
                    $query->orderBy('agreements.reference_no', $direction);
                } elseif ($sortBy === 'customer_name') {
                    $query->leftJoin('customers as sort_cust', 'agreements.party_1_id', '=', 'sort_cust.id')
                          ->orderBy('sort_cust.name', $direction)
                          ->select('agreements.*'); // Keep agreement fields primarily
                } elseif ($sortBy === 'advocate_name' && $hasAdvocateColumn) {
                    $query->leftJoin('advocates as sort_adv', 'agreements.advocate_id', '=', 'sort_adv.id')
                          ->orderBy('sort_adv.name', $direction)
                          ->select('agreements.*');
                } elseif ($sortBy === 'revenue') {
                    $query->orderBy('agreements.amount', $direction);
                } else {
                    $query->orderBy('agreements.created_at', $direction);
                }
            }
        } else {
            // Default Sorting per report type
            if ($reportType === 'revenue_wise') {
                $query->orderBy(DB::raw('SUM(agreements.amount)'), $direction);
            } elseif ($isGrouped) {
                $query->orderBy(DB::raw('COUNT(agreements.id)'), $direction);
            } else {
                $query->orderBy('agreements.created_at', $direction);
            }
        }

        return $query;
    }
}
