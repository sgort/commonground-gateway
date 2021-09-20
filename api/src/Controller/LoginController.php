<?php

// src/Controller/LoginController.php

namespace App\Controller;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
Use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class LoginController.
 *
 *
 * @Route("/")
 */
class LoginController extends AbstractController
{

    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Route("/me")
     */
    public function MeAction(Request $request, CommonGroundService $commonGroundService)
    {

        if ($this->getUser()) {
            $result = [
                'first_name' => $this->getUser()->getFirstName(),
                'last_name' => $this->getUser()->getLastName(),
                'name' => $this->getUser()->getName(),
                'email' => $this->getUser()->getEmail(),
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


}