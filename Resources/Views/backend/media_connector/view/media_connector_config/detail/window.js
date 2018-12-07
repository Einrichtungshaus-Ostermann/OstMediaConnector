Ext.define('Shopware.apps.MediaConnector.view.MediaConnectorConfig.detail.Window', {
    extend: 'Shopware.window.Detail',
    alias: 'widget.mediaserver-detail-window',

    title: '{s name=title}Mediaserver editieren{/s}',
    height: 420,
    width: 900,

    configure: function () {
        var me = this;
    },

    createDefaultController: function () {
        var me = this;

        var controller = me.callParent(arguments);

        Shopware.app.Application.on(me.getEventName('after-update-record-on-save'), function(controller, window, record, form) {
            var configItems = form.items.items[0].items.items[1].items.items;

            var config = {};
            for (var i = 0; i < configItems.length; i++) {
                var currentItem = configItems[i];
                config[currentItem.name] = currentItem.value;
            }

            record.data.config = JSON.stringify(config);
            return true;
        });

        return controller;
    }


});
