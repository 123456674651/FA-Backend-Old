<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Traits\UploadsToS3;

class ProfileController extends Controller
{
    use UploadsToS3;

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

            // Delete old profile picture if exists (strip any leading slash
            // from the old-style path before treating it as an S3 key)
            $this->deleteFromS3(ltrim($user->profile_picture ?? '', '/'));

            $path = $this->uploadToS3($image, 'uploads/profile', $fileName);

            $user->update([
                'profile_picture' => $path,
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

        $this->deleteFromS3(ltrim($user->profile_picture ?? '', '/'));

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

            // Fixed filename, always overwrites the previous logo on S3
            // (matches the old behaviour of overwriting dashboard_logo.png).
            $this->uploadToS3($image, 'assets/img/logo', 'dashboard_logo.png');
        }

        return redirect()->back()->with('success', 'Dashboard logo updated successfully.');
    }
}
