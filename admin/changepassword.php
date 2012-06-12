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
	require_once(dirname(__FILE__)."/../users.php");

	$SimpleUsers = new SimpleUsers();

	// This is a simple way of validating if a user is logged in or not.
	// If the user is logged in, the value is (bool)true - otherwise (bool)false.
  if( !$SimpleUsers->logged_in || !$SimpleUsers->is_admin)
	{
		header("Location: ../login.php");
		exit;
	}


	// If the user is logged in, we can safely proceed.


	$userId = $_GET["userId"];

	$user = $SimpleUsers->getSingleUser($userId);
	if( !$user )
		die("The user could not be found...");


	// Validation of input
	if( isset($_POST["password"]) )
	{
		if( empty($_POST["password"]) )
			$error = "You have to choose a password";
    else
    {
    	// Input validation is ok, set the password and then redirect
			$SimpleUsers->setPassword($_POST["password"], $user["userId"]);
			header("Location: usermanagement.php");
			exit;
		}

	} // Validation end

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Badaap Comic Reader</title>
	  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	  <link rel="stylesheet" href="../resources/css/users.css" type="text/css">

	</head>
	<body>
    <h1>Badaap Comic Reader</h1>
		<h2>Change password for user <?php echo $user["username"]; ?></h2>

		<?php if( isset($error) ): ?>
		<p>
			<?php echo $error; ?>
		</p>
		<?php endif; ?>

		<form method="post" action="">

			<p>
				<label for="password">New password:</label><br />
				<input type="text" name="password" id="password" />
			</p>

			<p>
				<input type="submit" name="submit" value="Save" />
			</p>

		</form>

	</body>
</html>