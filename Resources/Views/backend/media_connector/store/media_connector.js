Ext.define('Shopware.apps.MediaConnector.store.MediaConnector', {
    alternateClassName: 'MediaConnector.store.MediaConnector',
    extend: 'Ext.data.Store',
    storeId: 'MediaConnector.MediaConnector',
    autoLoad: true,
    remoteSort: false,
    remoteFilter: false,
    model: 'Shopware.apps.MediaConnector.model.MediaConnector',
    proxy: {
        type: 'ajax',
        url: '{url controller="MediaConnector" action="mediaProviderList"}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});