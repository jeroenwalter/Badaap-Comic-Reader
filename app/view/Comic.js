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

Ext.define('Comic.view.Comic', {
  extend: 'Ext.Container',
  xtype: 'comicview',
  requires: [
        'Comic.view.ImageViewer',
    ],
  config: {
    layout: 'fit',
    items: [
      {
        xtype: 'titlebar',
        itemId: 'comictitle',
        docked: 'top',
        style: 'opacity: 0.8;',
        top: '0px',
        width: '100%',
        title: 'Comic',
        items: [
          {
            //iconCls: 'home',
            //iconMask: true,
            align: 'left',
            itemId: 'backbutton',
            ui: "back",
            text: 'Back'
          },
          /* for debugging purposes:
          {
            //iconCls: 'home',
            //iconMask: true,
            align: 'left',
            itemId: 'logoutbutton',
            text: 'Logout'
          }
          */
        ]
      },
      {
        xtype: 'container',
        itemId: 'imageviewercontainer',
        layout: 'fit',
        items: [
        {
          xtype: 'imageviewer',
          itemId: 'imageviewer',
          style: {
              backgroundColor: '#000'
          },
          imageSrc: 'resources/images/no_image_available.jpg'
        }]

      },
      {
        xtype: 'toolbar',
        docked: 'bottom',
        ui: 'transparent',
        //style: 'opacity: 0.6;',
        bottom: '0px',
        width: '100%',
        height: 75,
        items: [
          {
            xtype: 'sliderfield',
            itemId: 'slider',
            minValue: 0,
            maxValue: 10000,
            //width: '50%',
            flex: 1,
          },
          {
            xtype: 'button',
            itemId: 'infobutton',
            //icon: 'resources/images/info.png',
            iconCls: 'info',
            iconMask: true,
          },
          {
            xtype: 'button',
            itemId: 'settingsbutton',
            //icon: 'resources/images/settings.png',
            iconCls: 'settings',
            iconMask: true,
          },
          {
            xtype: 'button',
            //icon: 'resources/images/arrow_left.png',
            itemId: 'previousbutton',
            iconCls: 'arrow_left',
            iconMask: true,
          },
          {
            xtype: 'button',
            //icon: 'resources/images/arrow_right.png',
            itemId: 'nextbutton',
            iconCls: 'arrow_right',
            iconMask: true,
          }
          /*
          {
            xtype: 'segmentedbutton',
            config: {
            allowDepress: true,
            },
            items: [
              
              
            ]
          }
          */
        ]
      },
      {
        xtype: 'image',
        itemId: 'prevPageIcon',
        left: '10px',
        top: '50%',
        src: 'resources/images/previous-page.png',
        width: 64,
        height: 64,
        hidden: true,
      },
      {
        xtype: 'image',
        itemId: 'nextPageIcon',
        right: '10px',
        top: '50%',
        src: 'resources/images/next-page.png',
        width: 64,
        height: 64,
        hidden: true,        
      }
    ]
  }

});