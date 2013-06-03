<?php
/*
mysql> describe User_Awards
    -> ;
+---------+-----------+------+-----+-------------------+-------+
| Field   | Type      | Null | Key | Default           | Extra |
+---------+-----------+------+-----+-------------------+-------+
| id      | int(11)   | NO   | PRI | NULL              |       |
| AwardID | int(11)   | YES  |     | NULL              |       |
| Date    | timestamp | NO   |     | CURRENT_TIMESTAMP |       |
+---------+-----------+------+-----+-------------------+-------+
*/

function GetUserAwards($ID)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = 'SELECT * FROM User_Awards WHERE ID = ' . $ID;
		$result = mysqli_query($con, $query);
		mysqli_close($con);
		return $result;
	}	
}

function AddAward($ID, $AwardID)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = 'INSERT INTO User_Awards (id, AwardID) VALUES(' . $ID . ", " . $AwardID . ");";
		$result = mysqli_query($con, $query);
		mysqli_close($con);
		return $result;
	}
}

if (isset($_GET['query']))
{
	if ($_GET['query'] == "GetUserAwards")
	{
		$Awards = GetUserAwards($_GET['args']);
		$rows = array();
		while( $SingleRow = mysqli_fetch_assoc($Awards))
		{
			$rows[] = $SingleRow;
		}
		echo json_encode($rows);
	}
}

?>