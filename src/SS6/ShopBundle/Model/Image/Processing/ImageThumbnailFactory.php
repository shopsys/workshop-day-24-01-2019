<?php

namespace SS6\ShopBundle\Model\Image\Processing;

use Intervention\Image\Constraint;
use SS6\ShopBundle\Model\Image\Processing\ImageProcessingService;

class ImageThumbnailFactory {

	// icon-font size 5x
	const THUMBNAIL_WIDTH = 60;
	const THUMBNAIL_HEIGHT = 70;

	/**
	 * @var \SS6\ShopBundle\Model\Image\Processing\ImageProcessingService
	 */
	private $imageProcessingService;

	/**
	 * @param \SS6\ShopBundle\Model\Image\Processing\ImageService $imageProcessingService
	 */
	public function __construct(ImageProcessingService $imageProcessingService) {
		$this->imageProcessingService = $imageProcessingService;
	}

	/**
	 * @param string $filepath
	 * @return \Intervention\Image\Image
	 */
	public function getImageThumbnail($filepath) {
		$image = $this->imageProcessingService->createInterventionImage($filepath);
		$image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, function (Constraint $constraint) {
			$constraint->aspectRatio();
		});

		return $image;
	}

}
