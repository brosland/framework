<?php

namespace Brosland\DI;

use Brosland\Models\PreferenceEntity,
	Brosland\Security\Models\PrivilegeEntity,
	Brosland\Security\Models\RoleEntity,
	Kdyby\Doctrine\DI\IEntityProvider,
	Nette\DI\Statement;

class BroslandExtension extends \Nette\DI\CompilerExtension implements IEntityProvider
{
	const TAG_MODULE_ROUTER = 'brosland.moduleRouter';

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

		$builder->addDefinition($this->prefix('router'))
			->setFactory(\Brosland\RouterFactory::class . '::createRouter')
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('authorizator'))
			->setClass(\Brosland\Security\Authorizator::class)
			->setArguments(array (
				new Statement('@doctrine.dao', array (PrivilegeEntity::class)),
				new Statement('@doctrine.dao', array (RoleEntity::class))
			))
			->addSetup('addPrivilegeDefinitions', array ($config['security']['privileges']))
			->addSetup('addRoleDefinitions', array ($config['security']['roles']));

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

		$router = $builder->getDefinition('router');
		
		$moduleRouters = array_keys($builder->findByTag(self::TAG_MODULE_ROUTER));
		
		foreach ($moduleRouters as $serviceName)
		{
			$router->addSetup('offsetSet', array(NULL, '@' . $serviceName));
		}

		$router->addSetup('offsetSet', array(NULL, $this->prefix('@router')));
	}

	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array ('Brosland' => __DIR__ . '/../');
	}
}