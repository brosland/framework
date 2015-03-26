<?php

namespace Brosland\Models;

use Doctrine\ORM\Event\LifecycleEventArgs,
	Kdyby\Events\Subscriber;

abstract class EventSubscriber extends \Nette\Object implements Subscriber
{

	/**
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
	 * @return \Kdyby\Doctrine\EntityDao
	 */
	protected function getDao(LifecycleEventArgs $args)
	{
		return $args->getEntityManager()->getRepository(get_class($args->getEntity()));
	}

	/**
	 * @return array
	 */
	public abstract function getSubscribedEvents();
}