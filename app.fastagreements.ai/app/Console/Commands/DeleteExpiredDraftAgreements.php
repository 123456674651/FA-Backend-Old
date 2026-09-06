<?php

namespace App\Console\Commands;

use App\Models\Aggriment;
use Illuminate\Console\Command;

/**
 * Deletes draft agreements that were never published within 24 hours.
 *
 * Only rows with is_draft = 1 are touched — a published agreement is never
 * removed, however old. Uploaded images (party photos, Aadhaar, vehicle
 * photos) are deleted from disk first, then the row itself; agreement_attribute
 * rows are removed automatically via the DB's cascadeOnDelete on agreement_id.
 *
 * Register in routes/console.php:
 *   Schedule::command('agreements:expire-drafts')->hourly();
 */
class DeleteExpiredDraftAgreements extends Command
{
    protected $signature = 'agreements:expire-drafts';

    protected $description = 'Delete draft agreements older than 24 hours.';

    // How long a draft is allowed to live before it's auto-removed.
    private const DRAFT_LIFETIME_HOURS = 24;

    // Maps each image field on the agreements table to the folder
    // ImageResizer::image_resize() saved it under (see app/Traits/ImageResizer.php).
    private const IMAGE_FOLDERS = [
        'party_1_image'       => 'person_images',
        'party_2_image'       => 'person_images',
        'party_1_adhar_front' => 'adhar_images',
        'party_1_adhar_back'  => 'adhar_images',
        'party_2_adhar_front' => 'adhar_images',
        'party_2_adhar_back'  => 'adhar_images',
        'vehicle_front_side'  => 'vehicle_images',
        'vehicle_back_side'   => 'vehicle_images',
        'vehicle_left_side'   => 'vehicle_images',
        'vehicle_right_side'  => 'vehicle_images',
    ];

    public function handle(): int
    {
        $expiredDrafts = Aggriment::where('is_draft', 1)
            ->where('created_at', '<', now()->subHours(self::DRAFT_LIFETIME_HOURS))
            ->get();

        $count = $expiredDrafts->count();

        foreach ($expiredDrafts as $draft) {
            $this->deleteDraftImages($draft);
            $draft->delete();
        }

        $this->info("Deleted {$count} expired draft agreement(s).");

        return self::SUCCESS;
    }

    private function deleteDraftImages(Aggriment $draft): void
    {
        foreach (self::IMAGE_FOLDERS as $field => $folder) {
            $filename = $draft->{$field};

            if (!$filename) {
                continue;
            }

            $original = public_path("admin/images/{$folder}/{$filename}");
            $thumb    = public_path("admin/images/{$folder}_thumb/{$filename}");

            if (file_exists($original)) {
                @unlink($original);
            }

            if (file_exists($thumb)) {
                @unlink($thumb);
            }
        }
    }
}