<?php

namespace Brosland;

use Brosland\Entities\PreferenceEntity,
	Kdyby\Doctrine\EntityManager;

class Preferences extends \Nette\Object
{

	/**
	 * @var \Kdyby\Doctrine\EntityDao
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
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->preferenceDao = $entityManager->getRepository(PreferenceEntity::class);
	}

	/**
	 * @param array $defaults
	 * @return self
	 */
	public function setDefaults(array $defaults)
	{
		$this->defaults = array_merge($this->defaults, $defaults);

		return $this;
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
	 * @param array $names
	 * @param bool $useKeys
	 * @return array
	 */
	public function getPreferences(array $names, $useKeys = FALSE)
	{
		$preferences = $this->loadPreferences(array_values($names));
		$result = [];

		foreach ($names as $key => $name)
		{
			$value = NULL;

			if ($preferences[$name] != NULL)
			{
				$value = $preferences[$name]->getValue();
			}
			else if (isset($this->defaults[$name]))
			{
				$value = $this->defaults[$name];
			}

			$result[$useKeys ? $key : $name] = $value;
		}

		return $result;
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
				$preference = new PreferenceEntity($name);
			}

			$preference->setValue($value);

			$this->preferenceDao->getEntityManager()->persist($preference);
			$this->preferences[$name] = $this->changedPreferences[$name] = $preference;
		}
		else
		{
			$this->preferences[$name] = NULL;

			if ($preference)
			{
				$this->preferenceDao->getEntityManager()->remove($preference);
				$this->changedPreferences[] = $preference;
			}
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
		if (!array_key_exists($name, $this->preferences))
		{
			$this->preferences[$name] = $this->preferenceDao->findOneBy(['name' => $name]);
		}

		return $this->preferences[$name];
	}

	/**
	 * @param array $names
	 * @return PreferenceEntity[]
	 */
	private function loadPreferences(array $names)
	{
		$tmp = array_filter($names, function ($name)
		{
			return !array_key_exists($name, $this->preferences);
		});

		if (!empty($tmp))
		{
			$result = $this->preferenceDao->findAssoc(['name' => $tmp], 'name');

			foreach ($tmp as $name)
			{
				$this->preferences[$name] = isset($result[$name]) ? $result[$name] : NULL;
			}
		}

		$preferences = [];

		foreach ($names as $name)
		{
			$preferences[$name] = $this->preferences[$name];
		}

		return $preferences;
	}
}