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
  The FileSystem view shows the folders and the comics in them as a nested list.
  
*/

// Use one template instance for all list items instead of creating one for each list item separately.....
var TheFileSystemItemTemplate = new Ext.XTemplate(
    '<tpl if="leaf === true"><img src="covers/{comic.id}_cover.jpg" height="60"/>{[this.getTitleText(values.comic)]}<span class="progress">{[this.getProgressText(values.comic)]}</span><span class="date_last_read"><tpl if="comic.date_last_read === null"></tpl><tpl if="comic.date_last_read !== null">{comic.date_last_read}</tpl></span></tpl><tpl if="leaf === false"><img src="resources/images/folder.png" height="60"/>{name}</br><span class="progress">{file_count} | {folder_count}</span></tpl>',
    {
      // XTemplate configuration:
      disableFormats: true,
      // member functions:
      getTitleText: function(comic)
      {
        var s = '';
        if (comic.Series)
          s += comic.Series + ' ' + comic.Number;
          else
          s += comic.name;
          
        if (comic.Title)
          s += ': ' + comic.Title;
        
        if (comic.Year > 0)
          s += '</br>[' + comic.Year + '/' + comic.Month + ']';
          
        return s;
      },
      getProgressText: function(comic)
      {
        if (comic.number_of_pages == 0)
          return "no pages"; // BUG: This comic should never have been added to the database.....
        
        if ((comic.last_page_read + 1) == comic.number_of_pages)
          return "finished";
        else
          return (comic.last_page_read + 1) + "/" + comic.number_of_pages;
      },
    }
); 

    
Ext.define('Comic.view.FileSystem', {
    extend: 'Ext.NestedList',
    xtype: 'filesystemview',
    requires: [
        'Comic.store.FileSystem',
        'Ext.field.Search',
    ],
    config: {
      title: 'Files',
      displayField: 'name',
      store: 'FileSystem',
      
      //padding: '10 10 10 10', // padding for entire list, not for the items....
      //style: 'background: transparent url(resources/background_1.jpg) 0 0;',
            
      // config of each list:
      
      listConfig : {

      itemTpl: TheFileSystemItemTemplate,
      // not working properly:
      //  indexBar    : true,
      //  grouped: true,
        itemId: 'folderlist',
        baseCls: 'filesystem-list',
                
        //onItemDisclosure: true,
        /*
        listeners: {
        
                        itemtaphold: function (list, idx, target, record, evt) {
                            Ext.Msg.alert('itemtaphold', record.data.name);
                        }, // itemtaphold
        
        
                        itemswipe: function (list, idx, target, record, evt) {
                            this.getParent().onSwipe();
                        } // itemswipe
        
                    }, // listeners
        */
        items: [
                {
                  xtype: 'toolbar',
                  docked: 'top',
                  items: [
                    { xtype: 'spacer' },
                    {
                      xtype: 'searchfield',
                      placeHolder: 'Filter...',
                    },
                    { xtype: 'spacer' }
                  ]
                }
            ],
      },
    },
    
    //onItemDisclosure: function() { alert('onItemDisclosure'); },
    
    getTitleTextTpl: function() {
      return '{' + this.getDisplayField() + '}<tpl if="leaf !== true">/</tpl>';
    },
    /*
    onSwipe: function() {
    Ext.Msg.alert('onSwipe');
    }*/
   
});
