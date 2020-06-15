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

        <div class="uk-offcanvas-bar uk-offcanvas-bar-flip uk-width-3-4 uk-flex uk-flex-column">
            <div class="uk-flex uk-flex-middle header">
                <span class="uk-badge">{opts.title || 'Linked Items' }</span>
                <a class="uk-margin-left" onclick="{ load }"><i class="uk-icon-refresh"></i></a>
                <div class="uk-flex-item-1 uk-text-right">
                    <a class="uk-offcanvas-close uk-link-muted uk-icon-close"></a>
                </div>
            </div>
            <div class="content uk-flex-item-1" if="{entry}">

                <cp-preloader class="uk-container-center uk-margin-large-top" if="{loading}"></cp-preloader>

                <div class="uk-margin-large-top uk-text-muted" if="{!loading && !App.Utils.count(data)}">
                    {App.i18n.get('No links found') }
                </div>

                <div if="{!loading && App.Utils.count(data)}">

                    <div class="uk-margin" each="{resources,group in data}">
                        <div class="uk-margin">
                            
                            <div class="uk-text-bold uk-text-capitalize uk-h3"><i class="uk-icon-cube"></i> { group }</div>
                            <hr>
                            <div class="uk-margin" each="{items,resource in resources}">

                                <div class="uk-text-small uk-text-bold uk-text-upper">{resource}</div>

                                <ul class="uk-list">
                                    <li class="uk-text-truncate" each="{item in items}">
                                        <a href="{ item.link }" target="_blank">{ item.label || item.link }</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </divdiv>
                
                </div>

            </div>
        </div>

    </div>

    <script>

        var $this = this;

        this.entry = null;

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