<?php

namespace Brosland\Security;

use Brosland\Security\Models\UserEntity;

interface IIdentityFactory
{

	/**
	 * @param UserEntity $user
	 * @return \Nette\Security\IIdentity
	 */
	public function create(UserEntity $user);
}