<div class="app-dashboard-widget">
    <div class="app-panel">
        @if(isset($title))

        <div class="uk-panel app-panel-box docked">
            <div class="uk-clearfix">
                <strong>{{ $title }}</strong>

                @if(isset($badge))
                    <span class="uk-float-right uk-badge">{{ $badge }}</span>
                @endif
            </div>
        </div>

        @endif

        {{ $content_for_layout }}
    </div>
</div>