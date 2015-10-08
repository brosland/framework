<?php

namespace Brosland\Forms\Controls;

use DateTime,
	Nette\Application\UI\Form,
	Nette\Forms\Controls\TextInput;

class DatePicker extends TextInput
{
	/**
	 * @var string
	 */
	private $format = 'd/m/Y';
	/**
	 * @var array
	 */
	private static $JS_DATE_FORMATS = [
		'd' => 'dd', 'j' => 'd', 'm' => 'mm', 'n' => 'm', 'z' => 'o',
		'Y' => 'yyyy', 'y' => 'y', 'U' => '@', 'h' => 'h', 'H' => 'hh',
		'g' => 'g', 'A' => 'TT', 'i' => 'mm', 's' => 'ss', 'G' => 'h'
	];


	/**
	 * @param string $label
	 * @return Forms\Controls\DatePicker
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);

		$this->addCondition(Form::FILLED)
			->addRule(function($control)
			{
				return $control->getValue() instanceof DateTime;
			}, 'brosland.forms.datePicker.invalidDate');
	}

	/**
	 * @param string $format
	 * @return self
	 */
	public function setDateFormat($format)
	{
		$this->format = $format;

		return $this;
	}

	/**
	 * @param string|DateTime $value
	 * @return self
	 */
	public function setDefaultValue($value = NULL)
	{
		if ($value instanceof DateTime)
		{
			$value = $value->format($this->format);
		}

		return parent::setDefaultValue($value);
	}

	/**
	 * @return DateTime|NULL
	 */
	public function getValue()
	{
		$value = DateTime::createFromFormat($this->format, parent::getValue());
		$err = DateTime::getLastErrors();

		if ($err['error_count'])
		{
			$value = NULL;
		}

		return $value;
	}

	/**
	 * @param string|DateTime $value
	 * @return self
	 */
	public function setValue($value = NULL)
	{
		if ($value instanceof DateTime)
		{
			$value = $value->format($this->format);
		}

		return parent::setValue($value);
	}

	/**
	 * Generates control's HTML element.
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->class[] = 'datepicker';
		$control->data('datepicker-format', $this->translateFormatToJs($this->format));

		return $control;
	}

	/**
	 * @param string $format
	 * @return string
	 */
	protected function translateFormatToJs($format)
	{
		return str_replace(array_keys(static::$JS_DATE_FORMATS), array_values(static::$JS_DATE_FORMATS), $this->translate($format));
	}
}