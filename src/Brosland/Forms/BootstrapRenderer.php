<?php

namespace Brosland\Forms;

use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Forms\Controls,
	Nette\Forms\Form;

class BootstrapRenderer extends DefaultFormRenderer
{
	const PRIMARY_BUTTON = 'btn-primary';
	const VIEW_VERTICAL = 'vertical',
		VIEW_HORIZONTAL = 'horizontal',
		VIEW_INLINE = 'inline';


	/**
	 * @var string
	 */
	private $view = self::VIEW_VERTICAL;
	/**
	 * @var \Nette\Application\UI\ITemplate
	 */
	private $template = NULL;


	/**
	 * @param Form $form
	 */
	public function __construct(Form $form)
	{
		$this->form = $form;

		$this->wrappers['controls']['container'] = NULL;
		$this->wrappers['pair']['container'] = 'div class="form-group"';
		$this->wrappers['pair']['.error'] = 'has-error';
		$this->wrappers['control']['container'] = NULL;
		$this->wrappers['label']['container'] = 'div class="control-label"';
		$this->wrappers['control']['description'] = 'span class="help-block"';
		$this->wrappers['control']['errorcontainer'] = 'span class="error-inline"';
	}

	/**
	 * @param string $view
	 * @return self
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setView($view)
	{
		if (!in_array($view, [self::VIEW_HORIZONTAL, self::VIEW_VERTICAL, self::VIEW_INLINE]))
		{
			throw new \Nette\InvalidArgumentException("Invalid view {$view}.");
		}

		$this->view = $view;

		return $this;
	}

	/**
	 * @return \Nette\Application\UI\ITemplate
	 */
	public function getTemplate()
	{
		if ($this->template == NULL)
		{
			$presenter = $this->form->lookup(\Nette\Application\UI\Presenter::class);
			/* @var $presenter \Nette\Application\UI\Presenter */

			$this->template = $presenter->getTemplateFactory()->createTemplate();
		}

		return $this->template;
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

		$template = $this->getTemplate();
		$template->form = $template->_form = $form;
		$template->renderer = $this;
		$template->view = $this->view;

		if (!$template->getFile())
		{
			$template->setFile(__DIR__ . '/templates/form.latte');
		}
		else
		{
			$template->formTemplate = __DIR__ . '/templates/form.latte';
		}

		$this->beforeRender();

		$template->render();
	}

	public function beforeRender()
	{
		$this->form->getElementPrototype()->addClass('form-' . $this->view);

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
				$control->getControlPrototype()->addClass('form-control input-sm');
			}
			else if ($control instanceof Controls\Checkbox ||
				$control instanceof Controls\CheckboxList ||
				$control instanceof Controls\RadioList)
			{
				$inline = $control->getOption(self::VIEW_INLINE, FALSE) ? '-inline' : '';

				$control->getSeparatorPrototype()->setName('div')
					->addClass($control->getControlPrototype()->type . $inline);
			}
		}
	}

	/**
	 * @param string $part
	 * @return \Nette\Utils\Html
	 */
	public function renderFormActions($part)
	{
		$formActions = $this->getWrapper('pair container');
		$formActions->addClass('form-actions');

		$labelContainer = $this->getWrapper('label container');
		$formActions->add($labelContainer);

		$controlContainer = $this->getWrapper('control container');
		$formActions->add($controlContainer);

		if ($part == 'begin')
		{
			return $formActions->startTag() . $labelContainer . $controlContainer->startTag();
		}
		else
		{
			return $controlContainer->endTag() . $formActions->endTag();
		}
	}
}