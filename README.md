Android Market application Parser
============

PHP class to parse information about application from google play store

DEPRICATED
--------------

*  PHP Simple HTML DOM Parser

    ``http://simplehtmldom.sourceforge.net/``

HOW TO USE
--------------

*  See the demo

```php
require 'GooglePlayParseApp.php';

//create new instance with url
$parsed = new AndroidMarketParser('https://play.google.com/store/apps/details?id=com.easit.sberny&hl=cs');
//allow donwload and save image
$parsed->allowDownloadImages(true);
//enable caching 
$parsed->setCacheTime(86400); //in seconds
//set temp directory to save temp files
$parsed->setTemp('temp');
//start parsing
$parsed->parseUrl();

//get company name
echo $parsed->getCompany();
//get title 
echo $parsed->getTitle();
//get size of build
echo $parsed->getSize();
//get banner image
echo $parsed->getBanner();
//get last software version
echo $parsed->getSoftvareVersion();
//get text
echo $parsed->getAbout();
//get icon image
echo $parsed->getIcon();
//get last published date
echo $parsed->getDatePublished();
//get all reviews
foreach( $parsed->getReviews() as $review ){
	//review author
	echo $review['author'];
	//review date
	echo $review['date'];
	//review title
	echo $review['title'];
	//review text
	echo $review['text'];
}

//get all images
foreach( $parsed->getImages()  as $image ){
	//preview image
	echo $image['preview'];
	//full size image
	echo $image['full_image'];
}
```