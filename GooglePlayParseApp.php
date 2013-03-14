<?php

include __DIR__ . '/libs/simple_html_dom.php';


class AndroidMarketParser{
	//application url
	private $_app_url = NULL;
	//alow download images into temp dir
	private $_download_images = FALSE;
	//set temp directory
	private $_temp_dir = './temp/';
	//output values
	private $_parsed = array();
	//temp html property
	private $_html;
	//caching time
	private $_cache_time = 0; //seconds

	const SELECTOR_ICON = 'div[class=doc-banner-icon]';
	const SELECTOR_TITLE = "h1";
	const SELECTOR_BANNER = "div[class=doc-banner-image-container]";
	const SELECTOR_IMAGES = "div[class=screenshot-carousel-content-container]";
	const SELECTOR_IMAGES_DIV = 'div[class=screenshot-image-wrapper]';
	const SELECTOR_ABOUT = "div[id=doc-original-text]";
	const SELECTOR_DATEPUBLISHED = 'time[itemprop=datePublished]';
	const SELECTOR_REVIEWS = 'div[class=doc-user-reviews-page]';
	const SELECTOR_REVIEWDATE = 'span[class=doc-review-date]';
	const SELECTOR_SOFTVERSION = 'dd[itemprop=softwareVersion]';
	const SELECTOR_SIZE = 'dd[itemprop=fileSize]';
	const SELECTOR_REVIEWAUTHOR = 'span[class=doc-review-author]';
	const SELECTOR_REVIEW_DETAIL = 'div[class=doc-review]'; 
	const SELECTOR_REVIEWTITLE = 'h4[class=review-title]';
	const SELECTOR_REVIEWTEXT = 'p[class=review-text]';
	const SELECTOR_NEWS = 'div[class=doc-whatsnew-container]';
	const SELECTOR_COMPANY = 'a[class=doc-header-link]';

	public function __construct( $app_url )
	{
		$this->setAppUrl($app_url);
	}

	/**
	 * set temp directory
	 * @param string $temp
	 * @return AndroidMarketParser
	 */
	public function setTemp( $temp )
	{
		if( !empty( $temp ) ){
			if( substr($temp, -1) != '/' ){
				$temp = $temp . '/';
			}

			if( !is_writeable($temp ) || !is_dir( $temp )){
				throw new Exception('temp directory is not writable');
			}

			$this->_temp_dir = $temp;
		}

		return $this;
	}

	/**
	 * Setup caching time
	 * @param int $time
	 * @return AndroidMarketParser
	 */
	public function setCacheTime($time)
	{
		$this->_cache_time = abs($time);
		return $this;
	}

	/**
	 * return temp directory
	 * @return string
	 */
	public function getTemp()
	{
		return $this->_temp_dir;
	} 

	/**
	 * Enable or disable donwload images into temp
	 * @param BOOL $allow
	 * @return AndroidMarketParser
	 */
	public function allowDownloadImages( $allow = FALSE )
	{
		$allow = (bool)$allow;
		$this->_download_images = $allow;
		return $this;
	}

	/**
	 * Return if download is enabled
	 * @return boolean
	 */
	public function isAllowedDownloadImages()
	{
		return $this->_download_images;
	}

	/**
	 * Set app url
	 * @param string $file
	 * @throws Exception
	 * @return AndroidMarketParser
	 */
	private function setAppUrl( $file )
	{
		$file_headers = @get_headers($file);
		if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
			throw new Exception('failed url of application');
		}
		else {
			$this->_app_url = $file;
		}
		return $this;
	}

	/**
	 * return app url
	 * @return string
	 */
	private function getAppUrl()
	{
		return $this->_app_url;
	}

	private function setProperty($name, $value )
	{
		if( empty($value)){
			$value = NULL;
		}

		if( !is_array($value )){
			//is string
			$this->_parsed[$name ] = $value;
		}else{
			//is array
			$this->_parsed[$name] = $value;
		}
	}

	/**
	 * return property by key 
	 * @param string $name
	 * @return multitype:|NULL
	 */
	public function getProperty( $name )
	{
		if( array_key_exists($name, $this->_parsed )){
			return $this->_parsed[$name];
		}
		return NULL;
	}

	/**
	 * return title
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
 	public function getTitle()  
	{
		return $this->getProperty('title');
	}
	
	/**
	 * return icon
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getIcon()  
	{
		return $this->getProperty('icon');
	}
	
	/**
	 * return banner url
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getBanner()  
	{
		return $this->getProperty('banner');
	}
	
	/**
	 * return text about application
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getAbout()  
	{
		return $this->getProperty('about');
	}
	
	/**
	 * return date of last pubhslied app
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getDatePublished()
	{
		return $this->getProperty('datePublished');
	}
	
	/**
	 * return software version
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getSoftvareVersion()  
	{
		return $this->getProperty('softVersion');
	}
	
	/**
	 * return size of application
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getSize()  
	{
		return $this->getProperty('size');
	}
	
	/**
	 * return array of reviews
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getReviews()  
	{
		return $this->getProperty('reviews');
	}

	/**
	 * return array of images
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getImages()  
	{
		return $this->getProperty('images');
	}

	/**
	 * get news from app
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getNews()
	{
		return $this->getProperty('news');
	}

	/**
	 * Return company name
	 * @return Ambigous <multitype:, NULL, multitype:>
	 */
	public function getCompany()
	{
		return $this->getProperty('company');
	}

	/**
	 * parse url
	 */
	public function parseUrl()
	{
		if( $this->_cache_time ){
			if( $this->loadFromCache() == true ){
				return;
			}
		}

		if( $this->createHtmlStruct() === false){
			throw new Exception('failed create structure');
		}

		$this->setProperty('title', $this->_html->find(self::SELECTOR_TITLE, 0)->plaintext);
		$this->setProperty('icon', $this->getIconFromHtml(self::SELECTOR_ICON));
		$this->setProperty('banner', $this->getIconFromHtml(self::SELECTOR_BANNER));
		$this->setProperty('about', $this->getAboutFromHtml(self::SELECTOR_ABOUT) );
		$this->setProperty('datePublished', $this->getAboutFromHtml(self::SELECTOR_DATEPUBLISHED));
		$this->setProperty('softVersion', $this->getAboutFromHtml(self::SELECTOR_SOFTVERSION));
		$this->setProperty('size', $this->getAboutFromHtml(self::SELECTOR_SIZE));
		$this->setProperty('reviews', $this->getReviewsFromHtml(self::SELECTOR_REVIEWS));
		$this->setProperty('images', $this->getImagesFromHtml(self::SELECTOR_IMAGES) );
		$this->setProperty('news', $this->getNewsFromHtml(self::SELECTOR_NEWS));
		$this->setProperty('company', $this->getCompanyFromHtml(self::SELECTOR_COMPANY));
		if( $this->_cache_time > 0 ){
			$this->saveIntoCache();
		}
	}

	private function getNewsFromHtml( $selector )
	{
		$news = $this->getAboutFromHtml($selector);
		$news = str_get_html( $news );
		//delete first paragragh
		$news = preg_replace('/<p>(.*)' . $news->find('p', 0)->innertext . '(.*)<\/p>/', '', $news);
		return $news;
	}
	
	private function loadFromCache()
	{
		$filename = sha1( $this->getAppUrl() );
		if( $this->_cache_time > 0 && file_exists( $this->getTemp() . $filename ) ){
			$age = time() - filemtime($this->getTemp() . $filename);
			if( $age > $this->_cache_time ){
				return false;
			}

			$f = file_get_contents($this->getTemp() . $filename);
			//make array from json
			$this->_parsed = json_decode($f, true);
			return true;
		}
		return false;
	}

	private function saveIntoCache()
	{
		if( count( $this->_parsed )){
			$json = json_encode( $this->_parsed );
			$cache_name = sha1($this->getAppUrl() );
			@unlink( $this->getTemp() . $cache_name );

			//save json
			$f = fopen($this->getTemp() . $cache_name, 'a');
			fwrite($f, $json);
			fclose($f);
		}
	}

	/**
	 * Return company name
	 * @param selector $selector
	 * @return string
	 */
	private function getCompanyFromHtml($selector)
	{
		return $this->_html->find($selector, 0)->innertext;
	}

	/**
	 * parse reviews from html 
	 * @param string $selector
	 * @return array
	 */
	private function getReviewsFromHtml($selector)
	{
		$outreviews = array();

		$reviews = $this->_html->find($selector, 0);
		$i = 0;

		if( $reviews ){
			foreach ($reviews->find(self::SELECTOR_REVIEW_DETAIL) as $review ){
				if( $review ){
					$author =  $review->find(self::SELECTOR_REVIEWAUTHOR, 0)->find('strong', 0)->plaintext;

					$date = $date = $review->find(self::SELECTOR_REVIEWDATE, 0);
					if( $date ){
						$date = str_replace(' - ', '', $date->plaintext);
					}

					$title = $review->find(self::SELECTOR_REVIEWTITLE, 0);
					if( $title ){
						$title = $title->plaintext;
					}

					$text = $review->find(self::SELECTOR_REVIEWTEXT, 0);
					if($text){
						$text = $text->plaintext;
					}

					$outreviews[++$i]['author'] = $author;
					$outreviews[$i]['title'] = $title;
					$outreviews[$i]['date'] = $date;
					$outreviews[$i]['text'] = $text;
				}
			}
		}
		return $outreviews;
	}

	/**
	 * parse about applicatoin text
	 * @param string $selector
	 * @return string
	 */
	private function getAboutFromHtml( $selector )
	{
		return nl2br($this->_html->find($selector, 0)->innertext);
	}

	private function getImagesFromHtml($selector)
	{
		$images = array();
		$images_html = $this->_html->find($selector, 0);
		
		if( $images_html ){
			$i = 0;
			foreach( $images_html->find(self::SELECTOR_IMAGES_DIV) as $container ){
				if( $container ){
					$images[++$i]['full_image'] = $this->downloadImage( $container->getAttribute('data-baseurl') );
					$images[$i]['preview'] 	= $this->downloadImage( $container->find('img', 0)->src );
				}
			}
		}
		return $images;
	}

	/**
	 * Parse icon from html
	 * @param string $selector
	 * @return string
	 */
	private function getIconFromHtml( $selector )
	{
		$icon = $this->_html->find($selector, 0);

		if( $icon ){
			$icon = $icon->find('img', 0)->src;
		}

		if( $icon && $this->isAllowedDownloadImages() ){
			$icon = $this->downloadImage( $icon );
		}

		return $icon;
	}

	/**
	 * Download image into temp dir
	 * @param string $image
	 * @return string
	 */
	private function downloadImage( $image )
	{
		if( $this->isAllowedDownloadImages() == false){
			return $image;
		}

		$name = $this->_temp_dir . sha1($image);
		file_put_contents($name, file_get_contents($image) );

		$type = getimagesize($name);
		$postfix = $this->getImagePostfixByImageInfo( $type[2] );		

		rename($name, $name . '.' . $postfix);
		return $name . '.' . $postfix;
	}

	/**
	 * Get postfix (image type) by image
	 * @param string $type
	 * @throws Exception
	 * @return string
	 */
	private function getImagePostfixByImageInfo( $type )
	{
		$postfix = explode('/', image_type_to_mime_type($type) );

		if( is_array( $postfix ) && count( $postfix ) > 0 &&  $postfix[0] == 'image' ){
			$postfix = str_replace('jpeg', 'jpg', $postfix[1]);
		}else{
			throw new Exception('Unknow file type');
		}

		return $postfix;

		/*
		$postfix = null;
		switch( image_type_to_mime_type($type )){
			case 'image/gif': $postfix = 'gif'; break;
			case 'image/jpeg':
			case 'image/jpg': $postfix = 'jpg'; break;
			case 'image/png': 
			default: $postfix = 'png';
		}

		return $postfix;
		*/
	}

	/**
	 * Create html structure from url
	 * @return boolean
	 */
	private function createHtmlStruct()
	{
		$content = file_get_contents($this->getAppUrl());

		//quick fix
		$content = str_replace('"src', '" src', $content);
		//$this->_html = file_get_html( $this->getAppUrl() );

		$this->_html = str_get_html( $content );
		if( get_class($this->_html) != 'simple_html_dom'){
			return false;
		}
		return true;
	}

	/**
	 * Get output array
	 * @return array:
	 */
	public function getOutputArray()
	{
		return $this->_parsed; 
	}
}
