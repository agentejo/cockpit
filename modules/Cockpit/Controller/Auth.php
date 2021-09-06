<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\Controller;

class Auth extends \LimeExtra\Controller {


    public function check() {

        if ($auth = $this->param('auth')) {

            if (!isset($auth['user'], $auth['password']) || !\is_string($auth['user']) || !\is_string($auth['password'])) {
                return ['success' => false, 'error' => 'Pre-condition failed'];
            }

            $auth = ['user' => $auth['user'], 'password' => $auth['password']];

            if (isset($auth['user']) && $this->app->helper('utils')->isEmail($auth['user'])) {
                $auth['email'] = $auth['user'];
                $auth['user']  = '';
            }

            if (!$this->app->helper('csrf')->isValid('login', $this->param('csrf'), true)) {
                $this->app->trigger('cockpit.authentication.failed', [$auth, 'Csrf validation failed']);
                return ['success' => false, 'error' => 'Csrf validation failed'];
            }

            $user = $this->module('cockpit')->authenticate($auth);

            if ($user && !$this->module('cockpit')->hasaccess('cockpit', 'backend', $user['group'] ?? null)) {
                $user = null;
            }

            if ($user) {

                $this->app->trigger('cockpit.authentication.success', [&$user]);
                $this->module('cockpit')->setUser($user);

                unset($user['api_key'], $user['_reset_token']);

            } else {
                $this->app->trigger('cockpit.authentication.failed', [$auth, 'Authentication failed']);
            }

            if ($this->app->request->is('ajax')) {
                return $user ? ['success' => true, 'user' => $user] : ['success' => false, 'error' => 'Authentication failed'];
            } else {
                $this->reroute('/');
            }

        }

        return false;
    }


    public function login() {

        $redirectTo = '/';

        if ($this->param('to') && \substr($this->param('to'), 0, 1) == '/') {
            $redirectTo = $this->param('to');
        }

        return $this->render('cockpit:views/layouts/login.php', compact('redirectTo'));
    }

    public function logout() {

        $this->module('cockpit')->logout();

        if ($this->app->request->is('ajax')) {
            return ['logout' => true];
        } else {
            $this->reroute('/auth/login?logout=1');
        }
    }

    public function forgotpassword() {

        return $this->render('cockpit:views/layouts/forgotpassword.php');
    }

    public function requestreset() {

        if ($user = $this->param('user')) {

            $query = ['active' => true];

            if ($this->app->helper('utils')->isEmail($user)) {
                $query['email'] = $user;
            } else {
                $query['user'] = $user;
            }

            $user = $this->app->storage->findOne('cockpit/accounts', $query);

            if (!$user) {
                // "fail silently" to not allow user-name bruteforce guessing
                return ['message' => $this('i18n')->get('Recovery email sent if user exists')];
            }

            $token  = uniqid('rp-'.bin2hex(random_bytes(16)));
            $target = $this->app->param('', $this->app->getSiteUrl(true).'/auth/newpassword');
            $data   = ['_id' => $user['_id'], '_reset_token' => $token];

            $this->app->storage->save('cockpit/accounts', $data);
            $message = $this->app->view('cockpit:emails/recover.php', compact('user','token','target'));

            try {
                $response = $this->app->mailer->mail(
                    $user['email'],
                    $this->param('subject', $this->app->getSiteUrl().' - '.$this('i18n')->get('Password Recovery')),
                    $message
                );
            } catch (\Exception $e) {
                $response = $e->getMessage();
            }

            if ($response !== true) {
                return $this->stop(['error' => $this('i18n')->get($response)], 404);
            }

            return ['message' => $this('i18n')->get('Recovery email sent if user exists')];
        }

        return $this->stop(['error' => $this('i18n')->get('User required')], 412);
    }

    public function newpassword() {

        if ($token = $this->param('token')) {

            if (!\is_string($token)) {
                return false;
            }

            $user = $this->app->storage->findOne('cockpit/accounts', ['_reset_token' => $token]);

            if (!$user) {
                return false;
            }

            $user = [
                'md5email' => md5($user['email']),
                'user' => $user['user'],
                'name' => $user['name'],
            ];

            return $this->render('cockpit:views/layouts/newpassword.php', compact('user', 'token'));
        }

        return false;

    }

    public function resetpassword() {

        if ($token = $this->param('token')) {

            if (!\is_string($token)) {
                return false;
            }

            $user = $this->app->storage->findOne('cockpit/accounts', ['_reset_token' => $token]);
            $password = trim($this->param('password'));

            if (!$user || !$password) {
                return false;
            }

            $data = ['_id' => $user['_id'], 'password' =>$this->app->hash($password), '_reset_token' => null];

            $this->app->storage->save('cockpit/accounts', $data);

            return ['success' => true, 'message' => $this('i18n')->get('Password updated')];
        }

        return $this->stop(['error' => $this('i18n')->get('Token required')], 412);
    }
}
