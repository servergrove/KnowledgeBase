<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;

/**
 * Class UsersControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UsersControllerTest extends ControllerTestCase
{

    public function testIndexAction()
    {
        $client  = $this->getClient();
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_users_index'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('table.table tbody tr')->count());
    }

    /**
     * @dataProvider getUsersData
     *
     * @param string  $name
     * @param string  $email
     * @param string  $username
     * @param string  $password
     * @param integer $expiration
     *
     * @return void
     */
    public function testNewAction($name, $email, $username, $password, $expiration)
    {
        $client = $this->getClient();

        /** @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_users_new'));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
        $this->assertEquals('User creation', $crawler->filter('form legend')->first()->text());
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form = $crawler->selectButton('Create')->form();

        $form['user[name]']               = $name;
        $form['user[email]']              = $email;
        $form['user[username]']           = $username;
        $form['user[password][password]'] = $form['user[password][confirm_password]'] = $password;
        $form['user[enabled]']->tick();
        $form['user[roles]']->select('ROLE_ADMIN');

        if (is_null($expiration)) {
            unset($form['user[expirationDate]']);
        } else {
            $form['user[expirationDate][day]']->select(date('j', $expiration));
            $form['user[expirationDate][month]']->select(date('n', $expiration));
            $form['user[expirationDate][year]']->select(date('Y', $expiration));
        }

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        /** @var $user \ServerGrove\KbBundle\Document\User */
        $user = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:User')->findOneByUsername($username);
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\User', $user);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($username, $user->getUsername());

        if (is_null($expiration)) {
            $this->assertNull($user->getExpirationDate());
        } else {
            $this->assertEquals(date('Y-m-d', $expiration), $user->getExpirationDate()->format('Y-m-d'));
        }
    }

    public function getUsersData()
    {
        return array(
            array('Test user', 'test@example.com', 'testuser', 'mytestpassword', null),
            array('Test user', 'test@example.com', 'testuser', 'mytestpassword', mktime(14, 21, 0, date('m') + 1, date('d') + 5))
        );
    }

    /**
     * @depends testNewAction
     */
    public function testCreateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_users_create'));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }

    public function testAdminUsersEdit()
    {
        $username = 'admin';
        $client   = $this->getClient();

        /** @var $crawler \Symfony\Component\DomCrawler\Crawler */
        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_users_edit', array('username' => $username)));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $this->assertEquals('User edit', $crawler->filter('form legend')->first()->text(), $this->getErrorMessage($client));
        $this->assertGreaterThan(0, $crawler->filter('form')->count());

        $form               = $crawler->selectButton('Save')->form();
        $form['user[name]'] = $name = 'New name '.md5(microtime(true));

        $client->submit($form);
        $this->assertEquals(302, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        /** @var $user \ServerGrove\KbBundle\Document\User */
        $user = $this->getDocumentManager()->getRepository('ServerGroveKbBundle:User')->findOneByUsername($username);
        $this->assertInstanceOf('ServerGrove\KbBundle\Document\User', $user);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($username, $user->getUsername());
    }

    public function testUpdateAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_users_update', array('username' => 'admin')));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }
}
