<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Token;
use App\Repository\TokenRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use mysql_xdevapi\Exception;
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

        /*dump([

            "Token Header" => $token->headers()->toString(),
            "Token Data" => $token->claims()->toString(),
            "Token Signature" => $token->signature(),
            "Token Total" => $token->headers()->toString().'.'.$token->claims()->toString().'.'.$token->signature()->toString()

        ]);
        dd();*/

        $entityManager->persist($tokenDb);
        $entityManager->flush();

        return new Response(

            $serializer->serialize([

                'accessToken' => $token->toString(),
                'accessTokenExpiresAt' => $token->claims()->get('exp'),
                'refreshToken' => $refreshToken->toString(),
                'refreshTokenExpiresAt' => $refreshToken->claims()->get('exp')

            ], $formats),
            Response::HTTP_CREATED,
            ["Content-Type" => $contentType]

        );
    }

    /**
     * @throws \Exception
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

        $configuration = Configuration::forSymmetricSigner(new Sha512(), InMemory::base64Encoded(base64_encode('test-45')));
        $token = $tokenRepository->findBy(['accessToken' => $accessToken]);

        if (empty($token)) {

            return new Response(

                $serializer->serialize(["message" => "Token inconnue!"], $formats),
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => $contentType]

            );

        }

        $parse = $configuration->parser()->parse($token[0]->getAccessToken());
        dd();
    }

    #[Route('api/token/logout', name: 'app_api_logout', methods: ["GET"])]
    public function logout(): void
    {
    }
}
