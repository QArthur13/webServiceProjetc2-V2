<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Token;
use App\Repository\TokenRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Decoder;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Serializer;

class ApiTokenController extends AbstractController
{
    /**
     * Permet de se connecter
     * @param Account|null $account
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    #[Route('/api/token', name: 'app_api_token', methods: ["POST"])]
    public function index(#[CurrentUser] ?Account $account, Request $request, ManagerRegistry $managerRegistry, SerializerInterface $serializer): Response
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

        $entityManager = $managerRegistry->getManager();
        $tokenDb = new Token();
        $configuration = Configuration::forSymmetricSigner(new Sha512(), InMemory::base64Encoded(base64_encode('test-45')));
        $date = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $token = $configuration
            ->builder()
            ->issuedBy('http://localhost:8000/api/token')
            ->permittedFor($account->getUserIdentifier())
            ->issuedAt($date)
            ->expiresAt($date->modify('+1 hour'))
            ->getToken($configuration->signer(), $configuration->signingKey())
        ;

        $refreshToken = $configuration
            ->builder()
            ->issuedBy('http://localhost:8000/api/token')
            ->permittedFor($account->getUserIdentifier())
            ->issuedAt($date)
            ->expiresAt($date->modify('+2 hour'))
            ->getToken($configuration->signer(), $configuration->signingKey())
        ;

        $tokenDb
            ->setAccessToken($token->toString())
            ->setAccessTokenExpiresAt($token->claims()->get('exp'))
            ->setRefreshToken($refreshToken->toString())
            ->setRefreshTokenExpiresAt($refreshToken->claims()->get('exp'))
        ;

        $entityManager->persist($tokenDb);
        $entityManager->flush();

        return new Response(

            $serializer->serialize([

                'accessToken' => $token->toString(),
                'accessTokenExpiresAt' => $token->claims()->get('exp'),
                'refreshToken' => $refreshToken->toString(),
                'refreshTokenExpiresAt' => $refreshToken->claims()->get('exp'),
                'User information' => $account->getUserIdentifier(),
                'User UID' => $account->getId()

            ], $formats),
            Response::HTTP_CREATED,
            ["Content-Type" => $contentType]

        );
    }

    /**
     * @param Request $request
     * @param string $accessToken
     * @param TokenRepository $tokenRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/api/validate/{accessToken}', name: 'api_validate_token', methods: ["GET"])]
    public function checkToken(Request $request, string $accessToken, TokenRepository $tokenRepository, SerializerInterface $serializer): Response
    {
        if ('application/xml' === $request->headers->get('Accept')) {

            $formats = 'xml';
            $contentType = 'application/xml';

        } else {

            $formats = 'json';
            $contentType = 'application/json';

        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $configuration = Configuration::forSymmetricSigner(new Sha512(), InMemory::base64Encoded(base64_encode('test-45')));
        $token = $tokenRepository->findBy(['accessToken' => $accessToken]);

        if (empty($token)) {

            return new Response(

                $serializer->serialize(['message' => 'Token inconnu!'], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        try {

            $parse = $configuration->parser()->parse($token[0]->getAccessToken());

        } catch (\Exception) {

            return new Response(

                $serializer->serialize(['message' => "Token Invalide ou Token changer!"], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        $dateNow = new \DateTimeImmutable();

        if ($token[0]->getAccessTokenExpiresAt() < $dateNow) {

            return new Response(

                $serializer->serialize(["message" => "Token expirer!"], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        return new Response(

            $serializer->serialize([

                'accessToken' => $parse->toString(),
                'accessTokenExpiresAt' => $token[0]->getAccessTokenExpiresAt()

            ], $formats),
            Response::HTTP_OK,
            ['Content-Type' => $contentType]

        );

    }

    /**
     * @param Request $request
     * @param string $refreshToken
     * @param TokenRepository $tokenRepository
     * @param ManagerRegistry $managerRegistry
     * @param Account|null $account
     * @param SerializerInterface $serializer
     * @return Response
     * @throws \Exception
     */
    #[Route('/api/refresh-token/{refreshToken}/token', name: 'api_refresh_token', methods: ["POST"])]
    public function refreshToken(Request $request, string $refreshToken, TokenRepository $tokenRepository, ManagerRegistry $managerRegistry, #[CurrentUser] ?Account $account, SerializerInterface $serializer): Response
    {
        if ('application/xml' === $request->headers->get('Accept')) {

            $formats = 'xml';
            $contentType = 'application/xml';

        } else {

            $formats = 'json';
            $contentType = 'application/json';

        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $configuration = Configuration::forSymmetricSigner(new Sha512(), InMemory::base64Encoded(base64_encode('test-45')));
        $entityManager = $managerRegistry->getManager();
        $token = $tokenRepository->findBy(['refreshToken' => $refreshToken]);

        if (empty($token)) {

            return new Response(

                $serializer->serialize(['message' => 'Token inconnu!'], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        $newDate = new \DateTimeImmutable('now', new \DateTimeZone("Europe/Paris"));


        if ($token[0]->getRefreshTokenExpiresAt() < $newDate) {

            return new Response(

                $serializer->serialize(['message' => 'Le refresh Token à expiré!'], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        $newToken = $configuration
            ->builder()
            ->issuedBy('http://localhost:8000/api/refresh-token/your-refresh-token/token')
            ->permittedFor($account->getUserIdentifier())
            ->issuedAt($newDate)
            ->expiresAt($newDate->modify('+1 hour'))
            ->getToken($configuration->signer(), $configuration->signingKey())
        ;
        $newRefreshToken = $configuration
            ->builder()
            ->issuedBy('http://localhost:8000/api/refresh-token/your-refresh-token/token')
            ->permittedFor($account->getUserIdentifier())
            ->issuedAt($newDate)
            ->expiresAt($newDate->modify('+2 hour'))
            ->getToken($configuration->signer(), $configuration->signingKey())
        ;

        $token[0]
            ->setAccessToken($newToken->toString())
            ->setAccessTokenExpiresAt($newToken->claims()->get('exp'))
            ->setRefreshToken($newRefreshToken->toString())
            ->setRefreshTokenExpiresAt($newRefreshToken->claims()->get('exp'))
        ;

        $entityManager->flush();

        return new Response(

            $serializer->serialize([

                'accessToken' => $newToken->toString(),
                'accessTokenExpiresAt' => $newToken->claims()->get('exp'),
                'refreshToken' => $newRefreshToken->toString(),
                'refreshTokenExpiresAt' => $newRefreshToken->claims()->get('exp'),
                'User information' => $account->getUserIdentifier(),


            ], $formats),
            Response::HTTP_OK,
            ['Content-Type' => $contentType]

        );
    }

    #[Route('api/token/logout', name: 'app_api_logout', methods: ["GET"])]
    public function logout(): void
    {
    }
}
