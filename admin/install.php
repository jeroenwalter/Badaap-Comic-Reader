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

/*
  No actual functionality here yet.
  But these php extension are used by this program, so this shows if they are available...
  
*/
  echo "rar loaded: " . extension_loaded("rar") . "<br/>";
  echo "sqlite3 loaded: " . extension_loaded("sqlite3"). "<br/>";
  echo "gd loaded: " . extension_loaded("gd"). "<br/>";
  echo "SimpleXML loaded: " . extension_loaded("SimpleXML"). "<br/>";
  echo "zip loaded: " . extension_loaded("zip"). "<br/>";
  echo "session loaded: " . extension_loaded("session"). "<br/>";
   
  echo "<br/>";

  // Print them all.
  print_r(get_loaded_extensions());
  
  
?>