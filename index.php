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
require_once(dirname(__FILE__)."/users.php");

$SimpleUsers = new SimpleUsers();

if( !$SimpleUsers->logged_in )
{
  header("Location: login.php");
  exit;
}

require_once("comics.php"); 
?>
<!DOCTYPE HTML>
<html manifest="" lang="en-US">
<html>
<head>
<!--
<script type="text/javascript" src="https://getfirebug.com/firebug-lite.js">
{
    overrideConsole: true,
    startInNewWindow: false,
    startOpened: true,
    enableTrace: true
}
</script>
-->
    <title>Badaap Comic Reader</title>
    
    <meta charset="utf-8">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="expires" content="-1">    
    
<script type="text/javascript">
  // Create global variable
  // If set to true, a call to the global function ShowError(message) will show the message in a separate
  // screen and stop all javascript processing.
  // This is mainly meant to debug the Ext.Direct communication between the browser and the webserver.
  DebugActive = true;
</script>
    
    <!-- The line below must be kept intact for Sencha Command to build your application -->
    <script id="microloader" type="text/javascript" src="sdk/microloader/development.js"></script>        

    
</head>
<body>
  <div id="appLoadingIndicator">
    <div></div>
    <div></div>
    <div></div>
  </div>
</body>
</html>