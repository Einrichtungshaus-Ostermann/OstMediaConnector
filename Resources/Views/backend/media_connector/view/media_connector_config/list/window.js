Ext.define('Shopware.apps.MediaConnector.view.MediaConnectorConfig.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.mediaserver-list-window',
    height: 450,
    title: '{s name=window_title}Mediaserver Liste{/s}',

    configure: function () {
        return {
            listingGrid: 'Shopware.apps.MediaConnector.view.MediaConnectorConfig.list.List',
            listingStore: 'Shopware.apps.MediaConnector.store.MediaConnectorConfig'
        };
    }
});