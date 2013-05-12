\<?php

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
			move_uploaded_file($_FILES["file"]["tmp_name"], $FolderNum . "/" . $ID % 100);
		}
		else
		{
			mkdir($FolderNum);
			move_uploaded_file($_FILES["file"]["tmp_name"], $FolderNum . "/" . $ID % 100);
		}
		mysqli_close($con);
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

?>
