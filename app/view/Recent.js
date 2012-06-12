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
  
// Use one template instance for all list items instead of creating one for each list item separately.....
var TheRecentItemTemplate = new Ext.XTemplate(
    '<img src="covers/{comic.id}_cover.jpg" height="60"/>{[this.getTitleText(values.comic)]}<span class="progress">{[this.getProgressText(values.comic)]}</span><span class="date_last_read">{comic.date_last_read}</span>',
    {
      // XTemplate configuration:
      disableFormats: true,
      // member functions:
      getTitleText: function(comic)
      {
        var s = '';
        if (comic.Series)
          s += comic.Series + ' ' + comic.Number;
        else
          s += comic.name;
          
        if (comic.Title)
          s += ': ' + comic.Title;
        
        if (comic.Year > 0)
          s += '</br>[' + comic.Year + '/' + comic.Month + ']';
          
        return s;
      },
      getProgressText: function(comic)
      {
        if (comic.number_of_pages == 0)
          return "no pages"; // BUG: This comic should never have been added to the database.....
        
        if ((comic.last_page_read + 1) == comic.number_of_pages)
          return "finished";
        else
          return (comic.last_page_read + 1) + "/" + comic.number_of_pages;
      },
    }
); 



Ext.define('Comic.view.Recent', {
    extend: 'Ext.dataview.List',
    xtype: 'recentview',
    
    requires: [ 'Comic.store.Recent' ],
    
    config: {
      itemTpl: TheRecentItemTemplate,
      baseCls: 'filesystem-list',
      title: 'Recent',
      displayField: 'comic.name',
      store: 'Recent',
      items: [
        {
          xtype: 'toolbar',
          docked: 'top',
          ui: 'light',
          title: 'Recent',
        }
      ]
    }
});