<?php

namespace App\Controller;

use App\Service\EnginService;
use App\Service\ExceptionService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiEnginController extends AbstractController
{
    private ExceptionService $exceptionService;
    private EnginService $enginService;

    public function __construct(ExceptionService $exceptionService, EnginService $enginService)
    {
        $this->exceptionService = $exceptionService;
        $this->enginService = $enginService;
    }

    #[Route('/api/engin/create', name: 'app_api_engin_get_create', methods: ['POST'])]
    public function apiCreate(Request $request): JsonResponse
    {
        try
        {
            $engin = $this->enginService->create($request->toArray());

            return $this->json($engin, Response::HTTP_OK, [], [
                'groups' => ['api', 'secret']
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/engin/token/{registration}', name: 'app_api_engin_get_token_by_registration', methods: ['GET'])]
    public function apiGetTokenByRegistration(string $registration): JsonResponse
    {
        try
        {
            $token = $this->enginService->getCipherRegistration($registration);

            return $this->json([
                'token' => $token
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/engin/find-by-registration/{registration}/{withCipher}', name: 'app_api_engin_find_by_registration', requirements: ['withCipher' => "0|1"], defaults: ['withCipher' => '0'], methods: ['GET'])]
    public function apiFindOneByRegistration(string $registration, bool $withCipher = false): JsonResponse
    {
        try
        {
            $engin = $this->enginService->findOneByRegistration($registration, $withCipher);

            $groups = $withCipher ? ['api', 'secret'] : ['api'];

            return $this->json($engin, Response::HTTP_OK, [], [
                'groups' => $groups
            ]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(['message' => $exception['message']], $exception['code']);
        }
    }


}
