<?php

namespace Brosland\UI;

use Nette\InvalidArgumentException;

abstract class Control extends \Nette\Application\UI\Control
{

	const VIEW_DEFAULT = 'default';


	/**
	 * @var string
	 */
	protected $view = self::VIEW_DEFAULT;
	/**
	 * @var string
	 */
	protected $templatePath = NULL;


	/**
	 * @param string $view Name of view or a template path
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public function setView($view)
	{
		$this->view = $view;

		return $this;
	}

	/**
	 * @param string $templatePath
	 * @return self
	 */
	public function setTemplatePath($templatePath)
	{
		$this->templatePath = $templatePath;

		return $this;
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

	public function render()
	{
		$this->beforeRender();

		$this->template->render();
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$this->beforeRender();

		return (string) $this->template;
	}

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
	 * @param string $view
	 * @return string
	 */
	protected function formatTemplatePath($view)
	{
		$reflection = $this->getReflection();
		$className = $reflection->getShortName();

		return dirname($reflection->getFileName()) . '/templates/'
			. $className . '/' . $view . '.latte';
	}

	/**
	 * @return \Nette\Application\UI\ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();

		if ($this->templatePath == NULL)
		{
			$this->templatePath = $this->formatTemplatePath($this->view);
		}

		$template->setFile($this->templatePath);

		return $template;
	}

	protected function beforeRender()
	{
		$renderMethod = 'render' . ucfirst($this->view);

		if (method_exists($this, $renderMethod))
		{
			$this->$renderMethod();
		}
	}
}