/*
  IOS5 toggle
  
  Original code:
  http://www.sencha.com/forum/showthread.php?192358-iOS-5-Toggle
*/

Ext.define('Ext.ux.IOS5Toggle', {
  extend: 'Ext.field.Toggle',
  xtype: 'ios5togglefield',

  config: {
    cls: 'ios5_toggle',
    listeners: {
        initialize: function () {
            
            var me = this;
            var mec = this.getComponent();
            var mythumb = this.element.down('.x-thumb');
            var mytoggle = this.element.down('.x-toggle');
            var myelement = this.element;
            
            mythumb.on({
                // this improves the ON/OFF effect 
                drag: {
                    fn: function () {
                        var value,oldvalue,onCls,offCls;
                        value = me.getValue();
                        onCls = me.getMaxValueCls(),
                        offCls = me.getMinValueCls();
                        if(value != oldvalue) {
                            mytoggle.addCls(value ? onCls : offCls);
                            mytoggle.removeCls(value ? offCls : onCls);
                        }
                        oldvalue = value;
                    }
                },
                // this improves the tap action (responds to tap on thumb)
                tap: {
                    fn: function (e,t) {
                        var value,oldValue,onCls,offCls,thumb;
                        oldValue = me.getValue();
                        value = ((me.getValue()==1) ? 0 : 1);
                        mec.setIndexValue(0, value, mec.getAnimation());
                        onCls = me.getMaxValueCls(),
                        offCls = me.getMinValueCls();
                        mytoggle.addCls(value ? onCls : offCls);
                        mytoggle.removeCls(value ? offCls : onCls);
                    }
                }
            });
            
        }, // initialize
    } // listeners   
  }

});