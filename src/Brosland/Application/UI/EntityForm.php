<?php

namespace Brosland\Application\UI;

use Brosland\Models\Entity,
	Doctrine\Common\Collections\Collection;

abstract class EntityForm extends Form
{
	const PROPERTY = ':property';


	/**
	 * @var callable
	 */
	private $entityFactory = NULL;
	/**
	 * @var Entity
	 */
	private $entity = NULL;


	public function __construct()
	{
		parent::__construct();

		$this->onSuccess[] = function()
		{
			if (!$this->hasEntity())
			{
				if (!$this->entityFactory)
				{
					return;
				}

				$this->entity = \Nette\Utils\Callback::invokeArgs($this->entityFactory, array ($this));
			}

			$this->updateEntity();
		};
	}

	/**
	 * @param callable $entityFactory
	 */
	public function setEntityFactory($entityFactory)
	{
		$this->entityFactory = $entityFactory;
	}

	/**
	 * @return bool
	 */
	public function hasEntity()
	{
		return $this->entity != NULL;
	}

	/**
	 * @return Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * @param Entity $entity
	 */
	public function bindEntity(Entity $entity)
	{
		$this->entity = $entity;

		if ($this->entity == NULL)
		{
			$this->setDefaults(array (), TRUE);
			return;
		}

		foreach ($this->getComponents(TRUE, \Nette\Forms\IControl::class) as $control)
		{
			if (!$control->getOption(self::PROPERTY))
			{
				continue;
			}

			$value = $this->getPropertyValue($control->getOption(self::PROPERTY));

			if ($value instanceof Entity)
			{
				$value = $value->getId();
			}
			else if ($value instanceof Collection)
			{
				$value = array_map(function(Entity $entity)
				{
					return $entity->getId();
				}, $value->toArray());
			}

			$control->setDefaultValue($value);
		}
	}

	private function updateEntity()
	{
		foreach ($this->getComponents(TRUE, \Nette\Forms\IControl::class) as $control)
		{
			if ($control->getOption(self::PROPERTY))
			{
				$value = $control instanceof \Brosland\Forms\Controls\EntitySelectBox ?
					$control->getSelectedItem() : $control->getValue();

				$this->setPropertyValue($control->getOption(self::PROPERTY), $value);
			}
		}
	}

	/**
	 * @param string $path
	 * @return mixed
	 */
	private function getPropertyValue($path)
	{
		$value = $this->entity;
		$properties = explode('.', $path);

		if (empty($properties))
		{
			throw new \Nette\InvalidArgumentException('The path to the property is empty.');
		}

		foreach ($properties as $property)
		{
			$value = $value->$property;

			if ($value === NULL)
			{
				return NULL;
			}
		}

		return $value;
	}

	/**
	 * @param string $path
	 * @param mixed $value
	 */
	private function setPropertyValue($path, $value)
	{
		$entity = $this->entity;
		$properties = explode('.', $path);

		if (empty($properties))
		{
			throw new \Nette\InvalidArgumentException('Invalid path to property.');
		}

		for ($i = 0; $i < count($properties) - 1; $i++)
		{
			$entity = $entity->{$properties[$i]};

			if ($entity === NULL)
			{
				return;
			}
		}

		if ($value instanceof Collection)
		{
			$collection = $entity->{end($properties)};
			$collection->clear();

			foreach ($value as $element)
			{
				$collection->add($element);
			}
		}
		else
		{
			$entity->{end($properties)} = $value;
		}
	}
}