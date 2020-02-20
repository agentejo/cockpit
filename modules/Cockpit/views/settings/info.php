
<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('System Information')</span></li>
    </ul>
</div>

<div riot-view>

    <div class="uk-grid" data-uk-grid-margin>

        <div class="uk-width-medium-3-4">

            <div id="settings-info" class="uk-switcher">

                <div>

                    <p><strong><span class="uk-badge app-badge">System</span></strong></p>

                    <h4 class="uk-text-bold">@lang('General')</h4>

                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%">@lang('Version')</td>
                                <td>{{ $info['app']['version'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4 class="uk-text-bold">@lang('Cache')</h4>

                    <div class="uk-margin" if="{cacheSize==null}">
                        <i class="uk-icon-spinner uk-icon-spin"></i>
                    </div>

                    <div class="uk-margin" if="{cacheSize!==null}">

                        <div class="uk-panel uk-panel-box" if="{ !cleaning && cacheSize }">
                            { cacheSize } <a class="uk-margin-small-left" title="@lang('Clear cache')" data-uk-tooltip="pos:'right'" onclick="{cleanUpCache}"><i class="uk-icon-button uk-icon-trash-o"></i></a>
                        </div>

                        <div class="uk-alert" if="{ cleaning }">
                            <i class="uk-icon-spinner uk-icon-spin"></i> @lang('Clearing cache...')
                        </div>

                        <div class="uk-alert uk-alert-success" if="{ !cacheSize }">
                            @lang('Cache is clean')
                        </div>
                    </div>

                    <h4 class="uk-text-bold">@lang('Jobs queue')</h4>

                    <table class="uk-table uk-table-striped">
                        <tbody>
                            <tr>
                                <td width="30%" class="uk-text-small uk-text-bold">@lang('Runner active')</td>
                                <td class="uk-text-small uk-flex">
                                    <div class="uk-flex-item-1 uk-flex-middle uk-margin-right">
                                        <span class="uk-badge uk-badge-outline uk-text-{ jobsQueue.running ? 'success':'danger' }">{ jobsQueue.running ? 'Yes':'No' }</span>
                                    </div>
                                    <div>
                                        <i class="uk-icon-spinner uk-icon-spin" show="{JobsRunnerLoading}"></i>
                                        <div class="uk-button-group" show="{!JobsRunnerLoading}">
                                            <button type="button" class="uk-button uk-button-small" if="{!jobsQueue.running}" onclick="{startJobsRunner}"><i class="uk-icon-play"></i></button>
                                            <button type="button" class="uk-button uk-button-small" if="{jobsQueue.running}" onclick="{restartJobsRunner}"><i class="uk-icon-refresh"></i></button>
                                            <button type="button" class="uk-button uk-button-small" if="{jobsQueue.running}" onclick="{stopJobsRunner}"><i class="uk-icon-stop"></i></button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td width="30%" class="uk-text-small uk-text-bold">@lang('Jobs in queue')</td>
                                <td class="uk-text-small"><span class="uk-text-{{ !$info['jobs_queue']['cntjobs'] ? 'muted':'' }}">{{ $info['jobs_queue']['cntjobs'] }}</span></td>
                            </tr>
                        </tbody>
                    </table>

                    @if($app->module('cockpit')->isSuperAdmin() && count(getenv()))

                    <h4 class="uk-text-bold">@lang('Environment Variables')</h4>

                    <table class="uk-table uk-table-striped">
                        <tbody>
                            @foreach(getenv() as $key => $value)
                            <tr>
                                <td width="30%" class="uk-text-small uk-text-bold">{{ $key }}</td>
                                <td class="uk-text-small">{{ $value }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @endif

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
                                <td width="30%">Max. execution time</td>
                                <td>{{ ini_get("max_execution_time") }} sec.</td>
                            </tr>
                            <tr>
                                <td width="30%">OPCache enabled</td>
                                <td><span class="uk-badge uk-badge-outline uk-text-{{ ini_get("opcache.enable") ? 'success':'danger' }}">{{ ini_get("opcache.enable") ? 'Yes':'No' }}</span></td>
                            </tr>
                            <tr>
                                <td width="30%">Memory limit</td>
                                <td>{{ ini_get("memory_limit") }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Upload file size limit</td>
                                <td>{{ ini_get("upload_max_filesize") }}</td>
                            </tr>
                            <tr>
                                <td width="30%">Realpath Cache</td>
                                <td>{{ ini_get("realpath_cache_size") }} / {{ ini_get("realpath_cache_ttl") }} (ttl)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            @trigger("cockpit.settings.infopage.main.menu")
            </div>


            @trigger("cockpit.settings.infopage.main")

        </div>

        <div class="uk-width-medium-1-4">

            <ul class="uk-nav uk-nav-side" data-uk-switcher="connect:'#settings-info'">
                <li><a href="#SYSTEM">System</a></li>
                <li><a href="#PHP">PHP</a></li>
                @trigger("cockpit.settings.infopage.aside.menu")
            </ul>

            @trigger("cockpit.settings.infopage.aside")
        </div>

        <script type="view/script">

            var $this = this;

            this._system = {};
            this.system  = {{ json_encode($info['app']) }};
            this.jobsQueue = {{ json_encode($info['jobs_queue']) }};
            this.cacheSize = null;
            this.loading = false;

            this.on('mount', function() {

                App.request('/cockpit/utils/getCacheSize').then(function(rsp) {

                    $this.cacheSize = rsp.size ? rsp.size_pretty : 0;
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

            startJobsRunner() {
                this.JobsRunnerLoading = true;
                App.request('/cockpit/utils/startJobRunner').then(function(rsp) {

                    if (!rsp.running) {
                        App.ui.notify('Runner failed to start', 'danger');
                    }

                    $this.jobsQueue.running = rsp.running;
                    $this.JobsRunnerLoading = false;
                    $this.update();
                });
            }

            restartJobsRunner() {
                this.JobsRunnerLoading = true;
                App.request('/cockpit/utils/restartJobRunner').then(function(rsp) {

                    if (!rsp.running) {
                        App.ui.notify('Runner failed to start', 'danger');
                    }

                    $this.jobsQueue.running = rsp.running;
                    $this.JobsRunnerLoading = false;
                    $this.update();
                });
            }

            stopJobsRunner() {
                this.JobsRunnerLoading = true;
                App.request('/cockpit/utils/stopJobRunner').then(function(rsp) {
                    
                    if (rsp.running) {
                        App.ui.notify('Runner failed to terminate', 'danger');
                    }

                    $this.jobsQueue.running = rsp.running;
                    $this.JobsRunnerLoading = false;
                    $this.update();
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
