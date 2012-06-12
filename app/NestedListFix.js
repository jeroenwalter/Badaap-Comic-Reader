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
  
  This fix allows for an XTemplate to be used as itemTpl instead of a string.
  If the function getItemTpl is present in the NestedList subclass, then that one is preferred above getItemTextTpl.
  If you don't need access to the node argument, you could also achieve the same by setting the itemTpl member of the list config directly to an XTemplate.
  
*/
Ext.define('Comic.NestedListFix', {
    override: 'Ext.dataview.NestedList',
    /**
     * @private
     * Returns the list config for a specified node.
     * @param {HTMLElement} node The node for the list config
     */
    getList: function(node) {
        var me = this,
            nodeStore = Ext.create('Ext.data.NodeStore', {
                recursive: false,
                node: node,
              rootVisible: false,
                model: me.getStore().getModel()
            });

        node.expand();

        return Ext.Object.merge({
            xtype: 'list',
            pressedDelay: 250,
            autoDestroy: true,
            store: nodeStore,
            onItemDisclosure: me.getOnItemDisclosure(),
            allowDeselect : me.getAllowDeselect(),
            listeners: [
                { event: 'itemdoubletap', fn: 'onItemDoubleTap', scope: me },
                { event: 'itemtap', fn: 'onItemInteraction', scope: me, order: 'before'},
                { event: 'itemtouchstart', fn: 'onItemInteraction', scope: me, order: 'before'},
                { event: 'itemtap', fn: 'onItemTap', scope: me },
                { event: 'itemtaphold', fn: 'onItemTapHold', scope: me },
                { event: 'beforeselectionchange', fn: 'onBeforeSelect', scope: me },
                { event: 'containertap', fn: 'onContainerTap', scope: me },
                { event: 'selectionchange', fn: 'onSelectionChange', order: 'before', scope: me }
            ],
            //itemTpl: '<span<tpl if="leaf == true"> class="x-list-item-leaf"</tpl>>' + me.getItemTextTpl(node) + '</span>'
            itemTpl: me.getItemTpl ? me.getItemTpl(node) : '<span<tpl if="leaf == true"> class="x-list-item-leaf"</tpl>>' + me.getItemTextTpl(node) + '</span>'
        }, this.getListConfig());
    },
    
    onItemTapHold: function(list, index, target, record, e) {
        var me = this,
            store = list.getStore(),
            node = store.getAt(index);

        me.fireEvent('itemtaphold', this, list, index, target, record, e);
        if (node.isLeaf()) {
            me.fireEvent('leafitemtaphold', this, list, index, target, record, e);
        }

    },
});