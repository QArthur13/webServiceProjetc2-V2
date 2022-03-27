<?php

namespace App\Controller;

use App\Entity\Account;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

class ApiTokenController extends AbstractController
{
    /**
     * Permet de se connecter
     * @param Account|null $account
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/api/token', name: 'app_api_token', methods: ["POST"])]
    public function index(#[CurrentUser] ?Account $account, Request $request, SerializerInterface $serializer): Response
    {
        if ('application/xml' === $request->headers->get('Accept')) {

            $formats = 'xml';
            $contentType = 'application/xml';

        } else {

            $formats = 'json';
            $contentType = 'application/json';

        }

        if (null === $account) {

            return new Response(

                $serializer->serialize(["message" => "Inforamtion manquantes"], $formats),
                Response::HTTP_UNAUTHORIZED,
                ["Content-Type" => $contentType]

            );

        }

        $token = 'test-45';

        return new Response(

            $serializer->serialize([

                //"user" => $account->getUserIdentifier(),
                "user" => $account,
                "token" => $token

            ], $formats),
            Response::HTTP_OK,
            ["Content-Type" => $contentType]

        );
    }

    #[Route('api/token/logout', name: 'app_api_logout', methods: ["GET"])]
    public function logout(): void
    {
    }
}
