<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the admin profile.
     */
    public function index()
    {
        return view('admin.profile.index', [
            'user' => Auth::user()
        ]);
    }

    /**
     * Update profile details (Name & Email).
     */
    public function updateDetails(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->back()->with('success', 'Profile details updated successfully.');
    }

    /**
     * Change admin password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors([
                'current_password' => 'The provided current password does not match our records.'
            ])->withInput();
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    /**
     * Upload or update profile picture.
     */
    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = Auth::user();
      

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/profile');

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the file
            $image->move($destinationPath, $fileName);

            // Delete old profile picture if exists
            if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                @unlink(public_path($user->profile_picture));
            }

            $user->update([
                'profile_picture' => '/uploads/profile/' . $fileName,
            ]);
        }

        return redirect()->back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Remove profile picture.
     */
    public function destroyImage()
    {
        $user = Auth::user();

        if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
            @unlink(public_path($user->profile_picture));
        }

        $user->update([
            'profile_picture' => null,
        ]);

        return redirect()->back()->with('success', 'Profile picture removed successfully.');
    }
  
  public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            
            // Define path
            $destinationPath = public_path('assets/img/logo');
            $fileName = 'dashboard_logo.png';

            // Ensure directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the file (overwriting dashboard_logo.png)
            $image->move($destinationPath, $fileName);
        }

        return redirect()->back()->with('success', 'Dashboard logo updated successfully.');
    }
}
