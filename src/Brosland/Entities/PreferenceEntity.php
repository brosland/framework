<?php

namespace Brosland\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Preference")
 */
class PreferenceEntity
{
	use \Kdyby\Doctrine\Entities\Attributes\Identifier,
	 \Kdyby\Doctrine\Entities\MagicAccessors;

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
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return mixin
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 * @return self
	 */
	public function setValue($value = NULL)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('%d - %s => %s', $this->id, $this->name, $this->value);
	}
}