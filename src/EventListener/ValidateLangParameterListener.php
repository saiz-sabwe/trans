<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class ValidateLangParameterListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private array $allowedLanguages = ['en', 'fr'];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Vérifier si le paramètre 'lang' est présent
        if ($request->query->has('lang')) {

            $lang = strtolower($request->query->get('lang'));

            $this->logger->info("# Passed lang ", ['lang' => $lang]);

            // Vérifier si 'lang' correspond aux valeurs autorisées
            if (!in_array($lang, $this->allowedLanguages)) {
                $this->logger->emergency("# Trying to pass unautorised lang. This Kind of information is an attack ", ['lang' => $lang]);
                // Retourner une réponse 400 Bad Request
                $response = new Response("Accès non autorisé.", Response::HTTP_BAD_REQUEST);
                $event->setResponse($response);
                return;
            }

            // Optionnel : Définir la locale de l'application en fonction de 'lang'
            $request->setLocale($lang);
        }
    }
}