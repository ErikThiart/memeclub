### bulletproof\utils 

This utils/ folder contains seperate function to crop, rezize or watermark images

Install
-----
Since these are separate, standalone functions, all you have to do is 
include them in your project, and call the functions like this: 

```php 
require_once 'src/utils/func.image-crop.php';

/**
 * $image : full image path
 * $mime : the mime type of the image
 * $width : the image width
 * $height : the image height
 * $newWidth : the new width of the image
 * $newHeight : the new height of the image:
 */
$crop = bulletproof\utils\crop($image, $mime, $width, $height, $newWidth, $newHeight); 
// call the function and pass the right arguments. 
$crop = bulletproof\utils\crop( 
	'images/my-car.jpg', 'jpg', 100, 200, 50, 25
); 
// now 'images/my-car.jpg' is cropped to 50x25 pixels.
```

#### with bulletproof

If you want to use these function with the [bulletproof][bulletproof], here are some examples: 

##### Resizing
```php 
// include bulletproof and the resize function.
require "src/bulletproof.php";
require "src/utils/func.image-resize.php";

$image = new Bulletproof\Image($_FILES);

if($image["picture"]){
	$upload = $image->upload();
	
	if($upload){
		bulletproof\utils\resize(
			$image->getFullPath(), 
			$image->getMime(),
			$image->getWidth(),
			$image->getHeight(),
			50,
			50
	 );
	}
}
```
The `crop()` method supports resizing by ratio, checkout the file for more. 

#### Croping
You can crop images the same way.
```php 
require "src/utils/func.image-crop.php";

$crop = Bulletproof\crop(
	$upload->getFullPath(), 
	$upload->getMime(),
	$upload->getWidth(),
	$upload->getHeight(),
	50,
	50
);

```
#### Watermark
```php 
require 'src/utils/func.image-watermark.php';

// the image to watermark
$logo = 'my-logo.png'; 
// where to place the watermark
$position = 'center'; 
// get the width and heigh of the logo
list($logoWidth, $logoHeight) = getimagesize($logo);

$watermark = Bulletproof\watermark(
	$upload->getFullPath(), 
	$upload->getMime(),
	$upload->getWidth(),
	$upload->getHeight(),
	$logo, 
	$logoHeight, 
	$logoWidth, 
	$position		
);
```

Contribution 
----- 

You are encouraged to add functions for other features (ex: add text, rotate images .. ) 

LICENSE 
----- 
Check the main [bulletproof][bulletproof] page for the license. 


[bulletproof]: http://github.com/samayo/bulletproof
