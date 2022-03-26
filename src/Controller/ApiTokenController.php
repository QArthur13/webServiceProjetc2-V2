<?php

namespace App\Controller;

use App\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiTokenController extends AbstractController
{
    #[Route('/api/token', name: 'app_api_token', methods: ["POST"])]
    public function index(#[CurrentUser] ?Account $account): Response
    {
        if (null === $account) {

            return $this->json(['message' => 'information manquante'], Response::HTTP_UNAUTHORIZED);

        }

        $token = 'test-45';

        return $this->json(['user' => $account->getUserIdentifier(), 'token' => $token]);
    }
}
