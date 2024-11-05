<?php

namespace App\Security;

use App\Service\ExceptionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private LoggerInterface $logger;
    private ExceptionService $exceptionService;

    public function __construct(LoggerInterface $logger,ExceptionService $exceptionService)
    {
        $this->logger = $logger;
        $this->exceptionService = $exceptionService;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        $this->logger->info("# ApiKeyAuthenticator > supports: Start");
        //throw new \RuntimeException("MAL...", Response::HTTP_UNAUTHORIZED);
        // "auth_token" is an example of a custom, non-standard HTTP header used in this application

        return true;

    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->info("# ApiKeyAuthenticator > authenticate: Start");

        //region Verify App-Token
        $this->logger->info("# ApiKeyAuthenticator > authenticate: Begin Verify App-Token");

        if (!$request->headers->has('App-Token')) {
            $this->logger->info("# ApiKeyAuthenticator > authenticate: App-Token non défini");
            throw new CustomUserMessageAuthenticationException('Accès non autorisé');
        }

        $appToken = trim($request->headers->get('App-Token')) ?: null;

        if (null === $appToken) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            $this->logger->info("# ApiKeyAuthenticator > authenticate: App-Token", ['appToken' => $appToken]);
            throw new CustomUserMessageAuthenticationException('Accès non autorisé');
        }
        $this->logger->info("# ApiKeyAuthenticator > authenticate: Begin Verify App-Token successfully");
        //endregion

        // implement your own logic to get the user identifier from `$apiToken`
        // e.g. by looking up a user in the database using its API key
        $userIdentifier = "243810666161";///** ... */;

        return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->info("# ApiKeyAuthenticator > onAuthenticationFailure: Start");
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}