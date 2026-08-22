<?php

namespace App\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as DriverGd;


trait ImageResizer
{
    public function image_resize($image, $folder)
    {
        $thumb = $folder . "_thumb";

        if ($image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('admin/images/' . $thumb . "/" . $imageName);
            $originalPath = public_path('admin/images/' . $folder . "/" . $imageName);

            $manager = new ImageManager(new DriverGd());
            $image = $manager->read($image);
            $image->save($originalPath);

            $requiredSize = 1500;
          //$vehicle = array("vehicle_front_side", "vehicle_back_side", "vehicle_left_side", "vehicle_right_side");
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
            $image->save($destinationPath, 80);


            return $imageName; // Return the image name
        }

        return null; // Return null if no image
    }
}
