<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;
use ServerGrove\KbBundle\Document\User;
use ServerGrove\KbBundle\Tests\Controller\ControllerTestCase as BaseTestCase;

/**
 * Class AdminControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
abstract class ControllerTestCase extends BaseTestCase
{

    protected function getArticleRepository()
    {
        return $this->getDocumentManager()->getRepository('ServerGroveKbBundle:Article');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->createTestUser();
        $this->login();
    }

    private function createTestUser()
    {
        $user = new User();
        $user->setName('Test User');
        $user->setUsername('mytestuser');

        $encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder();
        $user->setPassword($encoder->encodePassword('mytestpass', $user->getSalt()));

        $user->setEmail('mytest@example.com');
        $user->setEnabled(true);

        $user->setRoles(array('ROLE_ADMIN'));

        $dm = $this->getDocumentManager();

        /** @var $session \PHPCR\SessionInterface */
        $session = $dm->getPhpcrSession();
        $root    = $session->getNode('/');
        if (!$root->hasNode('users')) {
            $root->addNode('users');
        }

        $dm->persist($user);
        $dm->flush();
    }

    private function login()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', '/admin/login');

        $form              = $crawler->selectButton('Login')->form();
        $form['_username'] = 'mytestuser';
        $form['_password'] = 'mytestpass';

        $client->submit($form);
    }

    private function logout()
    {
        $this->getClient()->request('GET', '/admin/logout');
    }
}
