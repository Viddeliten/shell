<?php

class image
{
	public static function save_uploaded_file($file, $image_destination_path, $thumbnail_destination_path)
	{
		//kolla så att mappen finns
		$filename_parts = explode("/",$image_destination_path);
		$filename = array_pop($filename_parts);
		$uppath = implode("/",$filename_parts);
		if(!file_exists($uppath))
			mkdir($uppath);


		$filtnamn=$file['tmp_name'];
		$filnamn=$file['name'];
		$filtyp=$file['type'];

		$fil=fopen($file['tmp_name'], "rb");
		
		$filesize=$file['size'];
		if($prop=getimagesize($file['tmp_name']))
		{
			$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
			$detectedType = exif_imagetype($file['tmp_name']);
			$error = !in_array($detectedType, $allowedTypes);
			//Om det är en bild
			if(!$error)
			{
				// preprint($file, "file");
				$fildata=fread($fil, $filesize);
				$fildata=addslashes($fildata);
				$thmb=$fildata;
				// preprint($fildata, "fildata");

				//Lägg undan filen på servern
				move_uploaded_file($filtnamn, $image_destination_path);
				copy($image_destination_path, $thumbnail_destination_path);
				
				// preprint($image_destination_path, "image_destination_path");
				
				//Ändra storleken om den är för stor
				image::resize($image_destination_path, IMG_MAX_WIDTH, IMG_MAX_HEIGHT, $image_destination_path);
				image::resize($thumbnail_destination_path, IMG_MAX_WIDTH_THUMB, IMG_MAX_HEIGHT_THUMB, $thumbnail_destination_path);

				if(!file_exists($image_destination_path))
				{
					message_print_error("Error1171200: Image $uppath/$filename does not exist");
					return false;
				}
				if(!file_exists($thumbnail_destination_path))
				{
					message_print_error("Error1171201: Thumb $uppath/$tfilename does not exist");
					return false;
				}
			}
			else
			{
				message_print_error("The uploaded file does not seem to be a valid image");
				return false;
			}
		}
		else
		{
			message_print_error("The uploaded file doesn't seem to be a valid image");
			return false;
		}
		
		return true;
	}

	public static function save_url_to_file($img_url, $image_destination_path, $thumbnail_destination_path)
	{
		//kolla så att mappen finns
		$filename_parts = explode("/",$image_destination_path);
		$filename = array_pop($filename_parts);
		$uppath = implode("/",$filename_parts);
		if(!file_exists($uppath))
			mkdir($uppath);
		
		// initialize the image class
		$image = new GetImage;

		// just an image URL
		$image->source = $img_url;
		$image->save_to = "$uppath/"; // with trailing slash at the end
		$image->dest_name = $filename;
		$image->set_extension = ".jpg";

		$get = $image->download('curl'); // using GD

		if($get)
		{
			message_print_message('The image has been saved.');
			copy($image_destination_path, $thumbnail_destination_path);
		}
		else
		{
			message_print_error('Something went wrong with your image!');
			return false;
		}
		
		//Ändra storleken om den är för stor
		// image_resize($uppath, $filename, IMG_MAX_WIDTH, IMG_MAX_HEIGHT, "$uppath/$filename");
		// image_resize($uppath, $filename, IMG_MAX_WIDTH_THUMB, IMG_MAX_HEIGHT_THUMB, "$uppath/$tfilename");
		image::resize($image_destination_path, IMG_MAX_WIDTH, IMG_MAX_HEIGHT, $image_destination_path);
		image::resize($thumbnail_destination_path, IMG_MAX_WIDTH_THUMB, IMG_MAX_HEIGHT_THUMB, $thumbnail_destination_path);
		return true;
	}

	public static function resize($image_path, $max_width, $max_height, $destination)
	{
		// preprint(array($path, $img, $max_width, $max_height, $destination),"DEBUG_image_resize");
		$size=64*1024*1024;
		ini_set('memory_limit', $size);

		//echo "<br />image_open_from_file($image_path)";
		$image = image_open_from_file($image_path);
		
		if ($image == false)
		{
			echo "<p class=\"error\">Unable to open image $image_path for thumbcreation</p>";
			return NULL;
		}
		else
		{
			//echo "<p>Creating thumbnail for $path/$img</p>";
				 // get originalsize of image
				$im = $image;
				$iwidth  = imagesx($im);
				$iheight = imagesy($im);
				
				if($iwidth>$max_width)
				{
					// Set thumbnail-width to max_width pixel
					$imgw = $iwidth * ($max_width/$iwidth);

					// calculate thumbnail-height from given width to maintain aspect ratio
					$imgh = $iheight * ($max_width/$iwidth);
				}
				else if($iheight>$max_height)
				{
					// Set thumbnail-width to max_width pixel
					$imgw = $iwidth * ($max_height/$iheight);

					// calculate thumbnail-height from given width to maintain aspect ratio
					$imgh = $iheight * ($max_height/$iheight);
				}
				else
				{
					$imgw = $iwidth;
					$imgh = $iheight;
				}

				// create new image using thumbnail-size
				$thumb=ImageCreateTrueColor($imgw,$imgh);
				
				//Skaffa om det finns genomskinlig färg
				imagecolortransparent ($thumb , imagecolortransparent($im));
				imagefill($thumb,0,0,0xFF00FF);

				// copy original image to thumbnail
				imagecopyresized($thumb,$im,0,0,0,0,$imgw,$imgh,$iwidth,$iheight);
				
				//Lägg på "vattenmärke"
				//Hämta färg och invertera
				$x=35;
				$y=$imgh-12;
				$diff=45;

				$rgb=imagecolorat  ( $thumb  , $x , $y);
				$gr = ($rgb >> 16) & 0xFF;
				$gg = ($rgb >> 8) & 0xFF;
				$gb = $rgb & 0xFF;
				//echo "<br />RGB: $gr $gg $gb";
				if(($gr+$gg+$gb)>(128*3))
				{
					//Gör mörkare
					$r=(int)(($gr*$diff)/100);
					$g=(int)(($gg*$diff)/100);
					$b=(int)(($gb*$diff)/100);
				}
				else
				{
					//Gör ljusare
					$r=255-(int)(((255-$gr)*$diff)/100);
					$g=255-(int)(((255-$gg)*$diff)/100);
					$b=255-(int)(((255-$gb)*$diff)/100);
				}
				//echo "<br />RGB: $r $g $b";
				$tc = ImageColorAllocate ($thumb, $r, $g, $b);
				ImageString ($thumb, 3, 5, $imgh-30, user_get_name(login_get_user()), $tc);
				ImageString ($thumb, 3, 5, $imgh-15, "on ".SITE_NAME, $tc);
				
				// $tc = ImageColorAllocate ($thumb, 255, 0, 0);
				// imagesetpixel  ( $thumb  , $x  , $y  , $tc);
				
				imagejpeg($thumb, $destination);
			   
				// clean memory
				imagedestroy ($im);
				imagedestroy ($thumb);
		}

	}

}
?>