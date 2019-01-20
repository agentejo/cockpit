<div id="loginmodal" class="uk-modal" riot-view>

    <style>
        .uk-modal-dialog { width: 360px; }
    </style>

    <div class="uk-modal-dialog uk-form" ref="loginDialog">

        <form class="uk-form" method="post" action="@route('/auth/check')" onsubmit="{ submit }">

            <div class="uk-text-center">
                <img src="@base('assets:app/media/icons/login.svg')" width="100" height="100">
            </div>

            <div class="uk-form-row uk-text-center uk-h2 uk-text-bold">
                {{ $app['app.name'] }}
            </div>

            <div class="uk-form-row uk-margin-large-top">
                <input ref="user" class="uk-form-large uk-width-1-1" type="text" placeholder="@lang('Username')" required>
            </div>

            <div class="uk-form-row">
                <input ref="password" class="uk-form-large uk-width-1-1" type="password" placeholder="@lang('Password')" required>
            </div>

            <div class="uk-margin-top">
                <button class="uk-button uk-button-primary uk-button-large uk-width-1-1">@lang('Authenticate')</button>
            </div>

        </form>

    </div>

    <script type="view/script">

        var $this = this;

        this.on('mount', function() {

            this.modal = UIkit.modal($this.root, {
                keyboard: false,
                bgclose: false
            });

            // check session
            var check = function() {

                App.request('/check-backend-session').then(function(resp) {

                    if (resp && resp.status && $this.modal.isActive()) {
                        $this.modal.hide();
                    }

                    if (resp && !resp.status && !$this.modal.isActive()) {
                        $this.modal.show();
                    }
                });
            }

            setInterval(check, 60000);

            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) check();
            }, false);
        });

        submit(e) {

            e.preventDefault();

            App.request('/auth/check', {auth:{user:this.refs.user.value, password:this.refs.password.value}}).then(function(data) {

                if (data && data.success) {

                    if (data.user._id != App.$data.user._id) {
                        App.reroute('/');
                    } else {
                        $this.modal.dialog.find('form')[0].reset();
                        $this.modal.hide();
                    }

                } else {

                    $this.modal.dialog.removeClass('uk-animation-shake');

                    setTimeout(function(){
                        $this.modal.dialog.addClass('uk-animation-shake');
                    }, 50);
                }

            }, function(res) {
                App.ui.notify(res && (res.message || res.error) ? (res.message || res.error) : 'Login failed.', 'danger');
            });

            return false;
        }

    </script>

</div>
