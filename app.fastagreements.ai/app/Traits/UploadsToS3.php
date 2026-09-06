<?php
/**
 * PLACE THIS FILE AT: app/Traits/UploadsToS3.php
 *
 * Drop-in replacement for the old public_path()/move()/unlink() pattern used
 * across the controllers. Every method below stores to (or deletes from) the
 * 's3' disk configured in config/filesystems.php.
 *
 * Usage in a controller:
 *   use App\Traits\UploadsToS3;
 *   class CustomerController extends Controller {
 *       use UploadsToS3;
 *       ...
 *       $path = $this->uploadToS3($request->file('photo'), 'uploads/customers');
 *       // $path is what you save in the DB, e.g. "uploads/customers/6501...jpg"
 *       ...
 *       $this->deleteFromS3($customer->photo);
 *   }
 */

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait UploadsToS3
{
    /**
     * Upload a file to S3 under the given folder and return the stored path
     * (this is what gets saved in the DB column, same as the old filename-
     * only convention but now includes the folder so it can be resolved
     * back into a full S3 URL later).
     */
    protected function uploadToS3(UploadedFile $file, string $folder, ?string $filenameOverride = null): string
    {
        $filename = $filenameOverride
            ?? (time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension());

        // 'public' visibility so the returned URL is directly usable in <img src>
        // without needing signed URLs (matches how these assets were used before).
        Storage::disk('s3')->putFileAs($folder, $file, $filename, 'public');

        return trim($folder, '/') . '/' . $filename;
    }

    /**
     * Delete a previously uploaded file from S3. Safe to call with null/empty
     * or a path that doesn't exist — it just no-ops instead of throwing.
     */
    protected function deleteFromS3(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }
    }

    /**
     * Turn a stored path (e.g. "uploads/customers/xyz.jpg") into a public URL
     * for display. Use this in place of asset($model->photo) / public_path().
     */
    protected function s3Url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('s3')->url($path);
    }
}
