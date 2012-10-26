<?php

namespace ServerGrove\KbBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use ServerGrove\KbBundle\Document\User;
use ServerGrove\KbBundle\Form\UserType;
use ServerGrove\KbBundle\Form\UserEditType;
use ServerGrove\KbBundle\Form\UserPasswordType;
use Symfony\Component\HttpFoundation\Response;

/**
 * User controller.
 *
 * @Route("/admin/{_locale}/users")
 */
class UsersController extends Controller
{

    /**
     * Lists all User documents.
     *
     * @Route("/", name="sgkb_admin_users_index")
     * @Template()
     */
    public function indexAction()
    {
        $dm = $this->getDocumentManager();

        $documents = $dm->getRepository('ServerGroveKbBundle:User')->findAll();

        return array('documents' => $documents);
    }

    /**
     * Finds and displays a User document.
     *
     * @Route("/{username}/show", name="sgkb_admin_users_show")
     * @Template()
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     */
    public function showAction(User $user)
    {
        $deleteForm = $this->createDeleteForm($user);

        return array(
            'document'      => $user,
            'delete_form'   => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new User document.
     *
     * @Route("/new", name="sgkb_admin_users_new")
     * @Template()
     */
    public function newAction()
    {
        $document = new User();
        $form     = $this->createForm(new UserType(), $document);

        return array(
            'document' => $document,
            'form'     => $form->createView()
        );
    }

    /**
     * Creates a new User document.
     *
     * @Route("/create", name="sgkb_admin_users_create")
     * @Method("post")
     */
    public function createAction()
    {
        $document = new User();
        $request  = $this->getRequest();
        $form     = $this->createForm(new UserType(), $document);

        $form->bind($request);
        if ($form->isValid()) {
            $document->setPassword($this->getEncodedPassword($document, $form->get('password')->getData()));

            $dm = $this->getDocumentManager();
            $dm->persist($document);
            $dm->flush();

            return $this->redirect($this->generateUrl('sgkb_admin_users_show', array('username' => $document->getUsername())));
        }

        return $this->render('ServerGroveKbBundle:Admin/Users:new.html.twig', array(
            'document' => $document,
            'form'     => $form->createView()
        ), new Response('', 400));
    }

    /**
     * Displays a form to edit an existing User document.
     *
     * @Route("/{username}/edit", name="sgkb_admin_users_edit")
     * @Template()
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     */
    public function editAction(User $user)
    {
        $editForm   = $this->createForm(new UserEditType(), $user);
        $deleteForm = $this->createDeleteForm($user);

        return array(
            'document'    => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing User document.
     *
     * @Route("/{username}/update", name="sgkb_admin_users_update")
     * @Method("post")
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     */
    public function updateAction(User $user)
    {

        $editForm   = $this->createForm(new UserEditType(), $user);
        $deleteForm = $this->createDeleteForm($user);

        $request = $this->getRequest();

        $editForm->bind($request);
        if ($editForm->isValid()) {
            $dm = $this->getDocumentManager();
            $dm->persist($user);
            $dm->flush();

            return $this->redirect($this->generateUrl('sgkb_admin_users_edit', array('username' => $user->getUsername())));
        }

        return $this->render('ServerGroveKbBundle:Admin/Users:edit.html.twig', array(
            'document'    => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ), new Response('', 400));
    }

    /**
     * Deletes a User document.
     *
     * @Route("/{username}/delete", name="sgkb_admin_users_delete")
     * @Method("post")
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     */
    public function deleteAction(User $user)
    {
        $form    = $this->createDeleteForm($user);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $dm = $this->getDocumentManager();
            $dm->remove($user);
            $dm->flush();
        }

        return $this->redirect($this->generateUrl('sgkb_admin_users_index'));
    }

    /**
     * @Route("/{username}/password/edit", name="sgkb_admin_users_password_edit")
     * @Method("get")
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     * @Template()
     */
    public function passwordEditAction(User $user)
    {
        $form = $this->createForm(new UserPasswordType(), $user);

        return array('form' => $form->createView(), 'document' => $user);
    }

    /**
     * @Route("/{username}/password/update", name="sgkb_admin_users_password_update")
     * @Method("post")
     * @ParamConverter("user", class="ServerGroveKbBundle:User")
     */
    public function passwordUpdateAction(User $user)
    {
        $form    = $this->createForm(new UserPasswordType(), $user);
        $request = $this->getRequest();

        $form->bind($request);

        if ($form->isValid()) {
            $user->setPassword($this->getEncodedPassword($user, $form->get('password')->getData()));

            $dm = $this->getDocumentManager();
            $dm->persist($user);
            $dm->flush($user);

            return $this->redirect($this->generateUrl('sgkb_admin_users_password_edit', array('username' => $user->getUsername())));
        }

        return $this->render('ServerGroveKbBundle:Admin/Users:passwordEdit.html.twig', array(
            'form'     => $form->createView(),
            'document' => $user
        ));
    }

    /**
     * @param \ServerGrove\KbBundle\Document\User $user
     * @param string                              $password
     *
     * @return string
     */
    private function getEncodedPassword(User $user, $password)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);

        return $encoder->encodePassword($password, $user->getSalt());
    }

    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder(array('username' => $user->getUsername()))
            ->add('username', 'hidden')
            ->getForm();
    }
}
