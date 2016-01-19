<?php

namespace Biblioteca\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Biblioteca\UserBundle\Entity\usuario as User;
/**
 * ususario controller.
 *
 * @Route("/user")
 */
class usuarioController extends Controller
{

    /**
     * Lists all users.
     *
     * @Route("/", name="users")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        
        $users = $userManager->findUsers();
        /* fomulario para filtrar usuarios    
        $form = $this->createForm(new searchType(), null, array(
            'action' => $this->generateUrl('teg_search'),
            'attr'   => array('class' => 'searchform')));
        */
        return (array(
            'users' => $users
        ));
    }

    /**
     * Encuentra y muestra un usuario.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="user_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->findUserBy(array('id'=>$id));
        if (!$user) {
            throw $this->createNotFoundException('El usuario No se pudo encontrar.');
        }
        /*if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }*/
        $operationForm = $this->createtolockForm($id);

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'operation_form' => $operationForm->createView())
        );
    }
    /**
     * Bloquear a user entity.
     *
     * @Route("/{id}", name="user_lock")
     * @Method("POST")
     * @Template("BibliotecaUserBundle:usuario:show.html.twig")
     */
    public function toLockAction(Request $request, $id)
    {
        $form = $this->createToLockForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            
            $user = $userManager->findUserBy(array('id'=>$id));
            if (!$user) {
                throw $this->createNotFoundException('Unable to find usuario.');
            }
            $user->setLocked(!$user->isLocked());
            if(!$user->isEnabled()){
                $user->setConfirmationToken(null);
                $user->setEnabled(true);
            }
            $userManager->updateUser($user);
            
        }
        return $this->redirect($this->generateUrl('user_show', array('id' => $id)));
        
    }
    /**
     * Creates a form to "bloaquear" a user entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createToLockForm($id)
    {
        $userManager = $this->get('fos_user.user_manager');
            
        $user = $userManager->findUserBy(array('id'=>$id));
        $form = $this->createFormBuilder(null, array('attr' => array('style' => 'display:initial;')))
            ->setAction($this->generateUrl('user_lock', array('id' => $id)))
            ->setMethod('POST')
            ->add('submit', 'submit')
            ->getForm()
        ;
        return $form;
    }

}