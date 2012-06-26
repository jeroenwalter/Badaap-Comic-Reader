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
Ext.define('Comic.controller.FileSystem', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
          mainview: 'mainview',
          filesystemview: 'filesystemview',
          // useless refs because there are more than 1:
          //folderlist: 'filesystemview #folderlist',
          //searchfield: 'filesystemview #folderlist searchfield',
          comicview: { selector: 'comicview', xtype: 'comicview', autoCreate: true },
          comicinfoview: { selector: 'comicinfoview', xtype: 'comicinfoview', autoCreate: true },
        },
        
        control: {
          filesystemview: {
            leafitemtap: 'doTapLeafItem',
            itemtap: 'doTapItem',
            //leafitemtaphold: 'doLeafItemTapHold',
            show: 'onShow',
            activeitemchange: 'onActiveitemchange', // triggered when a list is about to be shown, except for first paint of the first root list.
            activate: 'onActivate', // fired when the view is activated (by the tab panel)
            listchange: 'onListchange', // triggered after the list is rendered.
          },
          
          searchfield: {
            clearicontap: 'onSearchClearIconTap',
            keyup: 'onSearchKeyUp'
          },
          
          folderlist: {
            //show: 'onSearchClearIconTap',
            //hide: 'onSearchClearIconTap',
          }
        },
        
    },
    onListchange: function( /*Ext.dataview.NestedList*/ nestedlist, /*Object*/ listitem, /*Object*/ eOpts )
      {
      /* too late here, list is already rendered
        var store = listitem.getStore();
        store.clearFilter();
        var searchfield = listitem.query('searchfield');
        searchfield[0].setValue('');
      */
        //alert('onListchange');

      },
    onActivate: function()
      {
        //alert('onActivate');
      },
    onActiveitemchange: function( /*Ext.Container*/ nestedlist, /*Object/Number*/ value, /*Object/Number*/ oldValue, /*Object*/ eOpts )
      {
        //alert('onActiveitemchange');
        // TODO: detect if we are going up or down in the tree.
        // If up, don't clear the filter. This must be a user setting.
        
        // If the filters of nodes must be kept between list changes, then the filter must be stored with the node,
        // because the list itself (and its searchfield) may be reused, but assigned a different store each time.
        
        var store = value.getStore();
        var newId = store.getNode().getId();
        var oldId = oldValue.getStore().getNode().getId();
        
        if (phpjs.strncmp(newId, oldId, newId.length) != 1)
          return;
        
        store.clearFilter();
        var searchfield = value.query('searchfield');
        searchfield[0].setValue('');
      },
    onShow: function()
      {
        Ext.app.Comics.GetRecent({}, function(result)
        {
          console.log(result);
        });
        
      },
    doTapItem: function(nestedList, list, index, target, record)
      {
      },
      
    doLeafItemTapHold: function(nestedList, list, index, target, record)
      {
        alert('doLeafItemTapHold');
      //  this.getMainview().push(this.getComicinfoview());
      },
      
    doTapLeafItem: function(nestedList, list, index, target, record)
      {
        // don't use routes for now
        //this.redirectTo('comic/' + record.get('comic_id'));
        
        Comic.new_comic_id = record.get('comic').id;
        Comic.context = {};
        Comic.context.source = 'filesystem';
        Comic.context.id = list.getStore().getNode().getId();
        Comic.context.index = index;
        Comic.context.record = record;
        
        this.getMainview().push(this.getComicview());
    
      },
    
    /**
    * Called when the search field has a keyup event.
    *
    * This will filter the store based on the fields content.
    */
    onSearchKeyUp: function(field, /*Ext.EventObject*/ e, /*Object*/ eOpts ) 
    {
        //get the store and the value of the field
        var value = field.getValue(),
            store = field.getParent().getParent().getStore();

        //first clear any current filters on the store
        store.clearFilter();

        //check if a value is set first, as if it isn't we dont have to do anything
        if (value) {
            //the user could have entered spaces, so we must split them so we can loop through them all
            var searches = value.split(' '),
                regexps = [],
                i;

            //loop them all
            for (i = 0; i < searches.length; i++) {
                //if it is nothing, continue
                if (!searches[i]) continue;

                //if found, create a new regular expression which is case insenstive
                regexps.push(new RegExp(searches[i], 'i'));
            }

            //now filter the store by passing a method
            //the passed method will be called for each record in the store
            store.filter(function(record) {
                var matched = [];

                //loop through each of the regular expressions
                for (i = 0; i < regexps.length; i++) {
                    var search = regexps[i],
                        didMatch = record.get('name').match(search);

                    //if it matched the first or last name, push it into the matches array
                    matched.push(didMatch);
                }

                //if nothing was found, return false (dont so in the store)
                if (regexps.length > 1 && matched.indexOf(false) != -1) {
                    return false;
                } else {
                    //else true true (show in the store)
                    return matched[0];
                }
            });
        }
    },

    /**
     * Called when the user taps on the clear icon in the search field.
     * It simply removes the filter form the store
     */
    onSearchClearIconTap: function( /*Ext.field.Input*/ field, /*Ext.EventObject*/ e, /*Object*/ eOpts )
    {
      var store = field.getParent().getParent().getStore();
      store.clearFilter();
    },
       
    
});