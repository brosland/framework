<?php

namespace Brosland\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Privilege")
 */
class PrivilegeEntity
{
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column
	 * @var string
	 */
	private $resource;
	/**
	 * @ORM\Column(length=64)
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $label;
	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $description = '';


	/**
	 * @param string $resource
	 * @param string $name
	 * @param string $label
	 */
	public function __construct($resource, $name, $label)
	{
		$this->resource = $resource;
		$this->name = $name;
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @param string $resource
	 * @return self
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;

		return $this;
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
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param string $label
	 * @return self
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return self
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}
}