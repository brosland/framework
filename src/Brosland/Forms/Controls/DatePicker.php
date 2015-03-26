<?php

namespace Brosland\Forms\Controls;

use Nette\Application\UI\Form,
	Nette\Forms\Controls\TextInput;

class DatePicker extends TextInput
{
	/** @var string */
	private $format = 'd.m.Y';
	/** @var string */
	private $formatLabel = 'dd.mm.rrrr';
	/** @var array */
	private static $formatPhpToJs = array (
		'd' => 'dd',
		'j' => 'd',
		'm' => 'mm',
		'n' => 'm',
		'z' => 'o',
		'Y' => 'yy',
		'y' => 'y',
		'U' => '@',
		'h' => 'h',
		'H' => 'hh',
		'g' => 'g',
		'A' => 'TT',
		'i' => 'mm',
		's' => 'ss',
		'G' => 'h',
	);


	/**
	 * @param string
	 * @param int
	 * @param int
	 * @return Forms\Controls\DatePicker
	 */
	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $cols, $maxLength);

		$this->control->class('datepicker');
		$this->control->data('datepicker-dateformat', $this->translateFormatToJs($this->format));

		$this->addCondition(Form::FILLED)
			->addRule(function($control)
			{
				return $control->getValue() instanceof \DateTime;
			}, 'Dátum musí byť zadaný vo formáte "' . $this->formatLabel . '"!');
	}

	/**
	 * @param string
	 * @return string
	 */
	protected function translateFormatToJs($format)
	{
		return str_replace(array_keys(static::$formatPhpToJs), array_values(static::$formatPhpToJs), $this->translate($format));
	}

	/**
	 * @return \DateTime|NULL
	 */
	public function getValue()
	{
		$value = \DateTime::createFromFormat($this->format, parent::getValue());
		$err = \DateTime::getLastErrors();

		if ($err['error_count'])
		{
			$value = NULL;
		}

		return $value;
	}

	/**
	 * @param \DateTime
	 * @return BaseDateTime
	 */
	public function setValue($value = NULL)
	{
		try
		{
			if ($value instanceof \DateTime)
			{
				return parent::setValue($value->format($this->format));
			}
			else
			{
				return parent::setValue($value);
			}
		}
		catch (\Exception $e)
		{
			return parent::setValue(NULL);
		}
	}

	/**
	 * @return bool
	 */
	public function isFilled()
	{
		return (bool) parent::getValue();
	}
}