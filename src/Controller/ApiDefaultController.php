<?php

namespace App\Controller;

use Symfony\Component\Uid\Uuid;
use App\Repository\AccountRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiDefaultController extends AbstractController
{
    #[Route('/api/default', name: 'app_api_default', methods: ["GET"])]
    public function index(Request $request, SerializerInterface $serializer, AccountRepository $accountRepository): Response
    {

        $users = $accountRepository->findAll();
        dd($users);
        if ('application/xml' === $request->headers->get('Accept')) {

            $formats = 'xlm';
            $contentType = 'application/xml';

        } else {

            $formats = 'json';
            $contentType = 'application/json';

        }

        return  new  Response(
            $serializer->serialize(['message' => 'Test', 'UID-v1' => Uuid::v1(), 'UIDv1' => UuidV1::v1(), 'UID-v4' => Uuid::v4(), 'UIDv4' => UuidV4::v4(), 'UID-v6' => Uuid::v6(), 'UIDv6' => UuidV6::v4(), $accountRepository->findAll()], $formats),
            Response::HTTP_OK,
            ['Content-Type' => $contentType]

            // $serializer->serialize(['message' => 'Test'], $formats),
            // Response::HTTP_OK,
            // ['Content-Type' => $contentType]

        );
      
    }
}
