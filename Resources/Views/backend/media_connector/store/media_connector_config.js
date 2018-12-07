Ext.define('Shopware.apps.MediaConnector.store.MediaConnectorConfig', {
    extend: 'Shopware.store.Listing',

    configure: function () {
        return {
            controller: 'MediaConnectorConfig'
        };
    },
    model: 'Shopware.apps.MediaConnector.model.MediaConnectorConfig'
});