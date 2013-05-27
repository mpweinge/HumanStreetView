<?php
 // Database structure
/*
mysql> describe relationships;
+---------+---------------------------------------+------+-----+---------+-------+
| Field   | Type                                  | Null | Key | Default | Extra |
+---------+---------------------------------------+------+-----+---------+-------+
| userID  | int(11)                               | NO   | PRI | NULL    |       |
| userID2 | int(11)                               | NO   | PRI | 0       |       |
| status  | enum('ACCEPTED','DECLINED','PENDING') | YES  |     | NULL    |       |
+---------+---------------------------------------+------+-----+---------+-------+
*/

function AddNewFriendship($userID, $userID2)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = "INSERT INTO relationships VALUES(" . $userID. ", " . $userID2 . ', "PENDING");';
		$result = mysqli_query($con, $query);
	}
	mysqli_close($con);
}

function GetFriendList($userID)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = "SELECT * FROM relationships WHERE userID = " . $userID " OR userID2 = " . $userID
		. "AND status = 'ACCEPTED'";
		$result = mysqli_query($con, $query);
		mysqli_close($con);
		return $result;
	}	
}

function AcceptFriendRequest($userID, $userID2)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = 'UPDATE relationships SET status = "ACCEPTED" WHERE 
			(userID = ' . $userID " AND userID2 = " . $userID2 . ") OR (userID = " . 
				$userID2 . " AND userID2 = " . $userID . ");";
		
		$result = mysqli_query($con, $query);
		mysqli_close($con);
	}
}

function DeclineFriendRequest($userID, $userID2)
{
	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
	if (mysqli_connect_errno() )
	{
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	else
	{
		$query = 'UPDATE relationships SET status = "DECLINED" WHERE 
			(userID = ' . $userID " AND userID2 = " . $userID2 . ") OR (userID = " . 
				$userID2 . " AND userID2 = " . $userID . ");";
		
		$result = mysqli_query($con, $query);
		mysqli_close($con);
	}
}
?>