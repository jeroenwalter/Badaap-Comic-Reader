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

Ext.define('Comic.view.About', {
  extend: 'Ext.form.Panel',
  xtype: 'aboutview',

  config: {
    items: [
      {
        xtype: 'fieldset',
        title: 'About',
        items: [
          {
            xtype: 'textfield',
            name: 'title',
            label: 'Title',
            value: 'Badaap Comic Reader',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'version',
            label: 'Version',
            value: '0.1',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'copyright',
            label: 'Copyright',
            value: 'Copyright (c) 2012 Jeroen Walter',
            readOnly: true,
          },
          {
            xtype: 'textfield',
            name: 'website',
            label: 'Website',
            value: 'http://www.badaap.nl/',
            //html: '<a href="http://www.badaap.nl/Badaap Comic Reader" target=_blank>http://www.badaap.nl/</a>',
            readOnly: true
          },
          {
            xtype: 'textfield',
            name: 'manual',
            label: 'Manual',
            value: '',
            html: '<a href="manual/index.html">Open the manual</a>',
            readOnly: true
          },
        ]
      },
      {
        xtype: 'fieldset',
        title: 'License',
        instructions: 'Badaap Comic Reader is free software: you can redistribute it and/or modify<br/>\
  it under the terms of the GNU General Public License as published by<br/>\
  the Free Software Foundation, either version 3 of the License, or<br/>\
  (at your option) any later version.<br/>\
<br/>\
  Badaap Comic Reader is distributed in the hope that it will be useful,<br/>\
  but WITHOUT ANY WARRANTY; without even the implied warranty of<br/>\
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br/>\
  GNU General Public License for more details.<br/>\
<br/>\
  You should have received a copy of the GNU General Public License<br/>\
  along with Badaap Comic Reader.  If not, see http://www.gnu.org/licenses/.',
      }
    ]
  }

});