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

Ext.define('Comic.view.Database', {
  extend: 'Ext.form.Panel',
  xtype: 'databaseview',

  config: {
    items: [
      {
        xtype: 'container',
        layout: 'hbox',
        items: [
          {
            xtype: 'button',
            text: 'Start',
            itemId: 'scanButton',
            margin: 10,
            
          },
          {
            xtype: 'button',
            text: 'Pause',
            itemId: 'pauseButton',
            margin: 10,
          },
        ]
      },
      {
        xtype: 'fieldset',
        title: 'Scan progress',
        items: [
          {
            xtype: 'textfield',
            itemId: 'nrComicsField',
            label: '# Comics',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'nrFoldersField',
            label: '# Folders',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'nrFilesField',
            label: '# Files',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'folderField',
            label: 'Folder',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'fileField',
            label: 'File',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'statusField',
            label: 'Status',
            readOnly: true
          },
          {
            xtype: 'textfield',
            itemId: 'etaField',
            label: 'ETA',
            readOnly: true
          },
        ]
      },
      /*
      {
        xtype: 'fieldset',
        title: 'Storage',
        items: [
          {
            xtype: 'textfield',
            itemId: 'comicsfolder',
            label: 'Comics folder'
          },
          {
            xtype: 'textfield',
            itemId: 'cachefolder',
            label: 'Cache folder'
          },
          {
            xtype: 'textfield',
            itemId: 'coversfolder',
            label: 'Covers folder'
          },
          {
            xtype: 'textfield',
            itemId: 'cacheSizeField',
            label: 'Used Cache',
            readOnly: true
          },
        ]
      },
      {
        xtype: 'fieldset',
        title: 'Images',
        items: [
          {
            xtype: 'numberfield',
            itemId: 'maxwidth',
            label: 'Max. width'
          },
          {
            xtype: 'numberfield',
            itemId: 'thumbnailsize',
            label: 'Thumbnail size'
          },
          {
            xtype: 'numberfield',
            itemId: 'smallcoversize',
            label: 'Small cover size'
          },
          {
            xtype: 'numberfield',
            itemId: 'largecoversize',
            label: 'Large cover size'
          },
          {
            xtype: 'checkboxfield',
            itemId: 'preloadimages',
            label: 'Preload images'
          }
        ]
      },
      */
      {
        xtype: 'toolbar',
        docked: 'top',
        ui: 'light',
        title: 'Database',
      }
    ]
  }

});