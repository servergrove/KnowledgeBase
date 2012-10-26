<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AdminDefaultController
 *
 * @Route("/admin")
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class DefaultController extends Controller
{

    /**
     * @Route("/login", name="sgkb_admin_login")
     * @Template
     */
    final public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error
        );
    }

    /**
     * @Route("/login-check", name="sgkb_admin_login_check")
     * @Method("post")
     * @Template
     */
    final public function loginCheckAction()
    {
        return array();
    }

    /**
     * @Route("/logout", name="sgkb_admin_logout")
     */
    final public function logoutAction()
    {
        return array();
    }

    /**
     * @Route("/", name="sgkb_admin")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    final public function redirectAction()
    {
        return $this->redirect($this->generateUrl('sgkb_admin_categories_index'));
    }

    /**
     * @Template
     */
    public function adminAccessAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_EDITOR')) {
            return new \Symfony\Component\HttpFoundation\Response('');
        }

        return array();
    }

    /**
     * @Template
     *
     * @return array
     */
    public function topbarAction()
    {
        /** @var $token \Symfony\Component\Security\Core\Authentication\Token\TokenInterface */
        $roles = $this->get('security.context')->getToken()->getRoles();

        return array('authenticated' => !empty($roles));
    }
}
