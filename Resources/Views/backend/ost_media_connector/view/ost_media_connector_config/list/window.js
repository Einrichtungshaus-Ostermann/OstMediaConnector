Ext.define('Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.mediaserver-list-window',
    height: 450,
    title: '{s name=window_title}Mediaserver Liste{/s}',

    configure: function () {
        return {
            listingGrid: 'Shopware.apps.OstMediaConnector.view.OstMediaConnectorConfig.list.List',
            listingStore: 'Shopware.apps.OstMediaConnector.store.OstMediaConnectorConfig'
        };
    }
});