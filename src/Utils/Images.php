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
}
