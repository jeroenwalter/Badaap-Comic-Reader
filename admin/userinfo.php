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

	$userInfo = $SimpleUsers->getInfoArray($userId);
/*
	if( isset($_POST["newkey"]) )
	{
		if( strlen($_POST["newkey"]) > 0 )
			$SimpleUsers->setInfo($_POST["newkey"], $_POST["newvalue"], $userId);
		
		if( isset($_POST["userInfo"]) )
		{
	    	foreach($_POST["userInfo"] as $pKey => $pValue)
					$SimpleUsers->setInfo($pKey, $pValue, $userId);
		} 

		header("Location: users.php");
		exit;
	}
*/


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
		<h2>User information for <?php echo $user["username"]; ?></h2>

		<?php if( isset($error) ): ?>
		<p>
			<?php echo $error; ?>
		</p>
		<?php endif; ?>

		<form method="post" action="">
			
			<?php foreach($userInfo as $key => $value): ?>
			<p>
				<label for="db_<?php echo $key; ?>"><?php echo $key; ?></label><br />
				<input type="text" name="userInfo[<?php echo $key; ?>]" id="db_<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>" /><br />
				<!--<a href="removeinfo.php?userId=<?php echo $userId; ?>&db_key=<?php echo urlencode($key); ?>">Permanently remove this key</a>-->
			</p>
			<?php endforeach; ?>
			<!--
        <h4>Create new database key?</h4>
			<p>
				<label for="newkey">Key name</label><br />
				<input type="text" name="newkey" id="newkey" />
			</p>

			<p>
				<label for="newvalue">Key value</label><br />
				<input type="text" name="newvalue" id="newvalue" />
			</p>

			
			<p>
				<input type="submit" name="submit" value="Save" />
			</p>
      -->

		</form>

	</body>
</html>