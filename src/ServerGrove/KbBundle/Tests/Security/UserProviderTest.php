<?php

namespace ServerGrove\KbBundle\Tests\Security;

use ServerGrove\KbBundle\Document\User;
use ServerGrove\KbBundle\Security\User\UserProvider;
use ServerGrove\KbBundle\Tests\WebTestCase;

/**
 * Class UserProviderTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UserProviderTest extends WebTestCase
{
    public function testLoadByUsername()
    {
        $provider = new UserProvider($this->getDocumentManager());

        $user = $provider->loadUserByUsername('admin');

        $this->assertInstanceOf('ServerGrove\KbBundle\Document\User', $user);
        $this->assertEquals('admin', $user->getUsername());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadByUsernameException()
    {
        $provider = new UserProvider($this->getDocumentManager());

        $provider->loadUserByUsername('non-existent');
    }

    public function testRefreshUser()
    {
        $provider = new UserProvider($dm = $this->getDocumentManager());

        $user = $provider->refreshUser($dm->getRepository('ServerGroveKbBundle:User')->find('/users/admin'));

        $this->assertInstanceOf('ServerGrove\KbBundle\Document\User', $user);
        $this->assertEquals('admin', $user->getUsername());
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshUserException()
    {
        $provider = new UserProvider($dm = $this->getDocumentManager());

        $user = new User();
        $user->setUsername('wrongusername');

        $provider->refreshUser($user);
    }
}
