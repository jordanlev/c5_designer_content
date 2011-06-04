	//Helper function for external URLs
	public function valid_url($url) {
		if ((strpos($url, 'http') === 0) || (strpos($url, 'mailto') === 0)) {
			return $url;
		} else if (strpos($url, '@') !== false) {
			return 'mailto:' . $url;
		} else {
			return 'http://' . $url;
		}
	}
	