<?php

namespace App\Controller;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiAccountController extends AbstractController
{
    /**
     * @param string $mime
     * @return string
     */
    private function getFormats(string $mime): string
    {
        return 'application/xml' === $mime ? 'xml' : 'json';
    }

    /**
     * @param Request $request
     * @param AccountRepository $accountRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/api/account', name: 'app_api_account', methods: ["GET"])]
    public function index(Request $request, AccountRepository $accountRepository, SerializerInterface $serializer): Response
    {
        //On vérifie si la personne est authentifier et possède le droit admin
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seuls les personnes avec le role "admin" peuvent accéder à cette information!');

        $users = $accountRepository->findAll();
        $mime = $this->getFormats($request->headers->get('Accept'));

        return new Response($serializer->serialize($users, $mime, [DateTimeNormalizer::FORMAT_KEY => 'H:i:s d/m/Y']), Response::HTTP_OK, [

            'Content-Type' => ('xml' === $mime ? 'application/xml' : 'application/json')

        ]);
    }

    /**
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param UserPasswordHasherInterface $passwordHasher
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('api/account', name: 'app_api_create_user', methods: ["POST"])]
    public function create(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher, SerializerInterface $serializer): Response
    {
        //On vérifie si la personne est authentifier et possède le droit admin
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seuls les personnes avec le role "admin" peuvent accéder à cette information!');

        $mime = $this->getFormats($request->headers->get('Accept'));
        $entityManager = $managerRegistry->getManager();
        //On désérialise les données qu'on à reçu, puis on créer un nouveau utilisateur
        $requestData = $serializer->deserialize($request->getContent(), Account::class, $mime);

        $entityManager->persist($requestData);
        $entityManager->flush();

        return new Response($serializer->serialize($requestData, $mime), Response::HTTP_CREATED, [

            'Content-Type' => ('xml' === $mime ? 'application/xml' : 'application/json')

        ]);
    }

    #[Route('api/token/{id}', name: 'api_one_user', methods: ["GET"])]
    public function show(Request $request, AccountRepository $accountRepository, int $id, SerializerInterface $serializer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $mime = $this->getFormats($request->headers->get('Accept', 'application/json'));
        $user = $accountRepository->find($id);

        if (!($user->getId() === $this->getUser()->getId())) {

            //L'admin peut lui modifier ce qu'il veut, par contre
            if ($this->isGranted('ROLE_ADMIN')) {

                return new Response(

                    $serializer->serialize($user, $mime),
                    Response::HTTP_OK,
                    ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

                );

            }

        } else {

            //S'il s'agit bien du même ID, alors on affiche

            return new Response(

                $serializer->serialize($user, $mime),
                Response::HTTP_OK,
                ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

            );

        }


        return new Response(

            $serializer->serialize(["message" => "Vous n'êtes pas autoriser à faire ça!"], $mime),
            Response::HTTP_FORBIDDEN,
            ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

        );

    }

    /**
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param AccountRepository $accountRepository
     * @param int $id
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('api/account/{id}', name: 'api_update_user', methods: ["PUT"])]
    public function edit(Request $request, ManagerRegistry $managerRegistry, AccountRepository $accountRepository, int $id, SerializerInterface $serializer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $mime = $this->getFormats($request->headers->get('Accept', 'application/json'));
        $user = $accountRepository->find($id);
        $requestData = $serializer->deserialize($request->getContent(), Account::class, $mime, [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);
        $entityManager = $managerRegistry->getManager();

        //On test pour savoir s'il s'agit du même Id pour afficher
        if (!($user->getId() === $this->getUser()->getId())) {

            //L'admin peut lui modifier ce qu'il veut, par contre
            if ($this->isGranted('ROLE_ADMIN')) {

                $entityManager->flush();

                return new Response(

                    $serializer->serialize($user, $mime),
                    Response::HTTP_OK,
                    ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

                );

            }

        } else {

            $entityManager->flush();

            return new Response(

                $serializer->serialize($user, $mime),
                Response::HTTP_OK,
                ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

            );

        }

        return new Response(

            $serializer->serialize(["message" => "Vous n'êtes pas autoriser à faire ça!"], $mime),
            Response::HTTP_FORBIDDEN,
            ["Content-Type" => ("xml" === $mime ? "application/xml" : "application/json")]

        );
    }
}
