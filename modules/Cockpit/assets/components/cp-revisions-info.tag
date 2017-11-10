<cp-revisions-info>

    <span>
        <span class="uk-icon-spinner uk-icon-spin" if="{ cnt === false || loading}"></span>
        <span if="{ cnt !== false && !loading}">{ cnt }</span>
    </span>


    <script>

        var $this = this;

        this.cnt = false;

        this.on('mount', function() {
            this.sync();

            if (opts.parent) {

                this.parent.on('update', function() {
                    $this.sync();
                });
            }
        });

        sync() {

            var rid = opts.rid || 0;

            this.loading = true;

            App.request('/cockpit/utils/revisionsCount', {id:opts.rid}, 'text').then(function(cnt){

                $this.loading = false;
                $this.cnt = cnt;
                $this.update();

            }).catch(function(e){});

        }

    </script>


</cp-revisions-info>
