<?php

namespace Brosland\Security;

use Brosland\Security\Models\PrivilegeEntity,
	Brosland\Security\Models\RoleEntity,
	Kdyby\Doctrine\EntityDao,
	Nette\Security\Permission;

class Authorizator extends Permission
{
	/**
	 * @var array
	 */
	public static $DEFAULT_ROLES = array (
		'guest', 'authenticated'
	);
	/**
	 * @var EntityDao
	 */
	private $privilegeDao;
	/**
	 * @var EntityDao
	 */
	private $roleDao;
	/**
	 * @var array
	 */
	private $privilegeDefinitions = array ();
	/**
	 * @var array
	 */
	private $roleDefinitions = array ();
	/**
	 * @var bool
	 */
	private $initialized = FALSE;


	/**
	 * @param EntityDao $privilegeDao
	 * @param EntityDao $roleDao
	 */
	public function __construct(EntityDao $privilegeDao, EntityDao $roleDao)
	{
		$this->privilegeDao = $privilegeDao;
		$this->roleDao = $roleDao;
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

		foreach (self::$DEFAULT_ROLES as $role)
		{
			if (!$this->hasRole($role))
			{
				$this->addRole($role);
			}
		}

		$this->initialized = TRUE;
	}

	public function setup()
	{
		$entityManager = $this->privilegeDao->getEntityManager();

		try
		{
			$entityManager->beginTransaction();

			$privileges = $this->createPrivileges();

			if (!empty($privileges))
			{
				$this->privilegeDao->save($privileges);
			}

			$this->removeInvalidPrivileges($privileges);

			$roles = $this->createRoles($privileges);

			if (!empty($roles))
			{
				$this->roleDao->save($roles);
			}

			$entityManager->commit();
		}
		catch (\Exception $ex)
		{
			$entityManager->rollback();

			throw $ex;
		}
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
		$privilegeEntities = array ();

		foreach ($this->privilegeDefinitions as $resource => $permissions)
		{
			foreach ($permissions as $name => $label)
			{
				$privilegeEntity = $this->privilegeDao->findOneBy(array (
					'resource' => $resource, 'name' => $name));

				if (!$privilegeEntity)
				{
					$privilegeEntity = new PrivilegeEntity($resource, $name, $label);
				}

				$privilegeEntity->setLabel($label);

				$privilegeEntities[$resource . ':' . $name] = $privilegeEntity;
			}
		}

		return $privilegeEntities;
	}

	/**
	 * @param PrivilegeEntity[] $privileges
	 */
	private function removeInvalidPrivileges(array $privileges)
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

		$invalidePrivileges = $queryBuilder->getQuery()->getResult();

		if (!empty($invalidePrivileges))
		{
			$this->privilegeDao->delete($invalidePrivileges);
		}
	}

	/**
	 * @param PrivilegeEntity[] $privileges
	 * @return RoleEntity[]
	 */
	private function createRoles(array $privileges)
	{
		$roleEntities = array ();

		foreach ($this->roleDefinitions as $name => $role)
		{
			$roleEntity = $this->roleDao->findOneBy(array ('name' => $name));

			if (!$roleEntity)
			{
				$roleEntity = new RoleEntity($name);
			}

			$roleEntity->setStatic(isset($role['static']) && $role['static'])
				->setDescription(isset($role['description']) ? $role['description'] : '')
				->getPrivileges()->clear();

			foreach (isset($role['privileges']) ? $role['privileges'] : array () as $resource => $permissions)
			{
				if ($resource == '*')
				{
					$roleEntity->addPrivileges($privileges);
					break;
				}

				$permissions = is_array($permissions) ? $permissions : (array) $permissions;

				foreach ($permissions as $permission)
				{
					if ($permission == '*')
					{
						$filter = function (PrivilegeEntity $privilege) use ($resource)
						{
							return $privilege->getResource() == $resource;
						};

						$roleEntity->addPrivileges(array_filter($privileges, $filter));
					}
					else // resource-privilege
					{
						$roleEntity->getPrivileges()->add($privileges[$resource . ':' . $permission]);
					}
				}
			}

			$roleEntities[] = $roleEntity;
		}

		return $roleEntities;
	}
}