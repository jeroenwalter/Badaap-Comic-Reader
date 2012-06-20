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

var SLIDER_RANGE = 10000;

// pagenr is 0..(nr_pages-1)

  
Ext.define('Comic.controller.Comic', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
          mainview: 'mainview',
          comicview: 'comicview',
          
          backbutton: 'comicview #backbutton',
          logoutbutton: 'comicview #logoutbutton',
          
          comictitle: 'comicview titlebar',
          toolbar: 'comicview toolbar',          
          slider: 'comicview #slider',
          nextbutton: 'comicview #nextbutton',
          previousbutton: 'comicview #previousbutton',
          settingsbutton: 'comicview #settingsbutton',
          infobutton: 'comicview #infobutton',
          nextPageIcon: 'comicview #nextPageIcon',
          prevPageIcon: 'comicview #prevPageIcon',
          
          imageviewer: 'comicview #imageviewer',
          
          filesystemview: 'filesystemview',
          maintabpanel: 'mainview maintabpanel',
                    
          comicsettingsview: { selector: 'comicsettingsview', xtype: 'comicsettingsview', autoCreate: true },
          
          comicinfoview: { selector: 'comicinfoview', xtype: 'comicinfoview', autoCreate: true },
        },
        
        control: {
        
          comicview: {
            show: 'onShow', // also triggered when the comic view is popped from the main navigation view.... so use active event instead
            hide: 'onHide',
            singletap: 'onTap',
            activate: 'onActivate',
          },
          
          slider: {
            change: 'onSliderChange'
          },
          nextbutton: {
            tap: 'onNextButton'
          },
          previousbutton: {
            tap: 'onPreviousButton'
          },
          settingsbutton: {
            tap: 'onSettingsButton'
          },
          infobutton: {
            tap: 'onInfoButton'
          },
          backbutton: {
            tap: 'onBackButton'
          },
          logoutbutton: {
            tap: 'onLogoutButton'
          },
          
          imageviewer: {
            imageLoaded: 'onImageLoaded',
            imageError: 'onImageError',
            zoomByTap: 'onZoomByTap',
            initDone: 'onImageViewerInitDone',
            singletap: 'onSingleTap',
          },
          
          
        },
    },
    
    init : function()
    {
      // called before application.launch()
      var me = this;
      
      me.preload_count = 1; // number of pages to preload before and after the current page.
      
      me.cache = []; // cache of preloaded page info
      me.waiting_for_page = -1; // page that must be displayed once loaded.

      // Atomic Browser doesn't always fire the orientationchange event ....
      //window.addEventListener( "orientationchange", function() { me.resize(); });
      /*
        if (Ext.Viewport.supportsOrientation()) {
            alert('supportsOrientation()');
         
        }
        else {
         
            alert('NOT supportsOrientation()');
        }      
      */
      
    },
    
    UpdateSettings: function()
    {
      var me = this;
      var imageviewer = me.getImageviewer();
      // 1: Fit width, 2: Full page
      if (Comic.userinfo.page_fit_mode == 2)
      {
        imageviewer.setAutoFitWidth(true);
        imageviewer.setAutoFitHeight(true);
      }
      else
      {
        // fit width
        imageviewer.setAutoFitWidth(true);
        imageviewer.setAutoFitHeight(false);
      }
      
      imageviewer.setZoomOnSingleTap(Comic.userinfo.zoom_on_tap == 1);
      imageviewer.setZoomOnDoubleTap(Comic.userinfo.zoom_on_tap == 2);
      
      imageviewer.resize();
    },
    
    onImageViewerInitDone: function()
    {
      var me = this;
      var imageviewer = me.getImageviewer();
      imageviewer.setResizeOnLoad(true);
      imageviewer.setErrorImage('resources/images/no_image_available.jpg');
      
      // 1: Fit width, 2: Full page
      if (Comic.userinfo.page_fit_mode == 2)
      {
        imageviewer.setAutoFitWidth(true);
        imageviewer.setAutoFitHeight(true);
      }
      else
      {
        // fit width
        imageviewer.setAutoFitWidth(true);
        imageviewer.setAutoFitHeight(false);
      }
      
      imageviewer.setZoomOnSingleTap(Comic.userinfo.zoom_on_tap == 1);
      imageviewer.setZoomOnDoubleTap(Comic.userinfo.zoom_on_tap == 2);
      
      // For some reason, I can't access the figure element via the controller refs and control options....
      imageviewer.figEl.addListener({
          scope: me,
          singletap: me.onSingleTap,
          doubletap: me.onDoubleTap,
          dragend: me.onDragEnd,
      });  
    },
    
    onHide: function()
    {
      
    },

    onShow: function() 
    {
      // useless event, gets triggered when the view is popped from a navigation view.....
    },

    
    RefreshStore: function()
    {
      var me = this;
      // Only update the comic record. This will cause a refresh of the list showing the store containing the record...
      // This will not cause the list to scroll to the top, but to maintain the last scroll position.
      if (Comic.context.source == "filesystem")
      {
        var comic = Comic.context.record.get('comic');
        comic.last_page_read = me.current_page_nr;
        Comic.context.record.set('comic', comic);
      }
      
      var recent = Ext.data.StoreManager.lookup('Recent');
      recent.load();
      
      // Only refresh the current folder. This will cause the list to scroll to the top.
      //var store = this.getFilesystemview().getStore();
      //var node = this.getFilesystemview().getLastNode();
      //store.load({ node: node });
    },
    
    
    onActivate: function()
    {
      var me = this;
      var titlebar = me.getComictitle();
      
      me.cache.length = 0;
      me.waiting_for_page = -1;
      
      if (Comic.new_comic_id == 0)
      {
        titlebar.setTitle('No comic selected');
        
        var imageviewer = me.getImageviewer();
        imageviewer.loadImage('resources/images/no_image_available.jpg');
      }
      else
      {
        titlebar.setTitle('Opening comic...');
        Comic.userinfo.current_comic_id = Comic.new_comic_id;
        // Get new comic from server
        Ext.app.Comics.GetComic(Comic.userinfo.current_comic_id, function(provider, response) {
             // process response
             me.current_comic = response.getResult();
             
             Comic.userinfo.current_comic_opened_from_type = 'folder';
             Comic.userinfo.current_comic_opened_from_id = Comic.context.id;
             Ext.app.Comics.SetUserInfos({ current_comic_id: Comic.userinfo.current_comic_id, 
                                          current_comic_opened_from_type: Comic.userinfo.current_comic_opened_from_type,
                                          current_comic_opened_from_id: Comic.userinfo.current_comic_opened_from_id });
                                          
             //Ext.app.Comics.SetSetting('current_comic_id', Comic.userinfo.current_comic_id);
             //Ext.app.Comics.SetSetting('current_comic_opened_from_type', 'folder');
             //Ext.app.Comics.SetSetting('current_comic_opened_from_id', Comic.context.id);
             
             
             // slider range is broken in sencha touch 2.0
             //me.getSlider().setMinValue(0);
             //me.getSlider().setMaxValue(SLIDER_RANGE);
                          
             me.current_page_nr = me.current_comic.last_page_read | 0;
             me.ShowPage(me.current_page_nr);
        });
      }
    },
    
    onDragEnd: function(/*Ext.event.Event*/ event, /*HTMLElement*/ node, /*Object*/ options, /*Object*/ eOpts) 
    { 
      var me = this;
      var imageviewer = me.getImageviewer();
      var scroller = imageviewer.getScrollable().getScroller();
      
      console.log("pos x: " + scroller.position.x + " y: " + scroller.position.y);
      console.log("min x: " + scroller.getMinPosition().x + " y: " + scroller.getMinPosition().y);
      console.log("max x: " + scroller.getMaxPosition().x + " y: " + scroller.getMaxPosition().y);
      
      if ((scroller.position.x < scroller.getMinPosition().x - Comic.userinfo.page_turn_drag_threshold) || 
          (scroller.position.y < scroller.getMinPosition().y - Comic.userinfo.page_turn_drag_threshold))
        this.onPreviousButton();
      else
      if ((scroller.position.x > scroller.getMaxPosition().x + Comic.userinfo.page_turn_drag_threshold) || 
          (scroller.position.y > scroller.getMaxPosition().y + Comic.userinfo.page_turn_drag_threshold))
        this.onNextButton();
    },
    
    onSliderChange: function(slider) 
    {
      var me = this;
      me.current_page_nr = Math.round((me.current_comic.number_of_pages-1) * slider.getValue() / SLIDER_RANGE);
      
      me.ShowPage(me.current_page_nr);
    },
              
    onNextButton: function() 
    {
      var me = this;
      if (me.current_page_nr < (me.current_comic.number_of_pages-1))
      {
        var nextPageIcon = me.getNextPageIcon();
        nextPageIcon.show();
        Ext.defer(function() { this.hide(); }, 500, nextPageIcon);
        me.ShowPage(++me.current_page_nr);
      }
      else
      {
        /*
        if (Comic.userinfo.open_next_comic == 1)
        {
          // TODO: need a way to determine what is the next comic...
        }
        else
        */
        {
          this.onBackButton();
        }
      }
    },
    
    onPreviousButton: function() 
    {
      var me = this;
      if (me.current_page_nr > 0)
      {
        var prevPageIcon = me.getPrevPageIcon();
        prevPageIcon.show();
        Ext.defer(function() { this.hide(); }, 500, prevPageIcon);
        me.ShowPage(--me.current_page_nr);
      }
      else
      {
        this.onBackButton();
      }
    },   
    
    
    onLogoutButton: function()
    {
      Ext.app.Comics.LogoutUser(function() 
        {
          window.location = 'index.php'; 
        });
      
    },
    onBackButton: function() 
    {
      this.RefreshStore();
      
      Comic.userinfo.current_comic_id = null;
      Comic.userinfo.current_comic_opened_from_type = null;
      Comic.userinfo.current_comic_opened_from_id = null;
      Ext.app.Comics.SetUserInfos({ current_comic_id: Comic.userinfo.current_comic_id, 
                                    current_comic_opened_from_type: Comic.userinfo.current_comic_opened_from_type,
                                    current_comic_opened_from_id: Comic.userinfo.current_comic_opened_from_id });
                                          
      this.getMainview().pop(1);
    },   
   
    onSingleTap: function(/*Ext.event.Event*/ event, /*HTMLElement*/ node, /*Object*/ options, /*Object*/ eOpts)
    {
      // This handler is called for both the figure and its image element, because of event bubbling.
      // If clicked in the image, then the event for the image comes before the event of the figure.
      // In order to prevent double page turns, stop event propagation here.
      var me = this;
      if (event.pageX < Comic.userinfo.page_change_area_width)
      {
        me.onPreviousButton();
        event.stopPropagation();
        return true;
      }
      else
      if (event.pageX > window.outerWidth - Comic.userinfo.page_change_area_width)
      {
        me.onNextButton();
        event.stopPropagation();
        return true;
      }
      else
      {
        if (Comic.userinfo.toggle_paging_bar == 1)
        {
          me.onToggleToolbars();
        }
        
        event.stopPropagation();
        return false;
      }
    },
    
    onDoubleTap: function(/*Ext.event.Event*/ event, /*HTMLElement*/ node, /*Object*/ options, /*Object*/ eOpts)
    {
      // This handler is called for both the figure and its image element, because of event bubbling.
      // If clicked in the image, then the event for the image comes before the event of the figure.
      // In order to prevent double page turns, stop event propagation here.
      var me = this;
      
      if (Comic.userinfo.toggle_paging_bar == 2)
      {
        me.onToggleToolbars();
      }
      
      event.stopPropagation();
      return false;
    },
    
    onToggleToolbars: function(ev, t)
    {
      var titlebar = this.getComictitle();
      var toolbar = this.getToolbar();
            
      if (titlebar.isHidden())
      {
        titlebar.show();
        toolbar.show();
      }
      else
      {
        titlebar.hide();
        toolbar.hide();
      }
        
      // no further processing
      return false;
    },
    
    onZoomByTap: function(ev, t)
    {
      return true;
    },
    
    onImageError: function()
    {
      var me = this;
      console.log('Error while loading the image.');
    },
    
    onImageLoaded: function()
    {
      var me = this;
      if (me.current_comic)
      {
        var imageviewer = this.getImageviewer();
        var scroller = imageviewer.getScrollable().getScroller();
        var titlebar = me.getComictitle();
        
        //scroller.scrollTo(0,0,false);
                
        // Assuming me.current_page_nr didn't change while the image was being loaded.....
        titlebar.setTitle(me.current_comic.name + " " + (me.current_page_nr + 1)+ "/" + me.current_comic.number_of_pages);
        me.getSlider().setValue((me.current_page_nr / (me.current_comic.number_of_pages-1)) * SLIDER_RANGE);
        Ext.app.Comics.SetComicProgress(Comic.userinfo.current_comic_id, me.current_page_nr);
      }
    },
   
    onInfoButton: function()
    {
      var me = this,
          view = me.getComicinfoview();
        
      view.comic = me.current_comic;
      /* popup test
      view.setModal(true);
      view.setCentered(true);
      view.setHideOnMaskTap(true);
      view.setWidth(Ext.os.deviceType == 'Phone' ? 260 : 600);
      view.setHeight(Ext.os.deviceType == 'Phone' ? 260 : 400);
      Ext.Viewport.add(view);
      view.show();
      */
      me.getMainview().push(view);
    },
    
    onSettingsButton: function()
    {
      if (!this.overlay) 
      {
        this.overlay = Ext.Viewport.add(this.getComicsettingsview());
      }

      this.overlay.show();
    },
   
    ShowPage: function(pagenr)
    {
      var me = this;
      var imageviewer = this.getImageviewer();
      var scroller = imageviewer.getScrollable().getScroller();
      var titlebar = me.getComictitle();
      scroller.stopAnimation();
      
      if (pagenr < 0 || pagenr >= me.current_comic.number_of_pages)
      {
        console.log("pagenr " + pagenr + " out of bounds [0.."+(me.current_comic.number_of_pages-1)+"]");
        return;
      }
      
      titlebar.setTitle(me.current_comic.name + " " + (pagenr + 1)+ "/" + me.current_comic.number_of_pages + " (loading...)");
      
      if (me.cache[pagenr] && me.cache[pagenr].img)
      { 
        
        scroller.scrollTo(0,0,false);
	  
        imageviewer.loadImage(me.cache[pagenr].src);
      }
      else
      {
        me.waiting_for_page = pagenr;
      }
      
      me.PreloadPages();
    }, 
    
   
    PreloadPage: function(pagenr)
    {
      var me = this;
      if (pagenr < 0 || pagenr >= me.current_comic.number_of_pages)
        return;
        
      if (me.cache[pagenr])
      {
        if (!me.cache[pagenr].img)
          me.PreloadImage(pagenr);
          
        return;
      }

      console.log("GetPage " + pagenr);
      Ext.app.Comics.GetPage(Comic.userinfo.current_comic_id, pagenr, 10000, function(provider, response) 
        {
          if (response.getResult().error)
          {
            console.log(response.getResult().error);
            if (response.getResult().message)
              console.log(response.getResult().message);
          }
            
          me.cache[response.getResult().page] = response.getResult();
          me.PreloadImage(response.getResult().page);
        });
    },
    
    PreloadImage: function(pagenr)
    {
      var me = this;
      if (pagenr < 0 || pagenr >= me.current_comic.number_of_pages)
        return;
        
      if (!me.cache[pagenr])
      {
        console.log("PreloadImage called with no cache entry for page " + pagenr);
        return;
      }

      me.cache[pagenr].img = Ext.create('Ext.Img', {
          src: me.cache[pagenr].src,
          mode: 'element', // create <img> instead of <div>
          listeners: {
            load: function( /*Ext.Img*/ image, /*Ext.EventObject*/ e, /*Object*/ eOpts )
              {
                me.ShowCacheStatus();
                
                if (me.waiting_for_page == pagenr)
                {
                  me.waiting_for_page = -1;
                  me.getImageviewer().loadImage(image.getSrc());
                }
              },
            error: function( /*Ext.Img*/ image, /*Ext.EventObject*/ e, /*Object*/ eOpts )
              {
                Ext.Msg.alert('Error while loading image ' + image.getSrc());
                console.log('Error while loading image ' + image.getSrc());
                me.cache[pagenr].img.destroy();
                delete me.cache[pagenr].img;
                me.ShowCacheStatus();
              },
          }
      });
    },
    
    ShowCacheStatus: function()
    {
      var me = this;
      var s = "ImageCache: ";
      var i = 0;
      for (i = 0; i < me.current_comic.number_of_pages; i++)
      {
        if (me.cache[i] && me.cache[i].img)
          s += " " + (i+1);
      }
      
      console.log(s);
    },
    
    PreloadPages: function()
    {
      var me = this;
      var i = 0;
      // Clear old cache images, not the page info.
      for (i = 0; i <= me.current_page_nr - me.preload_count - 1; i++)
      {
        if (me.cache[i] && me.cache[i].img)
        {
          me.cache[i].img.destroy();
          delete me.cache[i].img;
        }
      }
      
      for (i = me.current_page_nr + me.preload_count + 1; i < me.current_comic.number_of_pages; i++)
      {
        if (me.cache[i] && me.cache[i].img)
        {
          me.cache[i].img.destroy();
          delete me.cache[i].img;
        }
      }
      
      // Preload the next and previous pages.
      for (i = me.current_page_nr; i <= me.current_page_nr + me.preload_count; i++)
        me.PreloadPage(i);
        
      for (i = me.current_page_nr - 1; i >= me.current_page_nr - me.preload_count; i--)
        me.PreloadPage(i);
    }

});