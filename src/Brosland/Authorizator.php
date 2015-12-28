<?php

namespace Brosland;

use Brosland\Entities\PrivilegeEntity,
	Brosland\Entities\RoleEntity,
	Kdyby\Doctrine\EntityManager,
	Nette\Security\Permission;

class Authorizator extends Permission
{

	const ROLE_GUEST = 'guest', ROLE_AUTHENTICATED = 'authenticated';


	/**
	 * @var EntityManager
	 */
	private $entityManager;
	/**
	 * @var \Kdyby\Doctrine\EntityDao
	 */
	private $privilegeDao, $roleDao;
	/**
	 * @var array
	 */
	private $privilegeDefinitions = [], $roleDefinitions = [
			self::ROLE_GUEST => ['static' => TRUE],
			self::ROLE_AUTHENTICATED => ['static' => TRUE]
	];
	/**
	 * @var bool
	 */
	private $initialized = FALSE;


	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->privilegeDao = $entityManager->getRepository(PrivilegeEntity::class);
		$this->roleDao = $entityManager->getRepository(RoleEntity::class);
	}

	public function init()
	{
		if ($this->initialized)
		{
			return;
		}

		$roles = $this->roleDao->createQueryBuilder('role')
				->addSelect('privilege')
				->leftJoin('role.privileges', 'privilege')
				->getQuery()->execute();
		/* @var $roles RoleEntity[] */

		foreach ($roles as $role)
		{
			if (!$this->hasRole($role->getRoleId()))
			{
				$this->addRole($role->getRoleId());
			}

			foreach ($role->getPrivileges() as $privilege)
			/* @var $privilege PrivilegeEntity */
			{
				if (!$this->hasResource($privilege->getResource()))
				{
					$this->addResource($privilege->getResource());
				}

				$this->allow($role->getRoleId(), $privilege->getResource(), $privilege->getName());
			}
		}

		$this->initialized = TRUE;
	}

	public function setup()
	{
		$this->entityManager->transactional(function ()
		{
			$privileges = $this->createPrivileges();

			if (!empty($privileges))
			{
				$this->entityManager->persist($privileges);
			}

			$invalidPrivileges = $this->findInvalidPrivileges($privileges);

			if (!empty($invalidPrivileges))
			{
				$this->entityManager->remove($invalidPrivileges);
			}

			$roles = $this->createRoles($privileges);

			if (!empty($roles))
			{
				$this->entityManager->persist($roles);
			}
		});
	}

	/**
	 * @param array $privileges
	 * @return self
	 */
	public function addPrivilegeDefinitions($privileges)
	{
		$this->privilegeDefinitions = array_merge($this->privilegeDefinitions, $privileges);

		return $this;
	}

	/**
	 * @param array $roles
	 * @return self
	 */
	public function addRoleDefinitions($roles)
	{
		$this->roleDefinitions = array_merge($this->roleDefinitions, $roles);

		return $this;
	}

	/**
	 * @return PrivilegeEntity[]
	 */
	private function createPrivileges()
	{
		$storedPrivileges = $privileges = [];
		/* @var $storedPrivileges PrivilegeEntity[] */

		foreach ($this->privilegeDao->findAll() as $privilege)
		/* @var $privilege PrivilegeEntity */
		{
			$key = $privilege->getResource() . ':' . $privilege->getName();
			$storedPrivileges[$key] = $privilege;
		}

		foreach ($this->privilegeDefinitions as $resource => $permissions)
		{
			foreach ($permissions as $name => $label)
			{
				$key = $resource . ':' . $name;

				if (isset($storedPrivileges[$key]))
				{
					$privileges[$key] = $storedPrivileges[$key];
				}
				else
				{
					$privilegeEntity = new PrivilegeEntity($resource, $name, $label);
					$privilegeEntity->setLabel($label);

					$privileges[$key] = $privilegeEntity;
				}
			}
		}

		return $privileges;
	}

	/**
	 * @param PrivilegeEntity[] $privileges
	 */
	private function findInvalidPrivileges(array $privileges)
	{
		$queryBuilder = $this->privilegeDao->createQueryBuilder('privilege');

		if (!empty($privileges))
		{
			$ids = array_map(function (PrivilegeEntity $privilege)
			{
				return $privilege->getId();
			}, $privileges);

			$queryBuilder->where($queryBuilder->expr()->notIn('privilege.id', $ids));
		}

		return $queryBuilder->getQuery()->getResult();
	}

	/**
	 * @param PrivilegeEntity[] $privilegeEntities
	 * @return RoleEntity[]
	 */
	private function createRoles(array $privilegeEntities)
	{
		$roles = $this->roleDao->findAssoc([], 'name');
		/* @var $roles RoleEntity[] */

		foreach ($this->roleDefinitions as $name => $role)
		{
			if (isset($roles[$name]) && !$roles[$name]->isStatic())
			{
				continue;
			}

			$roleEntity = isset($roles[$name]) ? $roles[$name] : new RoleEntity($name);
			/* @var $roleEntity RoleEntity */
			$roleEntity->setStatic(isset($role['static']) && $role['static'])
				->setDescription(isset($role['description']) ? $role['description'] : '')
				->removePrivileges();

			$roles[$name] = $roleEntity;

			if (!isset($role['privileges']))
			{
				continue;
			}

			foreach ($role['privileges'] as $privilege)
			{
				list($resource, $permission) = explode(':', $privilege);

				if ($resource === self::ALL && $permission === self::ALL)
				{
					$roleEntity->addPrivileges($privilegeEntities);
				}
				else if ($permission === self::ALL)
				{
					$filter = function (PrivilegeEntity $privilege) use ($resource)
					{
						return $privilege->getResource() === $resource;
					};

					$roleEntity->addPrivileges(array_filter($privilegeEntities, $filter));
				}
				else
				{
					$roleEntity->addPrivilege($privilegeEntities[$privilege]);
				}
			}
		}

		return $roles;
	}
}