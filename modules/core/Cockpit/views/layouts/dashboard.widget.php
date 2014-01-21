<div class="app-dashboard-widget">
    <div class="app-panel">
        @if(isset($title))
        <div class="uk-margin-bottom uk-clearfix">
            <strong>{{ $title }}</strong>

            @if(isset($badge))
                <span class="uk-float-right uk-badge">{{ $badge }}</span>
            @endif
        </div>
        <hr>
        @endif

        {{ $content_for_layout }}
    </div>
</div>