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
if (!Ext.browser.is.WebKit) {
  alert("The current browser is unsupported.\n\nSupported browsers:\n" +
        "Google Chrome\n" +
        "Apple Safari\n" +
        "Mobile Safari (iOS)\n" +
        "Android Browser\n" +
        "BlackBerry Browser"
    );
}

// add2home configuration
var addToHomeConfig = {
        returningVisitor: true,		// Show the message only to returning visitors (ie: don't show it the first time)
        expire: 720					// Show the message only once every 12 hours
      };

