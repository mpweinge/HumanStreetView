<?php
header("Content-Type: text/plain");

$myFile = "LogFile.log";
$fh = fopen($myFile, 'w');

$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
if (mysqli_connect_errno() )
{
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
else
{
	echo "Connected successfullly";
}

//$encodedResponse = json_encode($accountJSON);

//fwrite($fh, "We're at the end of get_account_data with encodedResponse:\n");
//fwrite($fh, $encodedResponse."\n");

//echo $encodedResponse;
?>