<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Fast Agreement</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{ asset('assets/img/logo/app-logo-bg.png') }}" rel="icon">
    <link href="{{ asset('assets/img/app-logo-bg.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">


    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">


    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">

   
    <!-- Javascript Requirements -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.1/js/bootstrap.min.js"></script>

    <!-- Laravel Javascript Validation -->
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js') }}"></script>

    {!! JsValidator::formRequest('App\Http\Requests\MyFormRequest') !!}


    <!-- Sweet alert cdn -->

    {{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"
        integrity="sha512-AA1Bzp5Q0K1KanKKmvN/4d3IRKVlv9PYgwFPvm32nPO6QS8yH1HO7LbgB1pgiOxPtfeg5zEn2ba64MUcqJx6CA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <style>
        .error-help-block {
            color: red !important;
            font-size: smaller !important;
        }

        .swal-button {
            background-color: #57BD13 !important;
        }
    </style> --}}
    <script src="{{ asset('ckeditor6/ckeditor.js')}}"></script>

</head>

<body>
    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">

        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('assets/img/logo/dashboard_logo.png') }}?v={{ file_exists(public_path('assets/img/logo/dashboard_logo.png')) ? filemtime(public_path('assets/img/logo/dashboard_logo.png')) : time() }}"
                    alt="Fast Agreements" class="img-fluid" style="max-height: 90px;
                            width: auto;
                            object-fit: contain;">
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <div class="search-bar d-none">
            <form class="search-form d-flex align-items-center" method="POST" action="#">
                <input type="text" name="query" placeholder="Search" title="Enter search keyword">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <!-- End Search Bar -->

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">

                <li class="nav-item d-block d-lg-none d-none">
                    <a class="nav-link nav-icon search-bar-toggle " href="#">
                        <i class="bi bi-search"></i>
                    </a>
                </li><!-- End Search Icon-->

                <li class="nav-item dropdown d-none">

                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell"></i>
                        <span class="badge bg-primary badge-number">4</span>
                    </a><!-- End Notification Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <li class="dropdown-header">
                            You have 4 new notifications
                            <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="notification-item">
                            <i class="bi bi-exclamation-circle text-warning"></i>
                            <div>
                                <h4>Lorem Ipsum</h4>
                                <p>Quae dolorem earum veritatis oditseno</p>
                                <p>30 min. ago</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="notification-item">
                            <i class="bi bi-x-circle text-danger"></i>
                            <div>
                                <h4>Atque rerum nesciunt</h4>
                                <p>Quae dolorem earum veritatis oditseno</p>
                                <p>1 hr. ago</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="notification-item">
                            <i class="bi bi-check-circle text-success"></i>
                            <div>
                                <h4>Sit rerum fuga</h4>
                                <p>Quae dolorem earum veritatis oditseno</p>
                                <p>2 hrs. ago</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="notification-item">
                            <i class="bi bi-info-circle text-primary"></i>
                            <div>
                                <h4>Dicta reprehenderit</h4>
                                <p>Quae dolorem earum veritatis oditseno</p>
                                <p>4 hrs. ago</p>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="dropdown-footer">
                            <a href="#">Show all notifications</a>
                        </li>

                    </ul><!-- End Notification Dropdown Items -->

                </li>
                <!-- End Notification Nav -->

                <li class="nav-item dropdown d-none">

                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-left-text"></i>
                        <span class="badge bg-success badge-number">3</span>
                    </a><!-- End Messages Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                        <li class="dropdown-header">
                            You have 3 new messages
                            <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                                <div>
                                    <h4>Maria Hudson</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>4 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                                <div>
                                    <h4>Anna Nelson</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>6 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="message-item">
                            <a href="#">
                                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                                <div>
                                    <h4>David Muldon</h4>
                                    <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                                    <p>8 hrs. ago</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li class="dropdown-footer">
                            <a href="#">Show all messages</a>
                        </li>

                    </ul>
                    <!-- End Messages Dropdown Items -->

                </li>
                <!-- End Messages Nav -->

                <li class="nav-item dropdown pe-3">

                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->profile_picture ? asset(auth()->user()->profile_picture) : asset('assets/img/profile-img.jpg') }}"
                            alt="Profile" class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover;">
                        <span class="d-none d-md-block dropdown-toggle ps-2">{{ auth()->user()->name }}</span>
                    </a><!-- End Profile Iamge Icon -->

                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6>{{ auth()->user()->name }}</h6>
                            <span>Administrator</span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- <li>
                            <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.index') }}">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li> -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center"
                                href="{{ route('settings.index', ['tab' => 'profile']) }}">
                                <i class="bi bi-gear"></i>
                                <span>Account Settings</span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- <li>
                            <a class="dropdown-item d-flex align-items-center" href="https://fastagreements.ai"
                                target="_blank">
                                <i class="bi bi-question-circle"></i>
                                <span>Need Help?</span>
                            </a>
                        </li> -->
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item d-flex align-items-center" href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </li>

                    </ul><!-- End Profile Dropdown Items -->
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Icons Navigation -->

    </header><!-- End Header -->

    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <li class="nav-item">
                <a class="nav-link " href="{{ route('dashboard.index') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li><!-- End Dashboard Nav -->



            <li class="nav-item d-none">
                <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-journal-text"></i><span>Forms</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="forms-elements.html">
                            <i class="bi bi-circle"></i><span>Form Elements</span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-layouts.html">
                            <i class="bi bi-circle"></i><span>Form Layouts</span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-editors.html">
                            <i class="bi bi-circle"></i><span>Form Editors</span>
                        </a>
                    </li>
                    <li>
                        <a href="forms-validation.html">
                            <i class="bi bi-circle"></i><span>Form Validation</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Forms Nav -->


            <li class="nav-heading">Pages</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('agreements.*') || request()->routeIs('deal_categories.*') ? '' : 'collapsed' }}"
        data-bs-target="#agreements-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark-check"></i>
        <span>Agreements</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>

    <ul id="agreements-nav"
        class="nav-content collapse {{ request()->routeIs('agreements.*') || request()->routeIs('deal_categories.*') ? 'show' : '' }}"
        data-bs-parent="#sidebar-nav">

        <li>
            <a href="{{ route('agreements.index') }}"
                class="{{ request()->routeIs('agreements.*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i>
                <span>Agreements List</span>
            </a>
        </li>

        <li>
            <a href="{{ route('deal_categories.index') }}"
                class="{{ request()->routeIs('deal_categories.*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i>
                <span>Category</span>
            </a>
        </li>

    </ul>
</li>
<!-- End Agreements Page Nav -->
           <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('legal-notices.index') }}">
                    <i class="bi bi-file-earmark-medical"></i>
                    <span>Legal Notices</span>
                </a>
            </li><!-- End Legal Notices Page Nav -->
          
 <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('subscription-plans.index') }}">
                    <i class="bi bi-card-list"></i>
                    <span>Plans</span>
                </a>
            </li><!-- End Subscription Plans Page Nav -->
           <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('subscription-invoices.index') }}">
                    <i class="bi bi-receipt"></i>
                    <span>Invoices</span>
                </a>
            </li><!-- End Subscription Invoices Page Nav -->
          
           <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('subscription-invoices.index') }}">
                    <i class="bi bi-receipt"></i>
                    <span>Transactions</span>
                </a>
            </li><!-- End Subscription Invoices Page Nav -->
          
       <!--     <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notification-templates.*') || request()->routeIs('notifications.send.*') || request()->routeIs('notification-history.*') ? '' : 'collapsed' }}"
                    data-bs-target="#notifications-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-bell"></i><span>Notifications</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="notifications-nav"
                    class="nav-content collapse {{ request()->routeIs('notification-templates.*') || request()->routeIs('notifications.send.*') || request()->routeIs('notification-history.*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                 //   <li>
                        <a href="{{ route('notification-templates.index') }}"
                            class="{{ request()->routeIs('notification-templates.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Templates</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('notifications.send.index') }}"
                            class="{{ request()->routeIs('notifications.send.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Send Notification</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('notification-history.index') }}"
                            class="{{ request()->routeIs('notification-history.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Notification History</span>
                        </a>
                    </li>
                </ul>  -->
            </li><!-- End Notifications Nav -->
      
      
            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('cms-pages.index') }}">
                    <i class="bi bi-journal-text"></i>
                    <span>CMS Pages</span>
                </a>
            </li><!-- End CMS Pages Page Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('purposes.index') }}">
                    <i class="bi bi-bullseye"></i>
                    <span>Purpose</span>
                </a>
            </li><!-- End Purpose Page Nav -->


           

           

           


           


            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('languages.index') }}">
                    <i class="bi bi-translate"></i>
                    <span>Language</span>
                </a>
            </li><!-- End Deal Linex Page Nav -->

            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('customers.*') || request()->routeIs('customer-reports.*') ? '' : 'collapsed' }}"
                    data-bs-target="#customers-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-people"></i><span>Customers</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="customers-nav"
                    class="nav-content collapse {{ request()->routeIs('customers.*') || request()->routeIs('customer-reports.*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('customers.index') }}"
                            class="{{ request()->routeIs('customers.index') || request()->routeIs('customers.edit') || request()->routeIs('customers.create') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Customers</span>
                        </a>
                    </li>
                   
                </ul>
            </li><!-- End Customers Nav -->

            <li class="nav-item">
                <a class="nav-link collapsed" href="{{ route('users.index') }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Admin</span>
                </a>
            </li><!-- End Users Page Nav -->

            <!-- End Purpose Page Nav -->

           

            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-target="#settings-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-gear"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="settings-nav" class="nav-content collapse {{ request()->routeIs('settings.*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('settings.index', ['tab' => 'profile']) }}"
                            class="{{ request()->input('tab') === 'profile' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Profile</span>
                        </a>
                    </li>
               
                    <li>
                        <a href="{{ route('settings.index', ['tab' => 'smtp']) }}"
                            class="{{ request()->input('tab') === 'smtp' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>SMTP</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.index', ['tab' => 'firebase']) }}"
                            class="{{ request()->input('tab') === 'firebase' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Firebase</span>
                        </a>
                    </li>
                  
                </ul>
            </li>
      
    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('agreement-reports.*') || request()->routeIs('customer-reports.*') ? '' : 'collapsed' }}"
        data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-file-earmark-check"></i>
        <span>Reports</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>

    <ul id="reports-nav"
        class="nav-content collapse {{ request()->routeIs('agreement-reports.*') || request()->routeIs('customer-reports.*') ? 'show' : '' }}"
        data-bs-parent="#sidebar-nav">

        <li>
            <a href="{{ route('agreement-reports.index') }}"
                class="{{ request()->routeIs('agreement-reports.*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i>
                <span>Agreement Reports</span>
            </a>
        </li>

        <li>
            <a href="{{ route('customer-reports.index') }}"
                class="{{ request()->routeIs('customer-reports.*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i>
                <span>Customer Reports</span>
            </a>
        </li>
      
      <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reports.*') ? '' : 'collapsed' }}"
                    data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
                    <i class="bi bi-graph-up-arrow"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="reports-nav"
                    class="nav-content collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('reports.gst-tr.index') }}"
                            class="{{ request()->routeIs('reports.gst-tr.*') ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>GST TR Report</span>
                        </a>
                    </li>
                </ul>
            </li><!-- End Reports Nav -->


    </ul>
</li>

 <li class="nav-item">
                <a class="nav-link collapsed" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li><!-- End Login Page Nav -->
        </ul>

    </aside>

    @yield('content')


    <!-- ======= Footer ======= -->
    <footer id="footer" class="footer mt-auto py-3 bg-white border-top">
        <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center px-4">
            <div class="copyright text-muted mb-2 mb-sm-0 text-center text-sm-start">
                &copy; Copyright <strong><span>Fast Agreements</span></strong>. All Rights Reserved
            </div>
            <div class="version text-muted text-center text-sm-end">
                Version 1.0.0
            </div>
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
    {{--
    // Script For choose file Strat --}}
    <script>
        function displaySelectedImage(event, elementId) {
            const selectedImage = document.getElementById(elementId);
            const fileInput = event.target;

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    selectedImage.src = e.target.result;
                };

                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        // Script For choose file End
    </script>

    @if (Session::has('success'))
        <script>
            swal("Message", "{{ Session::get('success') }}", 'success', {
                timer: 10000,
            });
        </script>
    @elseif(Session::has('error'))
        <script>
            swal("Error", "{{ Session::get('error') }}", 'error', {
                timer: 10000,
            });
        </script>
    @endif


    <!-- Datatable -->
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
    @yield('js')

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

</body>

</html>