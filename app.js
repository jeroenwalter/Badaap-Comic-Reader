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

Ext.Loader.setConfig({
 // enabled : true,
  disableCaching: false, // disable caching mechanism disabled so we can debug js files....
  paths   : {
    'Comic': 'app',
    'Ext.ux': 'lib/ux',
  } 
});

//Ext.syncRequire( 'ExtDirectAPI' );

/*
  This creates the application.
  The application object is a global variable named 'Comic'.
  
*/
//Ext.Loader.loadScriptFile('ExtDirectAPI.js', function() {
Ext.application({
    name: 'Comic',
    
    icon: {
        //'57': 'resources/icons/Icon.png',
        //'72': 'resources/icons/Icon~ipad.png',
        '72': 'resources/icons/ConceptLogo72.png',
        //'114': 'resources/icons/Icon@2x.png',
        '114': 'resources/icons/ConceptLogo114.png',
        //'144': 'resources/icons/Icon~ipad@2x.png'
    },
    
    
    isIconPrecomposed: true,

    startupImage: {
        '320x460': 'resources/startup/320x460.jpg',
        '640x920': 'resources/startup/640x920.png',
        '768x1004': 'resources/startup/768x1004.png',
        '748x1024': 'resources/startup/748x1024.png',
        '1536x2008': 'resources/startup/1536x2008.png',
        '1496x2048': 'resources/startup/1496x2048.png'
    },
    
    views: ['Main', 'ComicInfo'],
    
    controllers: [
      'Main',
      'Comic',
      'FileSystem',
      'Settings',
      'Database',
      'Recent',
      'ComicInfo',
      'ComicSettings',
    ],
    stores: [
      'FileSystem', 'Recent'
    ],
    
    requires: [
      'ExtDirectAPI',
      
      'Comic.Scrollerfix',
      'Comic.Remotingproviderfix',
      //'Comic.NestedListFix', 
      'Ext.ux.IOS5Toggle',
      'Ext.ux.IconSpinner',
      'Ext.MessageBox',
      'Comic.view.Error',
    ],

    //profiles: ['Phone', 'Tablet'],
    models: ['Comic', 'File', 'Recent'],

    launch: function()
    {
      // Destroy the #appLoadingIndicator element
      Ext.fly('appLoadingIndicator').destroy();

      // Initialize the main view
      Ext.Viewport.add(Ext.create('Comic.view.Main'));
    },
    
    onUpdated: function() {
        Ext.Msg.confirm(
            "Application Update",
            "This application has just successfully been updated to the latest version. Reload now?",
            function(buttonId) {
                if (buttonId === 'yes') {
                    window.location.reload();
                }
            }
        );
    }
});

//});

