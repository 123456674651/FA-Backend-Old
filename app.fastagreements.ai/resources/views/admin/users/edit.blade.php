@extends('admin.layout.admin')

@section('content')
<main id="main" class="main">
    <div class="pagetitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-6 pt-2">
                <h1 class="fw-bold text-dark mb-1" style="font-size: 28px;">Edit User</h1>
            </div>
            <div class="col-md-6 text-md-end pt-2">
                <a href="{{ route('users.index') }}" class="btn button-color text-white">
                    <i class="bi bi-arrow-left-square text-white"></i> Back
                </a>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card border-0 shadow-sm" style="border-radius:10px;">
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" name="mobile" placeholder="e.g. 9876543210" class="form-control form-control-lg @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile ?? '') }}" required>
                            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-lg">
                                <option value="1" {{ old('status', $user->status) ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !old('status', $user->status) ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="text-muted">Leave blank to keep existing password.</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection
