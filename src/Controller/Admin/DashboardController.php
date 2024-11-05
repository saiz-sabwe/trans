<?php

namespace App\Controller\Admin;

use App\Entity\Agent;
use App\Entity\BusStop;
use App\Entity\Company;
use App\Entity\Engin;
use App\Entity\EnginAgent;
use App\Entity\EnginCategory;
use App\Entity\EnginItinerary;
use App\Entity\Itinerary;
use App\Entity\ItineraryPricing;
use App\Entity\Parking;
use App\Entity\SubscriptionCategory;
use App\Entity\SubscriptionPricing;
use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\WalletCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render("base/cpanel_dashboard.html.twig");
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MakutaTrans Cpanel');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Société', 'fas fa-university', Company::class);

        yield MenuItem::section('Gestion des trajets');
        yield MenuItem::linkToCrud('Trajets des véhicules', 'fa fa-road', EnginItinerary::class);
        yield MenuItem::linkToCrud('Prix des trajets', 'fa fa-money', ItineraryPricing::class);
        yield MenuItem::linkToCrud('Trajets', 'fas fa-road', Itinerary::class);
        yield MenuItem::linkToCrud('Arret Bus', 'fas fa-bus', BusStop::class);

        //yield MenuItem::section('Gestion des agents')->setPermission('ROLE_MANAGER');
        yield MenuItem::section('Gestion des agents');
        yield MenuItem::linkToCrud('Agent-Vehicule', 'fas fa-users', EnginAgent::class);
        yield MenuItem::linkToCrud('Agent', 'fas fa-user', Agent::class);

        yield MenuItem::section('Gestion Automobile');
        yield MenuItem::linkToCrud('Engin', 'fas fa-car', Engin::class);
        yield MenuItem::linkToCrud('Engin categories', 'fas fa-layer-group', EnginCategory::class);

        yield MenuItem::section("Gestion d'accès");
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);

        yield MenuItem::section("Gestion de Wallet");
        yield MenuItem::linkToCrud('Wallet', 'fas fa-credit-card', Wallet::class);

        yield MenuItem::section("Gestion des Parkings");
        yield MenuItem::linkToCrud('Parking', 'fas fa-parking', Parking::class);
        yield MenuItem::linkToCrud('Categorie Subscription', 'fas fa-parking', SubscriptionCategory::class);
        yield MenuItem::linkToCrud('Prix des parkings', 'fa fa-money', SubscriptionPricing::class);

    }
}
