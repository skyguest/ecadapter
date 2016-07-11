<?php
namespace Skyguest\Ecadapter\Auth;

class EloquentUserProvider extends UserProvider {

	protected $model;
	
	public function __construct($model) {
		$this->model = $model;
	}

	public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    public function getById($id) {
    	return $this->createModel()->newQuery()->find($id);
    }
}