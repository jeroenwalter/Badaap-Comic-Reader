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

Ext.define('Comic.view.ComicSettings', {
  extend: 'Ext.form.Panel',
  xtype: 'comicsettingsview',

  config: {
    centered: true,
    hideOnMaskTap: true,
    modal: true,
    padding: '0 10 10 10',
    showAnimation: {
        type: 'popIn',
        duration: 250,
        easing: 'ease-out'
    },
    hideAnimation: {
        type: 'popOut',
        duration: 250,
        easing: 'ease-out'
    },
    centered: true,
    width: Ext.os.deviceType == 'Phone' ? 260 : 600,
    height: Ext.os.deviceType == 'Phone' ? 220 : 500,    
    items: [
      {
        xtype: 'fieldset',
        title: 'Interface Settings',
        items: [
          {
            xtype: 'ios5togglefield',
            label: 'Open current comic at app launch',
            labelWidth: '60%',
            name: 'open_current_comic_at_launch',
          },
          {
            xtype: 'ios5togglefield',
            label: 'Open next comic on comic finish',
            labelWidth: '60%',
            name: 'open_next_comic',
            disabled: true,
          },
          {
            xtype: 'selectfield',
            label: 'Page fit mode',
            labelWidth: '60%',
            name: 'page_fit_mode',
            value: 1,
            options: [
              {
                text: 'Fit width',
                value: 1
              },
              {
                text: 'Full page',
                value: 2
              },
            ]
          },          
          {
            xtype: 'selectfield',
            label: 'Tap to zoom',
            labelWidth: '60%',
            name: 'zoom_on_tap',
            value: 2,
            options: [
              {
                text: 'Off',
                value: 0
              },
              {
                text: 'Single tap',
                value: 1
              },
              {
                text: 'Double tap',
                value: 2
              }
            ]
          },
          {
            xtype: 'selectfield',
            label: 'Toggle nav controls',
            labelWidth: '60%',
            name: 'toggle_paging_bar',
            value: 2,
            options: [
              {
                text: 'Off',
                value: 0
              },
              {
                text: 'Single tap',
                value: 1
              },
              {
                text: 'Double tap',
                value: 2
              }
            ]
          },
          {
            xtype: 'ios5togglefield',
            name: 'use_page_turn_drag',
            label: 'Drag page to change page',
            labelWidth: '60%',
          },
          {
            xtype: 'iconspinnerfield',
            label: 'Page turn drag threshold',
            labelWidth: '60%',
            name: 'page_turn_drag_threshold',
            minValue: 20,
            maxValue: 200,
            increment: 5,
            cycle: true
          },
          {
            xtype: 'ios5togglefield',
            name: 'use_page_change_area',
            label: 'Tap sides to change page',
            labelWidth: '60%',
          },
          {
            xtype: 'iconspinnerfield',
            label: 'Page turn area width',
            labelWidth: '60%',
            name: 'page_change_area_width',
            minValue: 20,
            maxValue: 200,
            increment: 10,
            cycle: true,
          },
          
        ]
      },
      
    ]
  }

});

