<?php
// Those two files can be included only if INCLUDE_CHECK is defined
header("Content-Type: text/plain");
require_once 'Swift-5.0.0/lib/swift_required.php';

session_name('tzLogin');
// Starting the session

session_set_cookie_params(2*7*24*60*60);
// Making the cookie live for 2 weeks

session_start();

print_r($_POST);

if($_SESSION['id'] && !isset($_COOKIE['tzRemember']) && !$_SESSION['rememberMe'])
{
    // If you are logged in, but you don't have the tzRemember cookie (browser restart)
    // and you have not checked the rememberMe checkbox:

    $_SESSION = array();
    session_destroy();

    // Destroy the session
}

if(isset($_GET['logoff']))
{
    $_SESSION = array();
    session_destroy();
    exit;
}

if ($_GET['submit'] == 'GetLogin')
{
    $err = array();
    if ( !isset($_COOKIE['loginCookie'] ) )
    {
        $err[] = "No login cookie";
    }
    else
    {
        $LoginInfo = unserialize($_COOKIE['loginCookie']);
        $LoginInfo['username'] = mysql_real_escape_string($LoginInfo['username']);
        $LoginInfo['password'] = mysql_real_escape_string($LoginInfo['password']);

        // Escaping all input data
        $query = "SELECT id,usr FROM tz_members WHERE usr='{$LoginInfo['username']}' AND pass='".md5($LoginInfo['password'])."'";

	$con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo"); 
     
        $row = mysqli_fetch_assoc(mysqli_query($con, $query));

        if($row['usr'])
        {
            echo "Logged in as " . $LoginInfo['username'];
        }
	else 
	{
		$err[] = "Invalid username/pass";
		print_r(unserialize($_COOKIE['loginCookie']));
		print_r($LoginInfo);
	}
    }
    exit;
}
else if($_POST['submit']=='Login')
{
    // Checking whether the Login form has been submitted

    $err = array();
    // Will hold our errors

    if(!$_POST['username'] || !$_POST['password'])
        $err[] = 'All the fields must be filled in!';

    $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");

    if(!count($err))
    {
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        $_POST['password'] = mysql_real_escape_string($_POST['password']);
        $_POST['rememberMe'] = (int)$_POST['rememberMe'];

        // Escaping all input data
        $query = "SELECT id,usr FROM tz_members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'";
        
        $row = mysqli_fetch_assoc(mysqli_query($con, $query));

        if($row['usr'])
        {
            // If everything is OK login

            $_SESSION['usr']=$row['usr'];
            $_SESSION['id'] = $row['id'];
            $_SESSION['rememberMe'] = $_POST['rememberMe'];

            // Store login data in cookie
            $LoginData = array();
            $LoginData['username'] = $_POST['username'];
            $LoginData['password'] = $_POST['password'];
            setcookie('loginCookie', serialize($LoginData));

            // We create the tzRemember cookie
            echo "Logged in as " . $LoginData['username'];
        }
        else $err[]='Wrong username and/or password!';
    }

    if($err)
        $_SESSION['msg']['login-err'] = implode('<br />',$err);
        // Save the error messages in the session

    print_r($err);
    exit;
}
else if($_POST['submit']=='Register')
{
    // If the Register form has been submitted
    $err = array();

    if(strlen($_POST['username'])<7 || strlen($_POST['username'])>32)
    {
        $err[]='Your username must be between 6 and 32 characters!';
    }

    if(preg_match('/[^a-z0-9\-\_\.]+/i',$_POST['username']))
    {
        $err[]='Your username contains invalid characters!';
    }

    //Check to see if username exists
    $_POST['username'] = mysql_real_escape_string($_POST['username']);
    $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");

    $query = "SELECT * FROM tz_members WHERE usr='{$_POST['username']}'";
    $row = mysqli_fetch_assoc(mysqli_query($con, $query));

    if ($row['usr'])
    {
        $err[] = 'Username already exists';
    }
    else if(!count($err))
    {
        // If there are no errors
        $pass = substr(md5($_SERVER['REMOTE_ADDR'].microtime().rand(1,100000)),0,6);
        // Generate a random password

        $_POST['email'] = mysql_real_escape_string($_POST['email']);
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        // Escape the input data

        $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
        
        mysqli_query($con, "   INSERT INTO tz_members(usr,pass,email,regIP,dt)
                    VALUES(
                    '".$_POST['username']."',
                    '".md5($pass)."',
                    '".$_POST['email']."',
                    '".$_SERVER['REMOTE_ADDR']."',
                    NOW()
        )");

        //if(mysql_affected_rows($link)==1)
        //{
            //send_mail(  'demo-test@michaelweingert.com',
            //        $_POST['email'],
            //        'Registration System Demo - Your New Password',
            //        'Your password is: '.$pass);
            //        $_SESSION['msg']['reg-success']='We sent you an email with your new password!';
        $transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
          ->setUsername('mpweingert@gmail.com')
          ->setPassword('Soccer12');

        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance('Wonderful Subject')
          ->setFrom(array('mpweingert@gmail.com' => 'Michael Weingert'))
          ->setTo(array('mpweingert@gmail.com' => 'Michael Weingert'))
          ->setBody('This is the text of the mail send by Swift using SMTP transport.');
        //$attachment = Swift_Attachment::newInstance(file_get_contents('path/logo.png'), 'logo.png');  
        //$message->attach($attachment);
        $numSent = $mailer->send($message);
        printf("Sent %d messages\n", $numSent);

        //}
        //else $err[]='This username is already taken!';
        echo "Works! Password" . $pass;
    }

    if(count($err))
    {
        $_SESSION['msg']['reg-err'] = implode('<br />',$err);
    }
    print_r($err);
    exit;
}
else if ($_POST['submit'] == 'ChangePass')
{
    $err = array();

   if(!$_POST['username'] || !$_POST['password'] || !$_POST['newpassword'])
        $err[] = 'All the fields must be filled in!';

    $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");

    if(!count($err))
    {
        $_POST['password'] = mysql_real_escape_string($_POST['password']);
        $_POST['username'] = mysql_real_escape_string($_POST['username']);
        $_POST['newpassword'] = mysql_real_escape_string($_POST['newpassword']);
        // Escape the input data

        $con = mysqli_connect("localhost", "root", "Soccer1&", "GPSInfo");
        // Escaping all input data
        $query = "SELECT id,usr FROM tz_members WHERE usr='{$_POST['username']}' AND pass='".md5($_POST['password'])."'";
        
        $row = mysqli_fetch_assoc(mysqli_query($con, $query));

        if($row['usr'])
        {
            $query = 'UPDATE tz_members SET pass = "' . md5($_POST['newpassword']) . 
            '" where usr = "' . $_POST['username'] . '"';

		echo $query;

            mysqli_query($con, $query);
		echo "Made it into here";
        }
	else
	{
		print_r($_POST);
		echo md5($_POST['password']);
	}

    }
}
?>
