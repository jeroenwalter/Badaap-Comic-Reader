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
Ext.define('Comic.controller.Database', {
    extend: 'Ext.app.Controller',

    config: {
        refs: {
          databaseView: 'databaseview',
          scanButton: 'databaseview #scanButton',
          pauseButton: 'databaseview #pauseButton',
          nrFoldersField: 'databaseview #nrFoldersField',
          nrFilesField: 'databaseview #nrFilesField',
          statusField: 'databaseview #statusField',
          fileField: 'databaseview #fileField',
          folderField: 'databaseview #folderField',
          etaField: 'databaseview #etaField',
          nrComicsField: 'databaseview #nrComicsField',
          maintabpanel: 'mainview maintabpanel',
          filesystemview: 'filesystemview',
        },
        
        control: {
          databaseView: {
            show: 'onShow',
          },
          scanButton: {
            tap: 'onScan'
          },
          pauseButton: {
            tap: 'onPause',
          },
        },
    },

    init: function()
    {
      // called before application.launch()
      
      Comic.scan = {};
      Comic.scan.state = 0; // 0: idle, 1: scanning for new files, 2: processing new files, 3: scanning for obsolete files
      Comic.scan.paused = false;
      Comic.scan.refresh_store = false;
      Comic.provider = Ext.direct.Manager.getProvider('Comics');
      /*
      Ext.direct.Manager.addListener('exception', function(ev) { console.log('Ext.direct exception'); console.log(ev); });
      Ext.direct.Manager.addListener('event', function(ev) { console.log('Ext.direct event'); console.log(ev); });
      
      Comic.provider.addListener('data', function(provider, event, eopts) { 
        console.log('data event');
        console.log(event);
        var t = Comic.provider.getTransaction(event._xhr);
        console.log(t);
      });
      */
      
      this.UpdateNrComicsField();
      
    },
    
    onShow: function()
    {
      if (Comic.userinfo.level == 1)
      {
        this.getScanButton().show();
        this.getPauseButton().show();
      }
      else
      {
        this.getScanButton().hide();
        this.getPauseButton().hide();
      }
      
      if (Comic.scan.state != 0)
      {
        // scan in progress
      }
      else
      {
      }
    },
    onScan: function()
    {
      if (Comic.userinfo.level != 1)
      {
        Ext.Msg.alert('Database', 'Insufficient persmissions.', Ext.emptyFn);
        return;
      }
      
      var me = this;
      var nrFoldersField = this.getNrFoldersField();
      var nrFilesField = this.getNrFilesField();
      var fileField = this.getFileField();
      var folderField = this.getFolderField();
      var statusField = this.getStatusField();
      var databaseTab = this.getMaintabpanel().getTabBar().getComponent(3);
      var etaField = this.getEtaField();
      var scanButton = this.getScanButton();
      
      databaseTab.setBadgeText("");
      
      
      Ext.app.Comics.Log(1, 'Badaap Comic Reader client', 'Database update BEGIN');
      
      if (Comic.scan.state != 0)
      {
        var databaseTab = this.getMaintabpanel().getTabBar().getComponent(3);
        var pauseButton = this.getPauseButton();
        
        //Ext.Msg.alert('Database', 'Database update is already running.', Ext.emptyFn);
        scanButton.setText('Start');
        
        if (Comic.scan.file_queue)
        {
          Comic.scan.file_queue.pause();
          Comic.scan.file_queue.clear();
        }
        
        if (Comic.scan.folder_queue)
        {
          Comic.scan.folder_queue.pause();
          Comic.scan.folder_queue.clear();
        }
        
        if (Comic.scan.obsolete_queue)
        {
          Comic.scan.obsolete_queue.pause();
          Comic.scan.obsolete_queue.clear();
        }
        
        statusField.setValue('Stopped');
        
        databaseTab.setBadgeText("");
        pauseButton.setText("Pause");        
        Comic.scan.state = 0;
        return;
      }
      
      
      scanButton.setText('Stop');
      
      Comic.scan.paused = false;
        
      Comic.scan.nr_updated = 0;
      Comic.scan.nr_new = 0;
      Comic.scan.nr_unchanged = 0;
      Comic.scan.nr_processed = 0;
      Comic.scan.nr_deleted = 0;
      Comic.scan.num_folders = 0;
      
      var d = new Date();
      Comic.scan.start_time = d.getTime(); // in ms
      
      nrFoldersField.setValue("Folders: " + Comic.scan.num_folders + " Files: " + (Comic.scan.nr_updated + Comic.scan.nr_new));
      nrFilesField.setValue("Processed: " + Comic.scan.nr_processed + " New: " + Comic.scan.nr_new + " Updated: " + Comic.scan.nr_updated + " Unchanged: " + Comic.scan.nr_unchanged);
      
      etaField.setValue("");
      
      Comic.scan.file_queue = jmq.create({
        delay: -1,
        // Process queue items one-at-a-time.
        batch: 1,
        paused: true,
        callback: function( filename ) 
        {
          var me = this;
          fileField.setValue(filename);  
          Ext.app.Comics.AddComic(filename, 
            function(result, event, success)
            { 
              Comic.scan.nr_processed++;
              if (!Comic.scan.paused && Comic.scan.state != 0)
                databaseTab.setBadgeText(Comic.scan.nr_new + Comic.scan.nr_updated - Comic.scan.nr_processed);
                
              nrFilesField.setValue("Processed: " + Comic.scan.nr_processed + " New: " + Comic.scan.nr_new + " Updated: " + Comic.scan.nr_updated + " Unchanged: " + Comic.scan.nr_unchanged);
              
              var d = new Date();
              var eta = (d.getTime() - Comic.scan.start_time) / Comic.scan.nr_processed * (Comic.scan.nr_new + Comic.scan.nr_updated - Comic.scan.nr_processed) / 1000;
              var hour = Math.floor(eta / 3600);
              var min =  Math.floor((eta - hour * 3600)/ 60);
              var sec =  Math.floor(eta - hour * 3600 - min * 60);
              
              var s = phpjs.sprintf("%02d:%02d:%02d", hour, min, sec);
              etaField.setValue(s);
              //etaField.setValue(hour.toFixed(0) + ':' + min.toFixed(0) + ':' + sec.toFixed(0));
              
              if (!success)
              {
                console.log("AddComic failed: " + filename);
                me.next( false );
                return;
              }
              
              if (result.id != -1 || result.status != "OK")
              {
                //Comic.scan.nr_new++;
              }
              else
              {
                if (result.status != "OK")
                {
                  statusField.setValue(result.status + " : " + filename);
                  console.log(result.status + ": " + filename);  
                }
              }
              //statusField.setValue(filename + "<br/>" + result.id + " " + result.error + "<br/>");
              
              me.next( false );
            }
          );
        },
        // When the queue completes naturally, execute this function.
        complete: function()
        {
          Comic.scan.state = 0;
          statusField.setValue("Done");
          scanButton.setText("Start");
          databaseTab.setBadgeText("");
          Ext.app.Comics.Log(1, 'Badaap Comic Reader client', 'Database update END');
          me.UpdateNrComicsField();
          me.RefreshStore();
        }
      });
      
      // Create a new queue.
      Comic.scan.folder_queue = jmq.create({
        delay: -1,
        // Process queue items one-at-a-time.
        batch: 1,
        paused: true,
        callback: function( folder ) 
        {
          var me = this;
          
          folderField.setValue(folder);
             
          Ext.app.Comics.GetComicsInFolder(folder, 
            function(result, event, success)
            { 
              if (!success)
              {
                console.log("GetComicsInFolder failed: " + folder);
                console.log(event.getMessage());
                me.next( false );
                return;
              }
              //var result = response.getResult();
              Comic.scan.num_folders += result.nr_folders;
              Ext.Object.each(result.folders, function(key, val) 
              {
                me.add(val);
              });
              
              Comic.scan.nr_updated += result.nr_updated;
              Comic.scan.nr_new += result.nr_new;
              Comic.scan.nr_unchanged += result.nr_unchanged;
              
              nrFilesField.setValue("Processed: " + Comic.scan.nr_processed + " New: " + Comic.scan.nr_new + " Updated: " + Comic.scan.nr_updated + " Unchanged: " + Comic.scan.nr_unchanged);
              
              if (!Comic.scan.paused && Comic.scan.state != 0)
                databaseTab.setBadgeText(Comic.scan.nr_new + Comic.scan.nr_updated);
              
              Ext.Object.each(result.newfiles, function(key, val) 
              {
                Comic.scan.file_queue.add(val);
              });
              
              Ext.Object.each(result.updated, function(key, val) 
              {
                Comic.scan.file_queue.add(val);
              });
              
              nrFoldersField.setValue("Folders: " + Comic.scan.num_folders + " Files: " + (Comic.scan.nr_updated + Comic.scan.nr_new));
              me.next( false );
            }
          );
        },
        // When the queue completes naturally, execute this function.
        complete: function()
        {
          // Now start the file queue
          nrFoldersField.setValue("Folders: " + Comic.scan.num_folders + " Files: " + (Comic.scan.nr_updated + Comic.scan.nr_new) + " Done");
          
          if ((Comic.scan.nr_updated + Comic.scan.nr_new) > 0)
          {
            statusField.setValue('Updating existing files and adding new files...');
            Comic.scan.state = 2;
            Comic.scan.file_queue.start();
          }
          else
          {
            Comic.scan.state = 0;
            statusField.setValue("Done");
            scanButton.setText("Start");
            databaseTab.setBadgeText("");
            Ext.app.Comics.Log(1, 'Badaap Comic Reader client', 'Database update END');
          }
        }
      });
      
      Ext.app.Comics.GetComicsCount(function(result, event, success)
        {
          if (!success)
          {
            console.log('GetComicsCount failed.');
            return;
          } 
          
          var count = result,
              current = 0,
              limit = 100;
          
          console.log('#comics: ' + count);
          
          Ext.app.Comics.Log(1, 'Badaap Comic Reader client', 'Remove obsolete comics BEGIN');
          statusField.setValue("Removing obsolete comics...");
          
          Comic.scan.obsolete_queue = jmq.create({
            delay: -1,
            // Process queue items one-at-a-time.
            batch: 1,
            paused: true,
            callback: function( current ) 
            {
              var me = this;
              Ext.app.Comics.MarkObsoleteComics(current, limit, function(result, event, success)
                {
                  if (!success)
                  {
                    console.log('MarkObsoleteComics failed.');
                    me.next( false );
                    return;
                  }
                  
                  statusField.setValue('Scan for obsolete comics ' + current + '/' + count);
                  //console.log('Scan for obsolete comics ' + current + ', ' + limit);
                  for (var i in result)
                  {
                    console.log('Obsolete: [' + result[i].id + '] ' + result[i].filename);
                  }
                  me.next( false );
                });
              
            },
            complete: function()
            {
              Ext.app.Comics.RemoveObsoleteComics();
              
              databaseTab.setBadgeText("");
              Ext.app.Comics.Log(1, 'Badaap Comic Reader client', 'Remove obsolete comics END');
              
              // Add the root folder, this will start the update process.
              Comic.scan.state = 1; 
              statusField.setValue('Getting a list of all files...');
              Comic.scan.folder_queue.add( "" );
              Comic.scan.folder_queue.start();
            }
          });
          
          for (current = 0; current <= count; current += limit)
          {
            Comic.scan.obsolete_queue.add(current);
          }
            
          Comic.scan.state = 3; 
          Comic.scan.obsolete_queue.start();
        });
    },
    
    onPause: function()
    {
      if (Comic.scan.state == 0)
        return;
        
      var databaseTab = this.getMaintabpanel().getTabBar().getComponent(3);
      var pauseButton = this.getPauseButton();
      
      if (Comic.scan.paused)
      {
        Comic.scan.paused = false;
        if (Comic.scan.state == 1)
          Comic.scan.folder_queue.start();
        else
        if (Comic.scan.state == 2)
          Comic.scan.file_queue.start();
        else
        if (Comic.scan.state == 3)
          Comic.scan.obsolete_queue.start();
        
        databaseTab.setBadgeText("");
        pauseButton.setText("Pause");
      }
      else
      {
        Comic.scan.paused = true;
        if (Comic.scan.state == 1)
          Comic.scan.folder_queue.pause();
        else
        if (Comic.scan.state == 2)
          Comic.scan.file_queue.pause();
        else
        if (Comic.scan.state == 3)
          Comic.scan.obsolete_queue.pause();
          
        var statusField = this.getStatusField();
        statusField.setValue('Paused');
        
        databaseTab.setBadgeText("Paused");
        pauseButton.setText("Resume");
      }
    },
    
    
    UpdateNrComicsField: function()
    {
      var me = this;
      Ext.app.Comics.GetComicsCount(function(result, event, success)
        {
          if (!success)
          {
            console.log('GetComicsCount failed.');
            return;
          } 
          
          var nrComicsField = me.getNrComicsField();
          nrComicsField.setValue(result);
        });
    },
    
     
    RefreshStore: function()
    {
      var store = this.getFilesystemview().getStore();
      store.load();
      this.getFilesystemview().goToNode(store.getRoot());
    }
    
});
    