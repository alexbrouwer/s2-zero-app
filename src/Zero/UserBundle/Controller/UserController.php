<?php


namespace Zero\UserBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zero\ApiBaseBundle\Controller\BaseController;
use Zero\ApiDocBundle\Annotation as ApiDoc;
use Zero\UserBundle\Entity\User;
use Zero\UserBundle\Service\UserManager;

class UserController extends BaseController implements ClassResourceInterface
{
    /**
     * @return UserManager
     */
    private function getManager()
    {
        return $this->container->get('zero_user.user.manager');
    }

    /**
     * Get users
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Output("array<Zero\UserBundle\Entity\User>")
     * @ApiDoc\StatusCode(200)
     *
     * @param Request $request The request object
     *
     * @return array
     */
    public function cgetAction(Request $request)
    {
        return array('users' => $this->getManager()->findBy(array()));
    }

    /**
     * Get user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Output("Zero\UserBundle\Entity\User")
     * @ApiDoc\StatusCode(200, description="Returned when successful")
     * @ApiDoc\StatusCode(404, description="Returned when user was not found")
     *
     * @param int $userId The userId of the user
     *
     * @return array
     */
    public function getAction($userId)
    {
        return $this->getOr404($userId);
    }

    /**
     * Create user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("Zero\UserBundle\Form\UserType")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(201, description="Returned when user created")
     * @ApiDoc\StatusCode(400, description="Returned when validation errors occurred")
     *
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function postAction(Request $request)
    {
        $newUser = $this->getManager()->create($request->request->all());

        $routeOptions = array(
            'userId'  => $newUser->getId(),
            '_format' => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_CREATED);
    }

    /**
     * Update user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("Zero\UserBundle\Form\UserType")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(204, description="Returned when user is updated")
     * @ApiDoc\StatusCode(400, description="Returned when validation errors occurred")
     * @ApiDoc\StatusCode(404, description="Returned when user was not found")
     *
     * @param int $userId The userId of the user
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function putAction($userId, Request $request)
    {
        $user = $this->getManager()->update(
            $this->getOr404($userId),
            $request->request->all()
        );

        $routeOptions = array(
            'userId'  => $user->getId(),
            '_format' => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Patch user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("Zero\UserBundle\Form\UserType")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(204, description="Returned when user is updated")
     * @ApiDoc\StatusCode(400, description="Returned when validation errors occurred")
     * @ApiDoc\StatusCode(404, description="Returned when user was not found")
     *
     * @param int $userId The userId of the user
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function patchAction($userId, Request $request)
    {
        $user = $this->getManager()->patch(
            $this->getOr404($userId),
            $request->request->all()
        );

        $routeOptions = array(
            'userId'  => $user->getId(),
            '_format' => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Delete user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("null")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(204, description="Returned when user is deleted")
     * @ApiDoc\StatusCode(404, description="Returned when user is not found")
     *
     * @param int $userId The userId of the user
     *
     * @return FormTypeInterface|View
     */
    public function deleteAction($userId)
    {
        $this->getManager()->delete(
            $this->getOr404($userId)
        );

        return View::create(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get groups for user
     *
     * @ApiDoc\Api
     * @ApiDoc\Resource
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("null")
     * @ApiDoc\Output("array<Zero\UserBundle\Entity\User\Group>")
     * @ApiDoc\StatusCode(200, description="Returned when successful")
     * @ApiDoc\StatusCode(404, description="Returned when user is not found")
     *
     * @param int $userId
     *
     * @return array
     */
    public function getGroupsAction($userId)
    {
        $user = $this->getOr404($userId);

        return array('groups' => $user->getGroups());
    }

    /**
     * Link user to group
     *
     * @ApiDoc\Api
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("null")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(200, description="Returned when successful")
     * @ApiDoc\StatusCode(404, description="Returned when user or group is not found")
     *
     * @param Request $request
     * @param int $userId The identity of the user
     * @param int $groupId The identity of the group
     *
     * @return array
     */
    public function linkGroupAction(Request $request, $userId, $groupId)
    {
        $user = $this->getOr404($userId);

        $groupManager = $this->container->get('zero_user.user.group.manager');
        $group        = $groupManager->get($groupId);
        if (!$group instanceof User\Group) {
            throw new NotFoundHttpException(sprintf("Group '%s' not found", $groupId));
        }

        $user->addGroup($group);
        $this->getManager()->saveEntity($user);

        $routeOptions = array(
            'userId'  => $user->getId(),
            '_format' => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Unlink user to group
     *
     * @ApiDoc\Api
     * @ApiDoc\Section("Users")
     * @ApiDoc\Input("null")
     * @ApiDoc\Output("null")
     * @ApiDoc\StatusCode(200, description="Returned when successful")
     * @ApiDoc\StatusCode(404, description="Returned when user or group is not found")
     *
     * @param Request $request
     * @param int $userId The identity of the user
     * @param int $groupId The identity of the group
     *
     * @return array
     */
    public function unlinkGroupAction(Request $request, $userId, $groupId)
    {
        $user = $this->getOr404($userId);

        $groupManager = $this->container->get('zero_user.user.group.manager');
        $group        = $groupManager->get($groupId);
        if (!$group instanceof User\Group) {
            throw new NotFoundHttpException(sprintf("Group '%s' not found", $groupId));
        }

        $user->removeGroup($group);
        $this->getManager()->saveEntity($user);

        $routeOptions = array(
            'userId'  => $user->getId(),
            '_format' => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get user
     *
     * @param int $id
     *
     * @return User
     *
     * @throws NotFoundHttpException
     */
    private function getOr404($id)
    {
        $user = $this->getManager()->get($id);
        if (!$user instanceof User) {
            throw new NotFoundHttpException(sprintf("User '%s' not found", $id));
        }

        return $user;
    }
} 
