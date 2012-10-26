<?php

namespace ServerGrove\KbBundle\Listener;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use ServerGrove\KbBundle\Document;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class UserActionListener
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $fromName;

    /**
     * @var string
     */
    private $fromEmail;

    public function __construct(ContainerInterface $container, $fromName, $fromEmail)
    {
        $this->container = $container;
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
    }

    public function postUpdate(LifecycleEventArgs $arg)
    {
        if (!$this->container->isScopeActive('request')) {
            return;
        }

        if (!$this->container->get('security.context')->getToken()->isAuthenticated()) {
            return;
        }

        if ($arg->getDocument() instanceof Document\Article) {
            if ($arg->getDocument()->wereTranslationsModified()) {
                $this->notifyModification($arg->getDocument(), $arg->getDocumentManager());
            }
        }
    }

    private function notifyModification(Document\Article $article, $dm)
    {
        $emails = $dm->getRepository('ServerGroveKbBundle:User')->getEditorsEmails();
        $message = \Swift_Message::newInstance()
            ->setContentType('text/html')
            ->setSubject('An article has been modified and requires your review')
            ->setFrom(array($this->fromEmail => $this->fromName))
            ->setTo($emails)
            ->setBody($this->container->get('templating')->render('ServerGroveKbBundle:Notice:modification.html.twig', $this->getModificationVars($article)));

        $this->container->get('mailer')->send($message);
    }

    private function getModificationVars(Document\Article $article)
    {
        $latestVersion = null;
        $previousVersion = null;
        $locale = '';
        foreach ($article->getTranslations() as $translation) {
            $versions = $translation->getVersions();
            foreach ($versions as $version) {
                if (is_null($latestVersion) || $version->getCreatedAt() > $latestVersion->getCreatedAt()) {
                    $latestVersion = $version;
                    $locale = $translation->getLocale();
                    if (($aux = $translation->getActiveVersion()) instanceof Document\Version) {
                        $previousVersion = $aux;
                    }
                }
            }
        }

        return array(
            'article'  => $article,
            'locale'   => $locale,
            'previous' => $previousVersion,
            'current'  => $latestVersion
        );
    }
}
