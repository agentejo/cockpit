(function($){

    App.module.controller("table", function($scope, $rootScope, $http, $timeout){

        var id = $("[data-ng-controller='table']").data("id");


        if (id) {

            // fetch table
            $http.post(App.route("/api/datastore/findOne"), {filter: {"_id":id}}, {responseType:"json"}).success(function(data){

                if (data && Object.keys(data).length) {

                    $scope.table = data;

                    // fetch entries
                    $scope.loadmore();
                }

            }).error(App.module.callbacks.error.http);

        } else {

            $scope.table = {
                name: ""
            };

            $scope.entries = [];
        }

        $scope.loadmore = function() {

            var limit  = 25, filter = false;

            $http.post(App.route("/api/datastore/entries"), {

                "table" : angular.copy($scope.table),
                "limit" : limit,
                "skip"  : $scope.entries ? $scope.entries.length : 0

            }, {responseType:"json"}).success(function(data){

                if (data) {

                    if (!$scope.entries) {
                        $scope.entries = [];
                    }

                    if (data.length) {

                        if (data.length < limit) {
                            $scope.nomore = true;
                        }

                        $scope.entries = $scope.entries.concat(data);

                    } else {
                       $scope.nomore = true;
                    }
                }

            }).error(App.module.callbacks.error.http);
        };


        $scope.save = function() {

            var table = angular.copy($scope.table);

            $http.post(App.route("/api/datastore/save"), {"table": table}).success(function(data){

                if (data && Object.keys(data).length) {
                    $scope.table = data;
                    App.notify(App.i18n.get("Table saved!"), "success");
                }

            }).error(App.module.callbacks.error.http);
        };


        $scope.remove = function(index, entryId){

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                $http.post(App.route("/api/datastore/removeentry"), {

                    "table": angular.copy($scope.table),
                    "entryId": entryId

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.entries.splice(index, 1);
                        App.notify(App.i18n.get("Entry removed"), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };

        $scope.edit = function(entry){

            Editor.show(entry);
        };

        $scope.saveEntry = function(entry) {

            $http.post(App.route("/api/datastore/saveentry"), {

                "table": angular.copy($scope.table),
                "entry": angular.copy(entry)

            }, {responseType:"json"}).success(function(data){

                $timeout(function(){

                    if (!entry['_id']) {
                        $scope.entries.push(data);
                    } else {

                        for (var i=0,max=$scope.entries.length;i<max;i++) {

                            if ($scope.entries[i]._id === entry._id) {
                                $scope.entries[i] = data;
                                break;
                            }
                        }
                    }

                    App.notify(App.i18n.get("Entry saved"), "success");
                }, 0);

            }).error(App.module.callbacks.error.http);
        };

        // batch actions

        $scope.selected = null;

        $scope.$on('multiple-select', function(e, data){
            $timeout(function(){
                $scope.selected = data.items.length ? data.items : null;
            }, 0);
        });

        $scope.removeSelected = function(){
            if ($scope.selected && $scope.selected.length) {

                App.Ui.confirm(App.i18n.get("Are you sure?"), function() {

                    var row, scope, $index, $ids = [], table = angular.copy($scope.table);

                    for(var i=0;i<$scope.selected.length;i++) {
                        row    = $scope.selected[i],
                        scope  = $(row).scope(),
                        entry  = scope.entry,
                        $index = scope.$index;

                        (function(row, scope, entry, $index){

                            $http.post(App.route("/api/datastore/removeentry"), {
                                "table": table,
                                "entryId": entry._id
                            }, {responseType:"json"}).error(App.module.callbacks.error.http);

                            $ids.push(entry._id);

                        })(row, scope, entry, $index);
                    }

                    $scope.entries = $scope.entries.filter(function(entry){
                        return ($ids.indexOf(entry._id)===-1);
                    });
                });
            }
        };

        $scope.emptytable = function() {

            App.Ui.confirm(App.i18n.get("Are you sure?"), function() {
                $http.post(App.route("/api/datastore/emptytable"), {

                    "table": angular.copy($scope.table)

                }, {responseType:"json"}).success(function(data){

                    $timeout(function(){
                        $scope.entries = [];
                        App.notify(App.i18n.get("Done."), "success");
                    }, 0);

                }).error(App.module.callbacks.error.http);
            });
        };


        Editor.init($scope);

    });

    var Editor = {

        init: function($scope) {

            if (this.element) {
                return;
            }

            var $this = this;

            this.scope   = $scope;

            this.element = $("#entry-editor");
            this.toolbar = this.element.find("nav");
            this.code    = CodeMirror.fromTextArea(this.element.find("textarea")[0], {
                               lineNumbers: true,
                               styleActiveLine: true,
                               matchBrackets: true,
                               autoCloseBrackets: true,
                               autoCloseTags: true,
                               mode: 'text',
                               theme: 'pastel-on-dark',
                               mode: 'javascript'
                           });

            this.resize();

            $(window).on("resize", UIkit.Utils.debounce(function(){
                $this.resize();
            }, 150));


            this.element.on("click", "[data-editor-action]", function(){

                switch($(this).data("editorAction")) {
                    case "close":
                        $this.close();
                        break;
                    case "save":
                        $this.save();
                        break;
                }
            });

            // key mappings

            this.code.addKeyMap({
                'Ctrl-S': function(){ Editor.save(); },
                'Cmd-S': function(){ Editor.save(); },
                'Esc': function(){ Editor.close(); }
            });
        },

        resize: function(){

            if (!this.element.is(":visible")) {
                return;
            }

            var wrap = this.code.getWrapperElement();

            wrap.style.height = (this.element.height() - this.toolbar.height())+"px";
            this.code.refresh();
        },

        save: function(){

            try {

                var json = JSON.parse(this.code.getValue());

                this.scope.saveEntry(json);
                this.close();

            } catch(e) {
                App.notify(App.i18n.get("<strong>False JSON syntax:</strong><br>"+e.message), "danger");
            }
        },

        show: function(entry){

            this.code.setValue(JSON.stringify(angular.copy(entry), undefined, 2));
            this.code.getDoc().clearHistory();

            this.element.show();
            this.resize();

            setTimeout(function(){
                Editor.code.focus();
            }, 50);

            this.entry = entry;
        },

        close: function(){
            this.entry = null;
            this.element.hide();
        }
    };

})(jQuery);