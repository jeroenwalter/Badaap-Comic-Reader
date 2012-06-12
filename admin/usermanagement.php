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

	// This is a simple way of validating if a user is logged in or not.
	// If the user is logged in, the value is (bool)true - otherwise (bool)false.
  if( !$SimpleUsers->logged_in || !$SimpleUsers->is_admin)
	{
		header("Location: ../login.php");
		exit;
	}


	// If the user is logged in, we can safely proceed.
	$users = $SimpleUsers->getUsers();
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Badaap Comic Reader</title>
	  <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	  <link rel="stylesheet" href="../resources/css/users.css" type="text/css">
    <script type="text/javascript">
    function onDeleteUser(userid)
      {
        if (confirm("Are you sure you want to delete the user?"))
        {
          window.location = "deleteuser.php?userId=" + userid;
          return false;
        }
      }
    </script>
	</head>
	<body>
    <h1>Badaap Comic Reader</h1>
		<h2>User administration</h2>
    <p>DO NOT DELETE THE LAST USER WITH ADMINISTRATOR ACCESS LEVEL !!!!!</p>
    </p>
		<table cellpadding="0" cellspacing="0" border="1">
			<thead>
				<tr>
					<th>Username</th>
          <th>Admin</th>
					<th>Last activity</th>
					<th>Created</th>
					<th></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5" class="right">
						<a href="newuser.php">Create new user</a> | <a href="../logout.php">Logout</a>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach( $users as $user ): ?>
				<tr>
					<td><?php echo $user["username"]; ?></td>
					<td class="right"><?php echo $user["level"]; ?></td>
          <td class="right"><?php echo $user["activity"]; ?></td>
					<td class="right"><?php echo $user["created"]; ?></td>
					<td class="right"><?php if ($SimpleUsers->userId != $user["id"]) {?><a onclick="onDeleteUser(<?php echo $user["id"]; ?>)" href="#">Delete</a><?php }?> | <a href="userinfo.php?userId=<?php echo $user["id"]; ?>">User info</a> | <a href="changepassword.php?userId=<?php echo $user["id"]; ?>">Change password</a></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	</body>
</html>