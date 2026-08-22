<?php

/*
|--------------------------------------------------------------------------
| Mobile API authentication
|--------------------------------------------------------------------------
|
| Settings for the customer-facing JWT session, Firebase phone verification
| and the minimum supported app build. The Blade admin panel is unaffected —
| it keeps using the `web` session guard.
|
*/

return [

    /*
    | Our own session tokens. Symmetric (HS256): the same server signs and
    | verifies, so there is no reason to carry a keypair. Mirrors the Node
    | service's src/libs/jwt.ts.
    */
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'algo' => 'HS256',
        'issuer' => env('JWT_ISSUER', 'fastagreements'),
        // 30 days. Customers sign in by SMS, so a short expiry means re-sending
        // an SMS — the cost of a rotation is real money, not just friction.
        'ttl' => (int) env('JWT_TTL_SECONDS', 60 * 60 * 24 * 30),
    ],

    /*
    | Firebase Phone Auth. Verifying an ID token needs only the project id —
    | it is checked against Google's *public* x509 certificates, so unlike FCM
    | this needs no service account. Left null here so it can fall back to the
    | `firebase_project_id` row in the settings table, which is where the
    | existing push-notification code already reads it from.
    */
    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'certs_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com',
        'certs_cache_key' => 'firebase_securetoken_certs',
        // Used only when Google's Cache-Control header is missing or unparseable;
        // normally their own max-age decides.
        'certs_fallback_ttl' => 3600,
        // Tolerates small clock drift between this server and Google.
        'leeway' => 60,
    ],

    /*
    | Builds older than this are refused with 426 and a message telling the
    | user to update, rather than failing with confusing 401s on every call.
    | Null disables the check entirely.
    */
    'min_app_version' => env('MIN_APP_VERSION'),

];
