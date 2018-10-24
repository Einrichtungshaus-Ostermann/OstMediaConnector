Ext.define('Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.list.List', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.mediaserver-list-grid',
    region: 'center',

    configure: function() {
        return {
            columns: { priority: "Priority", providerName: 'Mediaprovider', query: "Query" },
            detailWindow: 'Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.detail.Window'
        };
    }
});
