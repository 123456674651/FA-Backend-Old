<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard.index');
        }

        return view('admin.auth.login');
    }

    /**
     * Handle authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            /** @var Admin $admin */
            $admin = Auth::guard('admin')->user();

            if ($admin->status == 0) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'email' => __('Your account has been deactivated. Please contact the administrator.'),
                ]);
            }

            $request->session()->regenerate();

            $redirectUrl = redirect()->intended(route('dashboard.index'))->getTargetUrl();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Welcome back! You have logged in successfully.',
                    'redirect' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl)
                ->with('success', 'Welcome back! You have logged in successfully.');
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Handle logging out.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.');
    }
}
