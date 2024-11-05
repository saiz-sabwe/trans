<?php

namespace App\Controller;

use App\Service\CardAllocationService;
use App\Service\ExceptionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiCardAllocationController extends AbstractController
{
    private ExceptionService $exceptionService;
    private CardAllocationService $cardAllocationService;

    public function __construct(ExceptionService $exceptionService, CardAllocationService $cardAllocationService)
    {
        $this->exceptionService = $exceptionService;
        $this->cardAllocationService = $cardAllocationService;
    }

    #[Route('/api/card-allocation/allocate', name: 'app_card_allocation_allocate', methods: ['POST'])]
    public function apiAllocate(Request $request): JsonResponse
    {
        try {
            $cardNumberCypher = $this->cardAllocationService->allocate($request->toArray());

            return $this->json(['cardNumberCypher' => $cardNumberCypher]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(["message" => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/card-allocation/allocate/number', name: 'app_card_allocation_allocate_number', methods: ['POST'])]
    public function apiAllocateNumber(Request $request): JsonResponse
    {
        try {
            $cardNumber = $this->cardAllocationService->allocate($request->toArray(),false);

            return $this->json(['cardNumber' => $cardNumber]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(["message" => $exception['message']], $exception['code']);
        }
    }

    #[Route('/api/card-allocation/allocate/registration', name: 'app_card_allocation_allocate_registration', methods: ['POST'])]
    public function apiAllocateRegistration(Request $request): JsonResponse
    {
        try {
            $registration = $this->cardAllocationService->allocateRegistration($request->toArray());

            return $this->json(['registraion' => $registration]);
        } catch (\Exception $e) {
            $exception = $this->exceptionService->getException($e);
            return $this->json(["message" => $exception['message']], $exception['code']);
        }
    }
}
