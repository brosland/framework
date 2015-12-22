<?php

namespace Brosland\UI;

use Closure,
	Nette\Forms\IControl,
	Nette\InvalidArgumentException,
	Nette\InvalidStateException,
	Nette\Utils\Callback;

class EntityForm extends Form
{

	/**
	 * @example 'user.name'
	 */
	const PROPERTY = ':property';


	/**
	 * @var mixed
	 */
	private $entity = NULL;
	/**
	 * @var Closure
	 */
	private $entityFactory = NULL;


	public function __construct()
	{
		parent::__construct();

		$this->onSuccess[] = $this->updateEntity;
	}

	/**
	 * @param Closure $factory
	 */
	public function setEntityFactory(Closure $factory)
	{
		Callback::check($factory);

		$this->entityFactory = $factory;
	}

	/**
	 * @return mixed
	 */
	public function getEntity()
	{
		if ($this->entity == NULL)
		{
			if ($this->entityFactory == NULL)
			{
				throw new InvalidStateException('Undefined entity factory.');
			}

			$this->entity = Callback::invokeArgs($this->entityFactory, [$this]);

			if (!is_object($this->entity))
			{
				throw new InvalidStateException('Entity factory has to return an object.');
			}
		}

		return $this->entity;
	}

	/**
	 * @param mixed $entity
	 */
	public function bindEntity($entity)
	{
		$this->entity = $entity;

		if ($this->entity == NULL)
		{
			$this->setDefaults([], TRUE);

			return;
		}

		foreach ($this->getComponents(TRUE, IControl::class) as $control)
		{
			$property = $control->getOption(self::PROPERTY);

			if ($property)
			{
				$control->setDefaultValue($this->getEntityProperty($property));
			}
		}
	}

	public function updateEntity()
	{
		$this->getEntity(); // prepare entity
		$values = $this->getValues();

		foreach ($this->getComponents(TRUE, IControl::class) as $control)
		{
			$property = $control->getOption(self::PROPERTY);

			if ($property !== NULL)
			{
				$this->setEntityProperty($property, $values[$control->getName()]);
			}
		}
	}

	/**
	 * @param string $propertyPath
	 * @return mixed
	 */
	private function getEntityProperty($propertyPath)
	{
		$value = $this->entity;

		if (is_string($propertyPath))
		{
			$properties = explode('.', $propertyPath);

			if (empty($properties))
			{
				throw new InvalidArgumentException('The path to the property is empty.');
			}

			foreach ($properties as $property)
			{
				$value = $value->$property;

				if ($value === NULL)
				{
					return NULL;
				}
			}
		}

		return $value;
	}

	/**
	 * @param string $path
	 * @param mixed $value
	 */
	private function setEntityProperty($path, $value)
	{
		$object = $this->entity;
		$properties = explode('.', $path);

		if (empty($properties))
		{
			throw new InvalidArgumentException('Invalid path to property.');
		}

		for ($i = 0; $i < count($properties) - 1; $i++)
		{
			$object = $object->{$properties[$i]};

			if (!is_object($object))
			{
				throw new InvalidArgumentException("Unexpected value '$object', an object expected.");
			}
		}

		$object->{end($properties)} = $value;
	}
}