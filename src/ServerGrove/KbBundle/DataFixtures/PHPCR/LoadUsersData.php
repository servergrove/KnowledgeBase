<?php

namespace ServerGrove\KbBundle\DataFixtures\PHPCR;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ServerGrove\KbBundle\Document\User;

/**
 * Class LoadArticlesData
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class LoadUsersData extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var $session \PHPCR\SessionInterface */
        $session = $manager->getPhpcrSession();
        $root    = $session->getNode('/');
        $root->addNode('users');

        $this->createUser('User Foo', 'user', 'abc123', 'user@example.com', array('ROLE_USER'), $manager);
        $this->createUser('John Editor', 'editor', 'abc123', 'editor@example.com', array('ROLE_EDITOR'), $manager);
        $this->createUser('Administrator', 'admin', 'abc123', 'admin@example.com', array('ROLE_ADMIN'), $manager);

        $manager->flush();
    }

    /**
     * @param string                                     $name
     * @param string                                     $username
     * @param string                                     $password
     * @param string                                     $email
     * @param array                                      $roles
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     *
     * @return \ServerGrove\KbBundle\Document\User
     */
    private function createUser($name, $username, $password, $email, array $roles, ObjectManager $manager)
    {
        $user = new User();
        $user->setName($name);
        $user->setUsername($username);
        $user->setEnabled(true);
        $user->setLocked(false);
        $user->setEmail($email);

        foreach ($roles as $role) {
            $user->addRole($role);
        }

        # @todo Fix this
        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder();
        $user->setPassword($encoder->encodePassword($password, $user->getSalt()));

        $manager->persist($user);

        return $user;
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
