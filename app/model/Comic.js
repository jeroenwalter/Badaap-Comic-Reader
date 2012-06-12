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

Ext.define('Comic.model.Comic', {
     extend: 'Ext.data.Model',
     requires: ['ExtDirectAPI'],
     config: {
      fields: [
          'id', 
          'name', 
          'series_id', 
          'filename', 
          'file_last_modified_time', 
          'number_of_pages', 
          'date_added', 
          'date_last_read', 
          'last_page_read',
          'Series',
          'Number',
          'Title',
          'Year',
          'Month'],
      
      proxy: {
            type: 'direct',
            directFn: 'Ext.app.Comics.GetComic',
            paramsAsHash: true,
      }
    }
 });
