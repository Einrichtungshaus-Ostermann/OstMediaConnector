Ext.define('Shopware.apps.MediaConnector.view.MediaConnectorConfig.list.List', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.mediaserver-list-grid',
    region: 'center',

    configure: function () {
        return {
            columns: {
                priority: "Priority",
                providerName: 'Mediaprovider',
                query: "Query"
            },
            detailWindow: 'Shopware.apps.MediaConnector.view.MediaConnectorConfig.detail.Window'
        };
    }
});
