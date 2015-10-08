<?php

namespace Brosland\Models;

use Kdyby\Doctrine\EntityDao;

class Preferences extends \Nette\Object
{
	/**
	 * @var EntityDao
	 */
	private $preferenceDao;
	/**
	 * @var array
	 */
	private $defaults = [];
	/**
	 * @var PreferenceEntity[]
	 */
	private $changedPreferences = [];


	/**
	 * @param EntityDao $preferenceDao
	 */
	public function __construct(EntityDao $preferenceDao)
	{
		$this->preferenceDao = $preferenceDao;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return self
	 */
	public function addDefaultPreference($name, $value)
	{
		$this->defaults[$name] = $value;

		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getPreference($name)
	{
		$preference = $this->preferenceDao->findOneBy(['name' => $name]);

		if ($preference)
		{
			return $preference->getValue();
		}
		else
		{
			return isset($this->defaults[$name]) ? $this->defaults[$name] : NULL;
		}
	}

	/**
	 * @param string $name
	 * @param mixin $value
	 * @return self
	 */
	public function setPreference($name, $value = NULL)
	{
		$preference = $this->preferenceDao->findOneBy(['name' => $name]);

		if ($value !== NULL)
		{
			if (!$preference)
			{
				$preference = new PreferenceEntity($name, $value);
			}

			$preference->setValue($value);

			$this->changedPreferences[] = $preference;
			$this->preferenceDao->getEntityManager()->persist($preference);
		}
		else if ($preference)
		{
			$this->changedPreferences[] = $preference;
			$this->preferenceDao->getEntityManager()->remove($preference);
		}

		return $this;
	}

	/**
	 * @return self
	 */
	public function save()
	{
		if (!empty($this->changedPreferences))
		{
			$this->preferenceDao->getEntityManager()->flush($this->changedPreferences);
			$this->changedPreferences = [];
		}

		return $this;
	}
}