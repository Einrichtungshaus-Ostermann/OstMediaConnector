Ext.define('Shopware.apps.OstMediaConnector.controller.Main', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;
        me.mainWindow = me.getView('OstMediaConnectorConfig.list.Window').create({ }).show();

        me.control({
           'before-send-save-request': function(a,b,c,d) {
               console.log(arguments);
           }
        });
    },

    onSave: function() {
        var me = this;

        var containerComponent = me.getComponent(0).getComponent(0);

        console.log(containerComponent.configValues[containerComponent.selected]);

        // me.callParent(arguments);
    }
});