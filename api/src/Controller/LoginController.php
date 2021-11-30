<?php

// src/Controller/LoginController.php

namespace App\Controller;

use App\Service\EavService;
use App\Service\ResponseService;
use App\Service\UserService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\User;

/**
 * Class LoginController.
 *
 *
 * @Route("/")
 */
class LoginController extends AbstractController
{
    private CacheInterface $cache;
    private EntityManagerInterface $entityManager;

    public function __construct(CacheInterface $cache, EntityManagerInterface $entityManager)
    {
        $this->cache = $cache;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/me")
     * @Route("api/users/me", methods={"get"})
     */
    public function MeAction(Request $request, CommonGroundService $commonGroundService, ResponseService $responseService)
    {
        $userService = new UserService($commonGroundService, $this->entityManager, $responseService);
        if ($this->getUser()) {
            $result = [
                'id'         => $this->getUser()->getUserIdentifier(),
                'username'   => $this->getUser()->getUsername(),
                'roles'      => $this->getUser()->getRoles(),
                'first_name' => $this->getUser()->getFirstName(),
                'last_name'  => $this->getUser()->getLastName(),
                'name'       => $this->getUser()->getName(),
                'email'      => $this->getUser()->getEmail(),
                // TODO: if we have no person connected to this user create one? with $this->createPersonForUser()
                'person'        => $userService->getPersonForUser($this->getUser()),
                'organization'  => $userService->getOrganizationForUser($this->getUser()),
            ];
            $result = json_encode($result);
        } else {
            $result = null;
        }

        return new Response(
            $result,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    //TODO: ?
    /**
     * Creates a person for a user.
     *
     * @return array
     */
    private function createPersonForUser(CommonGroundService $commonGroundService): array
    {
        $person = [
            'givenName'     => $this->getUser()->getFirstName(),
            'familyName'    => $this->getUser()->getLastName(),
            'emails'        => [
                'name'  => 'email',
                'email' => $this->getUser()->getUsername(),
            ],
        ];

        //TODO: use commongroundService to create person?
        //TODO: update user object to connect person uri to the user?

        return $person;
    }
}
