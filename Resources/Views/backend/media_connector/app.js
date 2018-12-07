Ext.define('Shopware.apps.MediaConnector', {
    extend: 'Enlight.app.SubApplication',

    name: 'Shopware.apps.MediaConnector',

    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: ['Main'],

    views: [
        'MediaConnectorConfig.list.Window',
        'MediaConnectorConfig.list.List',

        'MediaConnectorConfig.detail.Container',
        'MediaConnectorConfig.detail.Window'
    ],

    models: ['MediaConnectorConfig', 'MediaConnector'],
    stores: ['MediaConnectorConfig', 'MediaConnector'],

    launch: function () {
        return this.getController('Main').mainWindow;
    }
});