<?php 
defined('C5_EXECUTE') or die("Access Denied.");

Loader::block('library_file');

class [[[GENERATOR_REPLACE_CLASSNAME]]] extends BlockController {
	
	var $pobj;

	protected $btName = '[[[GENERATOR_REPLACE_NAME]]]';
	protected $btDescription = '[[[GENERATOR_REPLACE_DESCRIPTION]]]';
	protected $btTable = '[[[GENERATOR_REPLACE_TABLENAME]]]';
	protected $btInterfaceWidth = "700";
	protected $btInterfaceHeight = "450";
	
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btCacheBlockOutputLifetime = 300;
		
	public function getSearchableContent() {
		$content = '';
[[[GENERATOR_REPLACE_GETSEARCHABLECONTENT]]]
		return $content;
	}
	
	public function view() {
[[[GENERATOR_REPLACE_VIEW]]]
	}
	
	private function get_image_object($fID, $max_width = 0, $max_height = 0) {
		if (empty($fID)) {
			$image = null;
		} else if (empty($max_width) && empty($max_height)) {
			//Show image at full size (do not generate a thumbnail)
			$file = File::getByID($fID);
			$size = @getimagesize($file->getPath());
			$image = new stdClass;
			$image->src = $file->getRelativePath();
			$image->width = $size[0];
			$image->height = $size[1];
		} else {
			//Generate a thumbnail
			$max_width = empty($max_width) ? 9999 : $max_width;
			$max_height = empty($max_height) ? 9999 : $max_height;
			$file = File::getByID($fID);
			$ih = Loader::helper('image');
			$image = $ih->getThumbnail($file, $max_width, $max_height);
		}
		
		return $image;
	}
	
	public function add() {
[[[GENERATOR_REPLACE_ADD]]]
	}
	
	public function edit() {
[[[GENERATOR_REPLACE_EDIT]]]
	}

	public function save($args) {
[[[GENERATOR_REPLACE_SAVE]]]
		parent::save($args);
	}

	public function getJavaScriptStrings() {
		return array(
			'text-required' => t('Missing required text'),
			'image-required' => t('Missing required image'),
			'link-required' => t('Missing required link'),
		);
	}

//CONTENT BLOCK UTILITY FUNCTIONS:
	function br2nl($str) {
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("<br />\n", "\n", $str);
		return $str;
	}
	
	function translateFromEditMode($text) {
		// old stuff. Can remove in a later version.
		$text = str_replace('href="{[CCM:BASE_URL]}', 'href="' . BASE_URL . DIR_REL, $text);
		$text = str_replace('src="{[CCM:REL_DIR_FILES_UPLOADED]}', 'src="' . BASE_URL . REL_DIR_FILES_UPLOADED, $text);

		// we have the second one below with the backslash due to a screwup in the
		// 5.1 release. Can remove in a later version.

		$text = preg_replace(
			array(
				'/{\[CCM:BASE_URL\]}/i',
				'/{CCM:BASE_URL}/i'),
			array(
				BASE_URL . DIR_REL,
				BASE_URL . DIR_REL)
			, $text);
			
		// now we add in support for the links
		
		$text = preg_replace(
			'/{CCM:CID_([0-9]+)}/i',
			BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=\\1',
			$text);

		// now we add in support for the files
		
		$text = preg_replace_callback(
			'/{CCM:FID_([0-9]+)}/i',
			array('[[[GENERATOR_REPLACE_CLASSNAME]]]', 'replaceFileIDInEditMode'),
			$text);
		

		return $text;
	}
	
	function translateFrom($text) {
		// old stuff. Can remove in a later version.
		$text = str_replace('href="{[CCM:BASE_URL]}', 'href="' . BASE_URL . DIR_REL, $text);
		$text = str_replace('src="{[CCM:REL_DIR_FILES_UPLOADED]}', 'src="' . BASE_URL . REL_DIR_FILES_UPLOADED, $text);

		// we have the second one below with the backslash due to a screwup in the
		// 5.1 release. Can remove in a later version.

		$text = preg_replace(
			array(
				'/{\[CCM:BASE_URL\]}/i',
				'/{CCM:BASE_URL}/i'),
			array(
				BASE_URL . DIR_REL,
				BASE_URL . DIR_REL)
			, $text);
			
		// now we add in support for the links
		
		$text = preg_replace_callback(
			'/{CCM:CID_([0-9]+)}/i',
			array('[[[GENERATOR_REPLACE_CLASSNAME]]]', 'replaceCollectionID'),
			$text);

		$text = preg_replace_callback(
			'/<img [^>]*src\s*=\s*"{CCM:FID_([0-9]+)}"[^>]*>/i',
			array('[[[GENERATOR_REPLACE_CLASSNAME]]]', 'replaceImageID'),
			$text);

		// now we add in support for the files that we view inline			
		$text = preg_replace_callback(
			'/{CCM:FID_([0-9]+)}/i',
			array('[[[GENERATOR_REPLACE_CLASSNAME]]]', 'replaceFileID'),
			$text);

		// now files we download
		
		$text = preg_replace_callback(
			'/{CCM:FID_DL_([0-9]+)}/i',
			array('[[[GENERATOR_REPLACE_CLASSNAME]]]', 'replaceDownloadFileID'),
			$text);
		
		return $text;
	}
	
	private function replaceFileID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			$path = File::getRelativePathFromID($fID);
			return $path;
		}
	}
	
	private function replaceImageID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			preg_match('/width\s*="([0-9]+)"/',$match[0],$matchWidth);
			preg_match('/height\s*="([0-9]+)"/',$match[0],$matchHeight);
			$file = File::getByID($fID);
			if (is_object($file) && (!$file->isError())) {
				$imgHelper = Loader::helper('image');
				$maxWidth = ($matchWidth[1]) ? $matchWidth[1] : $file->getAttribute('width');
				$maxHeight = ($matchHeight[1]) ? $matchHeight[1] : $file->getAttribute('height');
				if ($file->getAttribute('width') > $maxWidth || $file->getAttribute('height') > $maxHeight) {
					$thumb = $imgHelper->getThumbnail($file, $maxWidth, $maxHeight);
					return preg_replace('/{CCM:FID_([0-9]+)}/i', $thumb->src, $match[0]);
				}
			}
			return $match[0];
		}
	}

	private function replaceDownloadFileID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			$c = Page::getCurrentPage();
			return View::url('/download_file', 'view', $fID, $c->getCollectionID());
		}
	}

	private function replaceFileIDInEditMode($match) {
		$fID = $match[1];
		return View::url('/download_file', 'view_inline', $fID);
	}
	
	private function replaceCollectionID($match) {
		$cID = $match[1];
		if ($cID > 0) {
			$c = Page::getByID($cID, 'APPROVED');
			return Loader::helper("navigation")->getLinkToCollection($c);
		}
	}
	
	function translateTo($text) {
		// keep links valid
		$url1 = str_replace('/', '\/', BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME);
		$url2 = str_replace('/', '\/', BASE_URL . DIR_REL);
		$url3 = View::url('/download_file', 'view_inline');
		$url3 = str_replace('/', '\/', $url3);
		$url3 = str_replace('-', '\-', $url3);
		$url4 = View::url('/download_file', 'view');
		$url4 = str_replace('/', '\/', $url4);
		$url4 = str_replace('-', '\-', $url4);
		$text = preg_replace(
			array(
				'/' . $url1 . '\?cID=([0-9]+)/i', 
				'/' . $url3 . '([0-9]+)\//i', 
				'/' . $url4 . '([0-9]+)\//i', 
				'/' . $url2 . '/i'),
			array(
				'{CCM:CID_\\1}',
				'{CCM:FID_\\1}',
				'{CCM:FID_DL_\\1}',
				'{CCM:BASE_URL}')
			, $text);
		return $text;
	}
}
