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
Ext.define('Comic.controller.ComicSettings', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
          comicview: 'comicview',
          comicsettingsview: 'comicsettingsview',
          zoomOnTap: 'comicsettingsview [name=zoom_on_tap]',
          togglePagingBar: 'comicsettingsview [name=toggle_paging_bar]',
          usePageTurnDrag: 'comicsettingsview [name=use_page_turn_drag]',
          pageTurnDragThreshold: 'comicsettingsview [name=page_turn_drag_threshold]',
          usePageChangeArea: 'comicsettingsview [name=use_page_change_area]',
          pageChangeAreaWidth: 'comicsettingsview [name=page_change_area_width]',
          openNextComic: 'comicsettingsview [name=open_next_comic]',
          openCurrentComicAtLaunch: 'comicsettingsview [name=open_current_comic_at_launch]',
          pageFitMode: 'comicsettingsview [name=page_fit_mode]',
        },
        
        control: {
          comicsettingsview: {
            show: 'onShow',
            hide: 'onHide'
          },
          
          usePageTurnDrag: {
            change: 'onChangeUsePageTurnDrag',
          },
          usePageChangeArea: {
            change: 'onChangeUsePageChangeArea',
          },
          
          
        },
    },

    launch: function()
    {
      // called before application.launch()
    },
      
    onShow: function() {
      var me = this,
          comicsettingsview = me.getComicsettingsview(),
          pageTurnDragThreshold = me.getPageTurnDragThreshold(),
          pageChangeAreaWidth = me.getPageChangeAreaWidth();
             
      comicsettingsview.setValues({
        open_current_comic_at_launch: Comic.userinfo.open_current_comic_at_launch,
        open_next_comic: Comic.userinfo.open_next_comic,
        zoom_on_tap: Comic.userinfo.zoom_on_tap,
        page_fit_mode: Comic.userinfo.page_fit_mode,
        toggle_paging_bar: Comic.userinfo.toggle_paging_bar,
        use_page_turn_drag: (Comic.userinfo.page_turn_drag_threshold < 1000),
        page_turn_drag_threshold: (Comic.userinfo.page_turn_drag_threshold < 1000) ? Comic.userinfo.page_turn_drag_threshold : 50,
        use_page_change_area: (Comic.userinfo.page_change_area_width > 0),
        page_change_area_width: (Comic.userinfo.page_change_area_width > 0) ? Comic.userinfo.page_change_area_width : 75,
      });
      

      if (Comic.userinfo.page_change_area_width > 0)
        pageChangeAreaWidth.enable();
      else
        pageChangeAreaWidth.disable();
        
      if (Comic.userinfo.page_turn_drag_threshold < 1000)
        pageTurnDragThreshold.enable();
      else
        pageTurnDragThreshold.disable();
      
    },
    
    onHide: function() {
      var me = this,
          comicsettingsview = me.getComicsettingsview(),
          values = comicsettingsview.getValues(),
          comicview = me.getComicview();
        
      Comic.userinfo.open_current_comic_at_launch = values.open_current_comic_at_launch;
      Comic.userinfo.open_next_comic = values.open_next_comic;
      Comic.userinfo.zoom_on_tap = values.zoom_on_tap;
      Comic.userinfo.page_fit_mode = values.page_fit_mode;
      Comic.userinfo.toggle_paging_bar = values.toggle_paging_bar;
      Comic.userinfo.page_turn_drag_threshold = values.use_page_turn_drag ? values.page_turn_drag_threshold : 1000;
      Comic.userinfo.page_change_area_width = values.use_page_change_area ? values.page_change_area_width : 0;
      
      Ext.app.Comics.SetUserInfos(Comic.userinfo, function(provider, response) 
        {
          console.log('Settings saved');
          var comiccontroller = me.getApplication().getController('Comic');
          comiccontroller.UpdateSettings();
        });
    },
    
    
    onChangeUsePageTurnDrag: function( field, slider, thumb, newValue, oldValue, eOpts )
    {
      var pageTurnDragThreshold = this.getPageTurnDragThreshold();
      
      if (newValue == 1)
      {
        pageTurnDragThreshold.enable();
        //pageTurnDragThreshold.setValue(pageTurnDragThreshold.getMinValue());
      }
      else
      {
        pageTurnDragThreshold.disable();
        //pageTurnDragThreshold.setValue(pageTurnDragThreshold.getMinValue());
      }
    },
    onChangeUsePageChangeArea: function( field, slider, thumb, newValue, oldValue, eOpts )
    {
      var pageChangeAreaWidth = this.getPageChangeAreaWidth();
      
      if (newValue == 1)
      {
        pageChangeAreaWidth.enable();
      }
      else
      {
        pageChangeAreaWidth.disable();
      }
    },    
});