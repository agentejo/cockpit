<collection-entrypreview>

    <style>

        .collection-entrypreview {
            position: fixed;
            top: 0;
            bottom: 0;
            left:0;
            width: 100%;
            background: #fafafa;
            z-index: 1010;
        }

        .collection-entrypreview .preview-panel {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 600px;
            box-shadow: 0 0 50px rgba(0,0,0,.4);
            border-right: 1px rgba(0, 0, 0, 0.03) solid;
            background: #fafafa;
            z-index: 1;
        }

        .preview-panel > form {
            position: absolute;
            display: flex;
            flex-direction: column;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            animation-duration: 200ms;
        }

        .preview-panel-header,
        .preview-panel-content,
        .preview-panel-footer {
            padding: 20px;
            box-sizing: border-box;
        }

        .preview-panel-header {
            background: #fff;
        }

        .preview-panel-content {
            flex: 1;
            overflow-y: scroll;
        }

        .iframe-container {
            position: absolute;
            top: 0;
            left: 600px;
            width: calc(100% - 600px);
            height: 100%;
            overflow: scroll;
            z-index: 0;
        }

        .iframe-container iframe {
            background: #fff;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.22);
            margin-top: auto;
            margin-bottom: auto;
            transition: all 400ms;
        }

        iframe[mode="desktop"] {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
        }

        iframe[mode="laptop"] {
            width: 1000px;
            height: 800px;
        }

        iframe[mode="tablet"] {
            width: 768px;
            height: 1024px;
        }

        iframe[mode="phone"] {
            width: 375px;
            height: 667px;
        }

        .preview-mode {
            display: block;
            transition: all 200ms;
            opacity: 0.3;
        }

        .preview-mode-active {
            opacity: 1;
        }

    </style>

    <div class="collection-entrypreview">
        <div class="iframe-container uk-flex uk-flex-center uk-flex-middle"><iframe riot-src="{ settings.url }" mode="{ mode }" ref="iframe"></iframe></div>
        <div ref="previewpanel" class="preview-panel uk-animation-slide-left">

            <form class="uk-form" if="{ fields.length }" onsubmit="{ submit }">

                <div class="preview-panel-header">

                    <div class="uk-flex uk-flex-middle">
                        <span class="uk-text-large uk-flex-item-1">{ App.i18n.get('Content Preview') }</span>
                        <a class="uk-text-large" onclick="{ hidePreview }" title="{ App.i18n.get('Close Preview') }"><img class="uk-svg-adjust uk-text-primary" riot-src="{App.base('/assets/app/media/icons/misc/close.svg')}" width="40" height="40" data-uk-svg></a>
                    </div>

                    <div class="uk-margin-small-top uk-flex uk-flex-middle">

                        <div class="uk-form-select uk-margin-right" show="{ App.Utils.count(groups) > 1 }">
                            <span class="uk-text-bold uk-text-uppercase {group && 'uk-text-primary'} ">{ group || App.i18n.get('All') }</span>
                            <select onchange="{toggleGroup}" ref="selectGroup">
                                <option class="uk-text-capitalize" value="">{ App.i18n.get('All') }</option>
                                <option class="uk-text-capitalize" value="{_group}" each="{items,_group in groups}">{ App.i18n.get(_group) }</option>
                            </select>
                        </div>

                        <div class="uk-form-select" if="{ languages.length }">

                            <span class="{lang ? 'uk-text-primary':'uk-text-muted'}">
                                <i class="uk-icon-globe uk-margin-small-right"></i>{ lang ? _.find(languages,{code:lang}).label:'Default' }
                            </span>

                            <select bind="lang">
                                <option value="">{ App.$data.languageDefaultLabel }</option>
                                <option each="{language,idx in languages}" value="{language.code}">{language.label}</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="preview-panel-content">

                    <div class="uk-grid uk-grid-match uk-grid-gutter">

                        <div class="uk-width-1-1" each="{field,idx in fields}" show="{checkVisibilityRule(field) && (!group || (group == field.group)) }" if="{ hasFieldAccess(field.name) }" no-reorder>

                            <div class="uk-panel">

                                <label class="uk-text-bold">
                                    <i class="uk-icon-pencil-square uk-margin-small-right"></i> { field.label || field.name }
                                    <span if="{ field.localize }" class="uk-icon-globe" title="{ App.i18n.get('Localized field') }" data-uk-tooltip="pos:'right'"></span>
                                </label>

                                <div class="uk-margin uk-text-small uk-text-muted">
                                    { field.info || ' ' }
                                </div>

                                <div class="uk-margin">
                                    <cp-field type="{field.type || 'text'}" bind="entry.{ field.localize && parent.lang ? (field.name+'_'+parent.lang):field.name }" opts="{ field.options || {} }"></cp-field>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="preview-panel-footer">
                    <div class="uk-grid uk-grid-small uk-flex-center">
                        <div><a class="preview-mode { mode=='desktop' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'desktop')}"><img riot-src="{App.base('/assets/app/media/icons/devices/desktop.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='laptop' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'laptop')}"><img riot-src="{App.base('/assets/app/media/icons/devices/laptop.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='tablet' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'tablet')}"><img riot-src="{App.base('/assets/app/media/icons/devices/tablet.svg')}" width="20" height="20" data-uk-svg></a></div>
                        <div><a class="preview-mode { mode=='phone' && 'preview-mode-active'}" onclick="{setMode.bind(this, 'phone')}"><img riot-src="{App.base('/assets/app/media/icons/devices/phone.svg')}" width="20" height="20" data-uk-svg></a></div>
                    </div>
                </div>

            </form>

        </div>
    </div>


    <script>

        this.mixin(RiotBindMixin);

        var $this = this;

        this.fields = opts.fields;
        this.fieldsidx = opts.fieldsidx;
        this.excludeFields = opts.excludeFields || [];
        this.groups = opts.groups;
        this.languages = opts.languages || [];
        this.collection = opts.collection;
        this.entry = opts.entry;
        this.ws = {send:function(){}, close:function(){}};

        this.mode = 'desktop';
        this.group = '';
        this.lang = opts.lang || '';
        this.$idle = false;

        this.settings = App.$.extend({
            url: '',
            wsurl: '',
            wsprotocols: null
        }, opts.settings || {});

        var replacements = {
            'root://':'/',
            'site://':SITE_URL+'/',
            'base://':App.base('/')
        };

        Object.keys(replacements).forEach(function(key) {
            $this.settings.url = $this.settings.url.replace(key, replacements[key]);
        });

        this.on('mount', function() {

            setTimeout(function() {
                $this.refs.previewpanel.classList.remove('uk-animation-slide-left');
            }, 1000);

            $this.$cache = JSON.stringify(this.entry);

            if (this.settings.wsurl) {

                if (this.settings.wsurl && !window.WebSocket) {
                    console.log('Missing support for Websockets');
                } else {
                    this.initWebsocket();
                }
            };

            this.refs.iframe.addEventListener('load', function() {

                $this.$iframe = $this.refs.iframe.contentWindow;
                $this.$idle   = setInterval(_.throttle(function() {

                    var hash = JSON.stringify({entry:$this.entry, lang: $this.lang});

                    if ($this.$cache != hash) {
                        $this.$cache = hash;
                        $this.updateIframe();
                    }

                }, 600), 1000);

                $this.updateIframe();
            });

            this.refs.selectGroup.value = this.group;

            document.body.style.overflow = 'hidden';
        });

        this.on('unmount', function() {
            clearTimeout(this.$idle);
            this.ws.close(1000);
        });

        setMode(mode) {
            this.mode = mode;
        }

        updateIframe() {

            if (!this.$iframe) return;

            var data = {
                'event': 'cockpit:collections.preview',
                'collection': this.collection.name,
                'entry': this.entry,
                'lang': this.lang || 'default'
            };

            this.$iframe.postMessage(data, '*');
            this.ws.send(JSON.stringify(data));
        }

        toggleGroup() {
            this.group = this.refs.selectGroup.value;
        }

        hidePreview() {
            clearInterval(this.$idle);
            document.body.style.overflow = '';
            this.parent.preview = false;
            this.parent.lang = this.lang;
            this.parent.update();
        }

        hasFieldAccess(field) {

            var acl = this.fieldsidx[field] && this.fieldsidx[field].acl || [];

            if (this.excludeFields.indexOf(field) > -1) {
                return false;
            }

            if (field == '_modified' ||
                App.$data.user.group == 'admin' ||
                !acl ||
                (Array.isArray(acl) && !acl.length) ||
                acl.indexOf(App.$data.user.group) > -1 ||
                acl.indexOf(App.$data.user._id) > -1
            ) {
                return true;
            }

            return false;
        }
        
        checkVisibilityRule(field) {

            if (field.options && field.options['@visibility']) {

                try {
                    return (new Function('$', 'v','return ('+field.options['@visibility']+')'))(this.entry, function(key) {
                        var f = this.fieldsidx[key] || {};
                        return this.entry[(f.localize && this.lang ? (f.name+'_'+this.lang):f.name)];
                    }.bind(this));
                } catch(e) {
                    return false;
                }

                return this.data.check;
            }

            return true;
        }

        initWebsocket() {

            var protocols = (this.settings.wsprotocols || '').split(',').map(function(p) {
                return p.trim();
            });

            var ws = this.settings.wsurl ? new WebSocket(this.settings.wsurl, this.settings.wsprotocols ? protocols : undefined) : {send:function(){}, close:function(){}};

            this.ws = {send:function(){}, close:function(){}};

            ws.onopen = function() { 
                $this.ws = ws;
            };

            ws.reconnect = function(e){
                console.log(1)
                ws.removeAllListeners();
                setTimeout(function(){ $this.initWebsocket(); }, 5000);
            };

            ws.onclose = function(e) {
                if (e.code != 1000) ws.reconnect(e);
            };

            ws.onerror = function(e) {
                if (e.code == 'ECONNREFUSED') ws.reconnect(e);
            };

            return ws;
        }

    </script>


</collection-entrypreview>
