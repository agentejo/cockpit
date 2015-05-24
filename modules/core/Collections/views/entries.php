<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li class="uk-active" data-uk-dropdown="{mode:'click'}">

            <a><i class="uk-icon-bars"></i> {{ @$collection['label'] ? $collection['label']:$collection['name'] }}</a>

            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Collections')</li>
                    <li><a href="@route('/collections/collection/'.$collection['name'])">@lang('Edit')</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/export/'.$collection['name'])" download="{{ $collection['name'] }}.json">@lang('Export entries')</a></li>
                </ul>
            </div>

        </li>
    </ul>

</div>

@if($collection['description'])
<div class="uk-text-muted uk-panel-box">
    <i class="uk-icon-info-circle"></i> {{ $collection['description'] }}
</div>
@endif

<div class="uk-margin-top" riot-view>

    <div show="{ ready }">


        <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-middle" if="{ !entries.length && !filter }">

            <div class="uk-animation-fade">

                <p class="uk-text-xlarge">
                    <i class="uk-icon-list"></i>
                </p>

                <hr>

                @lang('No entries'). <a href="@route('/collections/entry/'.$collection['name'])">@lang('Create an entry').</a>

            </div>

        </div>


        <div if="{ entries.length || filter }">

            <div class="uk-grid uk-grid-divider uk-grid-margin uk-animation-fade">

                <div class="uk-width-medium-3-4">

                    <div class="uk-margin">

                        <div class="uk-form-icon uk-form uk-width-1-1 uk-text-muted">

                            <i class="uk-icon-filter"></i>
                            <input class="uk-width-1-1 uk-form-large uk-form-blank" type="text" name="txtfilter" placeholder="@lang('Filter items...')" onchange="{ updatefilter }">

                        </div>

                    </div>

                    <div class="uk-alert" if="{ !entries.length && filter }">
                        @lang('No entries found').
                    </div>


                    <table class="uk-table uk-table-striped uk-margin-top" if="{ entries.length }">
                        <thead>
                            <tr>
                                <th width="20"><input type="checkbox" data-check="all"></th>
                                <th each="{field,idx in fields}">
                                    <a class="uk-link-muted { parent.sort[field.name] ? 'uk-text-primary':'' }" onclick="{ parent.updatesort }" data-sort="{ field.name }">
                                        <span if="{parent.sort[field.name]}" class="uk-animation-fade uk-icon-caret-{ parent.sort[field.name] == 1 ? 'up':'down'}"></span>
                                        <span if="{!parent.sort[field.name]}" class="uk-icon-sort uk-text-muted"></span>
                                        { field.label || field.name }
                                    </a>
                                </th>
                                <th width="20"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr each="{entry,idx in entries}">
                                <td><input type="checkbox" data-check data-id="{ entry._id }"></td>
                                <td class="uk-text-truncate" each="{field,idy in parent.fields}" if="{ field.name != '_modified' }">
                                    <a class="uk-link-muted" href="@route('/collections/entry/'.$collection['name'])/{ parent.entry._id }">
                                        { String(parent.entry[field.name]) }
                                    </a>
                                </td>
                                <td>{ (new Intl.DateTimeFormat()).format( new Date( 1000 * entry._modified )) }</td>
                                <td>
                                    <span class="uk-float-right" data-uk-dropdown="\{mode:'click'\}">

                                        <a class="uk-icon-bars"></a>

                                        <div class="uk-dropdown uk-dropdown-flip">
                                            <ul class="uk-nav uk-nav-dropdown">
                                                <li class="uk-nav-header">@lang('Actions')</li>
                                                <li><a href="@route('/collections/entry/'.$collection['name'])/{ entry._id }">@lang('Edit')</a></li>
                                                <li><a onclick="{ parent.remove }">@lang('Delete')</a></li>
                                            </ul>
                                        </div>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="uk margin" if="{ loadmore }">
                        <a class="uk-button uk-width-1-1" onclick="{ load }">
                            @lang('Load more..')
                        </a>
                    </div>
                </div>
                <div class="uk-width-medium-1-4 uk-form">

                    <div class="uk-margin">

                        <a class="uk-button uk-button-large uk-button-primary uk-width-1-1" href="@route('/collections/entry/'.$collection['name'])">@lang('Add entry')</a>

                    </div>

                    <div class="uk-margin uk-animation-fade" if="{ selected.length }">

                        <ul class="uk-nav uk-nav-side">
                            <li class="uk-nav-header">{ selected.length } @lang('Selected')</li>
                            <li><a class="uk-text-danger" onclick="{ removeselected }"><i class="uk-icon-justify uk-icon-trash"></i> @lang('Delete')</a></li>
                        </ul>

                    </div>

                </div>
            </div>

        </div>

    </div>


    <script type="view/script">

        var $this = this, $root = App.$(this.root);

        this.ready      = false;
        this.collection = {{ json_encode($collection) }};
        this.loadmore   = false;
        this.entries    = [];
        this.fieldsidx  = {};
        this.fields     = this.collection.fields.filter(function(field){

            $this.fieldsidx[field.name] = field;

            return field.lst;
        });

        this.fields.push({name:'_modified', 'label':'@lang('Modified')'});

        this.sort     = {'_created': -1};
        this.selected = [];

        this.on('mount', function(){

            this.load();

            $root.on('click', '[data-check]', function() {

                if (this.getAttribute('data-check') == 'all') {
                    $root.find('[data-check][data-id]').prop('checked', this.checked);
                }

                $this.checkselected();

                $this.update();
            });
        });

        remove(e, entry, idx) {

            entry = e.item.entry
            idx   = e.item.idx;

            App.ui.confirm("Are you sure?", function() {

                App.callmodule('collections:remove', [this.collection.name, {'_id':entry._id}]).then(function(data) {

                    App.ui.notify("Entry removed", "success");

                    $this.entries.splice(idx, 1);

                    $this.update();

                    $this.checkselected(true);
                });

            }.bind(this));
        }

        removeselected() {

            if (this.selected.length) {

                App.ui.confirm("Are you sure?", function() {

                    var promises = [];

                    this.entries = this.entries.filter(function(entry, yepp){

                        yepp = ($this.selected.indexOf(entry._id) === -1);

                        if (!yepp) {
                            promises.push(App.callmodule('collections:remove', [$this.collection.name, {'_id':entry._id}]));
                        }

                        return yepp;
                    });

                    Promise.all(promises).then(function(){
                        App.ui.notify("Entries removed", "success");
                    });

                    this.update();
                    this.checkselected(true);

                }.bind(this));
            }
        }

        load() {

            var limit = 25;

            var options = {sort:this.sort, limit: limit, skip: this.entries.length || 0 };

            if (this.filter) {
                options.filter = this.filter;
            }

            return App.callmodule('collections:find', [this.collection.name, options]).then(function(data){

                this.entries = this.entries.concat(data.result);

                this.ready    = true;
                this.loadmore = data.result.length && data.result.length == limit;

                this.checkselected();

                this.update();

            }.bind(this))
        }

        updatesort(e, field) {

            field = e.target.getAttribute('data-sort');

            if (!field) {
                return;
            }

            if (!this.sort[field]) {
                this.sort        = {};
                this.sort[field] = 1;
            } else {
                this.sort[field] = this.sort[field] == 1 ? -1:1;
            }

            this.entries = [];

            this.load();
        }

        checkselected(update) {

            var checkboxes = $root.find('[data-check][data-id]'),
                selected   = checkboxes.filter(':checked');

            this.selected = [];

            if (selected.length) {

                selected.each(function(){
                    $this.selected.push(App.$(this).attr('data-id'));
                });
            }

            $root.find('[data-check="all"]').prop('checked', checkboxes.length === selected.length);

            if (update) {
                this.update();
            }
        }

        updatefilter() {

            var load = this.filter ? true:false;

            this.filter = null;

            if (this.txtfilter.value) {

                var filter       = this.txtfilter.value,
                    criterias    = [],
                    allowedtypes = ['text', 'longtext','boolean','select', 'boolean'],
                    criteria;

                if (App.Utils.str2json('{'+filter+'}')) {

                    filter = App.Utils.str2json('{'+filter+'}');

                    var key, field;

                    for (key in filter) {

                        field = this.fieldsidx[key] || {};

                        if (allowedtypes.indexOf(field.type) !== -1) {

                            criteria = {};
                            criteria[key] = field.type == 'boolean' ? filter[key]: {'$regex':filter[key]};
                            criterias.push(criteria);
                        }
                    }

                    if (criterias.length) {
                        this.filter = {'$and':criterias};
                    }

                } else {

                    this.collection.fields.forEach(function(field){

                       if (field.type != 'boolean' && allowedtypes.indexOf(field.type) !== -1) {
                           criteria = {};
                           criteria[field.name] = {'$regex':filter};
                           criterias.push(criteria);
                       }

                    });

                    if (criterias.length) {
                        this.filter = {'$or':criterias};
                    }
                }

            }


            if (this.filter || load) {
                this.entries = [];
                this.load();
            }
        }

    </script>

</div>
