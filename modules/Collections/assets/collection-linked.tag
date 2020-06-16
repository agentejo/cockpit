<collection-linked>

    <style>
        .header,
        .content {
            padding: 20px;
        }

        .content {
            overflow: auto;
        }
    </style>

    <div class="uk-offcanvas" ref="offcanvas">

        <div class="uk-offcanvas-bar uk-offcanvas-bar-flip uk-width-3-4 uk-width-medium-1-3 uk-flex uk-flex-column">
            <div class="uk-flex uk-flex-middle header">
                <span class="uk-badge">{opts.title || 'Linked Items' }</span>
                <a class="uk-margin-left" onclick="{ load }"><i class="uk-icon-refresh"></i></a>
                <div class="uk-flex-item-1 uk-text-right">
                    <a class="uk-offcanvas-close uk-link-muted uk-icon-close"></a>
                </div>
            </div>
            <div class="content uk-flex-item-1" if="{entry}">

                <cp-preloader class="uk-container-center uk-margin-large-top" if="{loading}"></cp-preloader>

                <div class="uk-margin-large-top uk-text-muted uk-text-center uk-h2" if="{!loading && !App.Utils.count(data)}">
                    <div><i class="uk-icon-unlink"></i></div>
                    <p>{App.i18n.get('No links found') }</p>
                </div>

                <div if="{!loading && App.Utils.count(data)}">

                    <div class="uk-margin" each="{resources,group in data}">
                        <div class="uk-margin">
                            
                            <div class="uk-text-bold uk-text-capitalize uk-h3"><i class="uk-icon-cubes"></i> { group }</div>
                            <hr>
                            <div class="uk-margin" each="{items,resource in resources}">

                                <div class="uk-text-small uk-text-bold uk-text-upper"><i class="uk-icon-cube"></i> {resource}</div>

                                <div class="uk-margin uk-margin-left { items.length > 10 ? 'uk-scrollable-box':'' }">
                                    <ul class="uk-list">
                                        <li class="uk-text-truncate uk-margin-small" each="{item in items}">
                                            <a href="{ item.link }" target="_blank">
                                                <i class="uk-badge uk-badge-outline uk-text-primary uk-icon-link uk-margin-small-right"></i> 
                                                <span class="uk-text-muted">{ item.label || item.link }</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this.entry = null;

        this.on('mount', function() {

            App.$(this.root).on('hide.uk.offcanvas', function() {
                $this.entry = null;
                setTimeout($this.update, 100);
            });
        });

        load() {

            this.data = [];
            this.loading = true;

            App.request('/collections/utils/getLinkedOverview', {id: this.entry._id}).then(function(data) {
                $this.data = data;
                $this.loading = false;
                $this.update();
            });
        }

        show(entry) {

            this.entry = entry;

            UIkit.offcanvas.show(this.refs.offcanvas);
            this.load();

            setTimeout(this.update, 100);
        }

    </script>

</collection-linked>