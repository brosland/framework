<?php

namespace Brosland\DI;

use Brosland\Models\PreferenceEntity,
	Brosland\Security\Models\PrivilegeEntity,
	Brosland\Security\Models\RoleEntity,
	Brosland\Security\Models\UserEntity,
	Kdyby\Doctrine\DI\IEntityProvider,
	Nette\DI\Statement;

class BroslandExtension extends \Nette\DI\CompilerExtension implements IEntityProvider
{
	/**
	 * @var array
	 */
	private static $DEFAULTS = array (
		'page' => array (
			'owner' => array (
				'name' => '',
				'email' => '',
				'tel' => '',
				'fax' => '',
				'street' => '',
				'zip' => '',
				'city' => ''
			)
		),
		'security' => array (
			'passwordSalt' => '',
			'privileges' => array (),
			'roles' => array ()
		)
	);


	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$config = $this->getConfig(self::$DEFAULTS);
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('routerFactory'))
			->setClass(\Brosland\RouterFactory::class);

		$builder->addDefinition($this->prefix('authorizator'))
			->setClass(\Brosland\Security\Authorizator::class)
			->setArguments(array (
				new Statement('@doctrine.dao', array (PrivilegeEntity::class)),
				new Statement('@doctrine.dao', array (RoleEntity::class))
			))
			->addSetup('addPrivilegeDefinitions', array ($config['security']['privileges']))
			->addSetup('addRoleDefinitions', array ($config['security']['roles']));

		$builder->addDefinition($this->prefix('authenticator'))
			->setClass(\Brosland\Security\Authenticator::class)
			->setArguments(array (
				new Statement('@doctrine.dao', array (UserEntity::class)),
				$config['security']['passwordSalt']
		));

		$builder->addDefinition($this->prefix('preferences'))
			->setClass(\Brosland\Models\Preferences::class)
			->setArguments(array (
				new Statement('@doctrine.dao', array (PreferenceEntity::class)),
		));

		$builder->addDefinition($this->prefix('pageConfig'))
			->setClass(PageConfiguration::class)
			->setArguments(array ($config['page']));
	}

	public function beforeCompile()
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		$builder->getDefinition('router')
			->setFactory($this->prefix('@routerFactory::createRouter'));
	}

	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array ('Brosland' => __DIR__ . '/../');
	}
}