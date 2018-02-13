
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('System Information')</span></li>
    </ul>
</div>

<div riot-view>

    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-medium-2-3">

            <div id="settings-info" class="uk-switcher">

                <div>

                    <p><strong><span class="uk-badge app-badge">System</span></strong></p>

                    <h4>@lang('General')</h4>

                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%">@lang('Version')</td>
                                <td>{{ $info['app']['version'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>@lang('Cache')</h4>

                    <div class="uk-margin">

                        <div class="uk-panel uk-panel-box" if="{ !cleaning && cacheSize }">
                            { cacheSize } <a title="@lang('Clean up')" data-uk-tooltip="pos:'right'" onclick="{cleanUpCache}"><i class="uk-icon-trash-o"></i></a>
                        </div>

                        <div class="uk-alert" if="{ cleaning }">
                            <i class="uk-icon-spinner uk-icon-spin"></i> @lang('Cleaning up...')
                        </div>

                        <div class="uk-alert uk-alert-success" if="{ !cacheSize }">
                            @lang('Cache is clean')
                        </div>
                    </div>

                </div>

                <div>
                    <p>
                        <strong><span class="uk-badge app-badge">PHP</span></strong>
                    </p>
                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%">Version</td>
                                <td>{{ $info['phpversion'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">PHP SAPI</td>
                                <td>{{ $info['sapi_name'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">System</td>
                                <td>{{ $info['system'] }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Loaded Extensions</td>
                                <td>{{ implode(", ", $info['extensions']) }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Memory limit</td>
                                <td>{{ ini_get("memory_limit") }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Upload file size limit</td>
                                <td>{{ ini_get("upload_max_filesize") }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            @trigger("cockpit.settings.infopage.main")

        </div>

        <div class="uk-width-medium-1-3">

            <div class="uk-panel uk-panel-box uk-panel-card uk-margin uk-animation-fade" if="{ !loading && version_compare(system.version, _system.version, '<') }">

                <div class="uk-margin uk-text-center">
                    <img class="uk-svg-adjust uk-text-muted" src="@url('assets:app/media/icons/misc/sysupdate.svg')" width="50" height="50" alt="@lang('System Update')" data-uk-svg />
                    <p class="uk-h2 uk-text-bold">{ _system.version }</p>
                </div>

                <button type="button" class="uk-button uk-button-large uk-button-outline uk-text-primary uk-width-1-1" onclick="{updateSystem}">@lang('Update System')</button>

                <div class="uk-text-center uk-alert uk-alert-warning">
                    @lang('Please consider doing a backup before updating to the latest version.')
                </div>

            </div>


            <ul class="uk-nav uk-nav-side" data-uk-switcher="connect:'#settings-info'">
                <li><a href="#SYSTEM">System</a></li>
                <li><a href="#PHP">PHP</a></li>
            </ul>

            @trigger("cockpit.settings.infopage.aside")
        </div>

        <script type="view/script">

            var $this = this;

            this._system = {};
            this.system  = {{ json_encode($info['app']) }};
            this.cacheSize = {{ $info['cacheSize'] ? '"'.$info['cacheSize'].'"':0 }};
            this.loading = true;

            this.on('mount', function() {

                var url = '{{ $update['package.json'] }}';

                this.loading = true;

                fetch_url_contents(url, 'json').then(function(data) {
                    $this._system = data;
                    $this.loading = false;
                    $this.update();
                });
            });

            cleanUpCache() {

                this.cleaning = true;

                App.callmodule('cockpit:clearCache').then(function(){
                    setTimeout(function(){
                        $this.cleaning = false;
                        $this.cacheSize = 0;
                        $this.update();
                    }, 1000);
                });
            }

            updateSystem() {

                App.ui.block('<div class="uk-text-center uk-text-bold uk-h2">'+App.i18n.get('Updating System...')+'</div><p class="uk-text-center"><i class="uk-icon-spinner uk-icon-spin"></i></p>');

                App.request('/settings/update', {v:this._system.version}).then(function() {
                    location.reload();
                });
            }

            version_compare(v1, v2, operator) {

              var i, x, compare = 0, vm = {
                'dev': -6,
                'alpha': -5,
                'a': -5,
                'beta': -4,
                'b': -4,
                'RC': -3,
                'rc': -3,
                '#': -2,
                'p': 1,
                'pl': 1
              }

              var _prepVersion = function (v) {
                v = ('' + v).replace(/[_\-+]/g, '.')
                v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
                return (!v.length ? [-8] : v.split('.'))
              }

              var _numVersion = function (v) {
                return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
              }

              v1 = _prepVersion(v1)
              v2 = _prepVersion(v2)
              x = Math.max(v1.length, v2.length)
              for (i = 0; i < x; i++) {
                if (v1[i] === v2[i]) {
                  continue
                }
                v1[i] = _numVersion(v1[i])
                v2[i] = _numVersion(v2[i])
                if (v1[i] < v2[i]) {
                  compare = -1
                  break
                } else if (v1[i] > v2[i]) {
                  compare = 1
                  break
                }
              }
              if (!operator) {
                return compare
              }

              switch (operator) {
                case '>':
                case 'gt':
                  return (compare > 0)
                case '>=':
                case 'ge':
                  return (compare >= 0)
                case '<=':
                case 'le':
                  return (compare <= 0)
                case '===':
                case '=':
                case 'eq':
                  return (compare === 0)
                case '<>':
                case '!==':
                case 'ne':
                  return (compare !== 0)
                case '':
                case '<':
                case 'lt':
                  return (compare < 0)
                default:
                  return null
              }
            }

        </script>

    </div>

</div>
