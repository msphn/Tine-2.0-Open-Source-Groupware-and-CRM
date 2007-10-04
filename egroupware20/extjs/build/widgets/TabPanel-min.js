/*
 * Ext JS Library 2.0 Alpha 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://extjs.com/license
 */

Ext.TabPanel=Ext.extend(Ext.Panel,{monitorResize:true,deferredRender:true,tabWidth:120,minTabWidth:30,resizeTabs:false,enableTabScroll:false,scrollIncrement:0,scrollRepeatInterval:400,scrollDuration:0.35,animScroll:true,tabPosition:"top",elements:"body",baseCls:"x-tab-panel",frame:false,autoTabs:false,itemCls:"x-tab-item",activeTab:null,headerAsText:false,tabMargin:2,plain:false,autoTabSelector:"div.x-tab",hideBorders:true,initComponent:function(){this.frame=false;Ext.TabPanel.superclass.initComponent.call(this);this.addEvents({beforetabchange:true,tabchange:true,contextmenu:true});this.setLayout(new Ext.layout.CardLayout({deferredRender:this.deferredRender}));if(this.tabPosition=="top"){this.elements+=",header";this.stripTarget="header"}else{this.elements+=",footer";this.stripTarget="footer"}if(!this.stack){this.stack=Ext.TabPanel.AccessStack()}this.initItems()},render:function(){Ext.TabPanel.superclass.render.apply(this,arguments);if(this.activeTab!==undefined){var A=this.activeTab;delete this.activeTab;this.setActiveTab(A)}},onRender:function(C,A){Ext.TabPanel.superclass.onRender.call(this,C,A);if(this.plain){var E=this.tabPosition=="top"?"header":"footer";this[E].addClass("x-tab-panel-"+E+"-plain")}var B=this[this.stripTarget];this.stripWrap=B.createChild({cls:"x-tab-strip-wrap",cn:{tag:"ul",cls:"x-tab-strip x-tab-strip-"+this.tabPosition}});this.stripSpacer=B.createChild({cls:"x-tab-strip-spacer"});this.strip=new Ext.Element(this.stripWrap.dom.firstChild);this.edge=this.strip.createChild({tag:"li",cls:"x-tab-edge"});this.strip.createChild({cls:"x-clear"});this.body.addClass("x-tab-panel-body-"+this.tabPosition);if(!this.itemTpl){var D=new Ext.Template("<li class=\"{cls}\" id=\"{id}\"><a class=\"x-tab-strip-close\" onclick=\"return false;\"></a>","<a class=\"x-tab-right\" href=\"#\" onclick=\"return false;\"><em class=\"x-tab-left\">","<span class=\"x-tab-strip-inner\"><span class=\"x-tab-strip-text {iconCls}\">{text}</span></span>","</em></a></li>");D.disableFormats=true;D.compile();Ext.TabPanel.prototype.itemTpl=D}this.items.each(this.initTab,this)},afterRender:function(){Ext.TabPanel.superclass.afterRender.call(this);if(this.autoTabs){this.readTabs(false)}},initEvents:function(){Ext.TabPanel.superclass.initEvents.call(this);this.on("add",this.onAdd,this);this.on("remove",this.onRemove,this);this.strip.on("mousedown",this.onStripMouseDown,this);this.strip.on("click",this.onStripClick,this);this.strip.on("contextmenu",this.onStripContextMenu,this)},findTargets:function(C){var B=null;var A=C.getTarget("li",this.strip);if(A){B=this.getComponent(A.id.split("__")[1]);if(B.disabled){return{close:null,item:null,el:null}}}return{close:C.getTarget(".x-tab-strip-close",this.strip),item:B,el:A}},onStripMouseDown:function(B){B.preventDefault();if(B.button!=0){return }var A=this.findTargets(B);if(A.close){this.remove(A.item);return }if(A.item){this.setActiveTab(A.item)}},onStripClick:function(B){var A=this.findTargets(B);if(!A.close&&A.item&A.item!=this.activeTab){this.setActiveTab(A.item)}},onStripContextMenu:function(B){B.preventDefault();var A=this.findTargets(B);if(A.item){this.fireEvent("contextmenu",this,A.item,B)}},readTabs:function(D){if(D===true){this.items.each(function(G){this.remove(G)},this)}var C=this.el.query(this.autoTabSelector);for(var B=0,A=C.length;B<A;B++){var E=C[B];var F=E.getAttribute("title");E.removeAttribute("title");this.add({title:F,contentEl:E})}},initTab:function(D,B){var E=this.strip.dom.childNodes[B];var A=D.closable?"x-tab-strip-closable":"";if(D.disabled){A+=" x-item-disabled"}if(D.iconCls){A+=" x-tab-with-icon"}var F={id:this.id+"__"+D.id,text:D.title,cls:A,iconCls:D.iconCls||""};var C=E?this.itemTpl.insertBefore(E,F):this.itemTpl.append(this.strip,F);Ext.fly(C).addClassOnOver("x-tab-strip-over");if(D.tabTip){Ext.fly(C).child("span.x-tab-strip-text",true).qtip=D.tabTip}D.on("disable",this.onItemDisabled,this);D.on("enable",this.onItemEnabled,this);D.on("titlechange",this.onItemTitleChanged,this);D.on("beforeshow",this.onBeforeShowItem,this)},onAdd:function(C,B,A){this.initTab(B,A);this.delegateUpdates()},onBeforeAdd:function(B){var A=B.events?(this.items.containsKey(B.id)?B:null):this.items.get(B);if(A){this.setActiveTab(B);return false}Ext.TabPanel.superclass.onBeforeAdd.apply(this,arguments);var C=B.elements;B.elements=C?C.replace(",header",""):C;B.border=(B.border===true)},onRemove:function(D,C){var B=this.getTabEl(C);if(B){B.parentNode.removeChild(B)}this.stack.remove(C);if(C==this.activeTab){var A=this.stack.next();if(A){this.setActiveTab(A)}else{this.setActiveTab(0)}}this.delegateUpdates()},onBeforeShowItem:function(A){if(A!=this.activeTab){this.setActiveTab(A);return false}},onItemDisabled:function(B){var A=this.getTabEl(B);if(A){Ext.fly(A).addClass("x-item-disabled")}this.stack.remove(B)},onItemEnabled:function(B){var A=this.getTabEl(B);if(A){Ext.fly(A).removeClass("x-item-disabled")}},onItemTitleChanged:function(B){var A=this.getTabEl(B);if(A){Ext.fly(A).child("span.x-tab-strip-text",true).innerHTML=B.title}},getTabEl:function(A){return document.getElementById(this.id+"__"+A.id)},onResize:function(){Ext.TabPanel.superclass.onResize.apply(this,arguments);this.delegateUpdates()},beginUpdate:function(){this.suspendUpdates=true},endUpdate:function(){this.suspendUpdates=false;this.delegateUpdates()},hideTabStripItem:function(B){B=this.getComponent(B);var A=this.getTabEl(B);if(A){A.style.display="none";this.delegateUpdates()}},unhideTabStripItem:function(B){B=this.getComponent(B);var A=this.getTabEl(B);if(A){A.style.display="";this.delegateUpdates()}},delegateUpdates:function(){if(this.suspendUpdates){return }if(this.resizeTabs&&this.rendered){this.autoSizeTabs()}if(this.enableTabScroll&&this.rendered){this.autoScrollTabs()}},autoSizeTabs:function(){var F=this.items.length;var B=this.header.dom.offsetWidth;var A=this.header.dom.clientWidth;if(!this.resizeTabs||F<1||!A){return }var H=Math.max(Math.min(Math.floor((A-4)/F)-this.tabMargin,this.tabWidth),this.minTabWidth);this.lastTabWidth=H;var J=this.stripWrap.dom.getElementsByTagName("li");for(var D=0,G=J.length-1;D<G;D++){var I=J[D];var K=I.childNodes[1].firstChild.firstChild;var E=I.offsetWidth;var C=K.offsetWidth;K.style.width=(H-(E-C))+"px"}},adjustBodyWidth:function(A){if(this.header){this.header.setWidth(A)}if(this.footer){this.footer.setWidth(A)}return A},setActiveTab:function(C){C=this.getComponent(C);if(!C||this.fireEvent("beforetabchange",this,C,this.activeTab)===false){return }if(!this.rendered){this.activeTab=C;return }if(this.activeTab!=C){if(this.activeTab){var A=this.getTabEl(this.activeTab);if(A){Ext.fly(A).removeClass("x-tab-strip-active")}this.activeTab.fireEvent("deactivate",this.activeTab)}var B=this.getTabEl(C);Ext.fly(B).addClass("x-tab-strip-active");this.activeTab=C;this.stack.add(C);this.layout.setActiveItem(C);if(this.scrolling){this.scrollToTab(C,this.animScroll)}C.fireEvent("activate",C);this.fireEvent("tabchange",this,C)}},getActiveTab:function(){return this.activeTab},getItem:function(A){return this.getComponent(A)},autoScrollTabs:function(){var F=this.items.length;var D=this.header.dom.offsetWidth;var C=this.header.dom.clientWidth;var E=this.stripWrap;var B=E.dom.offsetWidth;var G=this.getScrollPos();var A=this.edge.getOffsetsTo(this.stripWrap)[0]+G;if(!this.enableTabScroll||F<1||B<20){return }if(A<=C){E.dom.scrollLeft=0;E.setWidth(C);if(this.scrolling){this.scrolling=false;this.header.removeClass("x-tab-scrolling");this.scrollLeft.hide();this.scrollRight.hide()}}else{if(!this.scrolling){this.header.addClass("x-tab-scrolling")}C-=E.getMargins("lr");E.setWidth(C>20?C:20);if(!this.scrolling){if(!this.scrollLeft){this.createScrollers()}else{this.scrollLeft.show();this.scrollRight.show()}}this.scrolling=true;if(G>(A-C)){E.dom.scrollLeft=A-C}else{this.scrollToTab(this.activeTab,false)}this.updateScrollButtons()}},createScrollers:function(){var C=this.stripWrap.dom.offsetHeight;var A=this.header.insertFirst({cls:"x-tab-scroller-left"});A.setHeight(C);A.addClassOnOver("x-tab-scroller-left-over");this.leftRepeater=new Ext.util.ClickRepeater(A,{interval:this.scrollRepeatInterval,handler:this.onScrollLeft,scope:this});this.scrollLeft=A;var B=this.header.insertFirst({cls:"x-tab-scroller-right"});B.setHeight(C);B.addClassOnOver("x-tab-scroller-right-over");this.rightRepeater=new Ext.util.ClickRepeater(B,{interval:this.scrollRepeatInterval,handler:this.onScrollRight,scope:this});this.scrollRight=B},getScrollWidth:function(){return this.edge.getOffsetsTo(this.stripWrap)[0]+this.getScrollPos()},getScrollPos:function(){return parseInt(this.stripWrap.dom.scrollLeft,10)||0},getScrollArea:function(){return parseInt(this.stripWrap.dom.clientWidth,10)||0},getScrollAnim:function(){return{duration:this.scrollDuration,callback:this.updateScrollButtons,scope:this}},getScrollIncrement:function(){return(this.scrollIncrement||this.lastTabWidth+2)},scrollToTab:function(E,A){if(!E){return }var C=this.getTabEl(E);var G=this.getScrollPos(),D=this.getScrollArea();var F=Ext.fly(C).getOffsetsTo(this.stripWrap)[0]+G;var B=F+C.offsetWidth;if(F<G){this.scrollTo(F,A)}else{if(B>(G+D)){this.scrollTo(B-D,A)}}},scrollTo:function(B,A){this.stripWrap.scrollTo("left",B,A?this.getScrollAnim():false);if(!A){this.updateScrollButtons()}},onScrollRight:function(){var A=this.getScrollWidth()-this.getScrollArea();var C=this.getScrollPos();var B=Math.min(A,C+this.getScrollIncrement());if(B!=C){this.scrollTo(B,this.animScroll)}},onScrollLeft:function(){var B=this.getScrollPos();var A=Math.max(0,B-this.getScrollIncrement());if(A!=B){this.scrollTo(A,this.animScroll)}},updateScrollButtons:function(){var A=this.getScrollPos();this.scrollLeft[A==0?"addClass":"removeClass"]("x-tab-scroller-left-disabled");this.scrollRight[A>=(this.getScrollWidth()-this.getScrollArea())?"addClass":"removeClass"]("x-tab-scroller-right-disabled")}});Ext.reg("tabpanel",Ext.TabPanel);Ext.TabPanel.prototype.activate=Ext.TabPanel.prototype.setActiveTab;Ext.TabPanel.AccessStack=function(){var A=[];return{add:function(B){A.push(B);if(A.length>10){A.shift()}},remove:function(E){var D=[];for(var C=0,B=A.length;C<B;C++){if(A[C]!=E){D.push(A[C])}}A=D},next:function(){return A.pop()}}};