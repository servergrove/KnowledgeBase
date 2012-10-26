<?php

namespace ServerGrove\KbBundle\Tests\Controller\Admin;

use ServerGrove\KbBundle\Util\Sluggable;

/**
 * Class ArticlesFilesControllerTest
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ArticlesFilesControllerTest extends ControllerTestCase
{
    public function testUploaderAction()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', $this->generateUrl('sgkb_admin_articles_files_uploader'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $form = $crawler->selectButton('Submit')->form();
        $form['article_file[path]']->upload($file = __FILE__);

        $date = date('YmdHi');
        $client->submit($form);
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
        $this->assertFileExists($createdFile = $client->getKernel()->getRootDir().'/../web/uploads/'.$date.'-'.basename(__FILE__));

        $client->request('GET', $this->generateUrl('sgkb_admin_articles_files_all', array('_format' => 'json')));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));

        $json = (array) json_decode($client->getResponse()->getContent());

        $this->assertArrayHasKey($key = '/articles-files/uploads-'.Sluggable::urlize(strtolower(basename($createdFile))), $json);
        $this->assertObjectHasAttribute('path', $json[$key]);
        $this->assertEquals(str_replace(dirname(dirname($createdFile)), '', $createdFile), $json[$key]->path);

        unlink($createdFile);
    }

    public function testUploadAction()
    {
        $client = $this->getClient();

        $client->request('POST', $this->generateUrl('sgkb_admin_articles_files_upload'));
        $this->assertEquals(400, $client->getResponse()->getStatusCode(), $this->getErrorMessage($client));
    }
}
