<?php

namespace Brosland\Security;

use Kdyby\Doctrine\EntityDao,
	Nette\Object,
	Nette\Security as NS;

class Authenticator extends Object implements NS\IAuthenticator
{
	const NOT_ACTIVATED = 5;


	/**
	 * @var \Kdyby\Doctrine\EntityDao
	 */
	private $userDao;
	/**
	 * @var string
	 */
	private $salt;
	/**
	 * @var IIdentityFactory
	 */
	private $identityFactory = NULL;


	/**
	 * @param \Kdyby\Doctrine\EntityDao $userDao
	 * @param string $salt
	 */
	public function __construct(EntityDao $userDao, $salt)
	{
		$this->userDao = $userDao;
		$this->salt = $salt;
	}

	/**
	 * @param \Brosland\Security\IIdentityFactory $identityFactory
	 * @return self
	 */
	public function setIdentityFactory(IIdentityFactory $identityFactory)
	{
		$this->identityFactory = $identityFactory;

		return $this;
	}

	/**
	 * Performs an authentication
	 * @param array $credentials
	 * @return NS\Identity
	 * @throws NS\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;
		$user = $this->userDao->findOneBy(array ('email' => $email));
		/* @var $user UserEntity */

		if (!$user)
		{
			throw new NS\AuthenticationException('messages.signPresenter.errors.identityNotFound',
			self::IDENTITY_NOT_FOUND);
		}
		else if ($user->getPassword() !== $this->calculateHash($password))
		{
			throw new NS\AuthenticationException('messages.signPresenter.errors.invalidCredential',
			self::INVALID_CREDENTIAL);
		}
		else if (!$user->isActivated())
		{
			throw new NS\AuthenticationException('messages.signPresenter.errors.notActivated',
			self::NOT_ACTIVATED);
		}
		else if (!$user->isApproved())
		{
			throw new NS\AuthenticationException('messages.signPresenter.errors.notApproved',
			self::NOT_APPROVED);
		}

		$user->setLastLog(new \DateTime());
		$this->userDao->save($user);

		if ($this->identityFactory !== NULL)
		{
			return $this->identityFactory->create($user);
		}
		else
		{
			return new NS\Identity($user->getId(), $user->getRoles()->getValues(),
				array (
				'name' => $user->getName(),
				'surname' => $user->getSurname(),
				'email' => $user->getEmail(),
				'phone' => $user->getPhone()
			));
		}
	}

	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public function calculateHash($password)
	{
		return sha1($password . $this->salt);
	}
}