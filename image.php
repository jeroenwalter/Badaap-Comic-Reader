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
 * Resizes an image if width of image is bigger than the maximum width
 * @return array the imageinfo of the resized image
 * @param $filename String The path where the image is located
 * @param $maxwidth String[optional] The maximum width the image is allowed to be
 */
function resize($filename, $maxwidth = "1024", $maxheight = "768", $outputfunction = NULL)
{
	$inputfunctions = array(
    'image/jpeg'=>'imagecreatefromjpeg',
		'image/png'=>'imagecreatefrompng',
		'image/gif'=>'imagecreatefromgif'
    );
	
  $outputfunctions = array(
    'image/jpeg'=>'imagejpeg',
    'image/png'=>'imagepng',
    'image/gif'=>'imagegif'
    );
    
	$imageinfo = getimagesize($filename);
	$currentheight = $imageinfo[1];
	$currentwidth = $imageinfo[0];
  
	if ($currentwidth <= 0 || $currentwidth < $maxwidth)
	{
		return $imageinfo;
	}
  
	$img = $inputfunctions[$imageinfo['mime']]($filename);
  
	$newwidth = $maxwidth;
	$newheight = ($currentheight/$currentwidth)*$newwidth;
	$newimage = imagecreatetruecolor($newwidth,$newheight);
	imagecopyresampled($newimage,$img,0,0,0,0,$newwidth,$newheight,$currentwidth,$currentheight);
  
  if (!$outputfunction)
  {
    $outputfunction = $outputfunctions[$imageinfo['mime']];
  }
	$outputfunction($newimage,$filename);
	return getimagesize($filename);
}

?>
