<?php
namespace Skyguest\Ecadapter\Auth;

use Pimple\Container;
use Skyguest\Ecadapter\Session\ISession;

class Auth {

	protected $name;

	protected $provider;

	protected $app;

	protected $session;

	protected $user;

	protected $loggedOut = false;
	
	public function __construct(UserProvider $provider, Container $app = null, ISession $session = null, $name = 'user') {
		$this->provider = $provider;
		$this->app = $app ?: app();
		$this->session = $session ?: $this->app['session']->driver();
		$this->name = $name;
	}

	public function user() {

		if ($this->loggedOut) {
            return;
        }

        if (! is_null($this->user)) {
            return $this->user;
        }

        $id = $this->session->get($this->getName('user_id'));

        $user = null;

        if ( !is_null($id) ) {
        	$user = $this->provider->getById($id);
        }

        // 兼容老版本的cookie不安全，不然可以从cookie拿数据
        // $cookie = $this->getCookier();
        if ( $user ) {
        	$this->session->set($this->getName(), $user->getAuthIdentifier());
        }

        return $this->user = $user;
	}

	public function setUser(IAuthable $user) {
		$this->user = $user;

        $this->loggedOut = false;

        return $this;
	}

	public function getName()
    {
        return 'user_id';
    }

    public function logout() {

    	$this->user = null;

        $this->loggedOut = true;
    }

}