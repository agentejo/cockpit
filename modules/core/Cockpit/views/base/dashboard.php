<div>
    <ul class="uk-breadcrumb">
        <li><span>@lang('Dashboard')</span></li>
    </ul>
</div>

<div id="dashboard">

    <div class="uk-grid uk-margin" data-uk-grid-margin>
        <div class="uk-width-medium-1-2" data-area="main">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
            @trigger('admin.dashboard.main')
            </div>
        </div>
        <div class="uk-width-medium-1-4" data-area="aside-left">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-medium-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
                @trigger('admin.dashboard.aside-left')
            </div>
        </div>
        <div class="uk-width-medium-1-4" data-area="aside-right">
            <div class="uk-sortable uk-grid uk-grid-gutter uk-grid-width-medium-1-1" data-uk-sortable="{group:'dashboard',animation:false}">
                @trigger('admin.dashboard.aside-right')
            </div>
        </div>
    </div>

    <div class="uk-margin">
        @trigger('admin.dashboard.bottom')
    </div>



</div>

<script>

    App.$(function($){

        var data, dashboard = $('#dashboard').on('stop.uk.sortable', function(){

            data = {};

            dashboard.find('[data-area]').each(function(){
                var $a      = $(this),
                    area    = $a.data('area'),
                    widgets = $a.find('[data-widget]');

                widgets.each(function(prio){
                    data[this.getAttribute('data-widget')] = {
                        area: area,
                        prio: prio
                    };
                });
            });

            //console.log(data);
        });
    });

</script>
