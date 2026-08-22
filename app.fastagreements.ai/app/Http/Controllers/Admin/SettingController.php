<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Models\SettingsActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $activeTab = $request->input('tab', 'general');
        $activityLogs = SettingsActivityLog::with('user')->orderBy('created_at', 'desc')->take(20)->get();

        return view('admin.settings.index', compact('activeTab', 'activityLogs'));
    }

    /**
     * Update settings.
     */
    public function update(UpdateSettingsRequest $request)
    {
        //Gate::authorize('update', Setting::class);

        $group = $request->input('group', 'general');
        $validated = $request->validated();

        // Retrieve keys based on group from database to verify what needs to be saved
        $existingKeys = Setting::where('group', $group)->pluck('type', 'key')->toArray();

        DB::beginTransaction();
        try {
            $changes = [];

            // Identify boolean (switch) fields that are not sent in request and set them to '0'
            foreach ($existingKeys as $key => $type) {
                if ($type === 'boolean' && !$request->has($key)) {
                    $oldValue = Setting::get($key);
                    if ($oldValue !== '0') {
                        Setting::set($key, '0', $group, 'boolean');
                        $changes[$key] = ['old' => $oldValue, 'new' => '0'];
                    }
                }
            }

            // Save input fields
            foreach ($validated as $key => $value) {
                if ($request->hasFile($key)) {
                    // Handle file upload
                    $file = $request->file($key);
                    
                    if ($key === 'firebase_service_account') {
                        $jsonContent = json_decode(file_get_contents($file->getRealPath()), true);
                        if (!$jsonContent || !isset($jsonContent['private_key']) || !isset($jsonContent['client_email'])) {
                            if ($request->expectsJson()) {
                                return response()->json([
                                    'message' => 'The uploaded file is not a valid Firebase Service Account JSON credentials file.',
                                    'errors' => [
                                        'firebase_service_account' => ['The uploaded file is not a valid Firebase Service Account JSON credentials file.']
                                    ]
                                ], 422);
                            }
                            return redirect()->back()->withErrors([
                                'firebase_service_account' => 'The uploaded file is not a valid Firebase Service Account JSON credentials file.'
                            ])->withInput();
                        }

                        $destinationPath = storage_path('app/firebase');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        
                        // Delete old secure file if exists
                        $oldPath = Setting::get($key);
                        if ($oldPath && file_exists(storage_path('app/firebase/' . $oldPath)) && is_file(storage_path('app/firebase/' . $oldPath))) {
                            unlink(storage_path('app/firebase/' . $oldPath));
                        }
                        
                        $fileName = $file->getClientOriginalName();
                        $file->move($destinationPath, $fileName);
                        $path = $fileName;
                    } else {
                        $path = $file->store('settings', 'public');
                        
                        // Delete old file if exists
                        $oldPath = Setting::get($key);
                        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }

                    Setting::set($key, $path, $group, 'file');
                    $changes[$key] = ['old' => $oldPath, 'new' => $path];
                } else {
                    // Detect setting type from DB or default to text
                    $type = $existingKeys[$key] ?? 'text';
                    $oldValue = Setting::get($key);

                    // Skip password updates if they are empty
                    if ($type === 'password' && empty($value)) {
                        continue;
                    }

                    if ($oldValue !== $value) {
                        Setting::set($key, $value, $group, $type);
                        $changes[$key] = ['old' => $oldValue, 'new' => $value];
                    }
                }
            }

            DB::commit();

            // Clear cache
            Setting::clearCache();

            // Log activity
            if (!empty($changes)) {
                SettingsActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'settings_updated',
                    'description' => "Updated settings group: " . ucfirst($group),
                    'properties' => $changes,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Test Email.
     */
    public function testSmtp(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
            'mail_driver' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_name' => 'required|string',
            'mail_from_email' => 'required|email',
        ]);

        try {
            // Apply temporary configs for Mailer
            config([
                'mail.mailers.smtp_test.transport' => $request->mail_driver,
                'mail.mailers.smtp_test.host' => $request->mail_host,
                'mail.mailers.smtp_test.port' => $request->mail_port,
                'mail.mailers.smtp_test.username' => $request->mail_username,
                'mail.mailers.smtp_test.password' => $request->mail_password,
                'mail.mailers.smtp_test.encryption' => $request->mail_encryption,
                'mail.from.address' => $request->mail_from_email,
                'mail.from.name' => $request->mail_from_name,
            ]);

            Mail::mailer('smtp_test')->raw('This is a test email from Fast Agreements Settings module.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('SMTP Test Notification');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('SMTP Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Mail configuration test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send Test Firebase Push Notification.
     */
    public function testFirebase(Request $request)
    {
        
        $request->validate([
            'test_token' => 'required|string',
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $serviceAccount = Setting::get('firebase_service_account');
        $projectId = Setting::get('firebase_project_id');
        dd($serviceAccount, $projectId);

        if (!$serviceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a Firebase Service Account JSON file first.'
            ], 400);
        }

        if (!$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure the Firebase Project ID first.'
            ], 400);
        }

        try {
            $result = $this->sendFcmV1Notification(
                $serviceAccount,
                $projectId,
                $request->test_token,
                $request->title,
                $request->body
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification request sent successfully via FCM HTTP v1! Result: ' . $result
            ]);
        } catch (\Exception $e) {
            dd($e);
            Log::error('Firebase Notification Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'FCM notification test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export all settings as JSON.
     */
    public function export()
    {
        Gate::authorize('update', Setting::class);

        $settings = Setting::all(['group', 'key', 'value', 'type'])->toArray();
        $json = json_encode($settings, JSON_PRETTY_PRINT);
        
        $filename = 'settings_backup_' . date('Y-m-d_H-i-s') . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import settings from JSON.
     */
    public function import(Request $request)
    {
        Gate::authorize('update', Setting::class);

        $request->validate([
            'import_file' => 'required|file|mimes:json|max:5120',
        ]);

        try {
            $file = $request->file('import_file');
            $data = json_decode(file_get_contents($file->getRealPath()), true);

            if (!is_array($data)) {
                throw new \Exception('Invalid settings file format.');
            }

            DB::beginTransaction();
            $importedCount = 0;
            foreach ($data as $item) {
                if (isset($item['key'], $item['group'])) {
                    Setting::set(
                        $item['key'], 
                        $item['value'] ?? null, 
                        $item['group'], 
                        $item['type'] ?? 'text'
                    );
                    $importedCount++;
                }
            }
            DB::commit();

            // Clear cache
            Setting::clearCache();

            // Log activity
            SettingsActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'settings_imported',
                'description' => "Imported " . $importedCount . " settings keys from backup JSON.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->back()->with('success', 'Imported ' . $importedCount . ' settings successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Settings Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore default settings.
     */
    public function restore(Request $request)
    {
        Gate::authorize('update', Setting::class);

        try {
            Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
            
            // Clear Cache
            Setting::clearCache();

            // Log activity
            SettingsActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'settings_restored_defaults',
                'description' => "Restored settings to system defaults.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Restored settings to default values successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Settings Restore Defaults Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore defaults: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Maintenance utilities: backups and cache-clearing.
     */
    public function maintenanceAction(Request $request)
    {
        Gate::authorize('update', Setting::class);

        $action = $request->input('action');

        try {
            switch ($action) {
                case 'clear_cache':
                    Cache::flush();
                    return response()->json(['success' => true, 'message' => 'Application cache cleared successfully!']);
                
                case 'clear_config':
                    Artisan::call('config:clear');
                    return response()->json(['success' => true, 'message' => 'Configuration cache cleared successfully!']);

                case 'clear_route':
                    Artisan::call('route:clear');
                    return response()->json(['success' => true, 'message' => 'Route cache cleared successfully!']);

                case 'clear_view':
                    Artisan::call('view:clear');
                    return response()->json(['success' => true, 'message' => 'Compiled view templates cleared successfully!']);

                case 'optimize':
                    Artisan::call('optimize');
                    return response()->json(['success' => true, 'message' => 'Application optimized successfully!']);

                case 'backup_db':
                    return $this->backupDatabase();

                default:
                    return response()->json(['success' => false, 'message' => 'Invalid maintenance action specified.'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Maintenance Action Failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Action failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate standard database SQL backup.
     */
    private function backupDatabase()
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if ($connection === 'sqlite') {
            $databasePath = $dbConfig['database'];
            if (file_exists($databasePath)) {
                return response()->download($databasePath, 'backup_' . date('Y-m-d_H-i-s') . '.sqlite');
            }
            throw new \Exception('SQLite database file does not exist.');
        }

        // Standard MySQL SQL generation in raw PHP
        if ($connection === 'mysql') {
            $tables = DB::select('SHOW TABLES');
            $key = "Tables_in_" . $dbConfig['database'];
            
            $sqlDump = "-- Database Backup\n";
            $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $tableObj) {
                $tableName = $tableObj->$key;
                
                // Show Create Table
                $createTableRes = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $keyCreate = "Create Table";
                $sqlDump .= $createTableRes[0]->$keyCreate . ";\n\n";

                // Rows
                $rows = DB::table($tableName)->get()->toArray();
                foreach ($rows as $row) {
                    $rowArr = (array) $row;
                    $fields = implode('`, `', array_keys($rowArr));
                    
                    $escapedValues = array_map(function ($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes($val) . "'";
                    }, array_values($rowArr));
                    
                    $values = implode(', ', $escapedValues);
                    $sqlDump .= "INSERT INTO `{$tableName}` (`{$fields}`) VALUES ({$values});\n";
                }
                $sqlDump .= "\n";
            }
            
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

            return response($sqlDump, 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        throw new \Exception('Database driver not supported for backup.');
    }

    /**
     * Send FCM HTTP v1 Notification using pure PHP Google OAuth2 RSA signed JWT token.
     */
    private function sendFcmV1Notification($serviceAccountName, $projectId, $token, $title, $body)
    {
        $serviceAccountPath = storage_path('app/firebase/' . $serviceAccountName);
        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("Service account file not found at " . $serviceAccountPath);
        }

        $json = json_decode(file_get_contents($serviceAccountPath), true);
        if (!$json || !isset($json['private_key']) || !isset($json['client_email'])) {
            throw new \Exception("Invalid service account JSON structure.");
        }

        $privateKey = $json['private_key'];
        $clientEmail = $json['client_email'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        $success = openssl_sign(
            $base64UrlHeader . "." . $base64UrlPayload,
            $signature,
            $privateKey,
            'SHA256'
        );

        if (!$success) {
            throw new \Exception("Failed to sign JWT with private key.");
        }

        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        // Exchange JWT for Access Token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            throw new \Exception("OAuth2 token exchange failed: " . $response);
        }

        $responseData = json_decode($response, true);
        $accessToken = $responseData['access_token'] ?? null;
        if (!$accessToken) {
            throw new \Exception("Access token not returned from token endpoint.");
        }

        // Send FCM Notification
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        
        $fields = [
            'message' => [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'TOP_STORY_ACTIVITY'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1
                        ]
                    ]
                ]
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $result = curl_exec($ch);
        $fcmHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($fcmHttpCode !== 200) {
            throw new \Exception("FCM HTTP v1 returned status code: " . $fcmHttpCode . ". Response: " . $result);
        }

        return $result;
    }
}
