<?php

namespace App\Controller;

use App\Entity\Account;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ApiTokenController extends AbstractController
{



  /**
   * @param UserInterface $user
   * @param JWTTokenManagerInterface $JWTManager
   * @return JsonResponse
   */
  public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager)
  {
   return new JsonResponse([
     'token' => $JWTManager->create($user),
    ]);
  }
   
}
