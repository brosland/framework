<?php

namespace Brosland\Forms\Controls;

use Kdyby\Doctrine\Entities\BaseEntity,
	Nette\Utils\Callback;

class EntitySelectBox extends \Nette\Forms\Controls\SelectBox
{
	/**
	 * @var callable
	 */
	private $labelFactory = NULL;
	/**
	 * @var array
	 */
	private $entities = array ();


	/**
	 * @param string $label
	 * @param BaseEntity[] $entities
	 */
	public function __construct($label = NULL, array $entities = NULL)
	{
		parent::__construct($label);

		if (!empty($entities))
		{
			$this->setItems($entities);
		}
	}

	/**
	 * @param int|BaseEntity|NULL $value
	 * @return self
	 */
	public function setValue($value)
	{
		if ($value instanceof BaseEntity)
		{
			$value = $value->getId();
		}

		parent::setValue($value);

		return $this;
	}

	/**
	 * @param int|BaseEntity|NULL $value
	 * @return self
	 */
	public function setDefaultValue($value)
	{
		if ($value instanceof BaseEntity)
		{
			$value = $value->getId();
		}

		parent::setDefaultValue($value);

		return $this;
	}

	/**
	 * @return array
	 */
	public function getItems($onlyEntities = TRUE)
	{
		return $onlyEntities ? $this->entities : parent::getItems();
	}

	/**
	 * @param BaseEntity[] $entities
	 * @param bool $useKeys
	 * @return self
	 */
	public function setItems(array $entities, $useKeys = TRUE)
	{
		$this->entities = array ();
		$options = array ();

		foreach ($entities as $entity)
		{
			$this->entities[$entity->getId()] = $entity;

			$label = $this->labelFactory ?
				Callback::invokeArgs($this->labelFactory, array ($entity)) : $entity->getId();

			$options[$entity->getId()] = $label;
		}

		parent::setItems($options);

		return $this;
	}

	/**
	 * Returns selected value.
	 * @return mixed
	 */
	public function getSelectedItem()
	{
		$value = $this->getValue();

		return $value === NULL ? NULL : $this->entities[$value];
	}

	/**
	 * @param callable $labelFactory
	 * @retun self
	 */
	public function setLabelFactory($labelFactory)
	{
		$this->labelFactory = $labelFactory;

		if (!empty($this->entities))
		{
			$this->setItems($this->entities);
		}

		return $this;
	}
}