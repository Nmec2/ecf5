<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Child;
use App\Entity\Calendar;
use App\Entity\Presence;

use Symfony\Component\Routing\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Controller\Admin\ResponsableCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function index(): Response
    {
        $currentWeek = (int) (new \DateTimeImmutable())->format('W');
        $currentYear = (int) (new \DateTimeImmutable())->format('Y');

        $startOfWeek = (new \DateTimeImmutable())->setISODate($currentYear, $currentWeek, 0);
        $endOfWeek = (new \DateTimeImmutable())->setISODate($currentYear, $currentWeek, 7)->setTime(23, 59, 59);

        $currentDayWeek = $this->entityManager
            ->getRepository(Calendar::class)
            ->createQueryBuilder('c')
            ->where('c.date >= :startDate')
            ->andWhere('c.date <= :endDate')
            ->setParameter('startDate', $startOfWeek)
            ->setParameter('endDate', $endOfWeek)
            ->orderBy('c.date', 'ASC')
            ->getQuery()
            ->getResult();

        $dayCalendar = [];

        foreach ($currentDayWeek as $day) {

            $dayCalendar[] = [
                'date' => $day->getDate(),
                'jour' => $day->getDay(),
                'mois' => $day->getMois(),
                'isopen' => $day->isopen(),
                'formatted_date' => $day->getDate()->format('d/m/Y'),
                'day_number' => $day->getDate()->format('d'),
            ];
        }

        return $this->render('admin/main/index.html.twig', [
            'currentWeek' => $currentWeek,
            'currentYear' => $currentYear,
            'joursCalendrier' => $dayCalendar,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
        ]);
    }



    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/arbre.png" width="30px" height="30px"> LaCrèche.org')
            ->setFaviconPath('/images/favicon.ico')
            ->disableDarkMode()
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');
        yield MenuItem::linkToCrud('Enfants', 'fas fa-list', Child::class);
        yield MenuItem::linkToCrud('Responsables', 'fas fa-user-tie', User::class)
        ->setController(ResponsableCrudController::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Calendrier', 'fas fa-calendar-days', Calendar::class);
        
    }

    

    // public function configureAssets(): Assets 
    // {
    //     return Assets::new()
    //         ->addJsFile('build/app.js')
    //         ;
    // }
}
