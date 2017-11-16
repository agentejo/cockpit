<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Update System')</span></li>
    </ul>
</div>


<div class="uk-margin-large-top" riot-view>

    <div class="uk-margin-large uk-text-center">
        <img class="uk-svg-adjust uk-text-muted" src="@url('assets:app/media/icons/misc/sysupdate.svg')" width="100" height="100" alt="@lang('System Update')" data-uk-svg />
    </div>

    <div class="uk-width-medium-1-3 uk-margin-large-top uk-container-center uk-text-center" if="{ loading }">
        <i class="uk-text-xlarge uk-text-primary uk-icon-spin uk-icon-spinner"></i>
    </div>

    <div class="uk-container-center uk-width-medium-2-3" if="{ !loading && step=='start' }">

        <div class="uk-grid uk-grid-width-medium-1-2 uk-text-center">
            <div>
                <div class="uk-panel uk-panel-box uk-panel-space">
                    <strong>@lang('Installed Version')</strong>
                    <div class="uk-h1 uk-margin">{ system.version }</div>
                </div>
            </div>
            <div>
                <div class="uk-panel uk-panel-box uk-panel-space">
                    <strong>@lang('Latest Version')</strong>
                    <div class="uk-h1 uk-margin uk-text-primary">{ _system.version }</div>
                </div>
            </div>
        </div>

        <!--
        <div class="uk-margin-large uk-text-center" if="{ version_compare(system.version, _system.version, '<') }">
            <button type="button" class="uk-button uk-button-large uk-button-primary">@lang('Update System')</button>
        </div>
        -->
    </div>



    <script type="view/script">

        var $this = this;

        this.system  = {{ json_encode($info) }};
        this._system = {};

        this.loading = true;
        this.step = 'start';

        this.on('mount', function() {
            this.stepStart();
        });

        stepStart() {

            this.loading = true;

            fetch_url_contents('https://raw.githubusercontent.com/agentejo/cockpit/master/package.json', 'json').then(function(data) {
                $this._system = data;
                $this.loading = false;
                $this.update();
            });
        }


        this.version_compare = function(v1, v2, operator) {

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
