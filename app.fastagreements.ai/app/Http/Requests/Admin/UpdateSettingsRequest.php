<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->input('group', 'general');

        switch ($group) {
            case 'general':
                return [
                    'site_name' => 'required|string|max:255',
                    'company_name' => 'required|string|max:255',
                    'support_email' => 'required|email|max:255',
                    'support_mobile' => 'nullable|string|max:20',
                    'website_url' => 'nullable|url|max:255',
                    'company_address' => 'nullable|string|max:500',
                    'timezone' => 'nullable|string|max:100',
                    'currency' => 'nullable|string|max:10',
                    'default_language' => 'nullable|string|max:10',
                ];

            case 'branding':
                return [
                    'website_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'admin_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'login_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'favicon' => 'nullable|image|mimes:png,ico|max:1024',
                    'email_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'default_profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'primary_color' => 'nullable|string|max:7',
                    'secondary_color' => 'nullable|string|max:7',
                    'footer_text' => 'nullable|string|max:500',
                ];

            case 'smtp':
                return [
                    'mail_driver' => 'required|string|max:50',
                    'mail_host' => 'required|string|max:255',
                    'mail_port' => 'required|numeric',
                    'mail_username' => 'nullable|string|max:255',
                    'mail_password' => 'nullable|string|max:255',
                    'mail_encryption' => 'nullable|string|max:50',
                    'mail_from_name' => 'required|string|max:255',
                    'mail_from_email' => 'required|email|max:255',
                ];

            case 'firebase':
                return [
                    'firebase_project_id' => 'required|string|max:255',
                    'firebase_api_key' => 'nullable|string|max:255',
                    'firebase_sender_id' => 'nullable|string|max:255',
                    'firebase_app_id' => 'nullable|string|max:255',
                    'firebase_vapid_key' => 'nullable|string|max:255',
                    'firebase_service_account' => 'nullable|file|mimes:json|max:5120',
                    'enable_push_notification' => 'nullable|boolean',
                ];

            case 'storage':
                return [
                    'storage_type' => 'required|string|in:local,s3',
                    's3_access_key' => 'required_if:storage_type,s3|nullable|string|max:255',
                    's3_secret_key' => 'required_if:storage_type,s3|nullable|string|max:255',
                    's3_bucket' => 'required_if:storage_type,s3|nullable|string|max:255',
                    's3_region' => 'required_if:storage_type,s3|nullable|string|max:255',
                    's3_base_url' => 'nullable|url|max:255',
                ];

            case 'payment':
                return [
                    'enable_razorpay' => 'nullable|boolean',
                    'razorpay_key' => 'nullable|string|max:255',
                    'razorpay_secret' => 'nullable|string|max:255',
                    'enable_stripe' => 'nullable|boolean',
                    'stripe_key' => 'nullable|string|max:255',
                    'stripe_secret' => 'nullable|string|max:255',
                    'enable_paypal' => 'nullable|boolean',
                    'paypal_client_id' => 'nullable|string|max:255',
                    'paypal_secret' => 'nullable|string|max:255',
                    'paypal_mode' => 'nullable|string|in:sandbox,live',
                    'enable_cash' => 'nullable|boolean',
                ];

            case 'sms_whatsapp':
                return [
                    'twilio_sid' => 'nullable|string|max:255',
                    'twilio_token' => 'nullable|string|max:255',
                    'twilio_number' => 'nullable|string|max:255',
                    'whatsapp_api_url' => 'nullable|url|max:255',
                    'otp_length' => 'nullable|integer|min:4|max:10',
                    'otp_expiry' => 'nullable|integer|min:1',
                ];

            case 'social_links':
                return [
                    'social_facebook' => 'nullable|url|max:255',
                    'social_instagram' => 'nullable|url|max:255',
                    'social_twitter' => 'nullable|url|max:255',
                    'social_linkedin' => 'nullable|url|max:255',
                    'social_youtube' => 'nullable|url|max:255',
                    'social_telegram' => 'nullable|url|max:255',
                ];

            case 'agreement':
                return [
                    'agreement_prefix' => 'nullable|string|max:50',
                    'agreement_number_length' => 'nullable|integer|min:4|max:20',
                    'agreement_default_expiry_days' => 'nullable|integer|min:1',
                    'agreement_enable_auto_approval' => 'nullable|boolean',
                    'agreement_allow_download' => 'nullable|boolean',
                    'agreement_allow_sharing' => 'nullable|boolean',
                    'agreement_default_watermark' => 'nullable|file|mimes:png,jpg,jpeg,pdf|max:4096',
                ];

            case 'customer':
                return [
                    'customer_allow_registration' => 'nullable|boolean',
                    'customer_require_email_verification' => 'nullable|boolean',
                    'customer_require_mobile_verification' => 'nullable|boolean',
                    'customer_default_status' => 'nullable|string|max:50',
                    'customer_default_language' => 'nullable|string|max:10',
                    'customer_default_subscription_plan' => 'nullable|string|max:100',
                ];

            case 'advocate':
                return [
                    'advocate_allow_registration' => 'nullable|boolean',
                    'advocate_require_verification' => 'nullable|boolean',
                    'advocate_auto_approval' => 'nullable|boolean',
                    'advocate_commission_percentage' => 'nullable|numeric|min:0|max:100',
                ];

            case 'notifications':
                return [
                    'notify_email' => 'nullable|boolean',
                    'notify_sms' => 'nullable|boolean',
                    'notify_push' => 'nullable|boolean',
                    'notify_admin' => 'nullable|boolean',
                    'notify_customer' => 'nullable|boolean',
                    'notify_advocate' => 'nullable|boolean',
                ];

            case 'security':
                return [
                    'recaptcha_site_key' => 'nullable|string|max:255',
                    'recaptcha_secret_key' => 'nullable|string|max:255',
                    'login_attempt_limit' => 'nullable|integer|min:1',
                    'session_timeout' => 'nullable|integer|min:1',
                    'enable_2fa' => 'nullable|boolean',
                    'force_strong_password' => 'nullable|boolean',
                ];

            case 'api':
                return [
                    'api_base_url' => 'nullable|url|max:255',
                    'api_token' => 'nullable|string|max:500',
                    'enable_api_logs' => 'nullable|boolean',
                ];

            case 'seo':
                return [
                    'meta_title' => 'nullable|string|max:255',
                    'meta_description' => 'nullable|string',
                    'meta_keywords' => 'nullable|string',
                    'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
                ];

            case 'legal':
                return [
                    'privacy_policy' => 'nullable|string',
                    'terms_conditions' => 'nullable|string',
                    'refund_policy' => 'nullable|string',
                    'about_us' => 'nullable|string',
                ];

            case 'company':
                return [
                    'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                    'company_name' => 'required|string|max:255',
                    'company_gstin' => 'nullable|string|max:50',
                    'company_address_line_1' => 'nullable|string|max:255',
                    'company_address_line_2' => 'nullable|string|max:255',
                    'company_city' => 'nullable|string|max:100',
                    'company_state' => 'nullable|string|max:100',
                    'company_pin_code' => 'nullable|string|max:20',
                    'company_country' => 'nullable|string|max:100',
                    'company_phone_number' => 'nullable|string|max:20',
                    'company_email_address' => 'nullable|email|max:255',
                    'company_website' => 'nullable|url|max:255',
                    'company_bank_name' => 'nullable|string|max:100',
                    'company_account_holder_name' => 'nullable|string|max:255',
                    'company_account_number' => 'nullable|string|max:50',
                    'company_ifsc_code' => 'nullable|string|max:20',
                    'company_upi_id' => 'nullable|string|max:100',
                    'company_invoice_footer' => 'nullable|string|max:1000',
                    'company_terms_conditions' => 'nullable|string',
                ];

            case 'maintenance':
                return [
                    'enable_maintenance_mode' => 'nullable|boolean',
                    'maintenance_message' => 'nullable|string|max:1000',
                ];

            default:
                return [];
        }
    }
}
