<?php

namespace Brosland\DI;

class PageConfiguration extends \Nette\Object
{

	/**
	 * @var array
	 */
	private $config;


	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->config['url'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->config['name'];
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->config['email'];
	}

	/**
	 * @return int
	 */
	public function getCreated()
	{
		return $this->config['created'];
	}

	/**
	 * @return array
	 */
	public function getOwner()
	{
		return $this->config['owner'];
	}
}