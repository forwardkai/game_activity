<?php
namespace HxeVendor;
use Intervention\Image\Facades\Image;
class ImageAsset{
    public static function waterMark($originPic, $targetPic){
        $img = Image::make($originPic);
        $width = $img->getWidth();
        $height = $img->getHeight();
        $currentRatio = $width/$height;
        if($currentRatio>1.6){
            $img->resize(1200, null, function($constraint){
                $constraint->aspectRatio();
            });
        }else{
            $img->resize(null, 750, function($constraint){
                $constraint->aspectRatio();
            });
        }

        $waterMark = __DIR__.'/logo_m.png';
        $over_width = 0;
        while($over_width<1200){
            $over_height = 0;
            while($over_height<750){
                $img->insert($waterMark, 'top-left', $over_width, $over_height);
                $over_height += 150;
            }
            $over_width += 150;
        }

        $img->insert(__DIR__.'/logo_l.png', 'bottom-right', 0, 0);
        $img->save($targetPic);
    }
}