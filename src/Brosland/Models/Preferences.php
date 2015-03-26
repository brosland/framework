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
	private $defaults = array ();


	/**
	 * @param EntityDao $preferenceDao
	 */
	public function __construct(EntityDao $preferenceDao)
	{
		$this->preferenceDao = $preferenceDao;
	}

	/**
	 * @param array $defaults
	 */
	public function setDefaultValues(array $defaults)
	{
		$this->defaults = array_merge($this->defaults, $defaults);
	}

	/**
	 * @param string $name
	 * @param mixin $defaultValue
	 */
	public function getPreference($name, $defaultValue = NULL)
	{
		$preference = $this->preferenceDao->findOneBy(array ('name' => $name));

		if ($preference)
		{
			return $preference->getValue();
		}
		else if ($defaultValue !== NULL)
		{
			return $defaultValue;
		}
		else
		{
			return isset($this->defaults[$name]) ? $this->defaults[$name] : NULL;
		}
	}

	/**
	 * @param string $name
	 * @param mixin $value
	 */
	public function setPreference($name, $value = NULL)
	{
		$preference = $this->preferenceDao->findOneBy(array ('name' => $name));

		if ($value !== NULL)
		{
			if (!$preference)
			{
				$preference = new PreferenceEntity($name, $value);
			}

			$preference->setValue($value);
			$this->preferenceDao->save($preference);
		}
		else if ($preference)
		{
			$this->preferenceDao->delete($preference);
		}
	}
}