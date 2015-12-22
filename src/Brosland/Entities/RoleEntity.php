<?php

namespace Brosland\Entities;

use Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Role")
 */
class RoleEntity implements \Nette\Security\IRole
{
	use \Kdyby\Doctrine\Entities\Attributes\Identifier,
	 \Kdyby\Doctrine\Entities\MagicAccessors;

	/**
	 * @ORM\Column(length=64, unique=TRUE)
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column(type="text")
	 * @var string
	 */
	private $description;
	/**
	 * @ORM\Column(type="boolean")
	 * @var string
	 */
	private $static = FALSE;
	/**
	 * @ORM\ManyToMany(
	 * 		targetEntity="Brosland\Entities\PrivilegeEntity",
	 * 		cascade={"persist", "remove"}, fetch="EAGER"
	 * )
	 * @ORM\JoinTable(
	 * 		name="RolePrivilege",
	 * 		joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var ArrayCollection
	 */
	private $privileges;


	/**
	 * @param string $name
	 */
	public function __construct($name = NULL)
	{
		$this->name = $name;
		$this->privileges = new ArrayCollection();
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

	/**
	 * @return bool
	 */
	public function isStatic()
	{
		return $this->static;
	}

	/**
	 * @param bool $static
	 * @return self
	 */
	public function setStatic($static)
	{
		$this->static = $static;

		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getPrivileges()
	{
		return $this->privileges->toArray();
	}

	/**
	 * @param PrivilegeEntity[] $privilege
	 * @return self
	 */
	public function addPrivilege(PrivilegeEntity $privilege)
	{
		$this->privileges->add($privilege);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRoleId()
	{
		return (string) $this->id;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}
}