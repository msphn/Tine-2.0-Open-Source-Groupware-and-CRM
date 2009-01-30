/**
 * Tine 2.0
 * 
 * @package     Voipmanager
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Thomas Wadewitz <t.wadewitz@metaways.de>
 * @copyright   Copyright (c) 2007-2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id:$
 *
 */
 
Ext.namespace('Tine.Voipmanager');

/**
 * Asterisk SipPeer Edit Dialog
 */
Tine.Voipmanager.AsteriskSipPeerEditDialog = Ext.extend(Tine.widgets.dialog.EditDialog, {

    
    /**
     * @private
     */
    windowNamePrefix: 'AsteriskSipPeerEditWindow_',
    appName: 'Voipmanager',
    recordClass: Tine.Voipmanager.Model.AsteriskSipPeer,
    recordProxy: new Tine.Tinebase.widgets.app.JsonBackend({
	    appName: 'Voipmanager',
	    modelName: 'AsteriskSipPeer',
	    recordClass: Tine.Voipmanager.Model.AsteriskSipPeer
	}),
    loadRecord: false,
    tbarItems: [{xtype: 'widget-activitiesaddbutton'}],
    
    /**
     * overwrite update toolbars function (we don't have record grants yet)
     */
    updateToolbars: function(record) {
        this.onSipPeerUpdate();
    	Tine.Voipmanager.AsteriskSipPeerEditDialog.superclass.updateToolbars.call(this, record, 'id');
    },
    
    /**
     * this gets called when initializing and if a new timeaccount is chosen
     * 
     * @param {} field
     * @param {} timeaccount
     */
    onSipPeerUpdate: function(field, timeaccount) {
        
    },
    
    /**
     * returns dialog
     * 
     * NOTE: when this method gets called, all initalisation is done.
     */
    getFormItems: function() { 
        return {
            xtype: 'tabpanel',
            border: false,
            plain:true,
            activeTab: 0,
            items:[{
            title: this.app.i18n._('General'),
            border: false,
            anchor: '100%',
            xtype: 'columnform',
            items: [[{
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Name'),
                name: 'name',
                maxLength: 80,
                anchor: '98%',
                allowBlank: false
            }, {
                xtype: 'combo',
                fieldLabel: this.app.i18n._('Context'),
                name: 'context',
                mode: 'local',
                displayField: 'name',
                valueField: 'name',
                anchor: '98%',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                store: new Ext.data.JsonStore({
                    storeId: 'Voipmanger_EditSipPeer_Context',
                    id: 'id',
                    fields: ['id', 'name']
                })
            }, {
                xtype: 'combo',
                fieldLabel: this.app.i18n._('Type'),
                name: 'type',
                mode: 'local',
                displayField: 'value',
                valueField: 'key',
                anchor: '98%',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                store: new Ext.data.SimpleStore({
                    autoLoad: true,
                    id: 'key',
                    fields: ['key', 'value'],
                    data: [['friend', 'friend'], ['user', 'user'], ['peer', 'peer']]
                })
            }], [{
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Secret'),
                name: 'secret',
                maxLength: 80,
                anchor: '98%',
                allowBlank: false
            }, {
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Callerid'),
                name: 'callerid',
                maxLength: 80,
                anchor: '100%',
                allowBlank: true
            }, {
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Mailbox'),
                name: 'mailbox',
                maxLength: 50,
                anchor: '98%',
                allowBlank: true
            }], [{
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Callgroup'),
                name: 'callgroup',
                maxLength: 10,
                anchor: '98%',
                allowBlank: true
            }, {
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Pickup group'),
                name: 'pickupgroup',
                maxLength: 10,
                anchor: '98%',
                allowBlank: true
            }, {
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Accountcode'),
                name: 'accountcode',
                maxLength: 20,
                anchor: '98%',
                allowBlank: true
            }], [{
                xtype: 'textfield',
                fieldLabel: this.app.i18n._('Language'),
                name: 'language',
                maxLength: 2,
                anchor: '98%',
                allowBlank: true
            }, {
                xtype: 'combo',
                fieldLabel: this.app.i18n._('NAT'),
                name: 'nat',
                mode: 'local',
                displayField: 'value',
                valueField: 'key',
                anchor: '98%',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                store: new Ext.data.SimpleStore({
                    autoLoad: true,
                    id: 'key',
                    fields: ['key', 'value'],
                    data: [['no', 'off'], ['yes', 'on']]
                })
            }, {
                xtype: 'combo',
                fieldLabel: this.app.i18n._('Qualify'),
                name: 'qualify',
                mode: 'local',
                displayField: 'value',
                valueField: 'key',
                anchor: '98%',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                store: new Ext.data.SimpleStore({
                    autoLoad: true,
                    id: 'key',
                    fields: ['key', 'value'],
                    data: [['no', 'off'], ['yes', 'on']]
                })
            }]]
        }]
        };
    }
});

/**
 * Asterisk SipPeer Edit Popup
 */
Tine.Voipmanager.AsteriskSipPeerEditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 800,
        height: 470,
        name: Tine.Voipmanager.AsteriskSipPeerEditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Voipmanager.AsteriskSipPeerEditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};