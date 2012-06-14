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
    The class ComicsDB opens the database and updates the tables.
*/

require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/users.php");


define("APP_VERSION", "0.2"); // numbering will be MAJOR.MINOR

define("COMIC_DB_VERSION", 2);

// Log levels
define("SL_DEBUG", 0);
define("SL_INFO", 1);
define("SL_WARNING", 2);
define("SL_ERROR", 3);

// enum for the Type field of the ComicPageInfo record.
//$ComicPageType = [ 0=>"FrontCover", 1=>"InnerCover", 2=>"Roundup", 3=>"Story", 4=>"Advertisment", 5=>"Editorial", 6=>"Letters", 7=>"Preview", 8=>"BackCover", 9=>"Other", 10=>"Deleted"];

class ComicsDB
{
  protected $db; // the SQLite database connection
  public $settings;
  
  public function __construct()
  {
    global $options;
    $this->db = new SQLite3($options['database'], SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
    $this->db->busyTimeout(1000);
    
    $this->db->exec("PRAGMA foreign_keys=ON;");
    $on = $this->db->querySingle("PRAGMA foreign_keys");
    if ($on != 1)
    {
      echo "SQLite3 FOREIGN KEYS are disabled, please use a different php version.";
      die;
    }
    
    //$this->UpdateDatabase();
    
    //$this->InitUserInfo();
    
    //$this->ClearDatabase();
  }
  
  public function get()
  {
    return $this->db;
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function ClearDatabase()
  {
    $this->db->exec("DROP TABLE IF EXISTS settings;");
    $this->UpdateDatabase();
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function UpdateDatabase()
  {
    global $options;
    $name = $this->db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='settings';");
    
    if ($name != "settings")
    {
      $version = 0;
    }
    else
    {
      $this->ReadSettings();
      $version = (int)$this->settings["version"];
    }
    
    if ($version < 1)
    {
      $this->db->exec("BEGIN TRANSACTION;");
      
      $this->db->exec("DROP TABLE IF EXISTS comic;");
      $this->db->exec("DROP TABLE IF EXISTS ComicInfo;");
      $this->db->exec("DROP TABLE IF EXISTS ComicPageInfo;");
      $this->db->exec("DROP TABLE IF EXISTS user;");
      $this->db->exec("DROP TABLE IF EXISTS user_settings;");
      $this->db->exec("DROP TABLE IF EXISTS comic_progress;");
      
      $this->db->exec("DROP TABLE IF EXISTS settings;");
      $this->db->exec("DROP TABLE IF EXISTS log;");
      
      $this->db->exec("COMMIT TRANSACTION;");
      
      $this->db->exec("VACUUM;");
      
      $this->db->exec("BEGIN TRANSACTION;");
      
      
      
      $this->db->exec("CREATE TABLE comic(
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
          name TEXT NOT NULL, 
          filename TEXT NOT NULL UNIQUE, 
          file_last_modified_time INTEGER, 
          number_of_pages INTEGER, 
          date_added INTEGER NOT NULL DEFAULT (CURRENT_TIMESTAMP), 
          deleted INTEGER DEFAULT 0
          );");
          /*
          from ComicDb.xml:
          <Book Id="fdd6799c-afc0-4e99-8470-5e4127f22de4" File="J:\comic2\50 Girls 50\50 Girls 50 - 01 (of 04) (2011) (Minutemen-XxxX).cbz">
          
          <Added>2012-05-27T21:54:40.4152446+02:00</Added>
      <FileSize>7630722</FileSize>
      <FileModifiedTime>2011-01-16T16:56:14.8229463Z</FileModifiedTime>
      <FileCreationTime>2011-11-06T18:02:45.1442133Z</FileCreationTime>
      */
         
      
      
      $this->db->exec("CREATE TABLE settings(
          key TEXT PRIMARY KEY NOT NULL, 
          value TEXT
          );");
      
      // TODO: add type field to settings + min/max or other validators
      
      
      $this->db->exec("CREATE TABLE log(
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
          severity INTEGER NOT NULL, 
          source TEXT NOT NULL, 
          message TEXT NOT NULL, 
          date INTEGER NOT NULL DEFAULT (CURRENT_TIMESTAMP)
          );");
      
      
     
      //$this->db->exec("INSERT INTO settings (key,value) VALUES ('lazyload_covers','0');"); // if enabled, covers are only loaded when they scroll into view.
      // Lazy load plugin: https://github.com/tuupola/jquery_lazyload
      
      
      // max nr pages to preload
      //$this->db->exec("INSERT INTO settings (key,value) VALUES ('preload_count','1');");
      
      
      //$this->db->exec("INSERT INTO settings (key,value) VALUES ('cache_autoclear','0');");
      
      // filter the filename for a series name
      // regular expression on full path (sans comics_folder path)
      // the filter may return an array of names, these are series -> subseries -> subsubseries
      //$this->db->exec("INSERT INTO settings (key,value) VALUES ('series_filter',NULL);");

      // Create the one and only root folder.
      //$this->db->exec("INSERT INTO folder (name, content_type) VALUES ('".ROOT_FOLDER_NAME."',".FCT_NORMAL.");");
      //$this->db->exec("INSERT INTO folder (name, content_type) VALUES ('".IMPORT_FOLDER_NAME."',".FCT_NORMAL.");");
      
    
      $this->db->exec("CREATE TABLE user(
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
          username TEXT UNIQUE NOT NULL,
          password TEXT NOT NULL,
          salt TEXT NOT NULL,
          activity INTEGER NOT NULL DEFAULT (CURRENT_TIMESTAMP), 
          created INTEGER NOT NULL DEFAULT (CURRENT_TIMESTAMP), 
          level INTEGER DEFAULT 0
          );");
  
      $this->db->exec("CREATE TABLE user_settings(
          user_id INTEGER NOT NULL REFERENCES user(id) ON DELETE CASCADE,
          key TEXT NOT NULL, 
          value TEXT
          );");
          
      /*
        
        last_page_read = 0..(num_pages-1)
      */
      $this->db->exec("CREATE TABLE comic_progress(
        id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
        comic_id INTEGER NOT NULL REFERENCES comic(id) ON DELETE CASCADE, 
        user_id INTEGER NOT NULL REFERENCES user(id) ON DELETE CASCADE,
        date_last_read INTEGER, 
        last_page_read INTEGER DEFAULT 0
        );");

      /*
         ComicRack support
         
         Filled from ComicInfo.xml if found in the comic file.
         
         ComicInfo.xml format:
         http://comicrack.cyolito.com/downloads/comicrack/ComicRack/Support-Files/ComicInfoSchema.zip/
        
         Special fields format:
         YesNo fields: 0: No, 1: Yes, -1: Unknown
         ComicPageType: -1: Unknown
          0: FrontCover
          1: InnerCover
          2: Roundup
          3: Story
          4: Advertisment
          5: Editorial
          6: Letters
          7: Preview
          8: BackCover
          9: Other
          10: Deleted
      
        Manga field:
        Unknown: -1
        No: 0
        Yes: 1
        YesAndRightToLeft: 2
        TODO: Check ComicRack for a complete list of fields, because the ComicInfo.xsd is out of date.
      */
      


      // id field holds the comic id.
      $this->db->exec("CREATE TABLE ComicInfo(
          ComicId INTEGER PRIMARY KEY NOT NULL, 
          Title TEXT NOT NULL DEFAULT '',
          Series TEXT NOT NULL DEFAULT '',
          Number TEXT NOT NULL DEFAULT '',
          Count INTEGER NOT NULL DEFAULT -1,
          Volume INTEGER NOT NULL DEFAULT -1,
          AlternateSeries TEXT NOT NULL DEFAULT '',
          AlternateNumber TEXT NOT NULL DEFAULT '',
          AlternateCount INTEGER NOT NULL DEFAULT -1,
          Summary TEXT NOT NULL DEFAULT '',
          Notes TEXT NOT NULL DEFAULT '',
          Year INTEGER NOT NULL DEFAULT -1,
          Month INTEGER NOT NULL DEFAULT -1,
          Writer TEXT NOT NULL DEFAULT '',
          Penciller TEXT NOT NULL DEFAULT '',
          Inker TEXT NOT NULL DEFAULT '',
          Colorist TEXT NOT NULL DEFAULT '',
          Letterer TEXT NOT NULL DEFAULT '',
          CoverArtist TEXT NOT NULL DEFAULT '',
          Editor TEXT NOT NULL DEFAULT '',
          Publisher TEXT NOT NULL DEFAULT '',
          Imprint TEXT NOT NULL DEFAULT '',
          Genre TEXT NOT NULL DEFAULT '',
          Web TEXT NOT NULL DEFAULT '',
          PageCount INTEGER NOT NULL DEFAULT 0,
          LanguageISO TEXT NOT NULL DEFAULT '',
          Format TEXT NOT NULL DEFAULT '',
          BlackAndWhite TEXT NOT NULL DEFAULT '',
          Manga TEXT NOT NULL DEFAULT '',
          Tags TEXT NOT NULL DEFAULT '',
          Locations TEXT NOT NULL DEFAULT '',
          Characters TEXT NOT NULL DEFAULT '',
          StoryArc TEXT NOT NULL DEFAULT '',
          SeriesGroup TEXT NOT NULL DEFAULT '',
          AgeRating TEXT NOT NULL DEFAULT '',
          Teams TEXT NOT NULL DEFAULT '',
          ScanInformation TEXT NOT NULL DEFAULT ''
        );");
          
         
      $this->db->exec("CREATE TABLE ComicPageInfo(
          ComicId INTEGER PRIMARY KEY NOT NULL, 
          Image INTEGER NOT NULL DEFAULT -1,
          Type TEXT NOT NULL DEFAULT 'Story',
          DoublePage INTEGER NOT NULL DEFAULT 0,
          ImageSize  INTEGER NOT NULL DEFAULT 0,
          Key TEXT NOT NULL DEFAULT '',
          ImageWidth INTEGER NOT NULL DEFAULT -1,
          ImageHeight INTEGER NOT NULL DEFAULT -1
        );");
        
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('version','".COMIC_DB_VERSION."');");
      
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('cache_folder','cache');"); // temporary extracted pages and thumbnails
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('covers_folder','covers');"); 
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('last_scan_time','');");
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('max_width','2048');");
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('thumbnail_size','64');"); // shown in file list
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('small_cover_size','64');"); // shown in small cover grid
      $this->db->exec("INSERT INTO settings (key,value) VALUES ('large_cover_size','128');"); // shown in large cover grid

      // Create default admin user
      $SimpleUsers = new SimpleUsers();
      $SimpleUsers->createUser('admin', 'admin', 1);
      
      $this->db->exec("COMMIT TRANSACTION;");
      
      $this->Log(SL_INFO, "UpdateDatabase", "Database created");
      
      
    }

   
    $this->Log(SL_INFO, "UpdateDatabase", "Database version ". $version);
    
    if ($version < 2)
    {
      // $options["comicsfolder"] is no longer stored in the database.
      $this->db->exec("DELETE FROM settings WHERE key = 'comics_folder';");
    }
    
    if ($version < COMIC_DB_VERSION)
    {
      $this->db->exec("UPDATE settings SET value='".COMIC_DB_VERSION."' WHERE key='version';");
      
      $this->Log(SL_INFO, "UpdateDatabase", "Database updated to version ". COMIC_DB_VERSION);
    }
    
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function Log($severity, $source, $message)
  {
    $this->db->exec("INSERT INTO log (severity, source, message) VALUES (".(int)$severity.",'".SQLite3::escapeString($source)."','".SQLite3::escapeString($message)."');");
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function ClearLog()
  {
    $this->db->exec("DELETE FROM log;");
    $this->db->exec("VACUUM;");
  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function ReadSettings()
  {
    $result = $this->db->query('SELECT * FROM settings');
    while ($res = $result->fetchArray(SQLITE3_ASSOC))
    {
      
      $this->settings[$res["key"]] = $res["value"];
    }
    
    // root_folder_id should be 1....
    //$this->root_folder_id = $this->db->querySingle("SELECT id FROM folder WHERE name='".ROOT_FOLDER_NAME."'");
    //$this->import_folder_id = $this->db->querySingle("SELECT id FROM folder WHERE name='".IMPORT_FOLDER_NAME."'");
    
    $result->finalize();
  }  
};
  
$comicsdb = new ComicsDB();  
$comicsdb->UpdateDatabase();

?>