<?php

namespace Brosland;

use Nette\Application\IRouter,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;

class RouterFactory extends \Nette\Object
{
	/**
	 * @var \Nette\Application\Routers\RouteList
	 */
	private $router;


	public function __construct()
	{
		$this->router = new RouteList();
		$this->router[] = new SimpleRouter('Homepage:default', Route::ONE_WAY);
	}

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = clone $this->router;
		//$router[] = new Route('<locale=sk>/<module>[.<presenter=Frontend>][/<action>][/<id [0-9]+>][/<slug [a-z-0-9]+>]', 'Frontend:default');
		$router[] = new Route('<presenter>[/<action>][/<id>]', 'Homepage:default');

		return $router;
	}

	/**
	 * @param \Nette\Application\IRouter $router
	 * @return self
	 */
	public function addRouter(IRouter $router)
	{
		$this->router[] = $router;

		return $this;
	}
}