{{ $app->assets(['assets:vendor/masonry.js']) }}

<div class="mosaic">
    @trigger('admin.dashboard')
    <div class="masonary-column-indicator"></div>
</div>

<style>
    .mosaic {
      margin: -10px;
    }
    .mosaic .masonary-column-indicator { width: 25%;}
    .mosaic .app-dashboard-widget { width: 25%; }
    .mosaic .app-dashboard-widget .app-panel { min-height: 300px; }

    @media(max-width:960px) {
        .masonary-column-indicator { width: 50%; }
        .mosaic .app-dashboard-widget { width: 50%;}
    }

    @media(max-width:767px) {
        .masonary-column-indicator { width: 100%; }
        .mosaic .app-dashboard-widget { width: 100%;}
    }
</style>

<script>

    (function($){

         $('.mosaic').masonry({itemSelector: '.app-dashboard-widget', columnWidth:'.masonary-column-indicator', gutter: 0});

    })(jQuery);

</script>