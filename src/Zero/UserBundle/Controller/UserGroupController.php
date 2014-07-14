<?php


namespace Zero\UserBundle\Controller;

use Zero\UserBundle\Service\User\GroupManager;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zero\ApiBaseBundle\Controller\BaseController;
use Zero\UserBundle\Entity\User\Group;

class UserGroupController extends BaseController
{
    /**
     * @return GroupManager
     */
    private function getManager()
    {
        return $this->container->get('zero_user.user.group.manager');
    }

    /**
     * Get groups
     *
     * @param Request $request The request object
     *
     * @return array
     */
    public function cgetAction(Request $request)
    {
        return array('groups' => $this->getManager()->findBy(array()));
    }

    /**
     * Get group
     *
     * @param string $groupId The identity of the group
     *
     * @return array
     */
    public function getAction($groupId)
    {
        return array('group' => $this->getOr404($groupId));
    }

    /**
     * Create group
     *
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function postAction(Request $request)
    {
        $group = $this->getManager()->create($request->request->all());

        $routeOptions = array(
            'groupId' => $group->getId(),
            '_format'  => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_users_group', $routeOptions, Codes::HTTP_CREATED);
    }

    /**
     * Update group
     *
     * @param string $groupId The identity of the group
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function putAction($groupId, Request $request)
    {
        $group = $this->getManager()->update(
            $this->getOr404($groupId),
            $request->request->all()
        );

        $routeOptions = array(
            'groupId' => $group->getId(),
            '_format'  => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_users_group', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Patch group
     *
     * @param string $groupId The identity of the group
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function patchAction($groupId, Request $request)
    {
        $group = $this->getManager()->patch(
            $this->getOr404($groupId),
            $request->request->all()
        );

        $routeOptions = array(
            'groupId' => $group->getId(),
            '_format'  => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_users_group', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Delete group
     *
     * @param int $groupId The identity of the group
     *
     * @return FormTypeInterface|View
     */
    public function deleteAction($groupId)
    {
        $this->getManager()->delete(
            $this->getOr404($groupId)
        );

        return View::create(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get group
     *
     * @param string $id Name of the group
     *
     * @return Group
     *
     * @throws NotFoundHttpException
     */
    private function getOr404($id)
    {
        $group = $this->getManager()->get($id);
        if (!$group instanceof Group) {
            throw new NotFoundHttpException(sprintf("Group '%s' not found", $id));
        }

        return $group;
    }
} 