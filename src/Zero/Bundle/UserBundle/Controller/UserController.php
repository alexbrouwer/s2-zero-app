<?php


namespace Zero\Bundle\UserBundle\Controller;

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zero\Bundle\ApiBundle\Controller\BaseController;
use Zero\Bundle\UserBundle\Entity\User;
use Zero\Bundle\UserBundle\Service\UserManager;

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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 200 = "Returned when successful"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 200 = "Returned when successful",
     * 404 = "Returned when the user is not found"
     * }
     * )
     *
     * @param int $userId The userId of the user
     *
     * @return array
     */
    public function getAction($userId)
    {
        return array('user' => $this->getOr404($userId));
    }

    /**
     * Create user
     *
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * input = "Zero\Bundle\UserBundle\Form\UserType",
     * statusCodes = {
     * 201 = "Returned when created",
     * 400 = "Returned when validation errors occurred"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * input = "Zero\Bundle\UserBundle\Form\UserType",
     * statusCodes = {
     * 204 = "Returned when successful",
     * 400 = "Returned when validation errors occurred",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * input = "Zero\Bundle\UserBundle\Form\UserType",
     * statusCodes = {
     * 204 = "Returned when successful",
     * 400 = "Returned when validation errors occurred",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 204 = "Returned when deleted",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 200 = "Returned when successful",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 200 = "Returned when successful",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * statusCodes = {
     * 200 = "Returned when successful",
     * 404 = "Returned when the user is not found"
     * }
     * )
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
