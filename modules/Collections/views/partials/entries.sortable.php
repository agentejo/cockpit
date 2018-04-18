
<script type="riot/tag" src="@base('collections:assets/entries-tree.tag')"></script>

<div class="uk-margin-top" riot-view>

    <div class="uk-margin uk-text-center uk-text-muted" show="{ (Array.isArray(entries) && entries.length) || filter}">

        <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
        @if($collection['description'])
        <div class="uk-container-center uk-margin-top uk-width-medium-1-2">
            {{ $collection['description'] }}
        </div>
        @endif
    </div>

    <div show="{ ready }">

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ loading }">

            <div class="uk-animation-fade uk-text-center">

                <p class="uk-text-xlarge">
                    <i class="uk-text-primary uk-icon-spin uk-icon-spinner"></i>
                </p>

            </div>

        </div>

        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{ !loading && !entries.length && !filter}">

            <div class="uk-animation-scale">

                <img class="uk-svg-adjust" src="@url($collection['icon'] ? 'assets:app/media/icons/'.$collection['icon']:'collections:icon.svg')" width="50" alt="icon" data-uk-svg>
                @if($collection['description'])
                <div class="uk-margin-top uk-text-small uk-text-muted">
                    {{ $collection['description'] }}
                </div>
                @endif
                <hr>
                <span class="uk-text-large"><strong>@lang('No entries').</strong> <a href="@route('/collections/entry/'.$collection['name'])">@lang('Create an entry').</a></span>

            </div>

        </div>

        <div class="uk-clearfix uk-margin-top" show="{ !loading && (entries.length || filter) }">

            <div class="uk-float-left uk-width-1-2">
                <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">

                    <i class="uk-icon-search"></i>
                    <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" ref="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                </div>
            </div>

            <div class="uk-float-right">

                @if($app->module('collections')->hasaccess($collection['name'], 'entries_delete'))
                <a class="uk-button uk-button-large uk-button-danger uk-animation-fade uk-margin-small-right" onclick="{ removeselected }" if="{ selected && selected.length }">
                    @lang('Delete') <span class="uk-badge uk-badge-contrast uk-margin-small-left">{ selected.length }</span>
                </a>
                @endif

                @if($app->module('collections')->hasaccess($collection['name'], 'entries_create'))
                <a class="uk-button uk-button-large uk-button-primary" href="@route('/collections/entry/'.$collection['name'])"><i class="uk-icon-plus-circle uk-icon-justify"></i> @lang('Entry')</a>
                @endif
            </div>
        </div>

        <div class="uk-margin-top" if="{ (Array.isArray(entries) && entries.length) && !filter }">
            <entries-tree entries="{entries}" collection="{collection}"></entries-tree>
        </div>

        <div class="uk-text-xlarge uk-text-muted uk-viewport-height-1-3 uk-flex uk-flex-center uk-flex-middle" if="{ !entries.length && filter && !loading }">
            <div>@lang('No entries found')</div>
        </div>

        <div if="{(Array.isArray(entries) && entries.length) && filter}">

        </div>

    </div>

    <script>

        var $this = this, $root = App.$(this.root);

        this.ready      = false;
        this.filter     = null;
        this.collection = {{ json_encode($collection) }};
        this.entries    = [];

        this.on('mount', function(){

            this.load();
        });

        this.load = function() {

            var options = { };

            if (this.filter) {
                options.filter = this.filter;
            }

            this.loading = true;
            this.entries = [];

            App.request('/collections/find', {collection:this.collection.name, options:options}).then(function(data){

                window.scrollTo(0, 0);

                this.entries = data.entries;

                this.ready   = true;
                this.loading = false;
                this.update();

            }.bind(this))
        }

        this.updatefilter = function() {

            this.filter = this.refs.txtfilter.value || null;
            this.load();
        }

    </script>

</div>
