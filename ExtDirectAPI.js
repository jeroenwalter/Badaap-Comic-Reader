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
  This class uses a SYNCHRONOUS AJAX request to ensure that the Ext.Direct provider is initialized BEFORE the models and stores and other classes 
  use one of the remote API calls.
*/
Ext.app.REMOTING_API = {};

Ext.define('ExtDirectAPI', {
     extend: 'Ext.Base',
     
     requires: [
      'Ext.direct.Manager',
      'Ext.direct.RemotingProvider',
      'Ext.Ajax',
      'Ext.direct.RemotingEvent'
     ],

  },
  // Class create function, this is (and must be) called before all model classes with an Ext.DirectProxy are instantiated.
  function()
  {
    Ext.namespace( 'Ext.app' );
    
    // Retrieve the remote api via SYNCHRONOUS request. This is very important, otherwise the remote api is not yet initialized when the Ext.Direct proxies of the models are instantiated.
    Ext.Ajax.request({
    async: false,
    url: 'ExtDirectAPI.php?json',
    method: 'GET',
    
    success: function(response)
      {
        eval("Ext.app.REMOTING_API = " + response.responseText);
        
        // Disable batch requests, makes debugging easier.
        Ext.app.REMOTING_API.enableBuffer = false;
        
        /* doesn't work ?
        Ext.app.REMOTING_API.listeners = {
          exception: function(e) { alert(e); }
        };
        */
        Ext.Direct.addProvider( Ext.app.REMOTING_API );
      }
    });
  }
);
 
