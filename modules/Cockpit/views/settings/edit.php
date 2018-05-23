<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('System Settings')</span></li>
    </ul>
</div>


<div class="uk-margin-top" riot-view>

    @if ($configexists)

        @if (!is_writable($configexists))
        <div class="uk-alert uk-alert-danger">
            @lang('Custom config file is not writable').
        </div>
        @endif

        <picoedit path="{{ str_replace(COCKPIT_SITE_DIR.'/', '', $configexists) }}" height="auto" readonly="{ {{ !is_writable($configexists) ? 'true':'false'}} }"></picoedit>


    @else
    <div class="uk-alert">
        @lang('Custom config file does not exist').
        <a class="uk-button uk-button-link" href="@route('/settings/edit/true')"><i class="uk-icon-magic"></i> @lang('Create config file')</a>
    </div>
    @endif

</div>


<style>

    picoedit .CodeMirror {
        height: auto;
    }

</style>
