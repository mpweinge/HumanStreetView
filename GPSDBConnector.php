<?php

function ReadGPSInformationFromDB($con)
{
	$query = "SELECT * FROM PHOTOS";
	$results = mysqli_query($con, $query);
	if ($results)
	{
		
		$LongData = array();
		while ($row = $results->fetch_assoc())
		{
			array_push($Longdata, $row);
		}
		print_r($LongData);
		echo json_encode($LongData);

	}
}

function AddGPSInformationToDB()
{

}

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

	//Parse the query string to determine what we are attempting to do
	$q = $_GET["query"];

	if (strlen($q) > 0)
	{
		if ($q == "ReadGPS")
		{
			ReadGPSInformationFromDB($con);
			echo "Read GPS Information";
		}
		else
		{
			echo "Error parsing query" . $q;
		}
	}
	else
	{
		echo "Error parsing query" . print_r($_GET);
	}
}
?>