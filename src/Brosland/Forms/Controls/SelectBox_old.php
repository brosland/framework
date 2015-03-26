<?php

namespace Brosland\Forms\Controls;

use Nette\Utils\Callback;

class SelectBox_old extends \Nette\Forms\Controls\SelectBox
{
	const SIGNAL_ON_CHANGE = 'onChange';


	/**
	 * @var array
	 */
	private $onChange = array ();
	/**
	 * @var \Nette\Forms\Controls\SubmitButton
	 */
	private $submitButton = NULL;
	/**
	 * @var bool
	 */
	private $useSnippet = FALSE;
	/**
	 * @var callable
	 */
	private $optionFactory;
	/**
	 * @var array
	 */
	private $items = array ();


	/**
	 * @param string $label
	 * @param array $items
	 */
	public function __construct($label = NULL, array $items = NULL)
	{
		parent::__construct($label, $items);

		$this->monitor('Nette\Application\UI\Presenter');
	}

	/**
	 * Sets control's value.
	 * @param mixed $value
	 * @return self
	 */
	public function setValue($value)
	{
		parent::setValue($value);

		$this->onChange();

		return $this;
	}

	/**
	 * Sets control's default value.
	 * @param mixed $value
	 * @return self
	 */
	public function setDefaultValue($value)
	{
		parent::setDefaultValue($value);

		$this->onChange();

		return $this;
	}

	/**
	 * Sets items from which to choose.
	 * @param array $items
	 * @param bool $useKeys
	 * @return self
	 */
	public function setItems(array $items, $useKeys = TRUE)
	{
		if (!$this->optionFactory)
		{
			parent::setItems($items, $useKeys);

			$this->items = parent::getItems();
		}
		else
		{
			$this->items = array ();
			$options = array ();

			foreach ($items as $item)
			{
				$option = Callback::invoke($this->optionFactory, $item);

				if (!is_array($option))
				{
					throw new \Nette\InvalidArgumentException('Option has to be an array \'array($key => $value)\'.');
				}

				$value = reset($option);
				$key = key($option);

				$this->items[$key] = $item;
				$options[$key] = $value;
			}

			parent::setItems($options, TRUE);
		}

		$this->onChange();

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSelectedOption()
	{
		return parent::getSelectedItem();
	}

	/**
	 * @return mixed
	 */
	public function getSelectedItem()
	{
		return $this->getValue() !== NULL ? $this->items[$this->getValue()] : NULL;
	}

	/**
	 * @param callable $callback
	 * @return self
	 */
	public function addOnChange($callback)
	{
		$this->onChange[] = $callback;

		if (!$this->submitButton && $this->form)
		{
			$this->createOnChangeSubmitButton();
		}

		return $this;
	}

	/**
	 * @param bool $useSnippet
	 * @return self
	 */
	public function setUseSnippet($useSnippet = TRUE)
	{
		$this->useSnippet = $useSnippet;

		return $this;
	}

	/**
	 * @param callable $optionFactory
	 * @retun self
	 */
	public function setOptionFactory($optionFactory)
	{
		$this->optionFactory = $optionFactory;

		if (!empty($this->items))
		{
			$this->setItems($this->items);
		}

		return $this;
	}

	/**
	 * Generates control's HTML element.
	 * @return \Nette\Utils\Html
	 */
	public function getControl($redraw = FALSE)
	{
		$control = parent::getControl();
		$presenter = $this->getForm()->getPresenter();

		if ($this->submitButton)
		{
			$submitControl = $this->submitButton->getControl()
				->id($this->submitButton->getHtmlId())
				->addClass('onchange-submit-button ajax');

			$control->addAttributes(array (
				'class' => 'onchange-submit',
				'data-onchange-submit-button' => $this->submitButton->getHtmlId()
			));

			$control = \Nette\Utils\Html::el('span')
				->add($control)
				->add($submitControl);
		}

		if ($this->useSnippet && (!$presenter->isAjax() || !$redraw))
		{
			$control = \Nette\Utils\Html::el('div')
				->addAttributes(array ('id' => $presenter->getSnippetId($this->lookupPath())))
				->add($control);
		}

		return $control;
	}

	/**
	 * @return self
	 */
	public function redrawSnippet()
	{
		$presenter = $this->getForm()->getPresenter();
		$snippetId = $presenter->getSnippetId($this->lookupPath());

		$presenter->payload->snippets[$snippetId] = (string) $this->getControl(TRUE);

		return $this;
	}

	public function onChange()
	{
		foreach ($this->onChange as $callable)
		{
			Callback::invoke($callable, $this);
		}
	}

	/**
	 * @param \Nette\ComponentModel\IContainer $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof \Nette\Application\UI\Presenter)
		{
			return;
		}

		if (!empty($this->onChange) && !$this->submitButton)
		{
			$this->createOnChangeSubmitButton();
		}
	}

	private function createOnChangeSubmitButton()
	{
		$this->submitButton = $this->getParent()->addSubmit($this->getName() . 'OnChangeSubmitButton')
			->setValidationScope(FALSE);
		$this->submitButton->onClick[] = function ()
		{
			$presenter = $this->getForm()->getPresenter();

			$this->onChange();

			if ($presenter->isAjax())
			{
				$presenter->sendPayload();
			}
		};
	}
}