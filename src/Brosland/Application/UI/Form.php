<?php

namespace Brosland\Application\UI;

use Nextras\Forms\Controls,
	Nette\Forms\Container;

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
\Brosland\Forms\Controls\AntispamControl::register();

Container::extensionMethod('addEntitySelect', function (Container $container, $name, $label = NULL, array $entities = NULL)
{
	return $container[$name] = new \Brosland\Forms\Controls\EntitySelectBox($label, $entities);
});
Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL)
{
	return $container[$name] = new \Brosland\Forms\Controls\DatePicker($label);
});
Container::extensionMethod('addTypeahead', function(Container $container, $name, $label = NULL, $callback = NULL)
{
	return $container[$name] = new Controls\Typeahead($label, $callback);
});
