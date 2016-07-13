/*
 * Tine 2.0
 * 
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2007-2010 Metaways Infosystems GmbH (http://www.metaways.de)
 */
Ext.ns('Tine.Tinebase');

/**
 * <p>Abstract base class for all Tine applications</p>
 * 
 * @namespace   Tine.Tinebase
 * @class       Tine.Tinebase.Application
 * @extends     Ext.util.Observable
 * @consturctor
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 */
Tine.Tinebase.Application = function(config) {
    config = config || {};
    Ext.apply(this, config);
    
    Tine.Tinebase.Application.superclass.constructor.call(this);
    
    this.i18n = new Locale.Gettext();
    this.i18n.textdomain(this.appName);
    
    this.init();
    if (Tine.CoreData && Tine.CoreData.Manager) {
        this.registerCoreData();
    }
    this.initAutoHooks();
};

Ext.extend(Tine.Tinebase.Application, Ext.util.Observable , {
    
    /**
     * @cfg {String} appName
     * untranslated application name (required)
     */
    appName: null,
    
    /**
     * @cfg {Boolean} hasMainScreen
     */
    hasMainScreen: true,

    /**
     * @cfg {Object} routes
     */
    routes: null,

    /**
     * @property {Locale.gettext} i18n
     */
    i18n: null,

    /**
     * returns title of this application
     * 
     * @return {String}
     */
    getTitle: function() {
        return this.i18n._(this.appName);
    },
    
    /**
     * returns iconCls of this application
     * 
     * @param {String} target
     * @return {String}
     */
    getIconCls: function(target) {
        var iconCls = this.appName + 'IconCls';
        if (! Ext.util.CSS.getRule('.' + iconCls)) {
            iconCls = 'ApplicationIconCls';
        }

        return iconCls;
    },
    
    /**
     * returns the mainscreen of this application
     * 
     * @return {Tine.widgets.app.MainScreen}
     */
    getMainScreen: function() {
        if (! this.mainScreen && typeof Tine[this.appName].MainScreen === 'function') {
            this.mainScreen = new Tine[this.appName].MainScreen({
                app: this
            });
        }
        
        return this.mainScreen;
    },
    
    /**
     * returns registry of this app
     * 
     * @return {Ext.util.MixedCollection}
     */
    getRegistry: function() {
        return Tine[this.appName].registry;
    },
    
    /**
     * returns true if a specific feature is enabled for this application
     * 
     * @param {String} featureName
     * @return {Boolean}
     */
    featureEnabled: function(featureName) {
        var featureConfig = Tine[this.appName].registry.get("config").features,
            result = featureConfig && featureConfig.value[featureName];

        if (result == undefined) {
            // check defaults if key is missing
            result = featureConfig
                && featureConfig.definition
                && featureConfig.definition['default']
                && featureConfig.definition['default'][featureName];
        }

        return result;
    },
    
    /**
     * template function for subclasses to initialize application
     */
    init: Ext.emptyFn,

    /**
     * template function for subclasses to register app core data
     */
    registerCoreData: Ext.emptyFn,

    /**
     * init some auto hooks
     */
    initAutoHooks: function() {
        if (this.addButtonText) {
            Ext.ux.ItemRegistry.registerItem('Tine.widgets.grid.GridPanel.addButton', {
                text: this.i18n._hidden(this.addButtonText), 
                iconCls: this.getIconCls(),
                scope: this,
                handler: function() {
                    var ms = this.getMainScreen(),
                        cp = ms.getCenterPanel();
                        
                    cp.onEditInNewWindow.call(cp, {});
                }
            });
        }
    },

    /**
     * Ext 5 like route dispatcher
     *
     * @see http://docs.sencha.com/extjs/5.0/application_architecture/router.html
     *
     * @param {String} action
     * @param {Array} params
     */
    dispatchRoute: function(action, params) {
        var route, methodName, paramNames;

        if (this.routes) {
            for (route in this.routes) {
                if (this.routes.hasOwnProperty(route)) {
                    paramNames = route.split('/');
                    if (action == paramNames.shift()) {
                        methodName = this.routes[route].action;
                        break;
                    }
                }
            }
        }

        if (methodName) {
            // @TODO validate parameters according to docs

            return this[methodName].apply(this, params);
        }

        Ext.MessageBox.show(Ext.apply(defaults, {
            title: i18n._('Not Supported'),
            msg: i18n._('Your request is not supported by this version.'),
            fn: function() {
                Tine.Tinebase.common.reload();
            }
        }));
    },

    /**
     * template function for subclasses is called before app activation. Return false to cancel activation
     */
    onBeforeActivate: Ext.emptyFn,
    
    /**
     * template function for subclasses is called after app activation.
     */
    onActivate: Ext.emptyFn,
    
    /**
     * template function for subclasses is called before app deactivation. Return false to cancel deactivation
     */
    onBeforeDeActivate: Ext.emptyFn,
    
    /**
     * template function for subclasses is called after app deactivation.
     */
    onDeActivate: Ext.emptyFn
    
});
