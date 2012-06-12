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

Ext.define('Comic.view.ComicInfo', {
  extend: 'Ext.form.Panel',
  xtype: 'comicinfoview',

  config: {
    items: [
      {
        xtype: 'titlebar',
        docked: 'top',
        ui: 'light',
        title: 'Comic Info',
        items: [
          {
            xtype: 'button',
            itemId: 'backbutton',
            text: 'Back',
            ui: "back",
          }
        ]
      },
      {
        xtype: 'fieldset',
        title: 'Comic Info',
        itemId: 'comicinfofieldset',
        
        items: [
          
          {
            xtype: 'textfield',
            name: 'Filename',
            label: 'Filename',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Title',
            label: 'Title',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Series',
            label: 'Series',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Number',
            label: 'Number',
            readOnly: true
          },
          {
            xtype: 'textareafield',
            name: 'Summary',
            label: 'Summary',
            maxRows: 10,
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Year',
            label: 'Year',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Month',
            label: 'Month',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'PageCount',
            label: 'PageCount',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Writer',
            label: 'Writer',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Publisher',
            label: 'Publisher',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'Genre',
            label: 'Genre',
            readOnly: true
          },
          {
            xtype: 'urlfield',
            name: 'Web',
            label: 'Web',
            readOnly: true
          },
          
        ]
      },
      
    ]
  }

});