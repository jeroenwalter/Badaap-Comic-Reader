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
Ext.define('Comic.controller.Main', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
          mainview: 'mainview',
          maintabpanel: 'mainview tabpanel',
          filesystemview: 'filesystemview',
          comicview: { selector: 'comicview', xtype: 'comicview', autoCreate: true },
          errorview: { selector: 'errorview', xtype: 'errorview', autoCreate: true },
        },
        
        control: {
          
          
        },
        
        // don't use routes for now
        /*
        routes: {
            'comic/:id': 'showComicById',
        },
        */
    },

    init: function()
    {
      var me = this;
      Main = this;
      
      // For debugging purposes:
      // Create the global function ShowError
      ShowError = function(html) 
        { 
          if (typeof(DebugActive) == 'undefined' || !DebugActive)
          {
            console.log(html);
          }
          else
          {
            me.showError(html); 
          }
        };
      
      Comic.settings = {};
      Comic.userinfo = {};
      Comic.userinfo.current_comic_id = 0;
      Comic.new_comic_id = 0;
      
      // called before application.launch()
      console.log('Initialized ComicViewer! This happens before the Application launch function is called');
      
      console.log('Retrieving settings...');
      
      Ext.app.Comics.GetSettings(function(provider, response) {
        
        Comic.settings = response.getResult();
        console.log('Settings retrieved.');
        
        Ext.app.Comics.GetUserInfo(function(provider, response) {
          
          Comic.userinfo = response.getResult();
          console.log('UserInfo retrieved.');
          //console.log(Comic.userinfo);
          
          // Now convert the userinfo to correct types
          // TODO: move to server side, add type column to userinfo table?
          Comic.userinfo.page_turn_drag_threshold = parseInt(Comic.userinfo.page_turn_drag_threshold);
          Comic.userinfo.page_change_area_width = parseInt(Comic.userinfo.page_change_area_width);
          Comic.userinfo.toggle_paging_bar = parseInt(Comic.userinfo.toggle_paging_bar);
          Comic.userinfo.current_comic_id = parseInt(Comic.userinfo.current_comic_id);
          Comic.userinfo.zoom_on_tap = parseInt(Comic.userinfo.zoom_on_tap);
          Comic.userinfo.level = parseInt(Comic.userinfo.level);
          
          if (Comic.userinfo.open_current_comic_at_launch==1 && Comic.userinfo.current_comic_id)
          {
            Comic.new_comic_id = Comic.userinfo.current_comic_id;//record.get('comic').id;
            
            //var node = this.getMynestedlist().getStore().getNodeById('ext-record-XX');
            //this.getMynestedlist().goToNode(node);

            Comic.context = {};
            Comic.context.source = 'launch';
            Comic.context.id = Comic.userinfo.current_comic_opened_from_id;
            //var node = me.getFilesystemview().getStore().getNodeById(Comic.userinfo.current_comic_opened_from_id);
            
            //Comic.context.index = index;
            //Comic.context.record = record;
            
            me.getMainview().push(me.getComicview());
          }
        });
      });
    },
      
    showComicById: function(id) {
      Comic.new_comic_id = id;
      this.getMainview().push(this.getComicview());
    },
   
    showError: function(html)
    {
      var errorview = this.getErrorview();
      errorview.setHtml(html + "<hr>Stopped execution because there is a serious error.");
      this.getMainview().push(errorview);
      throw "Stopped execution because there is a serious error.";
    },
});