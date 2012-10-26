<?php

namespace ServerGrove\KbBundle\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\MappingException;

/**
 * Class PHPCRParamConverter
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class PHPCRParamConverter implements ParamConverterInterface
{

    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    private $manager;

    /**
     * @param \Doctrine\ODM\PHPCR\DocumentManager $manager
     */
    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                                $request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $name    = $configuration->getName();
        $class   = $configuration->getClass();
        $options = $this->getOptions($configuration);

        $document = $this->findOneBy($class, $request, $options);

        if (!$document) {
            return false;
        }

        $request->attributes->set($name, $document);

        return true;
    }

    /**
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface $configuration
     *
     * @return bool
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (preg_match('/[a-zA-Z]+\:[a-zA-Z]+/', $configuration->getClass())) {
            return true;
        }

        if (class_exists($configuration->getClass())) {
            try {
                $this->manager->getClassMetadata($configuration->getClass());

                return true;
            } catch (MappingException $e) {
                return false;
            }
        }

        return false;
    }

    protected function findOneBy($class, Request $request, $options)
    {
        if (!$options['mapping']) {
            $keys               = $request->attributes->keys();
            $options['mapping'] = $keys ? array_combine($keys, $keys) : array();
        }

        foreach ($options['exclude'] as $exclude) {
            unset($options['mapping'][$exclude]);
        }

        if (!$options['mapping']) {
            return false;
        }

        $criteria = array();
        $metadata = $this->manager->getClassMetadata($class);

        foreach ($options['mapping'] as $attribute => $field) {
            if ($metadata->hasField($field) || ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field))) {
                $criteria[$field] = $request->attributes->get($attribute);
            }
        }

        if (!$criteria) {
            return false;
        }

        return $this->manager->getRepository($class)->findOneBy($criteria);
    }

    protected function getOptions(ConfigurationInterface $configuration)
    {
        return array_replace(array(
            'exclude'        => array(),
            'mapping'        => array(),
        ), $configuration->getOptions());
    }
}
