<?php
/*
  This file is part of Badaap Comic Reader.
  
  Copyright (c) 2012 Jeroen Walter
  
  Badaap Comic Reader is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Badaap Comic Reader is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Badaap Comic Reader.  If not, see <http://www.gnu.org/licenses/>.
*/  
	/**
	* Make sure you started your'e sessions!
	* You need to include su.inc.php to make SimpleUsers Work
	* After that, create an instance of SimpleUsers and your'e all set!
	*/

	session_start();
	require_once(dirname(__FILE__)."/users.php");

	$SimpleUsers = new SimpleUsers();

	// Login from post data
  if( isset($_POST["username"]) )
	{
    $_SESSION['username'] = $_POST["username"];
    $_SESSION['password'] = $_POST["password"];
    }
  else
  if (isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass']))
  {
    // NB: UNENCRYPTED !!!
    $_SESSION['username'] = $_COOKIE['cookname'];
    $_SESSION['password'] = $_COOKIE['cookpass'];
  }  
    
	if( isset($_SESSION['username']) )
	{
		// Attempt to login the user - if credentials are valid, it returns the users id, otherwise (bool)false.
		$res = $SimpleUsers->loginUser($_SESSION['username'], $_SESSION["password"]);
		if(!$res)
			$error = "You supplied the wrong credentials.";
		else
		{
      if (isset($_POST['remember']))
      {
        // NB: UNENCRYPTED !!!
        
        // set cookie for 30 days.
        setcookie("cookname", $_SESSION['username'], time()+2592000, "/");
        setcookie("cookpass", $_SESSION['password'], time()+2592000, "/");
        
        /*
Re: [PHP] Best way to do "stay logged in"?
simple one:
on login:
- generate a random uuid, this is now the session id
- store the uuid in a cookie (this will get passed back as a header) with now()+X as expire date
- store the uuid with user id in a database

on visit:
- grab the the session id from the browser if there is one, otherwise point to login page (this is for your protected stuff)
- find the uuid in the database and lookup the user id
- if user logs out, remove the session from database and present proper page

Keep in mind that the browser itself will get rid of the cookie and the user's session will be lost. The other way to do it is to store the date of creation in the DB and timeout the session by yourself.

You will need this authentication code on every page that is supposed to care about the user id or is supposed to be protected.

You can also set referrer headers so that when directing someone to login, they will get directed back once doing so (in case the user has a direct address to a logged in page).

$code = sha1(md5($username . $password . $firstXDigitsOfIP)); 
 + salt
        */
      }

      header("Location: index.php");
      exit;
		}

	} // Validation end

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title></title>
	  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="resources/css/users.css" type="text/css">
	</head>
	<body>

    <h1>Badaap Comic Reader</h1>
		<h2>Login</h2>

		<?php if( isset($error) ): ?>
		<p>
			<?php echo $error; ?>
		</p>
		<?php endif; ?>

		<form method="post" action="">
			<p>
				<label for="username">Username:</label><br />
				<input type="text" name="username" id="username" />
			</p>

			<p>
				<label for="password">Password:</label><br />
				<input type="password" name="password" id="password" />
			</p>

      <p>
        <input type="checkbox" name="remember" checked="checked" />&nbsp;Remember me
      </p>
			<p>
				<input type="submit" name="submit" value="Login" />
			</p>

		</form>

	</body>
</html>