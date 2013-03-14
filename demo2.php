<?php
require 'GooglePlayParseApp.php';

//create new instance with url
$parsed = new AndroidMarketParser('https://play.google.com/store/apps/details?id=com.easit.sberny&hl=cs');
//allow donwload and save image
//$parsed->allowDownloadImages(true);
//enable caching 
//$parsed->setCacheTime(86400); //in seconds
//set temp directory to save temp files
//$parsed->setTemp('temp');
//start parsing
$parsed->parseUrl();
?>

<div style="border: 1px solid silver; width: 500px;">
	<img src="<?php echo $parsed->getIcon(); ?>" alt="<?php echo $parsed->getTitle(); ?>" align="right"/>
	<h1><?php echo $parsed->getTitle(); ?></h1>
	<br /><br /><br /><br /><br />
	Last update: <?php echo $parsed->getDatePublished(); ?><br />
	Size: <?php echo $parsed->getSize(); ?><br />
	Software Version:  <?php echo $parsed->getSoftvareVersion(); ?><br />
	<h3>About:</h3>
	<p>
	<?php if( $parsed->getBanner() ): ?>
	<img src="<?php echo $parsed->getBanner(); ?>" alt="<?php echo $parsed->getTitle(); ?>" align="left" width="300px"/>
	<?php endif; ?>
	<?php echo $parsed->getAbout(); ?>
	</p>
	
	<h3>News:</h3>
	<p> 
	<?php echo $parsed->getNews(); ?>
	</p>
	<hr />
	
	<?php foreach ($parsed->getImages() as $image ): ?>
		<a href="<?php echo $image['full_image']; ?>" target="_blank">
			<img src="<?php echo $image['preview']; ?>" alt="image" style="border: 1px solid black; padding: 3px; margin: 5px; "/>
		</a>
	<?php endforeach;?>
	<hr />
	
	<?php foreach( $parsed->getReviews() as $review ): ?>
		<div style="padding: 3px; border: 1px solid silver; margin: 5px; ">
			<p><?php echo $review['author']; ?> - <?php echo $review['date']; ?></p>
			<p>
				<?php echo $review['title']; ?><br />
				<?php echo $review['text']; ?>
			</p>
		</div>
	<?php endforeach; ?>
</div>
