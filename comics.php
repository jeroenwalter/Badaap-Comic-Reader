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
  
$path = dirname(__FILE__);

// avoid warning about already started sessions.
if( strlen(session_id()) == 0)
{
  session_start();
}  

require_once($path."/users.php");

$SimpleUsers = new SimpleUsers();

if( !$SimpleUsers->logged_in )
{
  exit("User not logged in.");
}

require_once($path."/image.php");



// TODO: automatically clear cache, all files older than some date, or leave at most the 1000 newest files?


// TODO: create VIEWs on the database, for example, make a view with the table folder combined with the parent folder id obtained from folder_items
// TODO: thumbnail page view, using css sprites, i.e. create one big image with all thumbnails of a book? http://css-tricks.com/css-sprites/
// TODO: put the Comics instance in a session variable for the user.


class Comics
{
  protected $db; // the SQLite database connection
  public $settings;
  public $userinfo;
  public $userid;
  public $cbr_supported;
  protected $abs_cache_folder;  // This is the folder in which the comic pages are extracted to
  protected $abs_comics_folder; // This is the absolute path to the folder containing the comic files.
  protected $abs_covers_folder; // This is the absolute path to the folder containing the covers for the comics, folder and the series.
  
  public function __construct()
  {
    global $comicsdb;
    global $options;
    $this->db = $comicsdb->get();
    
    $this->ReadSettings();
    
    $this->InitUserInfo();
    
    // Optional php extensions
    // The php rar extension is needed to read cbr files.
    $this->cbr_supported = extension_loaded("rar");

    // This is the folder in which the comic pages are extracted to
    //$this->abs_cache_folder =  pathinfo(realpath("comics.php"),PATHINFO_DIRNAME) . "/" . $this->settings["cache_folder"];
    $this->abs_cache_folder = realpath($this->settings["cache_folder"]) . DIRECTORY_SEPARATOR;
    
    // This is the absolute path to the folder containing the comic files.
    
    if (realpath($options["comicsfolder"]) === false)
    {
      echo "The comics path '" . $options["comicsfolder"] . "' does not exist."; 
      die;
    }
    
    $this->abs_comics_folder = realpath($options["comicsfolder"]) . DIRECTORY_SEPARATOR;
    $this->abs_covers_folder = realpath($this->settings["covers_folder"]) . DIRECTORY_SEPARATOR;
  }

  public function LogoutUser()
  {
    global $SimpleUsers;
    
    if (isset($_COOKIE['cookname']) && isset($_COOKIE['cookpass']))
    {
      setcookie("cookname", "", time()-2592000, "/");
      setcookie("cookpass", "", time()-2592000, "/");
    }
    
    unset($_SESSION['username']);
    unset($_SESSION['password']);
    
    $SimpleUsers->logoutUser();
  }
  
  public function InitUserInfo()
  {
    global $SimpleUsers;
    $this->userinfo = $SimpleUsers->getInfoArray();
    
    $this->userid = $this->userinfo["id"];
        
    if (!isset($this->userinfo["title"]))
    {
      $this->userinfo["title"] = "Jeroen's Badaap Comic Reader";
      $SimpleUsers->setInfo("title", $this->userinfo["title"]);
    }

    if (!isset($this->userinfo["name"]))
    {
      $this->userinfo["name"] = $this->userinfo["username"];
      $SimpleUsers->setInfo("name", $this->userinfo["name"]);
    }
    
    /*
    if (!isset($this->userinfo["email"]))
    {
      $this->userinfo["email"] = "";
      $SimpleUsers->setInfo("email", $this->userinfo["email"]);
    }
    */
    
    if (!isset($this->userinfo["zoom_on_tap"]))
    {
      $this->userinfo["zoom_on_tap"] = "1"; // 0: off, 1: singletap, 2: doubletap
      $SimpleUsers->setInfo("zoom_on_tap", $this->userinfo["zoom_on_tap"]);
    }
    
    if (!isset($this->userinfo["toggle_paging_bar"]))
    {
      $this->userinfo["toggle_paging_bar"] = "2"; // 0: off, 1: singletap, 2: doubletap
      $SimpleUsers->setInfo("toggle_paging_bar", $this->userinfo["toggle_paging_bar"]);
    }
        
    if (!isset($this->userinfo["page_turn_drag_threshold"]))
    {
      $this->userinfo["page_turn_drag_threshold"] = "75"; // nr pixels to drag at the side (l,r,t,b) of an image to automatically trigger a page turn.
      $SimpleUsers->setInfo("page_turn_drag_threshold", $this->userinfo["page_turn_drag_threshold"]);
    }
    
    
    if (!isset($this->userinfo["page_fit_mode"]))
    {
      $this->userinfo["page_fit_mode"] = "1"; // 1: Fit width, 2: Full page
      $SimpleUsers->setInfo("page_fit_mode", $this->userinfo["page_fit_mode"]);
    }
    
    if (!isset($this->userinfo["open_last_comic_at_launch"]))
    {
      $this->userinfo["open_last_comic_at_launch"] = "1"; // 0: off, 1:on
      $SimpleUsers->setInfo("open_last_comic_at_launch", $this->userinfo["open_last_comic_at_launch"]);
    }
    
    if (!isset($this->userinfo["page_change_area_width"]))
    {
      $this->userinfo["page_change_area_width"] = "50"; // 0: off, >0 width in pixels of the area on the left and right side of the screen, that, when tapped, will cause a page turn.
      $SimpleUsers->setInfo("page_change_area_width", $this->userinfo["page_change_area_width"]);
    }
    
    // Comic that is currently being read.
    if (!isset($this->userinfo["current_comic_id"]))
    {
      $this->userinfo["current_comic_id"] = "";
      $SimpleUsers->setInfo("current_comic_id", $this->userinfo["current_comic_id"]);
    }
    
    // Action to perform when you finish a comic.
    // "0": go back to folder list
    // "1": goto next comic
    // The next comic is only opened if you turn the last page.
    // If you turn back the first page, then you will always go to the folder, instead of opening the previous comic.
    if (!isset($this->userinfo["open_next_comic"]))
    {
      $this->userinfo["open_next_comic"] = "1"; 
      $SimpleUsers->setInfo("open_next_comic", $this->userinfo["open_next_comic"]);
    }
    
    if (!isset($this->userinfo["open_current_comic_at_launch"]))
    {
      $this->userinfo["open_current_comic_at_launch"] = "1"; // 0 or 1
      $SimpleUsers->setInfo("open_current_comic_at_launch", $this->userinfo["open_current_comic_at_launch"]);
    }
    
    if (!isset($this->userinfo["current_comic_opened_from_id"]))
    {
      $this->userinfo["current_comic_opened_from_id"] = "";
      $SimpleUsers->setInfo("current_comic_opened_from_id", $this->userinfo["current_comic_opened_from_id"]);
    }
    
    if (!isset($this->userinfo["current_comic_opened_from_type"]))
    {
      $this->userinfo["current_comic_opened_from_type"] = "";
      $SimpleUsers->setInfo("current_comic_opened_from_type", $this->userinfo["current_comic_opened_from_type"]);
    }
    
  }
  
  public function GetUserInfo()
  {
    return $this->userinfo;
  }
  
  public function SetUserInfo($key, $value)
  {
    global $SimpleUsers;
    if ($SimpleUsers->isReservedKey($key))
    {
      //echo "RESERVED '" .$key . "'\n";
      return false;
    }
      
    if ($SimpleUsers->hasInfo($key) === false)
    {
      //echo "NOT PRESENT '" .$key . "'\n";
      return false;
    }
      
    // add/update
    $this->userinfo[$key] = "".$value;
    return $SimpleUsers->setInfo("".$key, "".$value);
  }
  
  public function SetUserInfos($values)
  {
    foreach ($values as $key => $value)
    {
      $success = $this->SetUserInfo($key, $value);
      /*
      if ($success)
        echo "OK '" .$key . "' : '" .$value . "'\n";
      else
        echo "NOT OK '" .$key . "' : '" .$value . "'\n";
      */
    }
    
    //return $this->userinfo;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function ReadSettings()
  {
    $result = $this->db->query('SELECT * FROM settings');
    while ($res = $result->fetchArray(SQLITE3_ASSOC))
    {
      $this->settings[$res["key"]] = $res["value"];
    }
    
    $result->finalize();
  }

  //////////////////////////////////////////////////////////////////////////////
  public function GetSettings()
  {
    return $this->settings;
  }
  
  public function SetSetting($key, $value)
  {
    if (isset($this->settings[$key]))
    {
      // update
      $this->settings[$key] = $value;
      $this->db->exec("UPDATE settings SET value = '".SQLite3::escapeString($value)."' WHERE key = '".SQLite3::escapeString($key)."'");
    }
    else
    {
      // add
      $this->settings[$key] = $value;
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('".SQLite3::escapeString($key)."','".SQLite3::escapeString($value)."');");
    }
  }
  
  public function SetSettings($values)
  {
    foreach ($values as $key => $value)
    {
      $this->SetSetting($key, "" . $value);
    }
  }

  public function GetComicsCount()
  {
    $query = "SELECT COUNT(*) FROM comic";
    $result = $this->db->querySingle($query, false);
    return $result;
  }
  
  public function GetComics($limit, $offset)
  {
    $comics = array();
    $result = $this->db->query('SELECT * FROM comic LIMIT '. intval($limit). ','. intval($offset));
    while ($res = $result->fetchArray(SQLITE3_ASSOC))
    {
      $comics[] = $res;
    }
    
    $result->finalize();
    return comics;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function Log($severity, $source, $message)
  {
    global $comicsdb;
    $comicsdb->Log($severity, $source, $message);
  }
    
  //////////////////////////////////////////////////////////////////////////////
  public function GetComicsFolder()
  {
    return $this->abs_comics_folder;
  }

  public function GetCoversFolder()
  {
    return $this->settings["covers_folder"];
  }

  //////////////////////////////////////////////////////////////////////////////
  public function UpdateLastScanTime()
  {
    $this->db->exec("UPDATE settings SET value = CURRENT_TIMESTAMP WHERE key = 'last_scan_time'");
    $this->ReadSettings();
  }
  
  
  //////////////////////////////////////////////////////////////////////////////
  // Series management
  
  
  //////////////////////////////////////////////////////////////////////////////
  public function GetSeries($parent_id = NULL)
  {
    $result = $this->db->query("SELECT * FROM series" . ($parent_id ? " WHERE parent = " . $parent_id : ""));
    
    $items = array();
    while ($res = $result->fetchArray(SQLITE3_ASSOC))
      $items[] = $res;
    
    return $items;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function AddSeries($name, $parent_id = NULL)
  {
    // 1. Check if $series already exists
    //    If it exists, return NULL or some error.
    // 2. Check if the $parent_id exists 
    //    If it doesn't exist, return NULL or some error.
    // 3. Insert the series into the database.
    // 4. Return the auto generated key.
    
    $query = "SELECT COUNT(*) FROM series WHERE name = '" . SQLite3::escapeString($name) . "'";
    $count = $this->db->querySingle($query);
    if ($count != 0)
      return NULL;
    
    $query = "INSERT INTO series (name , parent) VALUES ('" . SQLite3::escapeString($name) . "'," . ($parent_id ? $parent_id : "NULL") .")";
    $success = $this->db->exec($query);
    
    if ($success)
    {
      return $this->db->lastInsertRowID();
    }
    else
    {
      echo "Database error, code " . $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg() . "<br/>";
      echo "Query: " . $query . "<br/>";
      return NULL;
    }
  }
  
  
  //////////////////////////////////////////////////////////////////////////////
  public function RemoveSeries($series_id)
  {
    // Remove all references to this series from the comics
    // Remove all sub series and their references.
    
    $query = "DELETE FROM series WHERE id = " . $series_id;
    if ($this->db->exec($query))
    {
      return true;
    }
    else
    {
      $this->Log(SL_ERROR, "RemoveSeries", "Database error, code " . $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg());
      echo "Database error, code " . $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg() . "<br/>";
      echo "Query: " . $query . "<br/>";
      return NULL;
    }
  }
    
  //////////////////////////////////////////////////////////////////////////////
  public function MoveSeries($series_id, $new_parent_id)
  {
    // TODO: check if $series_id is not a parent of $new_parent_id
    $query = "UPDATE series SET parent = " . $new_parent_id . " WHERE id = " . $series_id;
    $this->db->exec($query);
    return true;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function RenameSeries($series_id, $new_name)
  {
    $query = "UPDATE series SET name = '" . SQLite3::escapeString($new_name) . "' WHERE id = " . $series_id;
    $this->db->exec($query);
    return true;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  // Comic management
  public function GetComicExcerpt($comic)
  {
    if ($comic)
    {
      $progress = $this->GetComicProgress($comic["id"]);

      $comic["date_last_read"] = isset($progress["date_last_read"]) ? $progress["date_last_read"] : null;
      $comic["last_page_read"] = isset($progress["last_page_read"]) ? $progress["last_page_read"] : null;
      
      $comicinfo = $this->GetComicInfo($comic["id"]);
      if ($comicinfo)
      {
        $comic["Title"] = $comicinfo["Title"];
        $comic["Series"] = $comicinfo["Series"];
        $comic["Number"] = $comicinfo["Number"];
        $comic["Year"] = $comicinfo["Year"];
        $comic["Month"] = $comicinfo["Month"];
        $comic["Publisher"] = $comicinfo["Publisher"];
      }
    }
    
    return $comic;
  }
  
  public function GetComic($comic_id)
  {
    $query = "SELECT * FROM comic WHERE id = " . $comic_id ." LIMIT 1;";
    $result = $this->db->querySingle($query, true);
    return $this->GetComicExcerpt($result);
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function HasComic($filename)
  {
    $query = "SELECT COUNT(*) FROM comic WHERE filename = '" . SQLite3::escapeString($filename) ."';";
    $result = $this->db->querySingle($query, false);
    return $result == 1;
  }

  public function GetComicInfo($comic_id)
  {
    $query = "SELECT * FROM ComicInfo WHERE ComicId = " . $comic_id ." LIMIT 1;";
    $result = $this->db->querySingle($query, true);
    return $result;
  }
  
  public function SetComicInfo($comicinfo)
  {
    $query = "SELECT COUNT(*) FROM ComicInfo WHERE ComicId = " . $comicinfo["ComicId"] ." LIMIT 1;";
    $result = $this->db->querySingle($query, false);
    if ($result == 1)
    {
      $query = "UPDATE ComicInfo SET ".
      "Title = '" . SQLite3::escapeString($comicinfo["Title"]) . 
      "', Series = '" . SQLite3::escapeString($comicinfo["Series"]) . 
      "', Number = '" . SQLite3::escapeString($comicinfo["Number"]) . 
      "', AlternateSeries = '" . SQLite3::escapeString($comicinfo["AlternateSeries"]) . 
      "', AlternateNumber = '" . SQLite3::escapeString($comicinfo["AlternateNumber"]) . 
      "', Summary = '" . SQLite3::escapeString($comicinfo["Summary"]) . 
      "', Notes = '" . SQLite3::escapeString($comicinfo["Notes"]) . 
      "', Writer = '" . SQLite3::escapeString($comicinfo["Writer"]) .
      "', Penciller = '" . SQLite3::escapeString($comicinfo["Penciller"]) .
      "', Inker = '" . SQLite3::escapeString($comicinfo["Inker"]) .
      "', Colorist = '" . SQLite3::escapeString($comicinfo["Colorist"]) .
      "', Letterer = '" . SQLite3::escapeString($comicinfo["Letterer"]) .
      "', CoverArtist = '" . SQLite3::escapeString($comicinfo["CoverArtist"]) .
      "', Editor = '" . SQLite3::escapeString($comicinfo["Editor"]) .
      "', Publisher = '" . SQLite3::escapeString($comicinfo["Publisher"]) .
      "', Imprint = '" . SQLite3::escapeString($comicinfo["Imprint"]) .
      "', Genre = '" . SQLite3::escapeString($comicinfo["Genre"]) .
      "', Web = '" . SQLite3::escapeString($comicinfo["Web"]) .
      "', LanguageISO = '" . SQLite3::escapeString($comicinfo["LanguageISO"]) .
      "', Format = '" . SQLite3::escapeString($comicinfo["Format"]) .
      "', Tags = '" . SQLite3::escapeString($comicinfo["Tags"]) .
      "', Locations = '" . SQLite3::escapeString($comicinfo["Locations"]) .
      "', Characters = '" . SQLite3::escapeString($comicinfo["Characters"]) .
      "', StoryArc = '" . SQLite3::escapeString($comicinfo["StoryArc"]) .
      "', SeriesGroup = '" . SQLite3::escapeString($comicinfo["SeriesGroup"]) .
      "', AgeRating = '" . SQLite3::escapeString($comicinfo["AgeRating"]) .
      "', Teams = '" . SQLite3::escapeString($comicinfo["Teams"]) .
      "', ScanInformation = '" . SQLite3::escapeString($comicinfo["ScanInformation"]) .
      "', BlackAndWhite = '" . SQLite3::escapeString($comicinfo["BlackAndWhite"]) .
      "', Manga = '" . SQLite3::escapeString($comicinfo["Manga"]) .
      "', Count = " . (int)$comicinfo["Count"] . 
      ", Volume = " . (int)$comicinfo["Volume"] .
      ", AlternateCount = " . (int)$comicinfo["AlternateCount"] .
      ", Year = " . (int)$comicinfo["Year"] .
      ", Month = " . (int)$comicinfo["Month"] .
      ", PageCount = " . (int)$comicinfo["PageCount"] .
      " WHERE ComicId = " . $comicinfo["ComicId"];
      $success = $this->db->exec($query);
    }
    else
    {
      $query = "INSERT INTO ComicInfo (ComicId, Title, Series, Number, AlternateSeries, AlternateNumber, Summary, Notes, Writer, Penciller, Inker, Colorist, Letterer, CoverArtist, Editor, Publisher, Imprint, Genre, Web, LanguageISO, Format, Tags, Locations, Characters, StoryArc, SeriesGroup, AgeRating, Teams, ScanInformation, BlackAndWhite, Manga, Count, Volume, AlternateCount, Year, Month, PageCount) VALUES (" . $comicinfo["ComicId"] . 
      ", '" . SQLite3::escapeString($comicinfo["Title"]) .
      "', '" . SQLite3::escapeString($comicinfo["Series"]) .
      "', '" . SQLite3::escapeString($comicinfo["Number"]) .
      "', '" . SQLite3::escapeString($comicinfo["AlternateSeries"]) .
      "', '" . SQLite3::escapeString($comicinfo["AlternateNumber"]) .
      "', '" . SQLite3::escapeString($comicinfo["Summary"]) .
      "', '" . SQLite3::escapeString($comicinfo["Notes"]) .
      "', '" . SQLite3::escapeString($comicinfo["Writer"]) .
      "', '" . SQLite3::escapeString($comicinfo["Penciller"]) .
      "', '" . SQLite3::escapeString($comicinfo["Inker"]) .
      "', '" . SQLite3::escapeString($comicinfo["Colorist"]) .
      "', '" . SQLite3::escapeString($comicinfo["Letterer"]) .
      "', '" . SQLite3::escapeString($comicinfo["CoverArtist"]) .
      "', '" . SQLite3::escapeString($comicinfo["Editor"]) .
      "', '" . SQLite3::escapeString($comicinfo["Publisher"]) .
      "', '" . SQLite3::escapeString($comicinfo["Imprint"]) .
      "', '" . SQLite3::escapeString($comicinfo["Genre"]) .
      "', '" . SQLite3::escapeString($comicinfo["Web"]) .
      "', '" . SQLite3::escapeString($comicinfo["LanguageISO"]) .
      "', '" . SQLite3::escapeString($comicinfo["Format"]) .
      "', '" . SQLite3::escapeString($comicinfo["Tags"]) .
      "', '" . SQLite3::escapeString($comicinfo["Locations"]) .
      "', '" . SQLite3::escapeString($comicinfo["Characters"]) .
      "', '" . SQLite3::escapeString($comicinfo["StoryArc"]) .
      "', '" . SQLite3::escapeString($comicinfo["SeriesGroup"]) .
      "', '" . SQLite3::escapeString($comicinfo["AgeRating"]) .
      "', '" . SQLite3::escapeString($comicinfo["Teams"]) .
      "', '" . SQLite3::escapeString($comicinfo["ScanInformation"]) .
      "', '" . SQLite3::escapeString($comicinfo["BlackAndWhite"]) .
      "', '" . SQLite3::escapeString($comicinfo["Manga"]) .
      "', "  . (int)$comicinfo["Count"] . 
      ", "   . (int)$comicinfo["Volume"] .
      ", "   . (int)$comicinfo["AlternateCount"] .
      ", "   . (int)$comicinfo["Year"] .
      ", "   . (int)$comicinfo["Month"] .
      ", "   . (int)$comicinfo["PageCount"] .
      ")";
      $success = $this->db->exec($query);
    }
  }
  
  public function GetComicProgress($comic_id)
  {
    $query = "SELECT * FROM comic_progress WHERE comic_id = " . $comic_id ." AND user_id = " . $this->userid . " LIMIT 1;";
    $result = $this->db->querySingle($query, true);
    return $result;
  }
  
  // if returns false, then this comic has never been opened.
  public function HasComicProgress($comic_id)
  {
    $query = "SELECT COUNT(*) FROM comic_progress WHERE comic_id = " . $comic_id ." AND user_id = " . $this->userid . " LIMIT 1;";
    $result = $this->db->querySingle($query, false);
    return $result == 1;
  }
  
  public function SetComicProgress($comic_id, $last_page_read)
  {
    if ($last_page_read === null)
      return;
      
    $query = "SELECT COUNT(*) FROM comic_progress WHERE comic_id = " . $comic_id ." AND user_id = " . $this->userid . " LIMIT 1;";
    $result = $this->db->querySingle($query, false);
    if ($result == 1)
    {
      $query = "UPDATE comic_progress SET date_last_read = CURRENT_TIMESTAMP, last_page_read = " . $last_page_read . " WHERE comic_id = " . $comic_id . " AND user_id = " . $this->userid;
      $success = $this->db->exec($query);
    }
    else
    {
      $query = "INSERT INTO comic_progress (comic_id, user_id, date_last_read, last_page_read) VALUES (" . $comic_id . ", " . $this->userid . ", CURRENT_TIMESTAMP, " . $last_page_read . ")";
      $success = $this->db->exec($query);
    }
      
  }
  
  public function GetComicFromFilename($filename, $add_excerpt = true)
  {
    $query = "SELECT * FROM comic WHERE filename = '" . SQLite3::escapeString($filename) ."';";
    $result = $this->db->querySingle($query, true);
    if ($add_excerpt)
      return $this->GetComicExcerpt($result);
    else
      return $result;
  }
  
  public function GetComicIdFromFilename($filename)
  {
    $query = "SELECT id FROM comic WHERE filename = '" . SQLite3::escapeString($filename) ."';";
    $result = $this->db->querySingle($query, false);
    return $result;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function RemoveComic($comic_id)
  {
    $query = "DELETE FROM comic WHERE id = " . $comic_id;
    $result = $this->db->exec($query);
    return true;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function RenameComic($comic_id, $new_name)
  {
    /* Don't check for unique names, because comics in different series may have the same name.
    $query = "SELECT COUNT(*) FROM comic WHERE name = '" . SQLite3::escapeString($new_name) ."';";
    $result = $this->db->querySingle($query, false);
    if ($result != 0)
      return false;
    */
    
    $query = "UPDATE comic SET name = '".SQLite3::escapeString($new_name)."' WHERE id = " . $comic_id;
    return true;
  }
  
  public function UpdateComicInfo($comic_id, $comic = null)
  {
    if (!$comic)
      $comic = $this->GetComic($comic_id);
    
    if (!$comic)
    {
      exit("Comic id " . $comic_id . " not found!");
      return;
    }
      
    $abs_filename = realpath($this->GetComicsFolder() . DIRECTORY_SEPARATOR . $comic["filename"]);
    list($filelist, $comicinfo) = $this->ParseComicArchive($abs_filename);
    
    if (!$comicinfo)
    {
      // Don't delete the comicinfo record from the database if there is one.
      return;
    }
    
    return $this->InternalUpdateComicInfo($comic, $comicinfo, $filelist);
  }
  
  public function InternalUpdateComicInfo($comic, $comicinfo, $filelist)
  {
    // Add or update the comicinfo record.
    
    $xml = $this->GetComicInfoXml($comic["filename"], $comicinfo);
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xml);
    if ($xml)
    {
      $info = array();
      $info["ComicId"] = $comic["id"];
      $info["Title"] = (string)$xml->Title;
      $info["Series"] = (string)$xml->Series;
      $info["Number"] = (string)$xml->Number;
      $info["AlternateSeries"] = (string)$xml->AlternateSeries;
      $info["AlternateNumber"] = (string)$xml->AlternateNumber;
      $info["Summary"] = (string)$xml->Summary;
      $info["Notes"] = (string)$xml->Notes;
      $info["Writer"] = (string)$xml->Writer;
      $info["Penciller"] = (string)$xml->Penciller;
      $info["Inker"] = (string)$xml->Inker;
      $info["Colorist"] = (string)$xml->Colorist;
      $info["Letterer"] = (string)$xml->Letterer;
      $info["CoverArtist"] = (string)$xml->CoverArtist;
      $info["Editor"] = (string)$xml->Editor;
      $info["Publisher"] = (string)$xml->Publisher;
      $info["Imprint"] = (string)$xml->Imprint;
      $info["Genre"] = (string)$xml->Genre;
      $info["Web"] = (string)$xml->Web;
      $info["LanguageISO"] = (string)$xml->LanguageISO;
      $info["Format"] = (string)$xml->Format;
      $info["Tags"] = (string)$xml->Tags;
      $info["Locations"] = (string)$xml->Locations;
      $info["Characters"] = (string)$xml->Characters;
      $info["StoryArc"] = (string)$xml->StoryArc;
      $info["SeriesGroup"] = (string)$xml->SeriesGroup;
      $info["AgeRating"] = (string)$xml->AgeRating;
      $info["Teams"] = (string)$xml->Teams;
      $info["ScanInformation"] = (string)$xml->ScanInformation;
      $info["BlackAndWhite"] = (string)$xml->BlackAndWhite;
      $info["Manga"] = (string)$xml->Manga;
      $info["Count"] = (int)$xml->Count;
      $info["Volume"] = (int)$xml->Volume;
      $info["AlternateCount"] = (int)$xml->AlternateCount;
      $info["Year"] = (int)$xml->Year;
      $info["Month"] = (int)$xml->Month;
      $info["PageCount"] = (int)$xml->PageCount;
            
      $this->SetComicInfo($info);
    }
    else
    {
      echo "Failed loading the following XML:\n\n" . $xml;
      echo "\n\nlibxml errors: \n";
      foreach(libxml_get_errors() as $error) 
      {
        echo "\t", $error->message;
      }
      die;
    }
  }
  
  //////////////////////////////////////////////////////////////////////////////
  // NB: comic_name must be utf8 encoded.
  public function AddComic($filename)
  {
    $comic_name = utf8_encode(pathinfo($filename, PATHINFO_FILENAME));
    // convert to Windows Unicode
    $filename = utf8_decode($filename);
  
    $abs_filename = realpath($this->GetComicsFolder() . DIRECTORY_SEPARATOR . $filename);
  
    if (!is_file($abs_filename))
    {
      $this->Log(SL_ERROR, "AddComic", "File not found: " . $filename);
      return array("id" => -1, "status" => "FILE_NOT_FOUND");
    }
    
    // fix the dir separators:
    $filename = substr($abs_filename, strlen($this->GetComicsFolder() . DIRECTORY_SEPARATOR)-1);
  
    if (!$this->IsSupportedFormat($abs_filename))
    {
      $this->Log(SL_ERROR, "AddComic", "Unsupported format for file: " . $filename);
      return array("id" => -1, "status" => "UNSUPPORTED_FORMAT");
    }
  
    $path_parts = pathinfo($filename);
    $ext = strtolower($path_parts["extension"]);
    if ((($ext == "cbr" || $ext == "rar") && !Comics::IsRarFile($abs_filename)) || 
       (($ext == "cbz" || $ext == "zip") && !Comics::IsZipFile($abs_filename)))
    {
      $this->Log(SL_WARNING, "AddComic", "Incorrect extension '$ext' for file: $filename");
      return array("id" => -1, "status" => "INCORRECT_FORMAT");
    }
      
    $comic = $this->GetComicFromFilename($filename, false);
    if ($comic)
    {
      if (filemtime($abs_filename) != $comic["file_last_modified_time"])
      {
        // update comic info
        $this->UpdateComicInfo($comic["id"], $comic);
        $this->Log(SL_INFO, "AddComic", "ComicInfo updated: " . $filename);
        return array("id" => $comic["id"], "status" => "COMIC_UPDATED");
      }
      else
      {
        //$this->Log(SL_INFO, "AddComic", "Update skipped: " . $filename);
        return array("id" => $comic["id"], "status" => "COMIC_SKIPPED");
      }
      
    }
      
    list($filelist, $comicinfo) = $this->ParseComicArchive($abs_filename);
    if (count($filelist) == 0)
    {
      $this->Log(SL_ERROR, "AddComic", "Comic has no pages: " . $abs_filename);
      return array("id" => -1, "status" => "EMPTY_COMIC_FILE");
    }
    
    $query = "INSERT INTO comic (name, filename, file_last_modified_time, number_of_pages) VALUES ('".SQLite3::escapeString($comic_name)."', '".SQLite3::escapeString($filename)."', ".filemtime($abs_filename).", ". count($filelist). ");";
    $success = $this->db->exec($query);
    
    if ($success)
    {
      $comic_id = $this->db->lastInsertRowID();
     
      $this->UpdateComicInfo($comic_id);
     
    
      // Extract all the pages of the comic to disk.
      // This should only be done when importing via ajax requests so the php script only processes 1 comic at a time and doesn't time out, while also providing feedback to the user of the import process.
      //$this->ExtractComicArchive($comic_id);
      
      $this->CreateDefaultCover($filename, $comic_id, $filelist[0]);
      
      $this->Log(SL_INFO, "AddComic", "Comic added: " . $filename);
      
      return array("id" => $comic_id, "status" => "OK");
    }
    else
    {
      $this->Log(SL_ERROR, "AddComic", "Database error, code " . $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg());
      
//      echo "Database error, code " . $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg() . "<br/>";
//      echo "Query: " . $query . "<br/>";

      return array("id" => -1, "status" => "DATABASE_ERROR", "code" => $this->db->lastErrorCode() . " : " . $this->db->lastErrorMsg());
    }
  }

  
  
  
  //////////////////////////////////////////////////////////////////
  // File management

  
  // Delete all files in the cache folder
  // Dangerous method !!!
  // TODO: make this a safe method, i.e. only delete folders that lie beneath the path of the current file (i.e. comics.php)
  public function ClearCache()
  {
  /*
    global $options;
    $options['webcache'] = 'cache';
    $options['cachepath'] =  pathinfo(realpath("config.php"),PATHINFO_DIRNAME) . "/" . $options['webcache'];

    function SureRemoveDir($dir, $DeleteMe) 
    {
      if(!$dh = @opendir($dir)) return;
      while (false !== ($obj = readdir($dh))) 
      {
        if($obj=='.' || $obj=='..') continue;
        if(!@unlink($dir.'/'.$obj)) SureRemoveDir($dir.'/'.$obj, true);
      }
      closedir($dh);
      if($DeleteMe) 
      {
        @rmdir($dir);
      }
    }

    // Clear the cache folder.
    SureRemoveDir($options['cachepath'],false);
    */
  }

  // Retrieve sorted list of filenames from a rar file.
  public function ParseCBR($filename)
  {
    global $options;
    if (!$this->cbr_supported)
    {
      $this->Log(SL_WARNING, "ParseCBR", "CBR files not supported, because of missing php_rar extension.");
      return NULL;
    }
    
    $rar_file = rar_open($filename);
    
    if ($rar_file == FALSE)
    {
      $this->Log(SL_WARNING, "ParseCBR", "$filename not a rar file.");
      return NULL;
    }
        
    $entries = rar_list($rar_file);
        
    if ($entries === FALSE)
      die("Failed fetching entries");
    
    $filelist = array();
    $comicinfo = null;
    
    foreach ($entries as $file) 
    {
      if ((!$file->isDirectory()) && ($file->getUnpackedSize() > 0))
      {
        if (preg_match('/(jp(e?)g|gif|png)$/i',$file->getName()))
        {
          $filelist[] = $file->getName();
        }
        
        if (preg_match('/ComicInfo.xml$/i',$file->getName()))
        {
          $comicinfo = $entry["name"];
        }
      }
    }
    
    rar_close($rar_file);
    //natcasesort($filelist);
    sort($filelist);
    return array($filelist, $comicinfo);
  }
  
  // Retrieve sorted list of filenames from a zip file.
  public function ParseCBZ($filename)
  {
    $zip = new ZipArchive();
    $zip->open($filename) or die("cannot open $filename!\n");
    $filelist = array();
    $comicinfo = null;
    
    for ($i = 0; $i < $zip->numFiles; $i++) 
    {
      $entry = $zip->statIndex($i);
      if ($entry["size"] > 0)
      {
        if (preg_match('/(jp(e?)g|png|gif)$/i',$entry["name"]))
        {
          $filelist[] = $entry["name"];
        }
          
        if (preg_match('/ComicInfo.xml$/i',$entry["name"]))
        {
          $comicinfo = $entry["name"];
        }
      }
    }
    
    $zip->close();
    //natcasesort($filelist);
    sort($filelist);
    return array($filelist, $comicinfo);
  }
  
  public function IsRarFile($filename)
  {
    global $options;
    if (!$this->cbr_supported)
      return false;
		
    $level = error_reporting(0);
    $rar_file = rar_open($filename);
    error_reporting($level);
    
    if ($rar_file == FALSE)
    {
      return false;
    }
    
    rar_close($rar_file);
    return true;
  }
  
  public function IsZipFile($filename)
  {
    $level = error_reporting(0);
    $zip = new ZipArchive();
    error_reporting($level);
    if ($zip->open($filename) === TRUE) 
    {
      $zip->close();
      return true;
    } 
    
    return false;
  }
  
  public function ParseComicArchive($filename)
  {
    // Check file type based on first characters in the file....
    
    $path_parts = pathinfo($filename);
    $ext = strtolower($path_parts["extension"]);
    //if ($ext == "cbr" || $ext == "rar")
    if (($ext == "cbr" || $ext == "rar") && Comics::IsRarFile($filename))
    {
      return $this->ParseCBR($filename);
    }
    else
    //if ($ext == "cbz" || $ext == "zip")
    if (($ext == "cbz" || $ext == "zip") && Comics::IsZipFile($filename))
    {
      return $this->ParseCBZ($filename);
    }
    else
    {
      // unsupported format
      return NULL;
    }
  }
  
  
  public function ExtractComicArchive($comic_id)
  {
    $comic = $this->GetComic($comic_id);
    
    if (!$comic)
      return;
      
    list($filelist, $comicinfo) = $this->ParseComicArchive($comic["filename"]);
    
    for ($i = 0; $i < count($filelist); $i++)
      $this->InternalGetPage($comic_id, $i, 1024, $filelist);
  }
  
  public function GetComicInfoXml($filename, $comicinfo)
  {
    $realfilename = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $filename;
    
    if (true || !file_exists($cachepathname))
    {
      if (Comics::IsRarFile($realfilename))
      {
        $rar_file = rar_open($realfilename);
        $entry = rar_entry_get($rar_file, $comicinfo);
        $stream = $entry->getStream();
        $xml = fread($stream, $entry->getUnpackedSize());
        fclose($stream);
        rar_close($rar_file);
        return $xml;
      }
      elseif (Comics::IsZipFile($realfilename))
      {
        $zip = new ZipArchive();
        if ($zip->open($realfilename) === TRUE) 
        {
          $xml = $zip->getFromName($comicinfo);
          $zip->close();
          return $xml;
        } 
      }
    }
    
    return null;
  }
  
  public function CreateDefaultCover($comic_filename, $comic_id, $filename)
  {
    $cachepathname = $this->abs_covers_folder . "/" . $comic_id . "_cover.jpg";
    
    $realfilename = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $comic_filename;
    
    if (true || !file_exists($cachepathname))
    {
      if (Comics::IsRarFile($realfilename))
      {
        $rar_file = rar_open($realfilename);
        $entry = rar_entry_get($rar_file, $filename);
        if (!$entry->isDirectory())
          $entry->extract(false, $cachepathname);
          
        rar_close($rar_file);
      }
      elseif (Comics::IsZipFile($realfilename))
      {
        $zip = new ZipArchive();
        if ($zip->open($realfilename) === TRUE) 
        {
          file_put_contents($cachepathname,$zip->getFromName($filename));
          $zip->close();
        } 
      }
      else
        return;
        
      // remove readonly and hidden flags.
      exec("attrib -r -h \"" . $cachepathname . "\"");
      $size = $this->settings["small_cover_size"];
      resize($cachepathname, $size, $size, "imagejpeg");
    }
  }
  
  public function CreateThumbnails($comic_id, $filelist = NULL)
  {
    $comic = $this->GetComic($comic_id);
    // TODO: check if $page_id is a number and 0 < page_id < comic.numpages
    
    if (!$comic) 
    {
      return false;
    }
  }
  
  // Get page for display
  // Updates the read state of the comic
  public function GetPage($comic_id, $page_id, $max_width)
  {
    $result = $this->InternalGetPage($comic_id, $page_id, $max_width, NULL);
    /*
    if (!isset($result["error"]))
    {
      $this->SetComicProgress($comic_id, $page_id);
    }
    */
    return $result;
  }
  
  /**
   * InternalGetPage
   * If the page is not already present in cache:
   *   - extract the file from archive
   *   - resize image to max_width
   *   - store file in the cache
   * Returns the url to extracted file, the width and height.
   
   
   max_width is not the max width, is it the width of the device.
   TODO: also take device height into account for landscape pages.
   
   */
  public function InternalGetPage($comic_id, $page_id, $max_width, $filelist = NULL)
  {
    $comic = $this->GetComic($comic_id);
    // TODO: check if $page_id is a number and 0 < page_id < comic.numpages
    
    if (!$comic) 
    {
      return array(
        "page" => $page_id,
      	"width" => 100,	
        "height" => 100, 
        "src" => "img/invalid_page.png",
        "error" => "COMIC_NOT_FOUND"
        );
    }

    if ($page_id < 0 || $page_id >= $comic["number_of_pages"])
    {
      return array(
        "page" => $page_id,
      	"width" => 100,	
        "height" => 100, 
        "src" => "img/invalid_page.png",
        "error" => "INVALID_PAGE_NR"
        );
    }
    
    $max_width = $max_width ? $max_width : $this->settings["max_width"];
    
    // Extract the page from the comic archive to a temp file in the cache folder.
    if (!$filelist)
      list($filelist, $comicinfo) = $this->ParseComicArchive($this->GetComicsFolder() . DIRECTORY_SEPARATOR . $comic["filename"]);
    
    $path_parts = pathinfo($comic["filename"]);
    $ext = strtolower($path_parts["extension"]);
    
    $page_filename = $filelist[$page_id];
    $page_path_parts = pathinfo($page_filename);
    $page_ext = strtolower($page_path_parts["extension"]);
    $cachepathname = $this->abs_cache_folder . "/" . $comic_id . "_" . $page_id . "." . $page_ext;
    
    if (!file_exists($cachepathname))
    {
      $realfilename = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $comic["filename"];
      if (Comics::IsRarFile($realfilename))
      {
        $rar_file = rar_open($realfilename);
        $entry = rar_entry_get($rar_file, $page_filename);
        if (!$entry->isDirectory())
          $entry->extract(false, $cachepathname);
          
        rar_close($rar_file);
      }
      elseif (Comics::IsZipFile($realfilename))
      {
        $zip = new ZipArchive();
        if ($zip->open($realfilename) === TRUE) 
        {
          file_put_contents($cachepathname,$zip->getFromName($page_filename));
          $zip->close();
        } 
      }
      else
      {
        return array(
          "page" => $page_id,
          "width" => 100,	
          "height" => 100, 
          "src" => "img/invalid_page.png",
          "error" => "INVALID_FILE_FORMAT"
          );
      }

      $size = resize($cachepathname, $max_width);
    }
    else
    {
      $size = getimagesize($cachepathname);
    }
    
    return array(
      "page" => $page_id,
      "width" => $size[0],
      "height" => $size[1],
      "src" => $this->settings["cache_folder"] . "/" . $comic_id . "_" . $page_id . "." . $page_ext
    );   
  }
  

  // Retrieve a list of imported comics in the filesystem folder
  // $folder relative to comics_folder
  public function FS_GetComics($folder)
  {
    // convert to Windows Unicode
    $folder = utf8_decode($folder);
    
    $real_folder = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $folder;
    $real_folder = rtrim($real_folder,"/\\");

    $root = scandir($real_folder);
    $items = array();
    $items["files"] = array();
    $items["folders"] = array();
    foreach($root as $value)
    {
      if ($value === "." || $value === ".." || $value === ".svn") {continue;}
      if (is_file("$real_folder".DIRECTORY_SEPARATOR."$value")) 
      {
        $items["files"][] = utf8_encode($value);
      }
      else
      {
        // Convert from Windows Unicode to utf8.
        // This way json_encode will work with it. 
        $items["folders"][] = utf8_encode($value);
      }
    }
    
    natcasesort($items["files"]);
    natcasesort($items["folders"]);
  
    return $items;
  }
  
  
  /*
  
   Retrieve a list of all files and folders in the filesystem folder
   Called by ExtDirectAPI.php
   
   --- parameters ---
   example: {folder:"Alan Moore", filter: {folders:true, comics:true, count:true } }
   
   $folder: relative to comics_folder
   $filter: is an object
     "files"=>true, 
     "folders"=>true, 
     "count"=>false, 
     "sort"=>true, 
     "comics"=>true
     
   --- json output ---
   
   {
     "success": true,
     "items" : [
       { "id": "Path\Folder 1", "name": "Folder 1", "leaf": false, "count": 24 },
       { "name": "Folder 2", "leaf": false, "count": 2 },
       { "name": "File 1", "leaf": true },
       { "name": "File 2", "leaf": true },
       
     ]
   }
   
  */
  public function ListFolder(stdClass $params /*$folder, $filter = null*/)
  {
    if (isset($params->id) && $params->id != "root" && $params->id != "FileSystem-")
      $folder = $params->id;
    else
      $folder = ""; // get root folder
      
    if (isset($params->filter))
      $filter = $params->filter;
    else
      $filter = array();
      
    $default_filter = array("files"=>true, "folders"=>true, "count"=>true, "sort"=>true, "comics"=>true);
    $filter = is_object($filter) ? get_object_vars($filter) : (is_array($filter) ? $filter : array());
    $filter = array_merge($default_filter, $filter);
    
    // convert to Windows Unicode
    $folder = utf8_decode($folder);
    
    $real_folder = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $folder;
    $real_folder = rtrim($real_folder,"/\\");

    $root = scandir($real_folder);
    $items = array();
    foreach($root as $value)
    {
      if ($value === "." || $value === ".." || $value === ".svn") {continue;}
      
      $item = array();
      
      // Convert from Windows Unicode to utf8.
      // This way json_encode will work with it. 
      if ($folder == "")
        $item["id"] =  utf8_encode($value);
      else
        $item["id"] =  utf8_encode($folder . DIRECTORY_SEPARATOR . $value);
        
      $item["name"] =  utf8_encode($value);
      
      if (($filter["files"] == true) && (is_file($real_folder . DIRECTORY_SEPARATOR . $value))) 
      {
        if ($filter["comics"])
        {
          $comic = $this->GetComicFromFilename(($folder != "" ? $folder . DIRECTORY_SEPARATOR : "") . $value);
          if ($comic)
          {
            $item["leaf"] = true;
            $item["comic"] = $comic;
            /*
            $item["comic_id"] = $comic["id"];
            $item["comic_name"] = $comic["name"];
            $item["comic_number_of_pages"] = $comic["number_of_pages"];
            $item["comic_date_last_read"] = $comic["date_last_read"];
            $item["comic_last_page_read"] = $comic["last_page_read"];
            */
            
            $items[] = $item;        
          }
        }
        else
        {
          $item["leaf"] = true;
          $item["comic_id"] = -1;
          $items[] = $item;
        }
      }
      elseif ($filter["folders"] == true)
      {
        $item["leaf"] = false;
                
        if ($filter["count"] == true)
        {
          $count = $this->FS_GetContentCount($folder . DIRECTORY_SEPARATOR . $value);
          $item = array_merge($item, $count);
        }
        
        $items[] = $item;
      }
    }
    
    if (isset($filter["sort"]))
    {
      $args = $filter["sort"];
      usort($items, function ($item1, $item2) use($args)
      {
        // folders before items
        if ($item1["leaf"] && !$item2["leaf"]) return 1;
        if (!$item1["leaf"] && $item2["leaf"]) return -1;
        
        return strnatcasecmp($item1["name"], $item2["name"]);
      });
    }
    
    return array("items"=> $items, "success"=>true);
  }
  
  public function GetRecent(stdClass $params)
  {
    // for now, just return the 25 most recently viewed comics.
    /*
    $query = "SELECT comic.*, comic_progress.date_last_read, comic_progress.last_page_read FROM comic, comic_progress WHERE comic.id = comic_progress.comic_id AND comic_progress.user_id = " . $this->userid . " ORDER BY comic_progress.date_last_read DESC LIMIT 25;";
    //$query = "SELECT * FROM comic_progress";// WHERE comic_progress.user_id = " . $this->userid . " ORDER BY comic_progress.date_last_read DESC LIMIT 25;";
    
    $result = $this->db->query($query);
    */
    
    $query = "SELECT comic.* FROM comic, comic_progress WHERE comic.id = comic_progress.comic_id AND comic_progress.user_id = " . $this->userid . " ORDER BY comic_progress.date_last_read DESC LIMIT 25;";
    $result = $this->db->query($query);
    
    $items = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) 
    {
      $row = $this->GetComicExcerpt($row);
      
      $item = array();
      $item["id"] = $row["id"];
      $item["comic"] = $row;
      $items[] = $item;
    }

    
    return $items;
  }
  
  public function FS_GetRealFolder($folder)
  {
    $real_folder = ($folder != "") ? ($this->GetComicsFolder() . DIRECTORY_SEPARATOR . $folder) : ($this->GetComicsFolder());
    $real_folder = rtrim($real_folder,"/\\");
    return $real_folder;
  }
  
  public function FS_GetContentCount($folder)
  {
    $real_folder = $this->FS_GetRealFolder($folder);
    $folder_count = 0;
    $file_count = 0;
    $file = scandir($real_folder);
    
    foreach($file as $key => $value) 
    {
      if ($value === "." || $value === ".." || $value === ".svn") {continue;}
      if (is_file($real_folder . DIRECTORY_SEPARATOR . $value)) 
      {
        $file_count++;
      }
      else
      {
        $folder_count++;
      }
    }
    
    return array( "file_count" => $file_count, "folder_count" => $folder_count);
  }
            
  // returns array of comics that are in the database but not on disk.
  public function MarkObsoleteComics($from, $amount)
  {
    $obsolete = array();
    $count = 0;
    
    if ($from == -1 || $amount == -1)
      $query = "SELECT * FROM comic";
    else
      $query = "SELECT * FROM comic LIMIT " . $from . "," . $amount;
    
    $result = $this->db->query($query);
    while ($row = $result->fetchArray()) 
    {
      $abs_filename = realpath($this->GetComicsFolder() . DIRECTORY_SEPARATOR . $row["filename"]);
      if (!file_exists($abs_filename))
      {
        $success = $this->db->exec("UPDATE comic SET deleted = 1 WHERE id = " . $row["id"]);
        
        $this->Log(SL_INFO, "MarkObsoleteComics", "Obsolete [" . $row["id"]. "] " . $row["filename"]);
        
        $comic = array();
        $comic["id"] = $row["id"];
        $comic["filename"]= $row["filename"];
        $obsolete[] = $comic;
      }
    }
    
    return $obsolete;
  }
  
  public function RemoveObsoleteComics()
  {
    // Delete the covers
    $result = $this->db->query("SELECT id FROM comic WHERE deleted = 1;");
    while ($row = $result->fetchArray()) 
    {
      $cover_filename = $row["id"] . "_cover.jpg";
      $cachepathname = $this->abs_covers_folder . "/" . $cover_filename;      
      unlink($cachepathname);
    }
    
    $this->db->exec("DELETE FROM comic WHERE deleted = 1;");
    $this->db->exec("VACUUM;");    
    $this->Log(SL_INFO, "RemoveObsoleteComics", "Obsolete comics removed.");
  }
  
  // $folder relative to comics_folder
  public function GetComicsInFolder($folder)
  {
    // convert to Windows Unicode
    $folder = utf8_decode($folder);
    
    $real_folder = $this->GetComicsFolder() . DIRECTORY_SEPARATOR . $folder;
    $real_folder = rtrim($real_folder,"/\\");

    $folder = trim($folder,"/\\") . DIRECTORY_SEPARATOR;
    
    $root = scandir($real_folder);
    $items = array();
    
    $items["newfiles"] = array();
    $items["updated"] = array();
    $items["unchanged"] = array();
    $items["folders"] = array();
    $items["unsupported"] = array();
    
    $items["nr_new"] = 0;
    $items["nr_updated"] = 0;
    $items["nr_unchanged"] = 0;
    $items["nr_folders"] = 0;
    $items["nr_unsupported"] = 0;
    foreach($root as $value)
    {
      if ($value === "." || $value === ".." || $value === ".svn") {continue;}
      $abs_filename = realpath("$real_folder".DIRECTORY_SEPARATOR."$value");
      
      if (is_file($abs_filename)) 
      {
        // fix the dir separators:
        if ($this->IsSupportedFormat($abs_filename))
        {
          $filename = substr($abs_filename, strlen($this->GetComicsFolder() . DIRECTORY_SEPARATOR)-1);
          
          $comic = $this->GetComicFromFilename($filename, false);
          if ($comic)
          {
            if (filemtime($abs_filename) != $comic["file_last_modified_time"])
            {
              $items["nr_updated"]++;
              $items["updated"][] = utf8_encode(pathinfo($abs_filename, PATHINFO_BASENAME));
            }
            else
            {
              $items["nr_unchanged"]++;
              //$items["unchanged"][] = utf8_encode(pathinfo($abs_filename, PATHINFO_BASENAME));
            }
          }
          else
          {
            $items["nr_new"]++;
            $items["newfiles"][] = utf8_encode(pathinfo($abs_filename, PATHINFO_BASENAME));
          }
        }
        else
        {
          $items["nr_unsupported"]++;
          $items["unsupported"][] = utf8_encode(pathinfo($abs_filename, PATHINFO_BASENAME));
        }
      }
      else
      {
        // Convert from Windows Unicode to utf8.
        // This way json_encode will work with it. 
        $items["nr_folders"]++;
        $items["folders"][] = utf8_encode($value);
      }
    }
    
    //natcasesort($items["new"]);
    //natcasesort($items["existing"]);
    //natcasesort($items["folders"]);
  
    return $items;
  }
  

  public function IsSupportedFormat($filename)
  {
    global $options;
    $path_parts = pathinfo($filename);
    if (!isset($path_parts["extension"]))
    {
      return;
    }
      
    $ext = strtolower($path_parts["extension"]);

    if (($this->cbr_supported && ($ext == "cbr" || $ext == "rar")) || $ext == "cbz" || $ext == "zip")
    {
      return true;
    }
    else
    {
      // unsupported format
      return NULL;
    }
  }
};


$db = new Comics();

?>