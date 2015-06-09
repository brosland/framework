<?php

namespace Brosland\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Forms\Controls,
	Nette\Forms\Form,
	Nette\Forms\IControl;

class BootstrapRenderer extends DefaultFormRenderer
{
	/**
	 * @var Controls\Button
	 */
	public $primaryButton = NULL;
	/**
	 * @var bool
	 */
	private $initialized = FALSE;


	public function __construct()
	{
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class=form-group';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = 'div class=controls';
		$this->wrappers['label']['container'] = 'div class="control-label"';
		$this->wrappers['control']['description'] = 'span class=help-block';
		$this->wrappers['control']['errorcontainer'] = 'span class=help-block';
	}

	/**
	 * @return string
	 */
	public function renderBegin()
	{
		$this->controlsInit();

		return parent::renderBegin();
	}

	/**
	 * @return string
	 */
	public function renderEnd()
	{
		$this->controlsInit();

		return parent::renderEnd();
	}

	/**
	 * @return string
	 */
	public function renderBody()
	{
		$this->controlsInit();

		return parent::renderBody();
	}

	/**
	 * 
	 * @param \Nette\Forms\Container $parent
	 * @return string
	 */
	public function renderControls($parent)
	{
		$this->controlsInit();

		return parent::renderControls($parent);
	}

	/**
	 * @param \Nette\Forms\IControl $control
	 * @return string
	 */
	public function renderPair(IControl $control)
	{
		$this->controlsInit();

		return parent::renderPair($control);
	}

	/**
	 * @param array $controls
	 * @return string
	 */
	public function renderPairMulti(array $controls)
	{
		$this->controlsInit();

		return parent::renderPairMulti($controls);
	}

	/**
	 * @param \Nette\Forms\IControl $control
	 * @return string
	 */
	public function renderLabel(IControl $control)
	{
		$this->controlsInit();

		return parent::renderLabel($control);
	}

	/**
	 * @param \Nette\Forms\IControl $control
	 * @return string
	 */
	public function renderControl(IControl $control)
	{
		$this->controlsInit();

		return parent::renderControl($control);
	}

	private function controlsInit()
	{
		if ($this->initialized)
		{
			return;
		}

		$this->initialized = TRUE;
		$usedPrimary = FALSE;

		foreach ($this->form->getControls() as $control)
		{
			if ($control instanceof Controls\Button)
			{
				$markAsPrimary = $control === $this->primaryButton || (!isset($this->primary) && !$usedPrimary && $control->parent instanceof Form);

				if ($markAsPrimary)
				{
					$class = 'btn btn-primary';
					$usedPrimary = TRUE;
				}
				else
				{
					$class = 'btn btn-default';
				}

				$control->getControlPrototype()->addClass($class);
			}
			else if ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox)
			{
				$control->getControlPrototype()->addClass('form-control');
			}
			else if ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList)
			{
				$control->getSeparatorPrototype()->setName('div')
					->addClass($control->getControlPrototype()->type);
			}
		}
	}
}