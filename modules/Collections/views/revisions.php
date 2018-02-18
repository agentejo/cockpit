@if(isset($collection['color']) && $collection['color'])
<style>
    .app-header { border-top: 8px {{ $collection['color'] }} solid; }
    .revisions-box { height: auto; max-height: 45vh; }
</style>
@endif

<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/collections')">@lang('Collections')</a></li>
        <li data-uk-dropdown="mode:'hover', delay:300">
            <a href="@route('/collections/entries/'.$collection['name'])"><i class="uk-icon-bars"></i> {{ @$collection['label'] ? $collection['label']:$collection['name'] }}</a>

            @if($app->module('collections')->hasaccess($collection['name'], 'collection_edit'))
            <div class="uk-dropdown">
                <ul class="uk-nav uk-nav-dropdown">
                    <li class="uk-nav-header">@lang('Actions')</li>
                    <li><a href="@route('/collections/collection/'.$collection['name'])">@lang('Edit')</a></li>
                    <li class="uk-nav-divider"></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/export/'.$collection['name'])" download="{{ $collection['name'] }}.collection.json">@lang('Export entries')</a></li>
                    <li class="uk-text-truncate"><a href="@route('/collections/import/collection/'.$collection['name'])">@lang('Import entries')</a></li>
                </ul>
            </div>
            @endif
        </li>
        <li><a href="@route("/collections/entry/{$collection['name']}/{$entry['_id']}")">@lang('Entry')</a></li>
    </ul>
</div>


<div class="uk-margin-top-large" riot-view>

    <div class="uk-width-medium-1-3 uk-viewport-height-1-2 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{!revisions.length}">
        <div class="uk-text-muted uk-width-1-1">
            <img class="uk-svg-adjust" src="@url('assets:app/media/icons/revisions.svg')" width="150" alt="icon" data-uk-svg>
            <div class="uk-h2 uk-margin">@lang('No revisions available')</div>
            <div class="uk-margin-large">
                <a class="uk-button uk-button-large uk-button-primary" href="@route("/collections/entry/{$collection['name']}/{$entry['_id']}")">@lang('Back to entry')</a>
            </div>
        </div>
    </div>

    <div class="uk-grid" if="{revisions.length}">
        <div class="uk-width-3-4">

            <div class="uk-text-muted uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" if="{!active}">
                <div>
                    <img class="uk-svg-adjust" src="@url('assets:app/media/icons/revisions.svg')" width="150" alt="icon" data-uk-svg>
                    <div class="uk-h2 uk-margin uk-text-center">@lang('Please select a revision')</div>
                </div>
            </div>

            <div if="{active}">

                <div class="uk-panel uk-panel-box uk-panel-card">
                    <div class="uk-flex uk-flex-middle">
                        <div class="uk-flex-item-1 uk-text-small">
                            <strong>{ App.Utils.dateformat(active._created*1000, 'MMMM Do YYYY @ hh:mm:ss a') }</strong>
                            <div class="uk-margin-small-top"><cp-account account="{active._creator}"></cp-account></div>
                        </div>
                        <div>
                            <button onclick="{ restoreActive }" class="uk-button uk-button-large uk-button-danger" show="{ hasDiffs() }">
                                @lang('Restore to this version')
                            </button>

                            <a class="uk-margin-left uk-button uk-button-large uk-button-primary" href="@route("/collections/entry/{$collection['name']}/{$entry['_id']}")">@lang('Back to entry')</a>
                        </div>
                    </div>
                </div>

                <div class="uk-margin-large">

                    <div class="uk-text-muted uk-width-medium-1-3 uk-viewport-height-1-3 uk-container-center uk-text-center uk-flex uk-flex-center uk-flex-middle" show="{ !hasDiffs() }">

                        <div>
                            <img class="uk-svg-adjust" src="@url('assets:app/media/icons/revisions.svg')" width="150" alt="icon" data-uk-svg>
                            <div class="uk-h2 uk-margin uk-text-center">@lang('No changes')</div>
                        </div>
                    </div>

                    <div if="{ hasDiffs() }">

                        <div class="uk-margin uk-flex uk-flex-middle">
                            <div class="uk-h3 uk-flex-item-1">@lang('Changes')</div>
                            <div class="uk-margin-left">
                                <field-boolean bind="showOnlyChanged" label="@lang('Show only changed fields')"></field-boolean>
                            </div>
                        </div>

                        <div class="uk-panel uk-margin" each="{value,key in active.data}" if="{['_id','_modified','_created','_by'].indexOf(key) < 0 && (showOnlyChanged ? JSON.stringify(value) !== JSON.stringify(current[key]) : true)}">

                            <div class="uk-margin uk-panel uk-panel-box uk-panel-card">

                                <div class="uk-margin uk-grid uk-flex-middle">
                                    <div class="uk-flex-item-1"><span class="uk-badge uk-badge-outline uk-badge-primary">{ key }</span></div>
                                    <div show="{JSON.stringify(value) !== JSON.stringify(current[key])}"><a onclick="{restoreValue}" title="@lang('Restore value')" data-uk-tooltip><i class="uk-icon-refresh"></i></a></div>
                                </div>

                                <div>
                                    <cp-diff class="uk-display-block" oldtxt="{ value }" newtxt="{ parent.current[key] || '' }"></cp-diff>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="uk-width-1-4">

            <h3 class="uk-text-bold">@lang('Revisions')</h3>

            <div class="uk-margin revisions-box { revisions.length > 10 && 'uk-scrollable-box'}">
                <ul class="uk-nav uk-nav-side">
                    <li class="{rev == active && 'uk-active'}" each="{rev in revisions}">
                        <a class="{rev !== active && 'uk-text-muted'}" onclick="{ parent.selectRevision }">
                            { App.Utils.dateformat(rev._created*1000, 'MMMM Do YYYY') }<br>
                            <span class="uk-text-small">{ App.Utils.dateformat(rev._created*1000, 'hh:mm:ss a') }</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <script type="view/script">

        this.mixin(RiotBindMixin);

        var $this = this;

        this.collection   = {{ json_encode($collection) }};
        this.revisions = {{ json_encode($revisions) }};
        this.current   = {{ json_encode($entry) }};

        this.showOnlyChanged = true;

        selectRevision(e) {

            this.active = e.item.rev;
        }

        hasDiffs() {

            if (!this.active) {
                return;
            }

            for (var k in this.active.data) {

                if (['_id','_modified','_created','_by'].indexOf(k) > -1) continue;

                if (JSON.stringify(this.active.data[k]) != JSON.stringify(this.current[k])) {
                    return true;
                }
            }

            return false;
        }

        restoreValue(e) {

            App.ui.confirm("Are you sure?", function() {

                $this.current[e.item.key] = e.item.value;
                $this.update();

                $this.save("Value restored");
            });
        }

        restoreActive() {

            if (!this.active) {
                return;
            }

            App.ui.confirm("Are you sure?", function() {

                $this.current = _.extend($this.current, $this.active.data);
                $this.update();

                $this.save("Entry restored");
            });
        }

        save(message) {

            App.request('/collections/save_entry/'+this.collection.name, {entry:this.current}).then(function(entry) {

                if (entry) {
                    App.ui.notify(message, "success");
                } else {
                    App.ui.notify("Restoring failed.", "danger");
                }
            }, function(res) {
                App.ui.notify(res && res.message ? res.message : "Restoring failed.", "danger");
            });
        }

    </script>

</div>
