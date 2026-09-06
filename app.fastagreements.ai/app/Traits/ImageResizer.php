<?php

namespace App\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as DriverGd;
use Illuminate\Support\Facades\Storage;

trait ImageResizer
{
    public function image_resize($image, $folder)
    {
        $thumb = $folder . "_thumb";

        if ($image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $thumbKey = 'admin/images/' . $thumb . '/' . $imageName;
            $originalKey = 'admin/images/' . $folder . '/' . $imageName;

            $manager = new ImageManager(new DriverGd());
            $image = $manager->read($image);

            // Save the full-size (unresized) version to S3.
            Storage::disk('s3')->put($originalKey, (string) $image->encode(), 'public');

            $requiredSize = 1500;
            if ($folder === 'vehicle_images') {
                $requiredSize = 900;
            }
            $width = $image->width();
            $height = $image->height();
            $aspectRatio = $width / $height;

            if ($aspectRatio >= 1.0) {
                $newWidth = $requiredSize;
                $newHeight = $requiredSize / $aspectRatio;
            } else {
                $newWidth = $requiredSize * $aspectRatio;
                $newHeight = $requiredSize;
            }

            $image = $image->resize($newWidth, $newHeight);

            // Save the resized thumb version to S3 (quality 80, matches old behaviour).
            Storage::disk('s3')->put($thumbKey, (string) $image->encode(quality: 80), 'public');

            return $imageName; // Return the image name (unchanged — DB still stores just the filename)
        }

        return null; // Return null if no image
    }
}
