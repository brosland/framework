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
			$this->error('common.accessDenied.', 403);
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
}