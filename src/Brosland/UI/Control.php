<?php

namespace Brosland\UI;

abstract class Control extends \Nette\Application\UI\Control
{
	/**
	 * @var string
	 */
	protected $view = 'default';


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
	public function refresh($snippets = NULL, $destination = 'this',
		$args = array ())
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
	 * @param string $view
	 * @return self
	 */
	public function setView($view)
	{
		$this->view = $view;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function formatTemplatePath()
	{
		$reflection = $this->getReflection();
		$className = $reflection->getShortName();

		return dirname($reflection->getFileName()) . '/../templates/components/'
			. $className . '/' . $this->view . '.latte';
	}

	protected function beforeRender()
	{
		if (!$this->template->getFile())
		{
			$this->template->setFile($this->formatTemplatePath());
		}
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
}