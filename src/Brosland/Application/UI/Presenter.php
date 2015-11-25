<?php

namespace Brosland\Application\UI;

use Nette\Utils\Strings;

abstract class Presenter extends \Nette\Application\UI\Presenter
{

	/**
	 * @persistent
	 * @var string
	 */
	public $locale;
	/**
	 * @inject
	 * @var \Kdyby\Translation\Translator
	 */
	public $translator;


	public function checkAccessDeniedReason()
	{
		if ($this->user->isLoggedIn())
		{
			$this->error('common.accessDenied', 403);
		}

		if ($this->user->getLogoutReason() === \Nette\Security\User::INACTIVITY)
		{
			$this->flashMessage('common.loggedOutMessage', 'warning');
		}

		$this->flashMessage('common.signInRequestMessage', 'warning');
		$backlink = $this->storeRequest();

		if ($this->isAjax())
		{
			$this->payload->error = TRUE;
			$this->payload->redirect = $this->link(':Sign:in', $backlink);
			$this->sendPayload();
		}

		$this->redirect(':Sign:in', $backlink);
	}

	/**
	 * @param string|array snippet names
	 * @param string link destination in format "[[module:]presenter:]view" or "signal!"
	 * @param array|mixed
	 * @return void
	 */
	public function refresh($snippets = NULL, $destination = 'this', $args = [])
	{
		if ($this->isAjax())
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
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name = NULL)
	{
		$method = 'createComponent' . ucfirst($name);
		$rc = $this->getReflection()->getMethod($method);

		$this->checkRequirements($rc);

		return parent::createComponent($name);
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 * @throws \Nette\Application\ForbiddenRequestException
	 */
	public function checkRequirements($element)
	{
		parent::checkRequirements($element);

		if ($element instanceof \Nette\Reflection\Method)
		{
			$method = $element->getName();

			if (Strings::match($method, '/^createComponent|handle/') !== NULL &&
				$element->hasAnnotation('action'))
			{
				$action = (array) $element->getAnnotation('action');

				if (!in_array($this->getAction(), $action, TRUE))
				{
					throw new \Nette\Application\ForbiddenRequestException();
				}
			}
		}
	}
}