<?php

namespace Brosland\Security\Models;

use Brosland\Models\Entity,
	Doctrine\Common\Collections\ArrayCollection,
	Doctrine\ORM\Mapping as ORM,
	DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="User")
 */
class UserEntity extends Entity
{
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $name;
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $surname;
	/**
	 * @ORM\Column(unique=TRUE)
	 * @var string
	 */
	private $email;
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $phone;
	/**
	 * @ORM\Column(length=64)
	 * @var string
	 */
	private $password;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	private $activated = FALSE;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	private $approved = FALSE;
	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var DateTime
	 */
	private $registered;
	/**
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 * @var DateTime
	 */
	private $lastLog;
	/**
	 * @ORM\ManyToMany(targetEntity="Brosland\Security\Models\RoleEntity")
	 * @ORM\JoinTable(
	 * 	name="UserRole",
	 * 	joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 * 	inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
	 * )
	 */
	private $roles;


	public function __construct()
	{
		$this->registered = new DateTime();
		$this->roles = new ArrayCollection();
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
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @return string $surname
	 * @return self
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFullname()
	{
		return $this->name . ' ' . $this->surname;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return self
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 * @return self
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return self
	 */
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isActivated()
	{
		return $this->activated;
	}

	/**
	 * @param bool $activated
	 * @return self
	 */
	public function setActivated($activated)
	{
		$this->activated = $activated;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isApproved()
	{
		return $this->approved;
	}

	/**
	 * @param bool $approved
	 * @return self
	 */
	public function setApproved($approved)
	{
		$this->approved = $approved;

		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getRegistered()
	{
		return $this->registered;
	}

	/**
	 * @return DateTime
	 */
	public function getLastLog()
	{
		return $this->lastLog;
	}

	/**
	 * @param DateTime $lastLog
	 * @return self
	 */
	public function setLastLog(DateTime $lastLog)
	{
		$this->lastLog = $lastLog;

		return $this;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getRoles()
	{
		return $this->roles;
	}
}