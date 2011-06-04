	//Helper function for image fields
	private function get_image_object($fID, $width = 0, $height = 0, $crop = false) {
		if (empty($fID)) {
			$image = null;
		} else if (empty($width) && empty($height)) {
			//Show image at full size (do not generate a thumbnail)
			$file = File::getByID($fID);
			$size = @getimagesize($file->getPath());
			$image = new stdClass;
			$image->src = $file->getRelativePath();
			$image->width = $size[0];
			$image->height = $size[1];
		} else {
			//Generate a thumbnail
			$width = empty($width) ? 9999 : $width;
			$height = empty($height) ? 9999 : $height;
			$file = File::getByID($fID);
			$ih = Loader::helper('image_crop', 'designer_content');
			$image = $ih->getThumbnail($file, $width, $height, $crop);
		}
	
		return $image;
	}
	