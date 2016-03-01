<?php

namespace Brosland\UI;

class Form extends \Nette\Application\UI\Form
{

	public function __construct()
	{
		parent::__construct();

		$this->setRenderer(new \Brosland\Forms\BootstrapRenderer($this));
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
	 * @param Presenter $presenter
	 */
	protected function configure(Presenter $presenter)
	{
		
	}
}