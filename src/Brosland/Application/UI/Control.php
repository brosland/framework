<?php

namespace Brosland\Application\UI;

use Nette\Localization\ITranslator;

abstract class Control extends \Nette\Application\UI\Control
{
	/**
	 * @var string
	 */
	protected $view = 'default';
	/**
	 * @var \Nette\Localization\ITranslator
	 */
	protected $translator = NULL;


	/**
	 * @param \Nette\ComponentModel\IComponent $component
	 */
	protected function attached($component)
	{
		parent::attached($component);

		if (!$component instanceof Presenter)
		{
			return;
		}

		$this->configure($component);
	}

	/**
	 * @param \Brosland\Application\UI\Presenter $presenter
	 */
	protected function configure(Presenter $presenter)
	{
		
	}

	/**
	 * @param string|array snippet names
	 * @param string link destination in format "[[module:]presenter:]view" or "signal!"
	 * @param array|mixed
	 * @return void
	 */
	public function refresh($snippets = NULL, $destination = 'this', $args = array ())
	{
		if ($this->presenter->isAjax())
		{
			if ($snippets)
			{
				foreach ((array) $snippets as $snippet)
				{
					$this->redrawControl($snippet);
				}
			}
			else
			{
				$this->redrawControl();
			}
		}
		else if ($destination)
		{
			$this->redirect($destination, $args);
		}
	}

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 */
	public function setTranslator(ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}

	/**
	 * @param string $view
	 * @return self
	 */
	public function setView($view)
	{
		$this->view = $view;

		return $this;
	}

	public function render()
	{
		$this->beforeRender();

		$renderMethod = 'render' . ucfirst($this->view);

		if (method_exists($this, $renderMethod))
		{
			$this->$renderMethod();
		}

		$this->template->render();
	}

	protected function beforeRender()
	{
		$reflection = $this->getReflection();
		$className = $reflection->getName();

		$name = substr($className, strrpos($className, '\\') + 1, -7); // -7 = strlen('Control')
		$templatePath = dirname($reflection->getFileName())
			. '/../templates/components/' . ucfirst($name) . '/' . $this->view . '.latte';

		if (file_exists($templatePath))
		{
			$this->template->setFile($templatePath);
		}
	}

	/**
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$component = parent::createComponent($name);

		if ($component instanceof Control || $component instanceof Form)
		{
			$component->setTranslator($this->translator);
		}

		return $component;
	}
}