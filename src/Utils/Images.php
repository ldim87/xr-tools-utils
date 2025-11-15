<?php
/**
 * @author  Dmitriy Lukin <lukin.d87@gmail.com>
 */


namespace XrTools\Utils;

// Dependencies
use \Imagick;

/**
 * Image utilities
 */
class Images
{

	function createThumbnail(string $path, string $path_new, array $sys=[]): array {
		
		$result = [
			'status' => false,
			'error' => ''
		];

		$sys['format'] = $sys['format'] ?? 'jpg'; // export format
		$sys['width'] = $sys['width'] ?? 250; // in px
		$sys['height'] = $sys['height'] ?? $sys['width']; // use width not set
		$sys['quality'] = $sys['quality'] ?? 90; // 1..100 (currently jpeg only)
		$sys['bgcolor'] = $sys['bgcolor'] ?? 'white'; // background color

		try {
			
			$image = new Imagick();

			if ($image->readImage($path))
			{

				$image->setimagebackgroundcolor($sys['bgcolor']);

				$image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

				$image->setImageFormat($sys['format']);
				
				// Crop thumbnail
				$image->cropThumbnailImage($sys['width'], $sys['height']);

				// Jpeg options
				if ($sys['format'] == 'jpg')
				{
					// Set to use jpeg compression
					$image->setImageCompression(Imagick::COMPRESSION_JPEG);
					
					// Set compression level (1 lowest quality, 100 highest quality)
					$image->setImageCompressionQuality($sys['quality']);
				}

				// Strip out unneeded meta data
				$image->stripImage();

				// Writes resultant image to output directory
				if ($image->writeImage($path_new))
				{
					// Destroys Imagick object, freeing allocated resources in the process
					$image->destroy();

					$result['status'] = true;
				}
				else {
					$result['error'] = 'ERR_SAVE_FAILED';
				}
			}
			else {
				$result['error'] = 'ERR_READ_FAILED';
			}
		}
		catch (\Exception $e){
			$result['error'] = $e->getMessage();
		}

		return $result;
	}

	function getImageType(string $path): string|bool {

		try {
			$image = new Imagick();

			if ($image->readImage($path)){

				$format = strtolower($image->getImageFormat());
				$image->destroy();

				return $format;
			}
		}
		catch (\Exception $e){
			return false;
		}

		return false;
	}

	function convertImageFormat(string $path, string $path_new, array $sys = []): bool {

		try {
			$image = new Imagick();

			if ($image->readImage($path)){

				// Set output format from path_new extension
                $ext = strtolower(pathinfo($path_new, PATHINFO_EXTENSION) ?: '');
                if ($ext !== '') {
                    // Imagick commonly expects 'jpeg' not 'jpg'
                    if ($ext === 'jpg') {
                        $ext = 'jpeg';
                    }
                    $image->setImageFormat($ext);
                }

				// Set compression level (1 lowest quality, 100 highest quality)
				$image->setImageCompressionQuality($sys['quality'] ?? 90);

				// Writes resultant image to output directory
				if ($image->writeImage($path_new))
				{
					// Destroys Imagick object, freeing allocated resources in the process
					$image->destroy();

					return true;
				}
			}
		}
		catch (\Exception $e){
			return false;
		}

		return false;
	}

	function resizeImage(string $path, string $path_new, int $width, int $height, array $sys = []): bool {

        $keep_aspect_ratio = $sys['keep_aspect_ratio'] ?? false;
        $only_downsize = $sys['only_downsize'] ?? false;

        try {
            $image = new Imagick();

            if ($image->readImage($path)){

                // Original dimensions
                $orig_w = $image->getImageWidth();
                $orig_h = $image->getImageHeight();

                // Compute target dimensions (may be adjusted by keep_aspect_ratio / only_downsize)
                $target_w = (int) max(1, $width);
                $target_h = (int) max(1, $height);

                if ($orig_w > 0 && $orig_h > 0 && $keep_aspect_ratio) {
                    $ratio = min($width / $orig_w, $height / $orig_h);

                    // If only_downsize is set, never upscale
                    if ($only_downsize) {
                        $ratio = min($ratio, 1.0);
                    }

                    $target_w = (int) max(1, floor($orig_w * $ratio));
                    $target_h = (int) max(1, floor($orig_h * $ratio));
                }
				elseif ($orig_w > 0 && $orig_h > 0 && $only_downsize) {
                    // Not preserving aspect ratio but avoid upscaling each axis
                    $target_w = (int) max(1, min($width, $orig_w));
                    $target_h = (int) max(1, min($height, $orig_h));
                }

                // Set output format from path_new extension
                $ext = strtolower(pathinfo($path_new, PATHINFO_EXTENSION) ?: '');
                if ($ext !== '') {
                    // Imagick commonly expects 'jpeg' not 'jpg'
                    if ($ext === 'jpg') {
                        $ext = 'jpeg';
                    }
                    $image->setImageFormat($ext);
                }

                // Decide whether resize is needed
                $doResize = !($orig_w > 0 && $orig_h > 0 && $target_w === $orig_w && $target_h === $orig_h);

                if ($doResize) {
                    // Ensure edges are sampled from edge pixels, avoids dark/transparent fringes
                    $image->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_EDGE);

                    $image->resizeImage($target_w, $target_h, Imagick::FILTER_LANCZOS, 1, false);
                }

                // Set compression level (1 lowest quality, 100 highest quality)
                $image->setImageCompressionQuality($sys['quality'] ?? 90);

                // Writes resultant image to output directory
                if ($image->writeImage($path_new))
                {
                    // Destroys Imagick object, freeing allocated resources in the process
                    $image->destroy();

                    return true;
                }
            }
        }
        catch (\Exception $e){
            return false;
        }

        return false;
    }

}
