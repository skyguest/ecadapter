<?php
namespace Skyguest\Ecadapter\Session;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SessionHandlerInterface;

class Store implements ISession {

	protected $id;

	protected $name;

	protected $handler;

	protected $attributes = [];

	protected $started = false;
	
	public function __construct($name, SessionHandlerInterface $handler, $id = null) {
        $this->name = $name;
        $this->handler = $handler;
		$this->setId($id);
	}

	public function setId($id) {
		if (! $this->isValidId($id)) {
            $id = $this->generateSessionId();
        }

		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}

	public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

	public function start()
    {
        $this->loadSession();

        if ( !is_in_ecshop() ) {
			// 如果不在ECS中的话，每次关闭程序要保存一下session
			register_shutdown_function(array($this, 'save'));
		}

        return $this->started = true;
    }

    protected function loadSession()
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());
    }

    protected function readFromHandler()
    {
        $data = $this->handler->read($this->getId());

        if ($data) {
            $data = @unserialize($this->prepareForUnserialize($data));

            if ($data !== false && $data !== null && is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    protected function prepareForUnserialize($data)
    {
        return $data;
    }

    public function save()
    {
        $this->handler->write($this->getId(), $this->prepareForStorage(serialize($this->attributes)));

        $this->started = false;
    }

    protected function prepareForStorage($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        $keys = is_array($name) ? $name : func_get_args();

        foreach ($keys as $value) {
            if (is_null($this->get($value))) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $default = null)
    {
        return Arr::get($this->attributes, $name, $default);
    }

    /**
     * Get the value of a given key and then forget it.
     *
     * @param  string  $key
     * @param  string  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
    	if ( is_in_ecshop() ) {
    		unset($_SESSION[$key]);
    	}
        return Arr::pull($this->attributes, $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
    	if ( is_in_ecshop() ) {
    		$_SESSION[$name] = $value;
    	}
        Arr::set($this->attributes, $name, $value);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array  $key
     * @param  mixed       $value
     * @return void
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            $this->set($arrayKey, $arrayValue);
        }
    }

    /**
     * Push a value onto a session array.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->attributes;
    }

    public function isStarted()
    {
        return $this->started;
    }

	/**
     * Determine if this is a valid session ID.
     *
     * @param  string  $id
     * @return bool
     */
    public function isValidId($id)
    {
        return is_string($id) && preg_match('/^[a-f0-9]{40}$/', $id);
    }

    /**
     * Get a new, random session ID.
     *
     * @return string
     */
    public function generateSessionId()
    {
        return $this->handler->generateSessionId();
    }
}