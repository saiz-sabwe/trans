<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class AuthenticationSuccessListener
{
    private RoleHierarchyInterface $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        // Récupère tous les rôles étendus (hiérarchiques) de l'utilisateur
        $allRoles = $this->roleHierarchy->getReachableRoleNames($user->getRoles());

        // Vérifie si l'utilisateur a accès via ROLE_INTEGRATOR ou ses supérieurs
        if (
            !in_array('ROLE_COLLECTOR', $allRoles, true) &&
            !in_array('ROLE_VERIFICATOR', $allRoles, true)
        ) {
            $response = new JsonResponse(
                ['code' => '403', 'message' => "Accès refusé : Vous n'avez pas le rôle nécessaire pour vous connecter."],
                403
            );
            $response->send();

            exit;
        }

        // Ajout des données utilisateur si tout est OK
        if (method_exists($user, 'getId')) {
            $data['data'] = [
                'id' => $user->getId(),
                'roles' => $user->getRoles(),
                'username' => $user->getUserIdentifier(),
            ];

            $event->setData($data);
        }

    }
}
