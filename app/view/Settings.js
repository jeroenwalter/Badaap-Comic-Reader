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

Ext.define('Comic.view.Settings', {
  extend: 'Ext.form.Panel',
  xtype: 'settingsview',
  requires: [
        'Ext.form.FieldSet',
        'Ext.field.Select',
        'Ext.ux.IOS5Toggle',
        'Ext.ux.IconSpinner',
    ],

  config: {
    items: [
      {
        xtype: 'fieldset',
        title: 'User',
        items: [
          {
            xtype: 'textfield',
            name: 'login',
            label: 'Login',
            disabled: true
          },
          {
            xtype: 'textfield',
            name: 'name',
            label: 'Name',
            readOnly: false
          },
          /*
          {
            xtype: 'textfield',
            name: 'email',
            label: 'Email',
            readOnly: false
          },
          */
          /*
          {
            xtype: 'passwordfield',
            name: 'password',
            label: 'Password',
            readOnly: true
          }
          */
        ]
      },
      {
        xtype: 'fieldset',
        title: 'Interface',
        items: [
          {
            xtype: 'ios5togglefield',
            label: 'Open current comic at app launch',
            name: 'open_current_comic_at_launch',
          },
          {
            xtype: 'ios5togglefield',
            label: 'Open next comic on comic finish',
            name: 'open_next_comic',
            disabled: true,
          },
          {
            xtype: 'selectfield',
            label: 'Page fit mode',
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
            label: 'Drag page to change page'
          },
          {
            xtype: 'iconspinnerfield',
            label: 'Page turn drag threshold (px)',
            name: 'page_turn_drag_threshold',
            minValue: 20,
            maxValue: 200,
            increment: 5,
            cycle: true
          },
          {
            xtype: 'ios5togglefield',
            name: 'use_page_change_area',
            label: 'Tap sides to change page'
          },
          {
            xtype: 'iconspinnerfield',
            label: 'Page turn area width (px)',
            name: 'page_change_area_width',
            minValue: 20,
            maxValue: 200,
            increment: 10,
            cycle: true,
          },
          
        ]
      },
      
      
      {
        xtype: 'toolbar',
        docked: 'top',
        ui: 'light',
        title: 'Settings',
        items: [
          {
            xtype: 'spacer'
          },
          {
            xtype: 'button',
            itemId: 'reset',
            text: 'Reset'
          },
          {
            xtype: 'button',
            itemId: 'save',
            text: 'Save'
          }
        ]
      }
    ]
  }

});