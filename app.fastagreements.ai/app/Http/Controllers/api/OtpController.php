<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Otp;
use App\Models\Customer;

class OtpController extends Controller
{
    /**
     * Send OTP to the user's mobile number.
     */
    public function sendOtp(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mobile' => 'required|digits:10',
            ]);

            // Check if the mobile number exists in the customers table
            $customer = Customer::where('mobile', $validatedData['mobile'])->first();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile number not registered.',
                    'register' => true
                ], 404); // Not Found status code
            }

            // Generate a random OTP
            $otp = rand(100000, 999999);

            // Store OTP in the database with an expiration time
            Otp::create([
                'mobile' => $validatedData['mobile'],
                'otp' => $otp,
                'expires_at' => now()->addSeconds(30), // OTP valid for 5 minutes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully.',
                'otp' => $otp
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Enter valid 10 digits number.'
                // 'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error sending OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending OTP.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }

       public function registerCustomer(Request $request)
    {
        // dd($request->all());
        try {
            // Validate the incoming request data with custom messages
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'mobile' => 'required|digits:10|unique:customers,mobile',
                'address' => 'required|string|max:500',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'commercial_address' => 'nullable|string|max:500',
                'gst_number' => 'nullable',
                'is_company' => 'nullable|boolean',
            ], [
                // Custom messages for validation rules
                'name.required' => 'Please enter the customer name.',
                'email.required' => 'Please provide a valid email address.',
                'email.email' => 'The email must be a valid email address.',
                'mobile.required' => 'The mobile number is required.',
                'mobile.digits' => 'The mobile number must be exactly 10 digits.',
                'mobile.unique' => 'This mobile number is already registered. Please use a different number.',
                'address.required' => 'Please provide the address.',
                'address.max' => 'The address may not be more than 500 characters.',
                'lat.required' => 'Latitude is required.',
                'lat.numeric' => 'Latitude must be a valid number.',
                'long.required' => 'Longitude is required.',
                'long.numeric' => 'Longitude must be a valid number.',
                'commercial_address.required' => 'Please provide the commercial address.',
                'commercial_address.max' => 'The commercial address may not be more than 500 characters.',
                'is_company.boolean' => 'The "is company" field must be true or false.',
                'gst_number.nullable' => 'The GST number is optional.',
            ]);

            // Additional validation for company name if the user is a company
            if ($request->input('is_company') == 1) {
                $request->validate([
                    'company_name' => 'required|string|max:255',
                ], [
                    'company_name.required' => 'Please provide the company name.',
                ]);
            }else{
                $request->validate([
                    'company_name' => 'nullable',
                ], [
                    'company_name.required' => 'Please provide the company name.',
                ]);
            }

            // Handle image upload and resizing
            $personImage = $request->file('person_image')
                ? $this->image_resize($request->file('person_image'), 'person_images')
                : null;
            // Create a new customer
            $customer = Customer::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'mobile' => $validatedData['mobile'],
                'address' => $validatedData['address'],
                'last_lat' => $validatedData['lat'],
                'last_lon' => $validatedData['long'],
                'commercial_address' => $validatedData['commercial_address'],
                'gst_number' => $validatedData['gst_number'] ?? null,
                'person_image' => $personImage, 
                'company_name' => $request->company_name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer registered successfully.',
                'customer' => $customer,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(), // Return detailed validation errors
            ], 422);
        } catch (\Exception $e) {
            dd($e);
            Log::error('Error registering customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering the customer.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }


    /**
     * Verify the OTP.
     */
    public function verifyOtp(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mobile' => 'required|string|max:15',
                'otp' => 'required|string|max:6',
            ]);

            // Find the OTP record
            $otpRecord = Otp::where('mobile', $validatedData['mobile'])
                ->where('otp', $validatedData['otp'])
                ->where('expires_at', '>=', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired OTP.',
                ], 400);
            }

            // OTP is valid; delete the record
            $otpRecord->delete();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error verifying OTP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying OTP.',
                'error' => 'An unexpected error occurred.'
            ], 500);
        }
    }
}
