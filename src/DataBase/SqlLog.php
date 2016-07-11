<?php
namespace Skyguest\Ecadapter\DataBase;

class SqlLog {
	
	protected $query = [];

	public function set($query, $bindings = []) {
		array_push($this->query, ['sql' => $query, 'bindings' => $bindings]);
	}

	public function getQuery() {
		return $this->query;
	}

	public function dump() {
		dd($this->query);
	}

	public function all() {
		return $this->getQuery();
	}
}