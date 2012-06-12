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

/*
  ComicGrid 
  Shows a grid of comic covers.
  The number of rows can be configured.
  
*/

var store = Ext.create('Ext.data.Store',{
		    fields: ['album', 'artist', 'year', 'artwork'],
		    data: [
			    {album: 'Album 100', artist: 'Mirakelman', year:'2005', artwork: './artwork/100_cover.jpg'},
			    {album: 'Album 101', artist: 'Mirakelman', year:'2005', artwork: './artwork/101_cover.jpg'},
          {album: 'Album 102', artist: 'Mirakelman', year:'2005', artwork: './artwork/102_cover.jpg'},
          {album: 'Album 103', artist: 'Mirakelman', year:'2005', artwork: './artwork/103_cover.jpg'},
			    {album: 'Album 104', artist: 'Mirakelman', year:'2005', artwork: './artwork/104_cover.jpg'},
			    {album: 'Album 105', artist: 'Mirakelman', year:'2005', artwork: './artwork/105_cover.jpg'},
          {album: 'Album 106', artist: 'Mirakelman', year:'2005', artwork: './artwork/106_cover.jpg'},
          {album: 'Album 107', artist: 'Mirakelman', year:'2005', artwork: './artwork/107_cover.jpg'},
			    {album: 'Album 108', artist: 'Mirakelman', year:'2005', artwork: './artwork/108_cover.jpg'},
			    {album: 'Album 109', artist: 'Mirakelman', year:'2005', artwork: './artwork/109_cover.jpg'},
			    {album: 'Album 110', artist: 'Mirakelman', year:'2005', artwork: './artwork/110_cover.jpg'},
			    {album: 'Album 111', artist: 'Mirakelman', year:'2005', artwork: './artwork/111_cover.jpg'},
          {album: 'Album 112', artist: 'Mirakelman', year:'2005', artwork: './artwork/112_cover.jpg'},
          {album: 'Album 113', artist: 'Mirakelman', year:'2005', artwork: './artwork/113_cover.jpg'},
			    {album: 'Album 114', artist: 'Mirakelman', year:'2005', artwork: './artwork/114_cover.jpg'},
			    {album: 'Album 115', artist: 'Mirakelman', year:'2005', artwork: './artwork/115_cover.jpg'},
          {album: 'Album 116', artist: 'Mirakelman', year:'2005', artwork: './artwork/116_cover.jpg'},
          {album: 'Album 117', artist: 'Mirakelman', year:'2005', artwork: './artwork/117_cover.jpg'},
			    {album: 'Album 118', artist: 'Mirakelman', year:'2005', artwork: './artwork/118_cover.jpg'},
			    {album: 'Album 119', artist: 'Mirakelman', year:'2005', artwork: './artwork/119_cover.jpg'},
          {album: 'Album 120', artist: 'Mirakelman', year:'2005', artwork: './artwork/120_cover.jpg'},
			    {album: 'Album 121', artist: 'Mirakelman', year:'2005', artwork: './artwork/121_cover.jpg'},
          {album: 'Album 122', artist: 'Mirakelman', year:'2005', artwork: './artwork/122_cover.jpg'},
          {album: 'Album 123', artist: 'Mirakelman', year:'2005', artwork: './artwork/123_cover.jpg'},
			    {album: 'Album 124', artist: 'Mirakelman', year:'2005', artwork: './artwork/124_cover.jpg'},
			    {album: 'Album 125', artist: 'Mirakelman', year:'2005', artwork: './artwork/125_cover.jpg'},
          {album: 'Album 126', artist: 'Mirakelman', year:'2005', artwork: './artwork/126_cover.jpg'},
          {album: 'Album 127', artist: 'Mirakelman', year:'2005', artwork: './artwork/127_cover.jpg'},
			    {album: 'Album 128', artist: 'Mirakelman', year:'2005', artwork: './artwork/128_cover.jpg'},
			    {album: 'Album 129', artist: 'Mirakelman', year:'2005', artwork: './artwork/129_cover.jpg'},
			    {album: 'Album 130', artist: 'Mirakelman', year:'2005', artwork: './artwork/130_cover.jpg'},
			    {album: 'Album 131', artist: 'Mirakelman', year:'2005', artwork: './artwork/131_cover.jpg'},
          {album: 'Album 132', artist: 'Mirakelman', year:'2005', artwork: './artwork/132_cover.jpg'},
          {album: 'Album 133', artist: 'Mirakelman', year:'2005', artwork: './artwork/133_cover.jpg'},
			    {album: 'Album 134', artist: 'Mirakelman', year:'2005', artwork: './artwork/134_cover.jpg'},
			    {album: 'Album 135', artist: 'Mirakelman', year:'2005', artwork: './artwork/135_cover.jpg'},
          {album: 'Album 136', artist: 'Mirakelman', year:'2005', artwork: './artwork/136_cover.jpg'},
          {album: 'Album 137', artist: 'Mirakelman', year:'2005', artwork: './artwork/137_cover.jpg'},
			    {album: 'Album 138', artist: 'Mirakelman', year:'2005', artwork: './artwork/138_cover.jpg'},
			    {album: 'Album 139', artist: 'Mirakelman', year:'2005', artwork: './artwork/139_cover.jpg'},                    
			]
		});
    
Ext.define('Comic.view.ComicGrid', {
    extend: 'Ext.ux.Cover',
    xtype: 'comicgrid',
    
    requires: ['Ext.ux.Cover'],
    
    config: {
      store: store,
      itemCls: 'album-cover',
      itemTpl: [
        '<div class="artwork">',
        '	<tpl if="artwork"><img src="{artwork}">',
        '	<tpl else><img src="./artwork/album.png"></tpl>',
        '</div>',
        '<div class="description">{album} <br/> {artist} ({year})</div>'
      ],
      
      items: [
        {
          xtype: 'titlebar',
          docked: 'top',
          title: 'Recently added',
          items: [
            {
              //iconCls: 'home',
              //iconMask: true,
              align: 'right',
              itemId: 'backbutton',
              ui: "back",
              text: 'Bla'
            }
          ]
        }
      ],
    }

});
