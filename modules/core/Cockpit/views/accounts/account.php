{{ $app->assets(['assets:vendor/uikit/addons/js/form-password.min.js','assets:vendor/uikit/addons/css/form-password.min.css']) }}

<h1>
    <a href="@route('/accounts/index')">Accounts</a> / Account
</h1>

<div class="uk-grid" data-ng-controller="account" data-uk-margin>

    <div class="uk-width-medium-2-3">

        <div class="app-panel">

            <div class="uk-grid" data-uk-margin>

                <div class="uk-width-medium-1-4">

                    <div class="uk-panel uk-text-center">

                        <div class="uk-thumbnail uk-rounded">
                            <img src="http://www.gravatar.com/avatar/{{ md5(@$account['email']) }}?d=mm&s=100" width="100" height="100" alt="">
                        </div>

                        <h2>@@ account.name @@</h2>
                    </div>
                </div>
                <div class="uk-width-medium-3-4">

                    <form class="uk-form" data-ng-submit="save()" data-ng-show="account">


                        <div class="uk-form-row">
                            <label class="uk-text-small">Name</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.name">
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">Username</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.user">
                        </div>

                        <div class="uk-form-row">
                            <label class="uk-text-small">Email</label>
                            <input class="uk-width-1-1 uk-form-large" type="text" data-ng-model="account.email">
                        </div>

                        <hr>

                        <div class="uk-form-row">
                            <label class="uk-text-small">New Password</label>
                            <div class="uk-form-password uk-width-1-1">
                                <input class="uk-form-large uk-width-1-1" type="password" placeholder="Password" data-ng-model="account.password">
                                <a href="" class="uk-form-password-toggle" data-uk-form-password>Show</a>
                            </div>
                            <div class="uk-alert">
                                Leave the password field empty to keep your current password.
                            </div>
                        </div>

                        <div class="uk-form-row">
                            <button class="uk-button uk-button-large uk-button-primary uk-width-1-2">Save</button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>
</div>
<script>

    App.module.controller("account", function($scope, $rootScope, $http){

        $scope.account = {{ json_encode($account) }};

        $scope.save = function(){

            var account = angular.copy($scope.account),
                isnew   = account["_id"] ? false:true;

            $http.post(App.route("/accounts/save"), {"account": account}).success(function(data){

                if(data && Object.keys(data).length) {
                    App.notify("account saved!");

                    $scope.account = data;
                    $scope.account.password = "";
                }

            }).error(App.module.callbacks.error.http);
        };

    });


</script>