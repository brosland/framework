<?php

namespace Brosland\Application\UI;

use Nette\Application\ForbiddenRequestException,
	Nette\Reflection\Method,
	Nette\Security\User;

class SecurityPresenter extends Presenter
{

	public function startup()
	{
		parent::startup();

		$flags = $this->findFlag();

		if ($flags === NULL)
		{
			return;
		}

		if (!is_array($flags))
		{
			$flags = array ($flags);
		}

		foreach ($flags as $flag)
		{
			if (!$this->user->isAllowed($flag->resource, $flag->privilege))
			{
				$this->checkAccessDeniedReason();
			}
		}
	}

	public function checkAccessDeniedReason()
	{
		if ($this->user->isLoggedIn())
		{
			throw new ForbiddenRequestException('Access denied.', 403);
		}

		if ($this->user->getLogoutReason() === User::INACTIVITY)
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
	 * Provides an authentication check on methods and classes marked with @secured annotation
	 * 
	 * @return array|NULL
	 */
	protected function findFlag()
	{
		$annotation = 'secured';

		$signal = $this->signal;
		$signal = is_array($signal) ? reset($signal) : $signal;

		$actionMethod = $this->formatActionMethod($this->action);
		$signalMethod = $this->formatSignalMethod($signal);
		$renderMethod = $this->formatRenderMethod($this->view);

		if ($this->hasAnnotation($annotation))
		{
			return $this->getAnnotation($this->getReflection(), $annotation);
		}
		elseif ($this->hasMethodAnnotation($actionMethod, $annotation))
		{
			$reflection = Method::from($this, $actionMethod);
			return $this->getAnnotation($reflection, $annotation);
		}
		elseif ($this->isSignalReceiver($this) && $this->hasMethodAnnotation($signalMethod, $annotation))
		{
			$reflection = Method::from($this, $signalMethod);
			return $this->getAnnotation($reflection, $annotation);
		}
		elseif ($this->hasMethodAnnotation($renderMethod, $annotation))
		{
			$reflection = Method::from($this, $renderMethod);
			return $this->getAnnotation($reflection, $annotation);
		}

		return NULL;
	}

	/**
	 * Checks if class has a given annotation
	 * 
	 * @param string $annotation
	 * @return bool
	 */
	protected function hasAnnotation($annotation)
	{
		return $this->getReflection()->hasAnnotation($annotation);
	}

	/**
	 * Checks if given method has a given annotation
	 * 
	 * @param string $method
	 * @param string $annotation
	 * @return bool
	 */
	protected function hasMethodAnnotation($method, $annotation)
	{
		if (!$this->getReflection()->hasMethod($method))
		{
			return FALSE;
		}

		$rm = Method::from($this->getReflection()->getName(), $method);
		return $rm->hasAnnotation($annotation);
	}

	/**
	 * Get all anotations of given name
	 * 
	 * @param object $reflection
	 * @param string $name
	 * @return mixed|null
	 */
	protected function getAnnotation($reflection, $name)
	{
		$res = $reflection->getAnnotations();

		if (isset($res[$name]))
		{
			if (sizeof($res[$name]) > 1)
			{
				return $res[$name];
			}

			return end($res[$name]);
		}

		return NULL;
	}
}