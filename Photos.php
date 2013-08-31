<?php

header("Content-Type: text/plain");

function ReadGPSInformationFromDB()
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
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

function AddGPSInformationToDB()
{

}

if (isset($_GET['query']))
{
	if ($_GET['query'] == "GetGPSInformation")
	{
		ReadGPSInformationFromDB();
	}
}

?>
