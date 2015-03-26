<?php

namespace Brosland\Application\UI;

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
			$this->error('Access denied.', 403);
		}

		if ($this->user->getLogoutReason() === \Nette\Security\User::INACTIVITY)
		{
			$this->flashMessage('Pre neaktivitu ste boli odhlásený.', 'warning');
		}

		$this->flashMessage('Pre prístup k tejto operácií sa prosím prihláste.', 'warning');
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
	public function refresh($snippets = NULL, $destination = 'this', $args = array ())
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
	 * @param string $link
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeLink($link, $expiration = '+ 10 minutes')
	{
		$session = $this->getSession('Nette.Application/requests');

		do
		{
			$key = \Nette\Utils\Strings::random(5);
		}
		while (isset($session[$key]));

		$session[$key] = array ($this->getUser()->getId(), $link);
		$session->setExpiration($expiration, $key);

		return $key;
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