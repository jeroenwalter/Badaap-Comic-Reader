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
  
// For debugging purposes:
// Report all PHP errors
// As most requests are AJAX requests from Sencha Touch, you will probably find the error in the resonse text of the AJAX requests.....
error_reporting(E_ALL);
ini_set("display_errors", "1");

// Check requirements

if (!extension_loaded("sqlite3"))
{
  exit("sqlite3 extension not loaded. Please check your php.ini");
}

// GD library is needed to create thumbnails
if (!extension_loaded("gd"))
{
  exit("gd extension not loaded. Please check your php.ini");
}

// zip extension is needed for reading cbz files
if (!extension_loaded("zip"))
{
  exit("zip extension not loaded. Please check your php.ini");
}

$options = array();

// It's best to place the database in a folder not reachable via an url....
// But by default, use the application's root folder.

if (defined('BCR_BUILD'))
{
  // BCR is being build by the Sencha tools.
  // Always create a new database with the default admin account.
  // First delete the old one.
  unlink(dirname(__FILE__)."/build.sqlite");
  $options["database"]= dirname(__FILE__)."/build.sqlite"; 
}
else
{
  // Absolute path to the database file. You don't want to have this accessible via your webserver, so this default should be changed.
  $options["database"]= dirname(__FILE__)."/comics.sqlite"; 
}


// Location of the comic files. This must be an absolute path.
$options["comicsfolder"]= "J:\\comics";


?>
