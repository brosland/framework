<?php

namespace Brosland;

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;

class RouterFactory extends \Nette\Object
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();
		$router[] = new SimpleRouter('Homepage:default', Route::ONE_WAY);
		$router[] = new Route('<presenter>[/<action>][/<id>]', 'Homepage:default');

		return $router;
	}
}