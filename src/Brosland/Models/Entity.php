<?php

namespace Brosland\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * @property-read int $id
 */
abstract class Entity extends \Nette\Object
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	protected $id;


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}
}