<?php

namespace Brosland\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Forms\Controls,
	Nette\Forms\Form;

class BootstrapRenderer extends DefaultFormRenderer
{
	const PRIMARY_BUTTON = 'btn-primary';
	const TYPE_VERTICAL = 'vertical',
		TYPE_HORIZONTAL = 'horizontal',
		TYPE_INLINE = 'inline';


	/**
	 * @var string
	 */
	private $type = self::TYPE_HORIZONTAL;
	/**
	 * @var string
	 */
	private $templatePath = NULL;


	public function __construct()
	{
		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class="form-group"';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = NULL;
		$this->wrappers['label']['container'] = 'div class="control-label"';
		$this->wrappers['control']['description'] = 'span class="help-block"';
		$this->wrappers['control']['errorcontainer'] = 'span class="help-block"';
	}

	/**
	 * @param type $type
	 * @return self
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setRenderType($type)
	{
		if (!in_array($type, [self::TYPE_HORIZONTAL, self::TYPE_VERTICAL, self::TYPE_INLINE]))
		{
			throw new \Nette\InvalidArgumentException("Invalid render type {$type}.");
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * @param string $templatePath
	 * @return self
	 */
	public function setTemplatePath($templatePath = NULL)
	{
		$this->templatePath = $templatePath;

		return $this;
	}

	/**
	 * @param Form $form
	 * @param string $mode
	 * @return string
	 */
	public function render(Form $form, $mode = NULL)
	{
		$this->form = $form;

		if ($mode !== NULL)
		{
			parent::render($form, $mode);
		}

		$presenter = $form->lookup(\Nette\Application\UI\Presenter::class);
		/* @var $presenter \Nette\Application\UI\Presenter */

		$template = $presenter->getTemplateFactory()->createTemplate();
		$template->form = $template->_form = $form;
		$template->renderer = $this;

		if ($this->templatePath == NULL)
		{
			$template->setFile(__DIR__ . '/templates/form.latte');
		}
		else
		{
			$template->setFile($this->templatePath);
			$template->formTemplate = __DIR__ . '/templates/form.latte';
		}

		$this->beforeRender();

		$template->render();
	}

	public function beforeRender()
	{
		if ($this->type != self::TYPE_VERTICAL)
		{
			$this->form->getElementPrototype()->addClass('form-' . $this->type);
		}

		foreach ($this->form->getControls() as $control)
		{
			if ($control instanceof Controls\Button)
			{
				$class = 'btn btn-' . ($control->getOption(self::PRIMARY_BUTTON, FALSE) ? 'primary' : 'default');
				$control->getControlPrototype()->addClass($class);
			}
			else if ($control instanceof Controls\TextBase ||
				$control instanceof Controls\SelectBox ||
				$control instanceof Controls\MultiSelectBox)
			{
				$control->getControlPrototype()->addClass('form-control');
			}
			else if ($control instanceof Controls\Checkbox ||
				$control instanceof Controls\CheckboxList ||
				$control instanceof Controls\RadioList)
			{
				$inline = $control->getOption(self::TYPE_INLINE, FALSE) ? '-inline' : '';

				$control->getSeparatorPrototype()->setName('div')
					->addClass($control->getControlPrototype()->type . $inline);
			}
		}
	}
}