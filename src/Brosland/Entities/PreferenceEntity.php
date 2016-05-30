<?php

namespace Brosland\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Preference")
 */
class PreferenceEntity
{
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(unique=TRUE)
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column(nullable=TRUE)
	 * @var string
	 */
	private $value = NULL;


	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct($name, $value = NULL)
	{
		$this->name = $name;
		$this->setValue($value);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return self
	 */
	public function setValue($value = NULL)
	{
		$this->value = $value === NULL ? NULL : (string) $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('[%d] %s => %s', $this->id, $this->name, $this->value);
	}
}