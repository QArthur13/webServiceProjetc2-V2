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
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;

class ApiTokenController extends AbstractController
{
   /**
     * Cette fonction permet de savoir quel format Accept à choisi l'utilisateur
     *
     * @param string $mime Le format attendu
     * @return string Renvoie soit du XML ou du JSON
     */
    private function getFormats(string $mime): string
    {
        return 'application/xml' === $mime ? 'xml' : 'json';
    }

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

    #[Route('api/token/logout', name: 'app_api_logout', methods: ["GET"])]
    public function logout(): void
    {
    }
}
