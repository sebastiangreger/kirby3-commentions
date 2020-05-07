(function () {function k(e,n,t,r,o,i,a){try{var c=e[i](a),s=c.value}catch(u){return void t(u)}c.done?n(s):Promise.resolve(s).then(r,o)}function l(e){return function(){var n=this,t=arguments;return new Promise(function(r,o){var i=e.apply(n,t);function a(e){k(i,r,o,a,c,"next",e)}function c(e){k(i,r,o,a,c,"throw",e)}a(void 0)})}}var b={props:{status:{type:String,default:"pending"}},computed:{flag:function(){var t={icon:"circle-outline",color:"var(--color-notice)",theme:"commentions-".concat(this.status),disabled:!0};return"approved"===this.status?t.icon="circle":"unapproved"===this.status?t.icon="cancel":"update"===this.status&&(t.icon="circle-filled"),t}}};if(typeof b==="function"){b=b.options}Object.assign(b,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("k-button",_vm._b({staticClass:"k-commentions-item-status-flag"},"k-button",_vm.flag,false))};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());var d={props:{text:{type:String,default:""},source:{type:String,default:""},viewSource:{type:Boolean,default:!1}},data:function(){return[]},mounted:function(){this.postfixPreviewHTML()},updated:function(){this.postfixPreviewHTML()},methods:{postfixPreviewHTML:function(t){this.$refs.preview&&this.$refs.preview.querySelectorAll("a").forEach(function(t){"_blank"!==t.getAttribute("target")&&t.setAttribute("target","_blank")})}}};if(typeof d==="function"){d=d.options}Object.assign(d,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _vm.viewSource?_c("div",{staticClass:"k-commentions-text"},[_c("pre",{staticClass:"code"},[_c("code",{staticClass:"language-commention"},[_vm._v(_vm._s(_vm.source))])])]):_c("div",{ref:"preview",staticClass:"k-commentions-text",domProps:{"innerHTML":_vm._s(_vm.text)}})};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());var f={components:{"k-commentions-status":b,"k-commentions-text":d},props:{status:String,viewSource:{type:Boolean,default:!1},item:function(){return{type:Object,default:{}}}},data:function(){return{}},computed:{hasText:function(){return["reply","comment"].includes(this.item.type)&&item.text_sanitized},icon:function(){switch(this.item.type){case"webmention":case"mention":case"trackback":case"pingback":return"commentions-webmention";case"like":return"heart";case"bookmark":return"bookmark";case"reply":return"parent";case"comment":return"chat";default:return"circle-outline";}},dateFormatted:function(){return this.$library.dayjs(this.item.timestamp).format(this.$t("commentions.section.datetime.format"))},options:function(){return[{icon:"check",text:this.$t("commentions.section.option.approve"),disabled:"approved"===this.item.status,click:{status:"approved"}},{icon:"cancel",text:this.$t("commentions.section.option.unapprove"),disabled:"unapproved"===this.item.status,click:{status:"unapproved"}},"-",{icon:"url",text:this.$t("commentions.section.option.openwebsite"),link:this.item.source?this.item.source:this.item.website,target:"_blank",disabled:!this.item.source&&!this.item.website},{icon:"email",text:this.$t("commentions.section.option.sendemail"),link:"mailto:".concat(this.item.email),target:"_blank",disabled:!this.item.email},"-",{icon:"trash",text:this.$t("commentions.section.option.delete"),click:"delete"}]}}};if(typeof f==="function"){f=f.options}Object.assign(f,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("li",{staticClass:"k-commentions-item"},[_c("div",{staticClass:"k-commentions-item-content"},[_c("div",{staticClass:"k-commentions-item-icon"},[_c("k-icon",{attrs:{"type":_vm.icon}})],1),_vm._v(" "),_c("div",{staticClass:"k-commentions-item-text"},[_c("div",{staticClass:"k-commentions-item-header"},[_c("p",{staticClass:"k-commentions-item-source",domProps:{"innerHTML":_vm._s(_vm.item.source_formatted)}}),_vm._v(" "),_c("time",{staticClass:"k-commentions-item-date",attrs:{"datetime":_vm.item.timestamp}},[_vm._v(" "+_vm._s(_vm.dateFormatted)+" ")]),_vm._v(" "),_vm.item.email?_c("p",{staticClass:"k-commentions-item-email"},[_c("a",{attrs:{"href":"mailto:"+_vm.item.email}},[_vm._v(_vm._s(_vm.item.email))])]):_vm._e()]),_vm._v(" "),_vm.item.text?_c("k-commentions-text",{attrs:{"text":_vm.item.text_sanitized,"source":_vm.item.text,"view-source":_vm.viewSource}}):_vm._e()],1)]),_vm._v(" "),_c("nav",{staticClass:"k-commentions-item-options"},[_c("k-commentions-status",{attrs:{"status":_vm.item.status}}),_vm._v(" "),true?_c("k-button",{staticClass:"k-commentions-item-toggle",attrs:{"tooltip":_vm.$t("options"),"icon":"dots","alt":_vm.Options},on:{"click":function($event){$event.stopPropagation();return _vm.$refs.options.toggle()}}}):_vm._e(),_vm._v(" "),_c("k-dropdown-content",{ref:"options",attrs:{"options":_vm.options,"align":"right"},on:{"action":function($event){return _vm.$emit("action",$event,_vm.item.uid,_vm.item.pageid)}}})],1)])};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());var g={};if(typeof g==="function"){g=g.options}Object.assign(g,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("ul",{staticClass:"k-commentions-list"},[_vm._t("default")],2)};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());var h={data:function(){return{item:null}},computed:{message:function(){if(!this.item)return"";var e="";switch(this.item.type){case"webmention":e=this.$t("commentions.section.delete.webmention.confirm");break;case"mention":e=this.$t("commentions.section.delete.mention.confirm");break;case"trackback":e=this.$t("commentions.section.delete.trackback.confirm");break;case"pingback":e=this.$t("commentions.section.delete.pingback.confirm");break;case"like":e=this.$t("commentions.section.delete.like.confirm");break;case"bookmark":e=this.$t("commentions.section.delete.bookmark.confirm");break;case"reply":e=this.$t("commentions.section.delete.reply.confirm");break;case"comment":e=this.$t("commentions.section.delete.comment.confirm");break;default:e=this.$t("commentions.section.delete.unknown.confirm");}return e}},methods:{open:function(e){this.item=e,this.$refs.dialog.open(),this.$emit("open",this.item)},close:function(){this.$refs.dialog.close(),this.$emit("close",this.item),this.item=null},submit:function(){this.$refs.dialog.close(),this.$emit("submit",this.item),this.item=null}}};if(typeof h==="function"){h=h.options}Object.assign(h,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("k-dialog",{ref:"dialog",attrs:{"button":_vm.$t("delete"),"theme":"negative","icon":"trash"},on:{"submit":_vm.submit}},[_c("k-text",{domProps:{"innerHTML":_vm._s(_vm.message)}})],1)};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());var j={extends:"k-info-section",components:{"k-commentions-list":g,"k-commentions-item":f,"k-commentions-remove-dialog":h},props:{name:String,parent:String},data:function(){return{headline:null,commentions:[],empty:null,errors:[],viewSource:!1}},created:function(){var e=this;this.load().then(function(n){e.headline=n.headline,e.commentions=n.commentions,e.empty=n.empty,e.commentionsSystemErrors=n.commentionsSystemErrors})},methods:{load:function(){return this.$api.get(this.parent+"/sections/"+this.name)},toggleViewSource:function(){this.viewSource=!this.viewSource},action:function(e,n,t){"delete"!==e?this.updateCommention(t,n,e):this.$refs.remove.open(this.commentions.find(function(e){return e.uid===n}))},updateCommention:function(e,n,t){var r=this;return l(regeneratorRuntime.mark(function o(){var i;return regeneratorRuntime.wrap(function(o){for(;;)switch(o.prev=o.next){case 0:return e=e.replace(/\//,"+"),i="commentions/".concat(e,"/").concat(n),o.next=4,r.$api.patch(i,t);case 4:return o.sent,o.next=7,r.load().then(function(e){return r.commentions=e.commentions});case 7:r.$store.dispatch("notification/success",":)");case 8:case"end":return o.stop();}},o)}))()},deleteCommention:function(e){var n=this;return l(regeneratorRuntime.mark(function t(){var r,o;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return r=e.pageid.replace(/\//,"+"),o="commentions/".concat(r,"/").concat(e.uid),t.next=4,n.$api.delete(o);case 4:return t.next=6,n.load().then(function(e){return n.commentions=e.commentions});case 6:n.$store.dispatch("notification/success",":)");case 7:case"end":return t.stop();}},t)}))()},refresh:function(){var e=this;this.load().then(function(n){return e.commentions=n.commentions})}}};if(typeof j==="function"){j=j.options}Object.assign(j,function(){var render=function(){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c("section",{staticClass:"k-commentions-section k-section"},[_c("header",{staticClass:"k-section-header"},[_c("k-headline",[_vm._v(_vm._s(_vm.headline))]),_vm._v(" "),_c("k-button-group",[_c("k-button",{attrs:{"icon":"code","theme":_vm.viewSource?"active":""},on:{"click":function($event){return _vm.toggleViewSource()}}},[_vm._v(_vm._s(_vm.$t("commentions.section.button.viewsource")))]),_vm._v(" "),_c("k-button",{attrs:{"icon":"refresh"},on:{"click":_vm.refresh}},[_vm._v(_vm._s(_vm.$t("commentions.section.button.refresh")))])],1)],1),_vm._v(" "),_vm._l(_vm.commentionsSystemErrors,function(error){return _c("k-box",{key:error.id,attrs:{"theme":error.theme}},[_c("k-text",{attrs:{"size":"small"},domProps:{"innerHTML":_vm._s(error.message)}})],1)}),_vm._v(" "),_vm.commentions.length>0?_c("k-commentions-list",_vm._l(_vm.commentions,function(item){return _c("k-commentions-item",{key:item.uid,attrs:{"item":item,"view-source":_vm.viewSource},on:{"action":_vm.action}})}),1):_c("k-empty",{attrs:{"layout":"list","icon":"chat"},on:{"click":_vm.refresh}},[_vm._v(" "+_vm._s(_vm.empty)+" ")]),_vm._v(" "),_c("k-commentions-remove-dialog",{ref:"remove",on:{"submit":function($event){return _vm.deleteCommention($event)}}})],2)};var staticRenderFns=[];return{render:render,staticRenderFns:staticRenderFns,_compiled:true,_scopeId:null,functional:undefined}}());panel.plugin("sgkirby/commentions",{sections:{commentions:j},icons:{"commentions-webmention":"<polygon fill-rule=\"nonzero\" points=\"9.23775281 14.8085393 7.34377528 9.01793258 7.31235955 9.01793258 5.45002247 14.8085393 2.96193258 14.8085393 0.00413483146 3.62759551 2.4611236 3.62759551 4.2294382 11.2382921 4.26067416 11.2382921 6.20139326 5.44750562 8.50202247 5.44750562 10.411191 11.3318652 10.4425618 11.3318652 11.8070562 5.45137079 10.0297079 5.44750562 13.7139775 2.02314607 16 5.45191011 14.189618 5.45078652 11.6785169 14.8085393\"></polygon>"}});})();