/*
 * Ext JS Library 2.0 Alpha 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

/**
 * @class Ext.Panel
 * @extends Ext.Container
 * Panel is a container that has specific functionality and structural components that make it the perfect building
 * block for application-oriented user interfaces. The Panel contains bottom and top toolbars, along with separate
 * header, footer and body sections.  It also provides built-in expandable and collapsible behavior, along with a
 * variety of prebuilt tool buttons that can be wired up to provide other customized behavior.  Panels can be easily
 * dropped into any Container or layout, and the layout and rendering pipeline is completely managed by the framework.
 * @constructor
 * @param {Object} config The config object
 */
Ext.Panel = Ext.extend(Ext.Container, {
    /**
     * @cfg {Object/Array} tbar
     * The top toolbar of the panel.  This can be a {@link Ext.Toolbar} object, a toolbar config, or an array of
     * buttons/button configs to be added to the toolbar.  Note that this is not available as a property after render.
     * To access the top toolbar after render, use {@link #getTopToolbar}.
     */
    /**
     * @cfg {Object/Array} bbar
     * The bottom toolbar of the panel.  This can be a {@link Ext.Toolbar} object, a toolbar config, or an array of
     * buttons/button configs to be added to the toolbar.  Note that this is not available as a property after render.
     * To access the bottom toolbar after render, use {@link #getBottomToolbar}.
     */
    /**
     * @cfg {Boolean} header
     * True to create the header element explicitly, false to skip creating it.  By default, when header is not
     * specified, if a {@link #title} is set the header will be created automatically, otherwise it will not.  If
     * a title is set but header is explicitly set to false, the header will not be rendered.
     */
    /**
     * @cfg {Boolean} footer
     * True to create the footer element explicitly, false to skip creating it.  By default, when footer is not
     * specified, if one or more buttons have been added to the panel the footer will be created automatically,
     * otherwise it will not.
     */
    /**
     * @cfg {String} title
     * The title text to display in the panel header (defaults to '').  When a title is specified the header
     * element will automatically be created and displayed unless {@link #header} is explicitly set to false.
     */
    /**
     * @cfg {Array} buttons
     * An array of {@link Ext.Button} instances (or valid button configs) to add to the footer of this panel
     */
    /**
     * @cfg {Object/String/Function} autoLoad
     * A valid url spec according to the Updater {@link Ext.Updater#update} method.  If autoLoad is not null,
     * the panel will attempt to laod its contents immediately upon render.
     */
    /**
     * @cfg {Boolean} frame
     * True to render the panel with custom rounded borders, false to render with plain 1px square borders (defaults to false).
     */
    /**
     * @cfg {Boolean} border
     * True to display the borders of the panel's body element, false to hide them (defaults to true).  By default,
     * the border is a 2px wide inset border, but this can be further altered by setting {@link #bodyBorder} to false.
     */
    /**
     * @cfg {Boolean} bodyBorder
     * True to display an interior border on the body element of the panel, false to hide it (defaults to true).
     * This only applies when {@link #border} = true.  If border = true and bodyBorder = false, the border will display
     * as a 1px wide inset border, giving the entire body element an inset appearance.
     */
    /**
     * @cfg {Boolean} bodyStyle
     * Custom CSS styles to be applied to the body element in the format expected by {@link Ext.Element#applyStyles}
     * (defaults to null).
     */
    /**
     * @cfg {String} iconCls
     * A CSS class that will provide a background image to be used as the panel header icon (defaults to '').
     */
    /**
     * @cfg {Boolean} collapsible
     * True to make the panel collapsible and have the expand/collapse toggle button automatically rendered into
     * the header tool button area, false to keep the panel statically sized with no button (defaults to false).
     */
    /**
     * @cfg {Array} tools
     * An array of tool button configs to be added to the header tool area.  Each tool config should contain the id
     * of the tool, and can also contain an 'on' event handler config containing one or more event handlers to assign
     * to this tool.  The optional property 'hidden:true' can be included to hide the tool by default.  Example usage:
     * <pre><code>
tools:[{
    id:'refresh',
    // hidden:true,
    on:{
        click: function(){
            // refresh logic
        }
    }
}]
</code></pre>
     * A valid tool id should correspond to the CSS classes 'x-tool-{id}' (normal) and 'x-tool-{id}-over' (mouseover).
     * By default, the following tools are provided: toggle (default when collapsible = true), close, minimize,
     * maximize, restore, gear, pin, unpin, right, left, up, down, refresh, minus, plus, search and save.  Note that
     * these tool classes only provide the visual button -- any required functionality must be provided by adding
     * event handlers that implement the necessary behavior.
     */
    /**
     * @cfg {Boolean} hideCollapseTool
     * True to hide the expand/collapse toggle button when {@link #collapsible} = true, false to display it (defaults to false).
     */
    /**
     * @cfg {Boolean} titleCollapse
     * True to allow expanding and collapsing the panel (when {@link #collapsible} = true) by clicking anywhere in the
     * header bar, false to allow it only by clicking to tool button (defaults to false).
     */
    /**
     * @cfg {Boolean} autoScroll
     * True to use overflow:'auto' on the panel's body element and show scroll bars automatically when necessary,
     * false to clip any overflowing content (defaults to false).
     */
    /**
     * @cfg {Boolean} floating
     * True to float the panel (absolute position it with automatic shimming and shadow), false to display it
     * inline where it is rendered (defaults to false).  Note that by default, setting floating to true will cause the
     * panel to display at negative offsets so that it is hidden -- because the panel is absolute positioned, the
     * position must be set explicitly after render (e.g., myPanel.setPosition(100,100);).  Also, when floating a
     * panel you should always assign a fixed width, otherwise it will be auto width and will expand to fill to the
     * right edge of the viewport.
     */
    /**
     * @cfg {Boolean/String} shadow
     * True (or a valid Ext.Shadow {@link Ext.Shadow#mode} value) to display a shadow behind the panel, false to
     * display no shadow (defaults to 'sides').  Note that this option opnly applies when floating = true.
     */
    /**
     * @cfg {Boolean} shim
     * False to disable the iframe shim in browsers which need one (defaults to true).  Note that this option
     * opnly applies when floating = true.
     */
    /**
     * @cfg {String} html
     * An arbitrary HTML fragment to use as the panel's body content (defaults to '').
     */
    /**
     * @cfg {String} contentEl
     * The id of an existing HTML node to use as the panel's body content (defaults to '').
     */
    /**
     * @cfg {Object/Array} keys
     * A KeyMap config object (in the format expected by {@link Ext.KeyMap#addBinding) used to assign custom key 
     * handling to this panel (defaults to null).
     */
    /**
    * @cfg {String} baseCls
    * The base CSS class to apply to this panel's element (defaults to 'x-panel').
    */
    baseCls : 'x-panel',
    /**
    * @cfg {String} collapsedCls
    * A CSS class to add to the panel's element after it has been collapsed (defaults to 'x-panel-collapsed').
    */
    collapsedCls : 'x-panel-collapsed',
    /**
    * @cfg {Boolean} maskDisabled
    * True to mask the panel when it is disabled, false to not mask it (defaults to true).  Either way, the panel
    * will always tell its contained elements to disable themselves when it is disabled, but masking the panel
    * can provide an additional visual cue that the panel is disabled.
    */
    maskDisabled: true,
    /**
    * @cfg {Boolean} animCollapse
    * True to animate the transition when the panel is collapsed, false to skip the animation (defaults to true
    * if the {@link Ext.Fx} class is available, otherwise false).
    */
    animCollapse: Ext.enableFx,
    /**
    * @cfg {Boolean} headerAsText
    * True to display the panel title in the header, false to hide it (defaults to true).
    */
    headerAsText: true,
    /**
    * @cfg {String} buttonAlign
    * The alignment of any buttons added to this panel.  Valid values are 'right,' 'left' and 'center' (defaults to 'right').
    */
    buttonAlign: 'right',
    /**
     * @cfg {Boolean} collapsed
     * True to render the panel collapsed, false to render it expanded (defaults to false).
     */
    collapsed : false,
    /**
    * @cfg {Boolean} collapseFirst
    * True to make sure the collapse/expand toggle button always renders first (to the left of) any other tools
    * in the panel's title bar, false to render it last (defaults to true).
    */
    collapseFirst: true,
    /**
     * @cfg {Number} minButtonWidth
     * Minimum width in pixels of all buttons in this panel (defaults to 75)
     */
    minButtonWidth:75,
    /**
     * @cfg {String} elements
     * A comma-delimited list of panel elements to initialize when the panel is rendered.  Normally, this list will be
     * generated automatically based on the items added to the panel at config time, but sometimes it might be useful to
     * make sure a structural element is rendered even if not specified at config time (for example, you may want
     * to add a button or toolbar dynamically after the panel has been rendered).  Adding those elements to this
     * list will allocate the required placeholders in the panel when it is rendered.  Valid values are: 'header,'
     * 'body,' 'footer,' 'tbar' (top bar) abd 'bbar' (bottom bar) - defaults to 'body.'
     */
    elements : 'body',

    // protected - these could both be used to customize the behavior of the window,
    // but changing them would not be useful without further mofifications and could
    // lead to unexpected or undesirable results.
    toolTarget : 'header',
    collapseEl : 'bwrap',
    slideAnchor : 't',

    // private, notify box this class will handle heights
    deferHeight: true,
    // private
    expandDefaults: {
        duration:.25
    },
    // private
    collapseDefaults: {
        duration:.25
    },

    // private
    initComponent : function(){
        Ext.Panel.superclass.initComponent.call(this);

        this.addEvents({
            /**
             * @event bodyresize
             * Fires after the panel has been resized.
             * @param {Ext.Panel} this
             * @param {Number} width The panel's new width
             * @param {Number} height The panel's new height
             */
            bodyresize : true,
            /**
             * @event titlechange
             * Fires after the panel title has been set or changed.
             * @param {Ext.Panel} this
             * @param {String} The new title
             */
            titlechange: true,
            /**
             * @event collapse
             * Fires after the panel has been collapsed.
             * @param {Ext.Panel} this
             */
            collapse : true,
            /**
             * @event expand
             * Fires after the panel has been expanded.
             * @param {Ext.Panel} this
             */
            expand:true,
            /**
             * @event beforecollapse
             * Fires before the panel is collapsed.  A handler can return false to cancel the collapse.
             * @param {Ext.Panel} this
             * @param {Boolean} animate True if the collapse is animated, else false
             */
            beforecollapse : true,
            /**
             * @event beforeexpand
             * Fires before the panel is expanded.  A handler can return false to cancel the expand.
             * @param {Ext.Panel} this
             * @param {Boolean} animate True if the expand is animated, else false
             */
            beforeexpand:true,
            /**
             * @event beforeclose
             * Fires before the panel is closed.  Note that panels do not directly support being closed, but some
             * panel subclasses do (like {@link Ext.Window}).  This event only applies to such subclasses.
             * A handler can return false to cancel the close.
             * @param {Ext.Panel} this
             */
            beforeclose: true,
            /**
             * @event close
             * Fires after the panel is closed.  Note that panels do not directly support being closed, but some
             * panel subclasses do (like {@link Ext.Window}).
             * @param {Ext.Panel} this
             */
            close: true,
            /**
             * @event activate
             * Fires after the panel has been visually activated.  Note that panels do not directly support being
             * activated, but some panel subclasses do (like {@link Ext.Window}).
             * @param {Ext.Panel} this
             */
            activate: true,
            /**
             * @event deactivate
             * Fires after the panel has been visually deactivated.  Note that panels do not directly support being
             * deactivated, but some panel subclasses do (like {@link Ext.Window}).
             * @param {Ext.Panel} this
             */
            deactivate: true
        });

        // shortcuts
        if(this.tbar){
            this.elements += ',tbar';
            if(typeof this.tbar == 'object'){
                this.topToolbar = this.tbar;
            }
            delete this.tbar;
        }
        if(this.bbar){
            this.elements += ',bbar';
            if(typeof this.bbar == 'object'){
                this.bottomToolbar = this.bbar;
            }
            delete this.bbar;
        }

        if(this.header === true){
            this.elements += ',header';
            delete this.header;
        }else if(this.title && this.header !== false){
            this.elements += ',header';
        }

        if(this.footer === true){
            this.elements += ',footer';
            delete this.footer;
        }

        if(this.buttons){
            var btns = this.buttons;
            delete this.buttons;
            for(var i = 0, len = btns.length; i < len; i++) {
                this.addButton(btns[i]);
            }
        }
        if(this.autoLoad){
            this.on('render', this.doAutoLoad, this, {delay:10});
        }
    },

    // private
    createElement : function(name, pnode){
        if(this[name]){
            pnode.appendChild(this[name].dom);
            return;
        }

        if(name === 'bwrap' || this.elements.indexOf(name) != -1){
            if(this[name+'Cfg']){
                this[name] = Ext.fly(pnode).createChild(this[name+'Cfg']);
            }else{
                var el = document.createElement('div');
                el.className = this[name+'Cls'];
                this[name] = Ext.get(pnode.appendChild(el));
            }
        }
    },

    // private
    onRender : function(ct, position){
        Ext.Panel.superclass.onRender.call(this, ct, position);

        this.createClasses();

        if(this.el){ // existing markup
            this.el.addClass(this.baseCls);
            this.header = this.el.down('.'+this.headerCls);
            this.bwrap = this.el.down('.'+this.bwrapCls);
            var cp = this.bwrap ? this.bwrap : this.el;
            this.tbar = cp.down('.'+this.tbarCls);
            this.body = cp.down('.'+this.bodyCls);
            this.bbar = cp.down('.'+this.bbarCls);
            this.footer = cp.down('.'+this.footerCls);
            this.fromMarkup = true;
        }else{
            this.el = ct.createChild({
                id: this.id,
                cls: this.baseCls
            }, position);
        }
        var el = this.el, d = el.dom;

        if(this.cls){
            this.el.addClass(this.cls);
        }

        if(this.buttons){
            this.elements += ',footer';
        }

        // This block allows for maximum flexibility and performance when using existing markup

        // framing requires special markup
        if(this.frame){
            el.insertHtml('afterBegin', String.format(Ext.Element.boxMarkup, this.baseCls));

            this.createElement('header', d.firstChild.firstChild.firstChild);
            this.createElement('bwrap', d);

            // append the mid and bottom frame to the bwrap
            var bw = this.bwrap.dom;
            var ml = d.childNodes[1], bl = d.childNodes[2];
            bw.appendChild(ml);
            bw.appendChild(bl);

            var mc = bw.firstChild.firstChild.firstChild;
            this.createElement('tbar', mc);
            this.createElement('body', mc);
            this.createElement('bbar', mc);
            this.createElement('footer', bw.lastChild.firstChild.firstChild);

            if(!this.footer){
                this.bwrap.dom.lastChild.className += ' x-panel-nofooter';
            }
        }else{
            this.createElement('header', d);
            this.createElement('bwrap', d);

            // append the mid and bottom frame to the bwrap
            var bw = this.bwrap.dom;
            this.createElement('tbar', bw);
            this.createElement('body', bw);
            this.createElement('bbar', bw);
            this.createElement('footer', bw);

            if(!this.header){
                this.body.addClass(this.bodyCls + '-noheader');
                if(this.tbar){
                    this.tbar.addClass(this.tbarCls + '-noheader');
                }
            }
        }

        if(this.border === false){
            this.el.addClass(this.baseCls + '-noborder');
            this.body.addClass(this.bodyCls + '-noborder');
            if(this.header){
                this.header.addClass(this.headerCls + '-noborder');
            }
            if(this.footer){
                this.footer.addClass(this.footerCls + '-noborder');
            }
            if(this.tbar){
                this.tbar.addClass(this.tbarCls + '-noborder');
            }
            if(this.bbar){
                this.bbar.addClass(this.bbarCls + '-noborder');
            }
        }

        if(this.bodyBorder === false){
           this.body.addClass(this.bodyCls + '-noborder');
        }

        if(this.bodyStyle){
           this.body.applyStyles(this.bodyStyle);
        }

        this.bwrap.enableDisplayMode('block');

        if(this.header){
            this.header.unselectable();

            // for tools, we need to wrap any existing header markup
            if(this.headerAsText){
                this.header.dom.innerHTML =
                    '<span class="' + this.headerTextCls + '">'+this.header.dom.innerHTML+'</span>';

                if(this.iconCls){
                    this.setIconClass(this.iconCls);
                }
            }
        }

        if(this.floating){
            this.makeFloating(this.floating);
        }
        
        if(this.collapsible){
            this.tools = this.tools ? this.tools.slice(0) : [];
            if(!this.hideCollapseTool){
                this.tools[this.collapseFirst?'unshift':'push']({
                    id: 'toggle',
                    handler : this.toggleCollapse,
                    scope: this
                });
            }
            if(this.titleCollapse && this.header){
                this.header.on('click', this.toggleCollapse, this);
                this.header.setStyle('cursor', 'pointer');
            }
        }
        if(this.tools){
            var ts = this.tools;
            this.tools = {};
            this.addTool.apply(this, ts);
        }else{
            this.tools = {};
        }

        if(this.buttons && this.buttons.length > 0){
            // tables are required to maintain order and for correct IE layout
            var tb = this.footer.createChild({cls:'x-panel-btns-ct', cn: {
                cls:"x-panel-btns x-panel-btns-"+this.buttonAlign,
                html:'<table cellspacing="0"><tbody><tr></tr></tbody></table><div class="x-clear"></div>'
            }}, null, true);
            var tr = tb.getElementsByTagName('tr')[0];
            for(var i = 0, len = this.buttons.length; i < len; i++) {
                var b = this.buttons[i];
                var td = document.createElement('td');
                td.className = 'x-panel-btn-td';
                b.render(tr.appendChild(td));
            }
        }

        if(this.tbar && this.topToolbar){
            if(this.topToolbar instanceof Array){
                this.topToolbar = new Ext.Toolbar(this.topToolbar);
            }
            this.topToolbar.render(this.tbar);
        }
        if(this.bbar && this.bottomToolbar){
            if(this.bottomToolbar instanceof Array){
                this.bottomToolbar = new Ext.Toolbar(this.bottomToolbar);
            }
            this.bottomToolbar.render(this.bbar);
        }
    },

    /**
     * Sets the CSS class that provides the icon image for this panel.  This method will replace any existing
     * icon class if one has already been set.
     * @param {String} cls The new CSS class name
     */
    setIconClass : function(cls){
        var old = this.iconCls;
        this.iconCls = cls;
        if(this.rendered){
            if(this.frame){
                this.header.addClass('x-panel-icon');
                this.header.replaceClass(old, this.iconCls);
            }else{
                var hd = this.header.dom;
                var img = hd.firstChild && String(hd.firstChild.tagName).toLowerCase() == 'img' ? hd.firstChild : null;
                if(img){
                    Ext.fly(img).replaceClass(old, this.iconCls);
                }else{
                    Ext.DomHelper.insertBefore(hd.firstChild, {
                        tag:'img', src: Ext.BLANK_IMAGE_URL, cls:'x-panel-inline-icon '+this.iconCls
                    });
                 }
            }
        }
    },

    // private
    makeFloating : function(cfg){
        this.floating = true;
        this.el = new Ext.Layer(
            typeof cfg == 'object' ? cfg : {
                shadow: this.shadow !== undefined ? this.shadow : 'sides',
                constrain:false,
                shim: this.shim === false ? false : undefined
            }, this.el
        );
    },

    /**
     * Returns the toolbar from the top (tbar) section of the panel.
     * @return {Ext.Toolbar} The toolbar
     */
    getTopToolbar : function(){
        return this.topToolbar;
    },

    /**
     * Returns the toolbar from the bottom (bbar) section of the panel.
     * @return {Ext.Toolbar} The toolbar
     */
    getBottomToolbar : function(){
        return this.bottomToolbar;  
    },

    /**
     * Adds a button to this panel.  Note that this method must be called prior to rendering.  The preferred
     * approach is to add buttons via the {@link #buttons} config.
     * @param {String/Object} config A valid {@link Ext.Button} config.  A string will become the text for a default
     * button config, an object will be treated as a button config object.
     * @param {Function} handler The function to be called on button {@link Ext.Button#click}
     * @param {Object} scope The scope to use for the button handler function
     * @return {Ext.Button} The button that was added
     */
    addButton : function(config, handler, scope){
        var bc = {
            handler: handler,
            scope: scope,
            minWidth: this.minButtonWidth,
            hideParent:true
        };
        if(typeof config == "string"){
            bc.text = config;
        }else{
            Ext.apply(bc, config);
        }
        var btn = new Ext.Button(bc);
        if(!this.buttons){
            this.buttons = [];
        }
        this.buttons.push(btn);
        return btn;
    },

    // private
    addTool : function(){
        if(!this[this.toolTarget]) { // no where to render tools!
            return;
        }
        if(!this.toolTemplate){
            // initialize the global tool template on first use
            var tt = new Ext.Template(
                 '<div class="x-tool x-tool-{id}">&#160</div>'
            );
            tt.disableFormats = true;
            tt.compile();
            Ext.Panel.prototype.toolTemplate = tt;
        }
        for(var i = 0, a = arguments, len = a.length; i < len; i++) {
            var tc = a[i], overCls = 'x-tool-'+tc.id+'-over';
            var t = this.toolTemplate.insertFirst(this[this.toolTarget], tc, true);
            this.tools[tc.id] = t;
            t.enableDisplayMode('block');
            t.on('click', this.createToolHandler(t, tc, overCls, this));
            if(tc.on){
                t.on(tc.on);
            }
            if(tc.hidden){
                t.hide();
            }
            t.addClassOnOver(overCls);
        }
    },

    onShow : function(){
        if(this.floating){
            return this.el.show();
        }
        Ext.Panel.superclass.onShow.call(this);
    },

    onHide : function(){
        if(this.floating){
            return this.el.hide();
        }
        Ext.Panel.superclass.onHide.call(this);
    },

    // private
    createToolHandler : function(t, tc, overCls, panel){
        return function(e){
            t.removeClass(overCls);
            e.stopEvent();
            if(tc.handler){
                tc.handler.call(tc.scope || t, e, t, panel);
            }
        };
    },

    // private
    afterRender : function(){
        if(this.fromMarkup && this.height === undefined && !this.autoHeight){
            this.height = this.el.getHeight();
        }
        if(this.floating && !this.hidden && !this.initHidden){
            this.el.show();
        }
        if(this.title){
            this.setTitle(this.title);
        }
        if(this.autoScroll){
            this.body.dom.style.overflow = 'auto';
        }
        if(this.html){
            this.body.update(typeof this.html == 'object' ?
                             Ext.DomHelper.markup(this.html) :
                             this.html);
            delete this.html;
        }
        if(this.contentEl){
            var ce = Ext.getDom(this.contentEl);
            Ext.fly(ce).removeClass(['x-hidden', 'x-hide-display']);
            this.body.dom.appendChild(ce);
        }
        if(this.collapsed){
            this.collapsed = false;
            this.collapse(false);
        }
        Ext.Panel.superclass.afterRender.call(this); // do sizing calcs last
        this.initEvents();
    },

    // private
    getKeyMap : function(){
        if(!this.keyMap){
            this.keyMap = new Ext.KeyMap(this.el, this.keys);
        }
        return this.keyMap;
    },

    // private
    initEvents : function(){
        if(this.keys){
            this.getKeyMap();
        }
        if(this.draggable){
            this.initDraggable();
        }
    },

    initDraggable : function(){
        this.dd = new Ext.Panel.DD(this, typeof this.draggable == 'boolean' ? null : this.draggable);
    },

    // private
    beforeEffect : function(){
        if(this.floating){
            this.el.beforeAction();
        }
        this.el.addClass('x-panel-animated');
    },

    // private
    afterEffect : function(){
        this.syncShadow();
        this.el.removeClass('x-panel-animated');
    },

    // private - wraps up an animation param with internal callbacks
    createEffect : function(a, cb, scope){
        var o = {
            scope:scope,
            block:true
        };
        if(a === true){
            o.callback = cb;
            return o;
        }else if(!a.callback){
            o.callback = cb;
        }else { // wrap it up
            o.callback = function(){
                cb.call(scope);
                Ext.callback(a.callback, a.scope);
            };
        }
        return Ext.applyIf(o, a);
    },

    /**
     * Collapses the panel body so that it becomes hidden.  Fires the {@link #beforecollapse} event which will
     * cancel the collapse action if it returns false.
     * @param {Boolean} animate True to animate the transition, else false (defaults to the value of the
     * {@link #animCollapse} panel config)
     * @return {Ext.Panel} this
     */
    collapse : function(animate){
        if(this.collapsed || this.el.hasFxBlock() || this.fireEvent('beforecollapse', this, animate) === false){
            return;
        }
        var doAnim = animate === true || (animate !== false && this.animCollapse);
        this.beforeEffect();
        this.onCollapse(doAnim, animate);
        return this;
    },

    // private
    onCollapse : function(doAnim, animArg){
        if(doAnim){
            this[this.collapseEl].slideOut(this.slideAnchor,
                    Ext.apply(this.createEffect(animArg||true, this.afterCollapse, this),
                        this.collapseDefaults));
        }else{
            this[this.collapseEl].hide();
            this.afterCollapse();
        }
    },

    // private
    afterCollapse : function(){
        this.collapsed = true;
        this.el.addClass(this.collapsedCls);
        this.afterEffect();
        this.fireEvent('collapse', this);
    },

    /**
     * Expands the panel body so that it becomes visible.  Fires the {@link #beforeexpand} event which will
     * cancel the expand action if it returns false.
     * @param {Boolean} animate True to animate the transition, else false (defaults to the value of the
     * {@link #animCollapse} panel config)
     * @return {Ext.Panel} this
     */
    expand : function(animate){
        if(!this.collapsed || this.el.hasFxBlock() || this.fireEvent('beforeexpand', this, animate) === false){
            return;
        }
        var doAnim = animate === true || (animate !== false && this.animCollapse);
        this.el.removeClass(this.collapsedCls);
        this.beforeEffect();
        this.onExpand(doAnim, animate);
        return this;
    },

    // private
    onExpand : function(doAnim, animArg){
        if(doAnim){
            this[this.collapseEl].slideIn(this.slideAnchor,
                    Ext.apply(this.createEffect(animArg||true, this.afterExpand, this),
                        this.expandDefaults));
        }else{
            this[this.collapseEl].show();
            this.afterExpand();
        }
    },

    // private
    afterExpand : function(){
        this.collapsed = false;
        this.afterEffect();
        this.fireEvent('expand', this);
    },

    /**
     * Shortcut for performing an {@link #expand} or {@link #collapse} based on the current state of the panel.
     * @param {Boolean} animate True to animate the transition, else false (defaults to the value of the
     * {@link #animCollapse} panel config)
     * @return {Ext.Panel} this
     */
    toggleCollapse : function(animate){
        this[this.collapsed ? 'expand' : 'collapse'](animate);
        return this;
    },

    // private
    onDisable : function(){
        if(this.rendered && this.maskDisabled){
            this.el.mask();
        }
        Ext.Panel.superclass.onDisable.call(this);
    },

    // private
    onEnable : function(){
        if(this.rendered && this.maskDisabled){
            this.el.unmask();
        }
        Ext.Panel.superclass.onEnable.call(this);
    },

    // private
    onResize : function(w, h){
        if(w !== undefined || h !== undefined){
            if(!this.collapsed){
                if(typeof w == 'number'){
                    this.body.setWidth(
                            this.adjustBodyWidth(w - this.getFrameWidth()));
                }else if(w == 'auto'){
                    this.body.setWidth(w);
                }

                if(typeof h == 'number'){
                    this.body.setHeight(
                            this.adjustBodyHeight(h - this.getFrameHeight()));
                }else if(h == 'auto'){
                    this.body.setHeight(h);
                }
            }else{
                this.queuedBodySize = {width: w, height: h};
                if(!this.queuedExpand && this.allowQueuedExpand !== false){
                    this.queuedExpand = true;
                    this.on('expand', function(){
                        delete this.queuedExpand;
                        this.onResize(this.queuedBodySize.width, this.queuedBodySize.height);
                        this.doLayout();
                    }, this, {single:true});
                }
            }
            this.fireEvent('bodyresize', this, w, h);
        }
        this.syncShadow();
    },

    // private
    adjustBodyHeight : function(h){
        return h;
    },

    // private
    adjustBodyWidth : function(w){
        return w;
    },

    // private
    onPosition : function(){
        this.syncShadow();
    },

    // private
    onDestroy : function(){
        if(this.tools){
            for(var k in this.tools){
                Ext.destroy(this.tools[k]);
            }
        }
        Ext.destroy(
            this.topToolbar,
            this.bottomToolbar
        );
        Ext.Panel.superclass.onDestroy.call(this);
    },

    /**
     * Returns the width in pixels of the framing elements of this panel (not including the body width).  To
     * retrieve the body width see {@link #getInnerWidth}.
     * @return {Number} The frame width
     */
    getFrameWidth : function(){
        var w = this.el.getFrameWidth('lr');

        if(this.frame){
            var l = this.bwrap.dom.firstChild;
            w += (Ext.fly(l).getFrameWidth('l') + Ext.fly(l.firstChild).getFrameWidth('r'));
            var mc = this.bwrap.dom.firstChild.firstChild.firstChild;
            w += Ext.fly(mc).getFrameWidth('lr');
        }
        return w;
    },

    /**
     * Returns the height in pixels of the framing elements of this panel (including any top and bottom bars and
     * header and footer elements, but not including the body height).  To retrieve the body height see {@link #getInnerHeight}.
     * @return {Number} The frame height
     */
    getFrameHeight : function(){
        var h  = this.el.getFrameWidth('tb');
        h += (this.tbar ? this.tbar.getHeight() : 0) +
             (this.bbar ? this.bbar.getHeight() : 0);

        if(this.frame){
            var hd = this.el.dom.firstChild;
            var ft = this.bwrap.dom.lastChild;
            h += (hd.offsetHeight + ft.offsetHeight);
            var mc = this.bwrap.dom.firstChild.firstChild.firstChild;
            h += Ext.fly(mc).getFrameWidth('tb');
        }else{
            h += (this.header ? this.header.getHeight() : 0) +
                (this.footer ? this.footer.getHeight() : 0);
        }
        return h;
    },

    /**
     * Returns the width in pixels of the body element (not including the width of any framing elements).
     * For the frame width see {@link #getFrameWidth}.
     * @return {Number} The body width
     */
    getInnerWidth : function(){
        return this.getSize().width - this.getFrameWidth();
    },

    /**
     * Returns the height in pixels of the body element (not including the height of any framing elements).
     * For the frame height see {@link #getFrameHeight}.
     * @return {Number} The body height
     */
    getInnerHeight : function(){
        return this.getSize().height - this.getFrameHeight();
    },

    // private
    syncShadow : function(){
        if(this.floating){
            this.el.sync(true);
        }
    },

    // private
    getLayoutTarget : function(){
        return this.body;
    },

    /**
     * Sets the title text for the panel and optioanlly the icon class.
     * @param {String} title The title text to set
     * @param {String} (optional) iconCls A CSS class that provides the icon image for this panel
     */
    setTitle : function(title, iconCls){
        this.title = title;
        if(this.header && this.headerAsText){
            this.header.child('span').update(title);
        }
        if(iconCls){
            this.setIconClass(iconCls);
        }
        this.fireEvent('titlechange', this, title);
        return this;
    },

    /**
     * Get the {@link Ext.Updater} for this panel. Enables you to perform Ajax updates of this panel's body.
     * @return {Ext.Updater} The Updater
     */
    getUpdater : function(){
        return this.body.getUpdater();
    },

     /**
     * Loads this content panel immediately with content returned from an XHR call.
     * @param {Object/String/Function} config A config object containing any of the following options:
<pre><code>
panel.load({
    url: "your-url.php",
    params: {param1: "foo", param2: "bar"}, // or a URL encoded string
    callback: yourFunction,
    scope: yourObject, // optional scope for the callback
    discardUrl: false,
    nocache: false,
    text: "Loading...",
    timeout: 30,
    scripts: false
});
</code></pre>
     * The only required property is url. The optional properties nocache, text and scripts
     * are shorthand for disableCaching, indicatorText and loadScripts and are used to set their
     * associated property on this panel Updater instance.
     * @return {Ext.Panel} this
     */
    load : function(){
        var um = this.body.getUpdater();
        um.update.apply(um, arguments);
        return this;
    },

    // private
    beforeDestroy : function(){
        Ext.Element.uncache(
            this.header,
            this.tbar,
            this.bbar,
            this.footer,
            this.body
        );
    },

    // private
    createClasses : function(){
        this.headerCls = this.baseCls + '-header';
        this.headerTextCls = this.baseCls + '-header-text';
        this.bwrapCls = this.baseCls + '-bwrap';
        this.tbarCls = this.baseCls + '-tbar';
        this.bodyCls = this.baseCls + '-body';
        this.bbarCls = this.baseCls + '-bbar';
        this.footerCls = this.baseCls + '-footer';
    },

    // private
    createGhost : function(cls, useShim, appendTo){
        var el = document.createElement('div');
        el.className = 'x-panel-ghost ' + (cls ? cls : '');
        if(this.header){
            el.appendChild(this.el.dom.firstChild.cloneNode(true));
        }
        Ext.fly(el.appendChild(document.createElement('ul'))).setHeight(this.bwrap.getHeight());
        el.style.width = this.el.dom.offsetWidth;
        if(!appendTo){
            this.container.dom.appendChild(el);
        }else{
            Ext.getDom(appendTo).appendChild(el);
        }
        if(useShim !== false && this.el.useShim !== false){
            var layer = new Ext.Layer({shadow:false, useDisplay:true, constrain:false}, el);
            layer.show();
            return layer;
        }else{
            return new Ext.Element(el);
        }
    },

    // private
    doAutoLoad : function(){
        this.body.load(
            typeof this.autoLoad == 'object' ?
                this.autoLoad : {url: this.autoLoad});
    }
});
Ext.reg('panel', Ext.Panel);
