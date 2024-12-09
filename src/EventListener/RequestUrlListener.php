<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class RequestUrlListener
{
    private LoggerInterface $logger;
    private RouterInterface $router;

    public function __construct(LoggerInterface $logger, RouterInterface $router)
    {
        $this->logger = $logger;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $clientIp = $request->getClientIp();
        $remoteIp = $request->headers->get('X-Forwarded-For');

        // Récupère l'URL complète
        $currentUrl = $request->getUri();

        // Récupère uniquement le chemin (sans le domaine)
        $currentPath = $request->getPathInfo();



        $this->logger->info("# Requested Url", ['clientIp' => $clientIp, 'remoteIp' => $remoteIp ,'url' => $currentUrl]);
        //$this->logger->info("# Url", ['url' => $currentPath]);

        /**
         * Les URL contenant ce genre de point pur changer de repertoire
         * Conduise à une vulnérabilité d'attaque par le hacker
         */
        if (str_contains($currentUrl, "..")) {
            //$this->logger->info("# Auto redirected to home", ['badUrl' => $currentUrl]);

            $homepageUrl = $this->router->generate('app_base_home');

            $response = new RedirectResponse($homepageUrl);
            $event->setResponse($response);
        }
        
    }
}