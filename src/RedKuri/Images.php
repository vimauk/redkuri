<?php

namespace RedKuri;

//echo $t = Math::base_encode('90000000123154141231230000100000000000102500250');
//echo Math::base_decode($t);
/*
 * Need to look at Image handling as totally not working
 * Start by looking at Photos Class and work on a single class that will store and retrieve photos of various sizes
 * Don't forget that for the foods eaten montage we will need zoomed images too - possibly without watermarks?
 * Should be able to add a photo to a key, get a photo of a specified size from a key and set number, delete a photo
 * Consider id deleting does require removing all other photos from cache - Could we leave a gap, do we then know how many there are?
 * At the moment the food class (or brand) stores the number of photos. How could the photo class do this?
 * Use the base encoding and decoding to store as base 62 - Is this sotrage or just for URL presentation to the end user - Debugging
 *

    If the ID makes it unique then we only need the random to make things different.
    Too many folders is a concern, need fewer folders.

    Zone    x           A folder for this type of image
    Width	0000        (1 + Width) * 4
    Height	0000        (1 + Width) * 4
    Type	0           See Below
        Type 0 = Original image
        Type 1 = Watermarked image
        Type 2 = Zoomed image
    id      0000000000  ID from Database
    Set 	00          100 Images per set

*/

// Original - RK_BASEREALPATH originals / zone / floor(id / 250) / id set .jpg
// Public   - RK_BASEREALPATH img / zone / floor(id / 250) / Math::base_encode(id set Utility::paddedNumber($width, 4) Utility::paddedNumber($height, 4) Utility::paddedNumber($type, 2)) .jpg



class Images {
    protected $watermark;
    protected $zone;    // What sort of image e.g. Brands, Food Images, other
    protected $id;
    protected $set;     // Number within the set of images for id

    function __construct($zone, $id, $set=0, $watermark=false) {
        if ($watermark == true) {
            $this->watermark = RK_BASEREALPATH . 'img/watermark.png';
        } else {
            $this->watermark = null;
        }
        $this->zone = $zone;
        $this->set = $set;
        $this->id = rkUtility::paddedNumber($id, 12);
    }

    function originalPath($setnum = null) {
        if ($setnum === null) {
            $setnum = $this->set;
        }
        $setnum = rkUtility::paddedNumber($setnum,2);

        $path  = RK_PROTECTEDPATH;
        $path .= 'imgmaster/';
        $path .= $this->zone.'/';
        $path .= floor($this->id / 250).'/';
        $path .= $this->id.$setnum;
        $path .= '.jpg';

        return $path;
    }

    function cachePath($setnum, $type, $width, $height, $base=RK_BASEREALPATH) {
        $setnum = rkUtility::paddedNumber($setnum,2);

        $path  = $base;
        $path .= 'img/';
        $path .= $this->zone.'/';
        $path .= floor($this->id / 250).'/';

        $id = rkUtility::paddedNumber($this->id, 12).
              rkUtility::paddedNumber($setnum, 2).
              rkUtility::paddedNumber($width, 4).
              rkUtility::paddedNumber($height, 4).
              rkUtility::paddedNumber($type, 2);
        $id = strrev($id);

        $path .= rkUtility::encodeDecodeID($id, false, 9);

//          $id = Utility::paddedNumber($this->id, 12).substr(md5($id), 0, strlen($id));
//        define('OBSKEY', 'SomethingALittleBitRandom');
//        $path .= Math::url_base64encode(Utility::obfuscate($id, OBSKEY));

        $path .= '.jpg';

        return $path;
    }

    function split($path, $base = false, $splitsize = 3) {
        $split = array();
        $final = substr($path, -$splitsize);     // Get last three chars
        $path = substr($path, 0, -$splitsize);   // Get the reset of the string minus the final three
        while (strlen($path) > $splitsize) {
            $split[] = substr($path, -$splitsize);   // Get the last three again
            $path = Substr($path, 0, -$splitsize);   // Reduce the path again
        }
        $split[] = $path;   // Add the final part of the path which will be 3 or less characters.

        $split = array_reverse($split);     // Put Path back round the right way
        $splitpath = implode('/', $split);

        if ($base !== false) {
            $check = $base;
            if (substr($check, -1) == '/') $check = substr($check, 0, -1); // Remove trailing slash
            foreach ($split as $dir) {
                $check = $check.'/'.$dir;
                if (!file_exists($check)) {
                    mkdir($check);
                }
            }
        }

        return $splitpath.'/'.$final;
    }

    function delete($set, $numberImages) {
        $this->deletecache();

        if (file_exists($this->originalPath($set))) {
            @unlink($this->originalPath($set));
        }
        if ($set < ($numberImages-1)) {
            for ($i=$set; $i<$numberImages-1; $i++) {
                if (file_exists($this->originalPath($i+1))) {
                    rename($this->originalPath($i+1), $this->originalPath($i));
                }
            }
        }
        return $numberImages - 1;
    }

    function deletecache() {
        foreach (glob(RK_BASEREALPATH.'img/'.$this->zone.'/'.floor($this->id / 250).'/*.jpg') as $filename) {
            if(file_exists($filename)) @unlink($filename);
        }
    }

    function grab($uri, $filesystem=false) {
        if ($filesystem) {
            if (@file_exists($uri) === false) return false;
        }

        if (!is_dir(pathinfo($this->originalpath(), PATHINFO_DIRNAME))) {
            if (@mkdir(pathinfo($this->originalpath(), PATHINFO_DIRNAME), 0755, true) === false) return false;
        }

        // data:image/jpeg;base64,
        if (substr($uri, 0, 10) == 'data:image') {
            $comma = stripos($uri, ',');
            $data = substr($uri, $comma + 1);
            $data = base64_decode($data);
        } else {
            $data = @file_get_contents($uri);
        }

        $img = @imagecreatefromstring($data);

        if ($img != false) {
            if (file_exists($this->originalpath())) @unlink($this->originalpath());

            list($width, $height) = getimagesizefromstring($data);
            $output = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($output, 255, 255, 255);
            imagefilledrectangle($output, 0, 0, $width, $height, $white);
            imagecopy($output, $img, 0, 0, 0, 0, $width, $height);
            imagejpeg($output, $this->originalpath());
            $this->deletecache();
            return true;
        }
        return false;
    }

    function store($filepath, $delete = true) {
        if (file_exists($this->originalpath())) @unlink($this->originalpath());

        if (copy($filepath, $this->originalpath())) {
            if ($delete) {
                if(file_exists($filepath)) @unlink($filepath);
            }
        }
        $this->deletecache();
    }

    function file($setnum, $width = 128, $height = null, $type=0, $base='../') {
        if ($height == null) $height = $width;

        $cachepath = $this->cachepath($setnum, $type, $width, $height);
        if (!file_exists($cachepath)) {
            if (!is_dir(pathinfo($cachepath, PATHINFO_DIRNAME))) {
                @mkdir(pathinfo($cachepath, PATHINFO_DIRNAME), 0755, true);
            }
            if ((file_exists($this->originalpath($setnum)))
                && (filesize($this->originalpath($setnum)) > 0)) {

                $file_dimensions = getimagesize($this->originalpath($setnum));

                $file_type = strtolower($file_dimensions['mime']);
                if ($file_type=='image/jpeg' || $file_type=='image/pjpeg') {
                    $original = @imagecreatefromjpeg($this->originalpath($setnum));
                    if ($original) {
                        $oRatio = imagesx($original) / imagesy($original);
                        $tcimage = imagecreatetruecolor($width, $height);
                        $white = imagecolorallocate($tcimage, 255, 255, 255);
                        imagefill($tcimage, 0, 0, $white);
                        /*
                        So 800 x 400 = 2
                        400 x 800 = 0.5
                        < 1 = Portrait and > 1 = Landscape				 */
                        if ($oRatio > 1) { // Landscape
                            // Height = 1/Ratio * Width
                            $oHeight = 1/$oRatio * imagesx($tcimage);
                            $oWidth = imagesx($tcimage);
                            imagecopyresampled($tcimage, $original, 0, ($height/2)-$oHeight/2, 0, 0, $oWidth, $oHeight, imagesx($original), imagesy($original));
                        } else { // Portrait
                            // Weight = Ratio * Height
                            $oWidth = $oRatio * imagesy($tcimage);
                            $oHeight = imagesy($tcimage);
                            imagecopyresampled($tcimage, $original, ($width/2)-$oWidth/2, 0, 0, 0, $oWidth, $oHeight, imagesx($original), imagesy($original));
                        }

                        // Add the Watermark
                        if ($this->watermark != null) {
                            $stamp = imagecreatefrompng($this->watermark);
                            imagecopyresampled($tcimage, $stamp, 0, 0, 0, 0, $width, $height, imagesx($stamp), imagesy($stamp));
                        }

                        // Create the file in the cache path
                        imagejpeg($tcimage, $cachepath);
                        imagedestroy($tcimage);
                        $imgpath = $this->cachepath($setnum, $type, $width, $height, $base);
                    } else {
                        $imgpath = $base.'img/error.png';
                    }
                } else {
                    $imgpath = $base.'img/error.png';
                }
            } else {
                $imgpath = $base.'img/error.png';
            }
        } else {
            $imgpath = $this->cachepath($setnum, $type, $width, $height, $base);
        }
        return $imgpath;
    }
}
