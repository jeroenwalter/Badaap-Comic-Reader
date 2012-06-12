/*
  Icon spinner field
  
  Original code:
  http://davehiren.blogspot.nl/2012/04/sencha-touch-spinner-field-change.html
*/
  
Ext.define('Ext.ux.IconSpinner', {
  extend: 'Ext.field.Spinner',
  xtype: 'iconspinnerfield',

  requires: [
    'Ext.field.Spinner'
  ],
  config: {
    iconPlus: 'resources/images/spinner-plus.png',
    iconMinus: 'resources/images/spinner-minus.png',
  },    

  updateComponent: function (newComponent) {
        this.callParent(arguments);

        var innerElement = this.innerElement,
            cls = this.getCls();

        if (newComponent) {
            this.spinDownButton = Ext.Element.create({
                cls: 'minusButton',
                html: '<img class="icon-spinner-button" src="' + this.getIconMinus() + '"/>'
            });

            this.spinUpButton = Ext.Element.create({
                cls:'plusButton',
                html: '<img class="icon-spinner-button" src="' + this.getIconPlus() + '"/>'
            });

            this.downRepeater = this.createRepeater(this.spinDownButton, this.onSpinDown);
            this.upRepeater = this.createRepeater(this.spinUpButton, this.onSpinUp);
        }
    },
});