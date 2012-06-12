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
Ext.define('Comic.store.FileSystem', {
    extend: 'Ext.data.TreeStore',
    requires: [
        'Ext.data.TreeStore',
        'Comic.model.File',
    ],
    
    config: {
    model: 'Comic.model.File',
    storeId: 'FileSystem',
    defaultRootProperty: 'items',
    nodeParam: 'id',
    defaultRootId: "",
    
    /* sorterFn is not triggered correctly, or just doesn't sort correctly.
       For now, let the server sort the items.
    
    sorters: [
        {
            // Sort by folder, then by file
            sorterFn: function(record1, record2) 
            {
              if (record1.data.leaf == record2.data.leaf)
              {
                var name1 = record1.data.name,
                    name2 = record2.data.name;

                return strnatcasecmp(name1, name2);
              }
              else
              if (record1.data.leaf) return 1;
              else return -1;
            },
            direction: 'ASC'
        }
    ],
    */
    
   },    
});