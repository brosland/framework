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
	private $preferences = [], $changedPreferences = [];


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
	 * @param string $domain
	 * @param bool $indexWithoutDomain
	 * @return mixed
	 */
	public function getPreferences($domain, $indexWithoutDomain = FALSE)
	{
		$query = $this->preferenceDao->createQueryBuilder('preference', 'preference.name');
		$query->where('preference.name LIKE :domain')
			->setParameter('domain', $domain . '%');

		$result = $query->getQuery()->getResult();

		$this->preferences = array_merge($this->preferences, $result);

		$pattern = "/^{$domain}\.?(.*)$/";

		foreach ($this->defaults as $name => $value)
		{
			if (preg_match($pattern, $name) && !isset($result[$name]))
			{
				$result[$name] = $value;
			}
		}

		$preferences = [];

		foreach ($result as $name => $preference)
		{
			if ($indexWithoutDomain)
			{
				$name = preg_filter($pattern, '$1', $name);
			}

			$preferences[$name] = $preference instanceof PreferenceEntity ?
				$preference->getValue() : $preference;
		}

		return $preferences;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getPreference($name)
	{
		$preference = $this->loadPreference($name);

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
		$preference = $this->loadPreference($name);

		if ($value !== NULL)
		{
			if (!$preference)
			{
				$preference = new PreferenceEntity($name, $value);
			}

			$preference->setValue($value);

			$this->preferenceDao->getEntityManager()->persist($preference);

			$this->preferences[] = $this->changedPreferences[] = $preference;
		}
		else if ($preference)
		{
			if (isset($this->preferences[$name]))
			{
				unset($this->preferences[$name]);
			}

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

	/**
	 * @param string $name
	 * @return PreferenceEntity
	 */
	private function loadPreference($name)
	{
		if (isset($this->preferences[$name]))
		{
			return $this->preferences[$name];
		}

		return $this->preferenceDao->findOneBy(['name' => $name]);
	}
}