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
Ext.define('Comic.store.Settings', {
    extend: 'Ext.data.Store',
    //extend: 'Ext.data.DirectStore',
    requires: [
        'Ext.data.Store',
        'Comic.model.Setting',
    ],
    
    config: {
    model: 'Comic.model.Setting',
    storeId: 'Settings',
    /*
    directFn: Ext.app.Comics.GetSettings,
    paramsAsHash: true,
    idProperty: 'key',
    */
   },    
});