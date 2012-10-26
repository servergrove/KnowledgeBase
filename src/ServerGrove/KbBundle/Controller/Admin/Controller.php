<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use ServerGrove\KbBundle\Controller\Controller as BaseController;

/**
 * Class AdminController
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
abstract class Controller extends BaseController
{

    /**
     * @return string
     */
    protected function getDefaultLocale()
    {
        return $this->get('service_container')->getParameter('server_grove_kb.default_locale');
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }
}
