@extends('admin.layout.admin')
@section('content')
    <!-- End Sidebar-->
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Dashboard</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Dashboard Filters -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Dashboard Filters</h5>
                            <form method="GET" action="{{ route('dashboard.index') }}" class="row g-2 align-items-end">
                                <div class="col-sm-3">
                                    <label class="form-label small">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                                </div>
                                <div class="col-sm-3">
                                    <label class="form-label small">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                                </div>
                                <div class="col-sm-6 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('dashboard.index') }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="col-12 mb-4">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(13,110,253,0.12);">
                                        <i class="bi bi-people-fill fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Total Registered Users</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $totalRegisteredUsers ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(25,135,84,0.12);">
                                        <i class="bi bi-file-earmark-text fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Total Agreements</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $totalAgreements ?? $dealsCount ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-warning text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(255,193,7,0.12);">
                                        <i class="bi bi-calendar-day fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Today's Agreements</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $todaysAgreements ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(13,202,240,0.12);">
                                        <i class="bi bi-calendar4-week fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Monthly Agreements</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $monthlyAgreements ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(108,117,125,0.08);">
                                        <i class="bi bi-tags fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Active Categories</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $activeCategories ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(13,110,253,0.08);">
                                        <i class="bi bi-person-badge fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Active Advocates</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $activeAdvocates ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-12">
                            <div class="card shadow-sm rounded h-100">
                                <div class="card-body d-flex align-items-center">
                                    <div class="me-3 p-3 rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;box-shadow:0 6px 18px rgba(25,135,84,0.08);">
                                        <i class="bi bi-people fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Active Customers</small>
                                        <h4 class="mb-0 fw-bold" data-count>{{ $activeCustomers ?? $customersCount ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            

            </div>
        </section>

    </main>

    <!-- End #main -->
    <script>
        // Simple animated counter for elements with [data-count]
        document.addEventListener('DOMContentLoaded', function() {
            function animate(el, target) {
                var start = 0;
                var duration = 900;
                var startTime = null;

                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var current = Math.floor(progress * (target - start) + start);
                    el.textContent = current;
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    } else {
                        el.textContent = target;
                    }
                }
                window.requestAnimationFrame(step);
            }

            document.querySelectorAll('[data-count]').forEach(function(h) {
                var val = parseInt(h.textContent.replace(/,/g, '')) || 0;
                h.textContent = '0';
                if (val > 0) animate(h, val);
            });
        });
    </script>

@stop
