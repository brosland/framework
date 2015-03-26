<?php

namespace Brosland\DI;

class PageConfiguration extends \Nette\Object
{
	/**
	 * @var string
	 */
	public $version;
	/**
	 * @var string
	 */
	public $name;
	/**
	 * @var string
	 */
	public $email;
	/**
	 * @var int
	 */
	public $created;
	/**
	 * @var array
	 */
	public $owner;


	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		if (!isset($config['name']) || !isset($config['email']) || !isset($config['created']) || !isset($config['owner']))
		{
			throw new \Nette\InvalidArgumentException(
			'One of required parameters (name, email, created, owner) is missing.');
		}

		$this->version = $config['version'];
		$this->name = $config['name'];
		$this->email = $config['email'];
		$this->created = (int) $config['created'];
		$this->owner = $config['owner'];
	}
}