<?php

define('THUMBNAIL_IMAGE_MAX_WIDTH', 100);
define('THUMBNAIL_IMAGE_MAX_HEIGHT', 100);

if ($_FILES["file"]["error"] > 0)
{ 
  echo "Error: " . $_FILES["file"]["error"] . "<br>";
}
else
{
  echo "Upload: " . $_FILES["file"]["name"] . "<br>";
  echo "Type: " . $_FILES["file"]["type"] . "<br>";
  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["file"]["tmp_name"];
}

$exif=exif_read_data($_FILES["file"]["tmp_name"], 0, true);

if (!$exif || $exif['GPS']['GPSLatitude'] =='' || 
	$exif['GPS']['GPSLongitude'] == '' )
{
	echo "Missing information";
}
else
{
	$LONGITUDE = getGPS($exif['GPS']['GPSLongitude']);
	$LATITUDE = getGPS($exif['GPS']['GPSLatitude']);;
	print_r($LONGITUDE);
	print_r($LATITUDE);

  //Get the trail the thing came from
  // set your API key here
  $api_key = "AIzaSyDArYdyABFtER6IWwrfnsQq8jwd8JFt1po";
  // format this string with the appropriate latitude longitude
  $url = 'http://maps.google.com/maps/geo?q='.$LONGITUDE .','.$LATITUDE. '&output=json&sensor=true_or_false&key=' . $api_key;
  // make the HTTP request
  $data = @file_get_contents($url);
  // parse the json response
  $jsondata = json_decode($data,true);
  // if we get a placemark array and the status was good, get the addres
  if(is_array($jsondata )&& $jsondata ['Status']['code']==200)
  {
        print_r($jsondata);
        $addr = $jsondata['results'][0]['formatted_address'];
        print_r($addr);
        $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
        //Check connection
        if (mysqli_connect_errno() )
        {
          echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }
        else
        {
          mysqli_query($con, "INSERT INTO PHOTOS VALUES(".$LONGITUDE['degrees'].", ".$LONGITUDE['minutes'].", ".$LONGITUDE['seconds'].", ".$LATITUDE['degrees'].", ".$LATITUDE['minutes'].", ".$LATITUDE['seconds'].", 0, 0, 0, 'TEST')");
          //mysqli_query($con, "INSERT INTO PHOTOS VALUES(0, 0, 0, 0, 0, 0, 0, 0, 0, 'LOL')");
          $query = "SELECT COUNT(*) FROM PHOTOS;";
          $result = mysqli_query($con, $query);
          $count = mysqli_fetch_assoc($result);
          print_r($count);
          $ID = intval($count["COUNT(*)"]);
          $FolderNum = floor($ID / 100);

          if (is_dir($FolderNum))
          {
          }
          else
          {
            mkdir($FolderNum);
          }
          echo "Reached here";
          //Time to compress the image
          $destinationURL = $FolderNum . "/" . $ID % 100 . ".jpg";
          //echo $FolderNum . $ID . $destinationURL;    
          
          $filename = compress_image($_FILES["file"]["tmp_name"], $destinationURL, 80);
          
          echo $destinationURL;
          $ret = generate_image_thumbnail($destinationURL, $FolderNum . "/" . $ID % 100 . "_thumb.jpg" );
          //move_uploaded_file($_FILES["file"]["tmp_name"], $destinationURL);

          mysqli_close($con);
        }
  }
}


//Pass in GPS.GPSLatitude or GPS.GPSLongitude or something in that format
function getGps($exifCoord)
{
  $degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
  $minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
  $seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;

  //normalize
  $minutes += 60 * ($degrees - floor($degrees));
  $degrees = floor($degrees);

  $seconds += 60 * ($minutes - floor($minutes));
  $minutes = floor($minutes);

  //extra normalization, probably not necessary unless you get weird data
  if($seconds >= 60)
  {
    $minutes += floor($seconds/60.0);
    $seconds -= 60*floor($seconds/60.0);
  }

  if($minutes >= 60)
  {
    $degrees += floor($minutes/60.0);
    $minutes -= 60*floor($minutes/60.0);
  }

  return array('degrees' => $degrees, 'minutes' => $minutes, 'seconds' => $seconds);
}

function gps2Num($coordPart)
{
  $parts = explode('/', $coordPart);

  if(count($parts) <= 0)// jic
    return 0;
  if(count($parts) == 1)
    return $parts[0];

  return floatval($parts[0]) / floatval($parts[1]);
}

function compress_image($source_url, $destination_url, $quality) 
{
	$info = getimagesize($source_url);

	if ($info['mime'] == 'image/jpeg')
		$image = imagecreatefromjpeg($source_url);

	elseif ($info['mime'] == 'image/gif')
		$image = imagecreatefromgif($source_url);

	elseif ($info['mime'] == 'image/png')
    		$image = imagecreatefrompng($source_url);

	imagejpeg($image, $destination_url, $quality);
	imagedestroy($image);
	return $destination_url;
}
/*
 * PHP function to resize an image maintaining aspect ratio
 * http://salman-w.blogspot.com/2008/10/resize-images-using-phpgd-library.html
 *
 * Creates a resized (e.g. thumbnail, small, medium, large)
 * version of an image file and saves it as another file
 */

function generate_image_thumbnail($source_image_path, $thumbnail_image_path)
{
    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
    switch ($source_image_type) {
        case IMAGETYPE_GIF:
            $source_gd_image = imagecreatefromgif($source_image_path);
            break;
        case IMAGETYPE_JPEG:
            $source_gd_image = imagecreatefromjpeg($source_image_path);
            break;
        case IMAGETYPE_PNG:
            $source_gd_image = imagecreatefrompng($source_image_path);
            break;
    }
    if ($source_gd_image === false) {
        return false;
    }
    $source_aspect_ratio = $source_image_width / $source_image_height;
    //For now, set aspect ratio to 1
    $source_aspect_ratio = 1;

    $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
    if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) 
    {
        $thumbnail_image_width = $source_image_width;
        $thumbnail_image_height = $source_image_height;
    } 
    elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) 
    {
        $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
        $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
    } 
    else 
    {
        $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
        $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
    }
    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
    imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
    imagedestroy($source_gd_image);
    imagedestroy($thumbnail_gd_image);
    return true;
}

?>
