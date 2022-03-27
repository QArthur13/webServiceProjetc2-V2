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
     * Récupère tous les utilisateurs
     *
     * @param Request $request
     * @param AccountRepository $accountRepository
     * @param SerializerInterface $serializer
     * @return Response Renvoie la liste des utilisateurs sois en XML ou en JSON
     */
    #[Route('/api/account', name: 'app_api_account', methods: ["GET"])]
    public function index(Request $request, AccountRepository $accountRepository, SerializerInterface $serializer): Response
    {
        //On vérifie si la personne est authentifié et possède le droit admin
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Seuls les personnes avec le role "admin" peuvent accéder à cette information!');

        /**
         * @var AccountRepository $users
         * Récupère tous les utilisateurs
         */
        $users = $accountRepository->findAll();
        //On récupère le format choisi
        $mime = $this->getFormats($request->headers->get('Accept'));

        return new Response($serializer->serialize($users, $mime, [DateTimeNormalizer::FORMAT_KEY => 'H:i:s d/m/Y']), Response::HTTP_OK, [

            'Content-Type' => ('xml' === $mime ? 'application/xml' : 'application/json')

        ]);
    }

    /**
     * Création d'un utilisateur
     *
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
        $requestData = $serializer->deserialize($request->getContent(), Account::class, 'json');

        //On hash le mot de passe et on rajoute la date de création et de mise à jour à l'utilisateur créer
        $requestData->setPassword($passwordHasher->hashPassword(new Account(), $requestData->getPassword()));
        $requestData->setCreatedAt(new \DateTime());
        $requestData->setUpdatedAt(new \DateTime());

        //On envoie les nouvelles données à la BDD
        $entityManager->persist($requestData);
        $entityManager->flush();

        return new Response(

            $serializer->serialize($requestData, $mime),
            Response::HTTP_CREATED,
            ['Content-Type' => ('xml' === $mime ? 'application/xml' : 'application/json')]

        );
    }

    /**
     * Récupère un utilisateur
     *
     * @param Request $request
     * @param AccountRepository $accountRepository
     * @param int $id
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('api/account/{id}', name: 'api_one_user', methods: ["GET"])]
    public function show(Request $request, AccountRepository $accountRepository, int $id, SerializerInterface $serializer): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $mime = $this->getFormats($request->headers->get('Accept', 'application/json'));
        //On récupère un utilisateur
        $user = $accountRepository->find($id);

        //S'il n'y a pas d'utilisateur alors on renvoie une erreur
        if (empty($user)) {

            return new Response(

                $serializer->serialize(["message" => "Pas d'utilisateur"], $mime),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => ($mime === 'xml' ? 'application/xml' : 'application/json')]

            );

        }

        //Si l'id n'est pas le même que celui de l'utilisateur connecter, alors on regarde s'il s'agit de l'admin
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
     * Mets à jour un utilisateur
     *
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

        $mime = $this->getFormats($request->headers->get('Accept'));
        $entityManager = $managerRegistry->getManager();
        $user = $accountRepository->find($id);

        //Le quatrième paramètre permet de mettre à jour l'utilisateur actuel
        $requestData = $serializer->deserialize($request->getContent(), Account::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $user]);

        //On met à jour l'heure de modification
        $requestData->setUpdatedAt(new \DateTime());

        //Voir plus haut pour l'explication du test
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
