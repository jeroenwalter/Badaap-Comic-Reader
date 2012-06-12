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

	session_start();
	require_once(dirname(__FILE__)."/../users.php");

	$SimpleUsers = new SimpleUsers();

  if( !$SimpleUsers->logged_in || !$SimpleUsers->is_admin)
	{
		header("Location: ../login.php");
		exit;
	}
  
	// Validation of input
	if( isset($_POST["username"]) )
	{
		if( empty($_POST["username"]) || empty($_POST["password"]) )
			$error = "You have to choose a username and a password";
    else
    {
      $level = 0;
      if (isset($_POST['level']))
        $level = 1;
      
    	// Both fields have input - now try to create the user.
    	// If $res is (bool)false, the username is already taken.
    	// Otherwise, the user has been added, and we can redirect to some other page.
			$res = $SimpleUsers->createUser($_POST["username"], $_POST["password"], $level);

			if(!$res)
				$error = "Username already taken.";
			else
			{
					header("Location: usermanagement.php");
					exit;
			}
		}

	} // Validation end

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title></title>
	  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="../resources/css/users.css" type="text/css">
	</head>
	<body>
    <h1>Badaap Comic Reader</h1>
		<h2>Register new user</h2>

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
				<input type="text" name="password" id="password" />
			</p>

      <p>
      <input type="checkbox" name="level" />&nbsp;Administrator
      </p>
			<p>
				<input type="submit" name="submit" value="Register" />
			</p>

		</form>

	</body>
</html>