<?php
namespace Skyguest\Ecadapter\Auth;

abstract class UserProvider {
	
	abstract public function getById($id);

}