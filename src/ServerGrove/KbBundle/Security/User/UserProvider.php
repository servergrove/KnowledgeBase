<?php

namespace ServerGrove\KbBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * Class UserProvider
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UserProvider implements UserProviderInterface
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        if (!($user = $this->manager->find(null, sprintf('/users/%s', $username)))) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found', $username));
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        try {
            return $this->loadUserByUsername($user->getUsername());
        } catch (UsernameNotFoundException $ex) {
            throw new UnsupportedUserException($ex->getMessage());
        }
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return Boolean
     */
    public function supportsClass($class)
    {
        return 'ServerGrove\KbBundle\Document\User' === $class;
    }
}
