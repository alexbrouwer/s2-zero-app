<?php


namespace Zero\UserBundle\Controller;

use Zero\UserBundle\Model\UserInterface;
use Zero\UserBundle\Service\UserManager;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zero\ApiBaseBundle\Controller\BaseController;

class UserController extends BaseController implements ClassResourceInterface
{
    /**
     * @return UserManager
     */
    private function getUserManager()
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
        if($request->query->has('test')) {

        }
        return array('users' => $this->getUserManager()->findBy(array()));
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
     * @param string $username The username of the user
     *
     * @return array
     */
    public function getAction($username)
    {
        return array('user' => $this->getOr404($username));
    }

    /**
     * Create user
     *
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * input = "Zero\UserBundle\Form\UserType",
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
        $newUser = $this->getUserManager()->create($request->request->all());

        $routeOptions = array(
            'username' => $newUser->getUserName(),
            '_format'  => $request->get('_format')
        );

        return $this->routeRedirectView('api_get_user', $routeOptions, Codes::HTTP_CREATED);
    }

    /**
     * Update user
     *
     * @ApiDoc(
     * section="Users",
     * resource = true,
     * input = "Gearbox\SecurityBundle\Form\UserType",
     * statusCodes = {
     * 204 = "Returned when successful",
     * 400 = "Returned when validation errors occurred",
     * 404 = "Returned when the user is not found"
     * }
     * )
     *
     * @param string $username The username of the user
     * @param Request $request The request object
     *
     * @return FormTypeInterface|View
     */
    public function putAction($username, Request $request)
    {
        $user = $this->getUserManager()->update(
            $this->getOr404($username),
            $request->request->all()
        );

        $routeOptions = array(
            'username' => $user->getUserName(),
            '_format'  => $request->get('_format')
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
     * @param int $username The username of the user
     *
     * @return FormTypeInterface|View
     */
    public function deleteAction($username)
    {
        $this->getUserManager()->delete(
            $this->getOr404($username)
        );

        return View::create(null, Codes::HTTP_NO_CONTENT);
    }

    /**
     * Get user
     *
     * @param string $username
     *
     * @return UserInterface
     *
     * @throws NotFoundHttpException
     */
    private function getOr404($username)
    {
        $user = $this->getUserManager()->get($username);
        if (!$user instanceof UserInterface) {
            throw new NotFoundHttpException(sprintf('User "%s" not found', $username));
        }

        return $user;
    }
} 