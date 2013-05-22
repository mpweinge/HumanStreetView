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
	$query = "SELECT * FROM PHOTOS";
	$results = mysqli_query($con, $query);
	if ($results)
	{
		$LongData = array();
		while ($row = $results->fetch_assoc())
		{
			array_push($LongData, $row);
		}
		echo json_encode($LongData);
	}
}

//$encodedResponse = json_encode($accountJSON);

//fwrite($fh, "We're at the end of get_account_data with encodedResponse:\n");
//fwrite($fh, $encodedResponse."\n");

//echo $encodedResponse;
?>
