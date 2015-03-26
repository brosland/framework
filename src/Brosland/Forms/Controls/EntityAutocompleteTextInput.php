<?php

namespace Brosland\Forms\Controls;

use Doctrine\Common\Collections\ArrayCollection,
	Kdyby\Doctrine\EntityDao,
	Nette\Application\UI\ISignalReceiver,
	Nette\Application\UI\Link,
	Nette\Forms\Controls\TextBase,
	Nette\Forms\Controls\TextInput,
	Nette\Forms\IControl,
	Nette\InvalidArgumentException;

class EntityAutocompleteTextInput extends TextInput implements ISignalReceiver
{
	/** @var string */
	const UNIQUE = ':unique';
	/** @var string */
	const ORIGINAL = ':original';


	/** @var EntityDao */
	private $dao;
	/** @var string */
	private $joiner = ', ';
	/** @var string */
	private $delimiter = '/(,|;)\s*/';
	/** @var string */
	private $nameKey;
	/** @var callable */
	private $factory = NULL;


	/**
	 * @param EntityDao $dao 
	 * @param string $nameKey
	 * @param string $label
	 * @param int $cols
	 */
	public function __construct(EntityDao $dao, $nameKey, $label = NULL, $cols = NULL)
	{
		parent::__construct($label, $cols, NULL);

		$this->dao = $dao;
		$this->nameKey = $nameKey;
	}

	/**
	 * @param string $joiner
	 * @return EntityAutocompleteTextInput
	 */
	public function setJoiner($joiner)
	{
		$this->joiner = $joiner;
		return $this;
	}

	/**
	 * @param string $delimiter
	 * @return EntityAutocompleteTextInput
	 */
	public function setDelimiter($delimiter)
	{
		$this->delimiter = $delimiter;
		return $this;
	}

	/**
	 * @param callable $callback
	 * @return EntityAutocompleteTextInput
	 */
	public function setFactory($callback)
	{
		$this->factory = $callback;
		return $this;
	}

	/**
	 * @param array $value
	 */
	public function setDefaultValue($value)
	{
		if (!is_array($value))
		{
			throw new InvalidArgumentException('Default value must be an array!');
		}
		else if (!empty($value))
		{
			$entities = $this->dao->findBy(array ('id' => $value));
			$nameKey = $this->nameKey;

			$value = implode($this->joiner, array_map(function($entity) use($nameKey)
				{
					return $entity->$nameKey;
				}, $entities));
		}
		else
		{
			$value = null;
		}

		return parent::setDefaultValue($value);
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		$values = preg_split($this->delimiter, parent::getValue());
		$entities = new ArrayCollection();

		foreach ($values as $value)
		{
			if (strlen($value) > 0)
			{
				$entity = $this->dao->findOneBy(array ($this->nameKey => $value));

				if (!$entity && $this->factory)
				{
					$entity = $this->factory->invoke($value);
				}

				if ($entity)
				{
					$entities->add($entity);
				}
			}
		}

		return $entities;
	}

	/**
	 * @param string
	 */
	public function handleAutocomplete($query)
	{
		$presenter = $this->getForm()->getPresenter();

		$presenter->payload->query = $query;
		$presenter->payload->suggestions = $this->getSimilarExpressions($query);
		$presenter->sendPayload();
	}

	/**
	 * @param string
	 * @return array
	 */
	private function getSimilarExpressions($value)
	{
		$nameKey = $this->nameKey;
		$query = $this->dao->createQueryBuilder('e');

		$query->where($query->expr()->like("e.$nameKey", ':value'))
			->setParameter('value', $value . '%');

		$results = $query->getQuery()->execute();

		return array_map(function($entity) use($nameKey)
		{
			return $entity->$nameKey;
		}, $results);
	}

	/**
	 * Generates control's HTML element.
	 * @return \Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();

		$name = $this->lookupPath('Nette\Application\UI\Presenter');
		$presenter = $this->getForm()->getPresenter();

		$control->attrs['data-delimiter'] = $this->delimiter;
		$control->attrs['data-autocomplete'] = new Link($presenter, $name . self::NAME_SEPARATOR . 'autocomplete!', array ());

		return $control;
	}

	/**
	 * This method is called by presenter.
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
		if ($signal === 'autocomplete')
		{
			$this->handleAutocomplete($this->getForm()->getPresenter()->getParam('query'));
		}
		else
		{
			$class = get_class($this);
			throw new BadSignalException("Missing handler for signal '$signal' in $class.");
		}
	}
	/*	 * ***************************** validation ****************************** */

	/**
	 * Filled validator: is control filled?
	 * @param  IControl
	 * @return bool
	 */
	public static function validateFilled(IControl $control)
	{
		return count($control->getValue()) > 0;
	}

	/**
	 * Uniqueness validator: is each value of tag of control unique?
	 * @param  EntityAutocompleteTextInput
	 * @return bool
	 */
	public static function validateUnique(EntityAutocompleteTextInput $control)
	{
		return count(array_unique($control->getValue()->toArray())) === count($control->getValue());
	}

	/**
	 * Equal validator: are control's value and second parameter equal?
	 * @param  IControl
	 * @param  mixed
	 * @return bool
	 */
	public static function validateEqual(IControl $control, $arg)
	{
		throw new \LogicException(':EQUAL validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Min-length validator: has control's value minimal length?
	 * @param  IControl $control
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMinLength(IControl $control, $length)
	{
		throw new \LogicException(':MIN_LENGTH validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Max-length validator: is control's value length in limit?
	 * @param  IControl $control
	 * @param  int  length
	 * @return bool
	 */
	public static function validateMaxLength(IControl $control, $length)
	{
		throw new \LogicException(':MAX_LENGTH validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Length validator: is control's value length in range?
	 * @param  IControl $control
	 * @param  array  min and max length pair
	 * @return bool
	 */
	public static function validateLength(IControl $control, $range)
	{
		throw new \LogicException(':LENGTH validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Email validator: is control's value valid email address?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateEmail(TextBase $control)
	{
		throw new \LogicException(':EMAIL validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * URL validator: is control's value valid URL?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateUrl(TextBase $control)
	{
		throw new \LogicException(':URL validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * @deprecated
	 */
	public static function validateRegexp(TextBase $control, $regexp)
	{
		throw new \LogicException(':REGEXP validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Regular expression validator: matches control's value regular expression?
	 * @param  TextBase
	 * @param  string
	 * @return bool
	 */
	public static function validatePattern(TextBase $control, $pattern)
	{
		throw new \LogicException(':PATTERN validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Integer validator: is each value of tag of control decimal number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateInteger(TextBase $control)
	{
		throw new \LogicException(':INTEGER validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Float validator: is each value of tag of control value float number?
	 * @param  TextBase
	 * @return bool
	 */
	public static function validateFloat(TextBase $control)
	{
		throw new \LogicException(':FLOAT validator is not applicable to EntityAutocompleteTextInput.');
	}

	/**
	 * Range validator: is a control's value number in specified range?
	 * @param  IControl $control
	 * @param  array  min and max value pair
	 * @return bool
	 */
	public static function validateRange(IControl $control, $range)
	{
		throw new \LogicException(':RANGE validator is not applicable to EntityAutocompleteTextInput.');
	}
}