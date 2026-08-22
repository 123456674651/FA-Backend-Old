@extends('admin.layout.admin')

@section('content')

<main id="main" class="main">

  <div class="pagetitle">
    <h1>Account Setting</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
        <li class="breadcrumb-item">Admin</li>
        <li class="breadcrumb-item active">Account Setting</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <!-- Success & General Error Alerts -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i>
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-octagon me-1"></i>
      Please correct the errors in the form below.
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <section class="section profile">
    <div class="row">
      <div class="col-xl-4">

        <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
            <img src="{{ $user->profile_picture ? asset($user->profile_picture) : asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #ddd;">
            <h2 class="mt-3 text-dark">{{ $user->name }}</h2>
            <h3 class="text-muted">Administrator</h3>
          </div>
        </div>

      </div>

      <div class="col-xl-8">

        <div class="card">
          <div class="card-body pt-3">
            <!-- Bordered Tabs -->
            <ul class="nav nav-tabs nav-tabs-bordered">
              <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview">Overview</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profile</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Change Password</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-logo">Dashboard Logo</button>
              </li>
            </ul>

            <div class="tab-content pt-2">

              <!-- Overview Tab -->
              <div class="tab-pane fade show active profile-overview" id="profile-overview">
                <h5 class="card-title">Profile Details</h5>

                <div class="row mb-2">
                  <div class="col-lg-3 col-md-4 label text-muted">Full Name</div>
                  <div class="col-lg-9 col-md-8 text-dark fw-semibold">{{ $user->name }}</div>
                </div>

                <div class="row mb-2">
                  <div class="col-lg-3 col-md-4 label text-muted">Email</div>
                  <div class="col-lg-9 col-md-8 text-dark fw-semibold">{{ $user->email }}</div>
                </div>

                <div class="row mb-2">
                  <div class="col-lg-3 col-md-4 label text-muted">Role</div>
                  <div class="col-lg-9 col-md-8 text-dark fw-semibold">Super Administrator</div>
                </div>
              </div>

              <!-- Edit Profile Tab -->
              <div class="tab-pane fade profile-edit pt-3" id="profile-edit">

                <!-- Profile Picture Form -->
                <div class="row mb-4">
                  <label class="col-md-4 col-lg-3 col-form-label">Profile Image</label>
                  <div class="col-md-8 col-lg-9">
                    <div class="d-flex align-items-center gap-3">
                      <img id="avatar-preview" src="{{ $user->profile_picture ? asset($user->profile_picture) : asset('assets/img/profile-img.jpg') }}" alt="Profile" class="rounded-circle" style="width: 90px; height: 90px; object-fit: cover; border: 1px solid #ccc;">
                      <div class="d-flex flex-column gap-2">
                        <div class="d-flex gap-2">
                          <button type="button" class="btn btn-dark btn-sm fw-semibold" onclick="document.getElementById('profile_picture_input').click();">
                            <i class="bi bi-upload"></i> Upload
                          </button>
                          @if($user->profile_picture)
                            <button type="button" class="btn btn-outline-danger btn-sm fw-semibold" onclick="event.preventDefault(); document.getElementById('delete-avatar-form').submit();">
                              <i class="bi bi-trash"></i> Remove
                            </button>
                          @endif
                        </div>
                        <span class="text-muted small">JPG, PNG or GIF. Max 2MB.</span>
                      </div>
                    </div>

                    <!-- Hidden upload form -->
                    <form action="{{ route('profile.image') }}" method="POST" enctype="multipart/form-data" id="avatar-upload-form" class="d-none">
                      @csrf
                      <input type="file" name="profile_picture" id="profile_picture_input" accept="image/*" onchange="document.getElementById('avatar-upload-form').submit();">
                    </form>

                    <!-- Hidden delete form -->
                    @if($user->profile_picture)
                      <form action="{{ route('profile.image.delete') }}" method="POST" id="delete-avatar-form" class="d-none">
                        @csrf
                        @method('DELETE')
                      </form>
                    @endif

                    @error('profile_picture')
                      <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <!-- Profile Details Edit Form -->
                <form method="POST" action="{{ route('profile.update') }}">
                  @csrf

                  <div class="row mb-3">
                    <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Full Name</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" id="fullName" value="{{ old('name', $user->name) }}" required>
                      @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email Address</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="email" type="email" class="form-control @error('email') is-invalid @enderror" id="Email" value="{{ old('email', $user->email) }}" required>
                      @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="text-center pt-2">
                    <button type="submit" class="btn btn-dark fw-semibold px-4">Save Changes</button>
                  </div>
                </form>

              </div>

              <!-- Change Password Tab -->
              <div class="tab-pane fade pt-3" id="profile-change-password">
                <form method="POST" action="{{ route('profile.password') }}">
                  @csrf

                  <div class="row mb-3">
                    <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Current Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" id="currentPassword" required>
                      @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPassword" required>
                      @error('new_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Re-enter New Password</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="new_password_confirmation" type="password" class="form-control" id="renewPassword" required>
                    </div>
                  </div>

                  <div class="text-center pt-2">
                    <button type="submit" class="btn btn-dark fw-semibold px-4">Change Password</button>
                  </div>
                </form>
              </div>

              <!-- Dashboard Logo Tab -->
              <div class="tab-pane fade pt-3" id="profile-logo">
                <form method="POST" action="{{ route('logo.update') }}" enctype="multipart/form-data">
                  @csrf

                  <div class="row mb-4">
                    <label class="col-md-4 col-lg-3 col-form-label">Current Logo</label>
                    <div class="col-md-8 col-lg-9">
                      <div class="bg-dark p-3 rounded d-inline-block border">
                        <img id="logo-preview" src="{{ asset('assets/img/logo/dashboard_logo.png') }}?v={{ file_exists(public_path('assets/img/logo/dashboard_logo.png')) ? filemtime(public_path('assets/img/logo/dashboard_logo.png')) : time() }}" alt="Current Logo" style="max-height: 80px; width: auto; object-fit: contain;">
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <label for="logoInput" class="col-md-4 col-lg-3 col-form-label">Choose New Logo</label>
                    <div class="col-md-8 col-lg-9">
                      <input name="logo" type="file" class="form-control @error('logo') is-invalid @enderror" id="logoInput" accept="image/*" onchange="previewLogo(event)" required>
                      <span class="text-muted small">PNG, JPG, JPEG, SVG or GIF. Max 2MB. Recommendation: Use horizontal shape with transparent background.</span>
                      @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="text-center pt-2">
                    <button type="submit" class="btn btn-dark fw-semibold px-4">Upload Logo</button>
                  </div>
                </form>
              </div>

            </div><!-- End Bordered Tabs -->

            <script>
            function previewLogo(event) {
                const reader = new FileReader();
                reader.onload = function(){
                    const output = document.getElementById('logo-preview');
                    output.src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            }
            </script>

          </div>
        </div>

      </div>
    </div>
  </section>

</main><!-- End #main -->

@stop