Ext.define('Shopware.apps.OstMediaConnector.model.MediaProvider', {
    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'OstMediaConnector'
        };
    },

    fields: [
        { name: 'name', type: 'string' },
        { name: 'configParameter', type: 'json' }
    ]
});

