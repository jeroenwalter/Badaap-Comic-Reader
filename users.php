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
  Original code: https://github.com/Repox/SimpleUsers
  Used with permission from the original author Dan Storm.
*/

  require_once(dirname(__FILE__)."/comicsdb.php");

  // User level
  define("UL_USER", 0);
  define("UL_ADMIN", 1);

  

  /**
   * This file is part of SimpleUsers.
   *
   */

  /**
  * SimpleUsers is a small-scale and flexible way of adding
  * user management to your website or web applications.
  */

  class SimpleUsers
  {

    private $db; // the SQLite database connection
    private $sessionName = "SimpleUsers";
    public $logged_in = false;
    public $is_admin = false;
    public $userdata;
    public $reservedKeys;

    /**
    * Object construct verifies that a session has been started and that a SQLite3 connection can be established.
    * It takes no parameters.
    *
    * @exception  Exception  If a session id can't be returned.
    */

    public function __construct()
    {
      global $comicsdb;

      $this->reservedKeys = array("id", "username", "password", "salt", "activity", "created", "level");
      $this->db = $comicsdb->get();
      
      $sessionId = session_id();
      if( strlen($sessionId) == 0)
        throw new Exception("No session has been started.\n<br />Please add `session_start();` initially in your file before any output.");

      $this->_validateUser();
      $this->_populateUserdata();
      $this->_updateActivity();
    }

      
    /**
    * Returns a (int)user id, if the user was created succesfully.
    * If not, it returns (bool)false.
    *
    * @param  username  The desired username
    *  @param  password  The desired password
    *  @return  The user id or (bool)false (if the user already exists)
    */

    public function createUser( $username, $password, $level = 0 )
    {
      $username = SQLite3::escapeString($username);
      $salt = $this->_generateSalt();
      $password = sha1($salt . $password);

      $success = $this->db->exec("INSERT INTO user VALUES (NULL, '".$username."', '".$password."', '".$salt."', (DATETIME('now')), (DATETIME('now')), ".$level.")");

      if ($success)
        return $this->db->lastInsertRowID();
        
      return false;
    }

    /**
    * Pairs up username and password as registrered in the database.
    * If the username and password is correct, it will return (int)user id of
    * the user which credentials has been passed and set the session, for
    *  use by the user validating.
    *
    * @param  username  The username
    * @param  password  The password
    * @return  The (int)user id or (bool)false
    */

    public function loginUser( $username, $password )
    {
      // get salt
      $username = SQLite3::escapeString($username);
      $result = $this->db->querySingle("SELECT id, salt, password, level FROM user WHERE username='" . $username . "' LIMIT 1", true);
      if (!$result)
        return false; // user not found
      
      $password = sha1($result["salt"]. $password);
      $userId =  $result["id"];
      
      if (!$userId || $password != $result["password"])
        return false;

      $_SESSION[$this->sessionName]["userId"] = $userId;
      $_SESSION[$this->sessionName]["userLevel"] = $result["level"];
      $this->logged_in = true;
      $this->is_admin = $result["level"] == 1;

      return $userId;
    }

    public function isReservedKey($key)
    {
      return in_array($key, $this->reservedKeys);
    }
    /**
    * Sets an information pair, consisting of a key name and that keys value.
    * The information can be retrieved with this objects getInfo() method.
    *
    * @param  key  The name of the key
    * @param  value  The keys value
    * @param  userId  Can be used if administrative control is needed
    * @return  This returns (bool)true or false.
    */

    public function setInfo( $key, $value, $userId = null)
    {
      if ($userId == null)
      {
        if( !$this->logged_in )
          return false;
      }

      // all columns from user table are reserved...
      if ( $this->isReservedKey($key) )
        throw new Exception("User information key \"".$key."\" is reserved for internal use!");

      if( $userId == null )
        $userId = $_SESSION[$this->sessionName]["userId"];

      $value = SQLite3::escapeString("".$value);
      
      if( $this->hasInfo($key, $userId) )
      {
        $key = SQLite3::escapeString($key);
        $success = $this->db->exec("UPDATE user_settings SET value='".$value."' WHERE key='".$key."' AND user_id=".$userId);
        if( !$success )
        {
          throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
        }
      }
      else
      {
        $key = SQLite3::escapeString($key);
        $success = $this->db->exec("INSERT INTO user_settings VALUES (".$userId.", '".$key."', '".$value."')");
        if( !$success )
        {
          throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
        }
      }

      return true;
    }

    /**
    * Use this function to retrieve user information attached to a certain user
    * that has been set by using this objects setInfo() method.
    *
    * @param  key  The name of the key you wan't the value from
    *  @param  userId  Can be used if administrative control is needed
    * @return  String with a given keys value or (bool) false if the user isn't logged in.
    */

    public function getInfo( $key, $userId = null )
    {
      if( $userId == null )
      {
        if( !$this->logged_in )
          return false;

        $userId = $_SESSION[$this->sessionName]["userId"];
      }

      $value = $this->db->querySingle("SELECT value FROM user_settings WHERE user_id=".$userId." AND key='".SQLite3::escapeString($key)."' LIMIT 1", true);
      if (count($value) == 0)
        return NULL;
      
      return $value["value"];
    }
    
    public function hasInfo( $key, $userId = null )
    {
      $value = $this->getInfo($key, $userId);
      return gettype($value) == "string";
    }
    
    /**
    * Use this function to permanently remove information attached to a certain user
    * that has been set by using this objects setInfo() method.
    *
    * @param  key  The name of the key you wan't the value from
    *  @param  userId  Can be used if administrative control is needed
    * @return  (bool) true on success or (bool) false if the user isn't logged in.
    */

    public function removeInfo( $key, $userId = null )
    {
      if( $userId == null )
      {
        if( !$this->logged_in )
          return false;

        $userId = $_SESSION[$this->sessionName]["userId"];
      }

      $success = $this->db->exec("DELETE FROM user_settings WHERE user_id=".$userId." AND key='".SQLite3::escapeString($key)."' LIMIT 1");

      return $success;
    }        


    /**
    * Use this function to retrieve all user information attached to a certain user
    * that has been set by using this objects setInfo() method into an array.
    *
    *  @param  userId  Can be used if administrative control is needed
    * @return  An associative array with all stored information
    */

    public function getInfoArray( $userId = null )
    {
      if( $userId == null )
        $userId = $_SESSION[$this->sessionName]["userId"];

      $result = $this->db->query("SELECT key, value FROM user_settings WHERE user_id=".$userId." ORDER BY key ASC");
      
      $userInfo = array();
      
      while ($res = $result->fetchArray(SQLITE3_ASSOC))
      {
        $userInfo[$res["key"]] = "".$res["value"];
      }
      
      $result->finalize();
      
      $user = $this->getSingleUser($userId);
      $userInfo = array_merge($userInfo, $user);
      asort($userInfo);

      return $userInfo;
    }

    /**
    * Logout the active user, unsetting the userId session.
    * This is a void function
    */

    public function logoutUser()
    {
      if( isset($_SESSION[$this->sessionName]) )
        unset($_SESSION[$this->sessionName]);

      $this->logged_in = false;
    }

    /**
    * Update the users password with this function.
    * Generates a new salt and a sets the users password with the given parameter
    *
    * @param  password  The new password
    * @param  userId  Can be used if administrative control is needed
    */

    public function setPassword( $password, $userId = null )
    {
      if( $userId == null )
        $userId = $_SESSION[$this->sessionName]["userId"];

      $salt = $this->_generateSalt();
      $password = sha1($salt . $password);

      $success = $this->db->exec("UPDATE user SET password='".$password."', salt='".$salt."' WHERE id=".$userId);
      if( !$success )
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }

      return true;
    }
    
    public function setLevel( $level, $userId = null )
    {
      if( $userId == null )
        $userId = $_SESSION[$this->sessionName]["userId"];

      $success = $this->db->exec("UPDATE user SET level=".$level." WHERE id=".$userId);
      if( !$success )
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }

      return true;
    }

    /**
    * Returns an array with each user in the database.
    *
    * @return  An array with user information
    */

    public function getUsers()
    {
      $result = $this->db->query("SELECT DISTINCT id, username, level, activity, created FROM user ORDER BY username ASC");

      if($result === false)
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }
      
      $users = array();
      $i = 0;
      while ($res = $result->fetchArray(SQLITE3_ASSOC))
      {
        $users[$i] = $res;
        $i++;
      }
      
      $result->finalize();

      return $users;
    }

    /**
    * Gets the basic info for a single user based on the userId
    *
    * @param  userId  The users id
    * @return  An array with the result or (bool)false.
    */

    public function getSingleUser( $userId = null )
    {
      if( $userId == null )
        $userId = $_SESSION[$this->sessionName]["userId"];

      $result = $this->db->querySingle("SELECT id, username, activity, created, level FROM user WHERE id=".$userId." LIMIT 1", true);
      
      if($result === false)
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }
      
      if (count($result) == 0)
        return false;

      return $result;

    }

    /**
    * Deletes all information regarding a user.
    * This is a void function.
    *
    * @param  userId  The userId of the user you wan't to delete
    * @return Boolean true if the user was successfully deleted. false if user was not found.
    */

    public function deleteUser( $userId )
    {
      if ($userId == $_SESSION[$this->sessionName]["userId"])
      {
        // you can't delete yourself
        return false;
      }
      
      if ($userId == null || !$this->getSingleUser($userId))
      {
        // user not found
        return false;
      }
        
      $success = $this->db->exec("DELETE FROM user WHERE id=".$userId);
      if( !$success )
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }

      // user_settings are deleted via reference cascading
      /*
      $success = $this->db->exec("DELETE FROM user_settings WHERE user_id=".$userId);
      if( !$success )
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }
      */

      return true;
    }

    /**
    * Returns a hidden input field with a unique token value
    * for CSRF to be used with post data.
    * The token is saved in a session for later validation.
    * 
    * @param  xhtml  set to (bool) true for xhtml output
    * @return Returns a string with a HTML element and attributes
    */
    
    public function getToken( $xhtml = true )
    {
      $token = $this->_generateSalt();
      $name = "token_".md5($token);
      
      $_SESSION[$this->sessionName]["csrf_name"] = $name;
      $_SESSION[$this->sessionName]["csrf_token"] = $token;
      
      $string = "<input type=\"hidden\" name=\"".$name."\" value=\"".$token."\"";
      if($xhtml)
        $string .= " />";
      else
        $string .= ">";
      
      return $string;
    }    
    
    /**
    * Use this method when you wish to validate the CSRF token from your post data.
    * The method returns true upon validation, otherwise false. 
    *
    * @return bool true or false
    */
    
    public function validateToken()
    {
      $name = $_SESSION[$this->sessionName]["csrf_name"];
      $token = $_SESSION[$this->sessionName]["csrf_token"];
      unset($_SESSION[$this->sessionName]["csrf_token"]);
      unset($_SESSION[$this->sessionName]["csrf_name"]);
      
      if($_POST[$name] == $token)
        return true;
        
      return false;
    }    

    /**
    * This function updates the users last activity time
    * This is a void function.
    */

    private function _updateActivity()
    {
      if( !$this->logged_in )
        return;

      $userId = $_SESSION[$this->sessionName]["userId"];

      $success = $this->db->exec("UPDATE user SET activity=(DATETIME('now')) WHERE id=".$userId);
      if (!$success)
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }

      return;
    }

    /**
    * Validates if the user is logged in or not.
    * This is a void function.
    */

    private function _validateUser()
    {
      if( !isset($_SESSION[$this->sessionName]["userId"]) )
        return;

      if( !$this->_validateUserId() )
        return;

      $this->userId = $_SESSION[$this->sessionName]["userId"];
      $this->logged_in = true;
      $this->is_admin = $_SESSION[$this->sessionName]["userLevel"];
    }

    /**
    * Validates if the user id, in the session is still valid.
    *
    * @return  Returns (bool)true or false
    */

    private function _validateUserId()
    {
      if( !isset($_SESSION[$this->sessionName]["userId"]) )
        return false;
      $userId = $_SESSION[$this->sessionName]["userId"];

      $result = $this->db->querySingle("SELECT id FROM user WHERE id=".$userId." LIMIT 1");
      if ($result === false)
      {
        throw new Exception("SQLite3 statement failed, code ".$this->db->lastErrorCode().": ".$this->db->lastErrorMsg());
      }
      
      if( $result !== null )
        return true;

      $this->logoutUser();

      return false;
    }
    
    /**
    * Populates the current users data information for 
    * quick access as an object.
    *
    * @return void
    */  
    
    private function _populateUserdata()
    {
      $this->userdata = array();
      
      if( $this->logged_in )
      {
        $userId = $_SESSION[$this->sessionName]["userId"];
        $data = $this->getInfoArray();
        foreach($data as $key => $value)
          $this->userdata[$key] = $value;
      }
    }

    /**
    * Generates a 128 len string used as a random salt for
    * securing you oneway encrypted password
    *
    * @return String with 128 characters
    */

    private function _generateSalt()
    {
      $salt = null;

      while( strlen($salt) < 128 )
        $salt = $salt.uniqid(null, true);

      return substr($salt, 0, 128);
    }

  }
  
?>