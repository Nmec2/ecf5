<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Child;
use App\Entity\Calendar;
use App\Entity\Presence;


use Symfony\Component\Routing\Annotation\Route;
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
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $date = $request->query->get('date');

        if ($date) {
            $date = new \DateTimeImmutable($date);
        } else {
            $date = new \DateTimeImmutable();
        }

        $currentWeek = (int) $date->format('W');
        $currentYear = (int) $date->format('Y');

        $startOfWeek = (new \DateTimeImmutable())->setISODate($currentYear, $currentWeek, 0);
        $endOfWeek = (new \DateTimeImmutable())->setISODate($currentYear, $currentWeek, 7)->setTime(23, 59, 59);

        $previousWeekStartDate = $startOfWeek->modify('-7 days');
        $nextWeekStartDate = $startOfWeek->modify('+8 days');

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

        $user = $this->getUser();

        if($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')){
            $children = $this->entityManager->getRepository(Child::class)
                ->createQueryBuilder('c')
                ->join('c.Responsables', 'r')
                ->where('r.id = :userId')
                ->setParameter('userId', $user->getId())
                ->getQuery()
                ->getResult();
        } else {
            $children = $this->entityManager->getRepository(Child::class)->findAll();
        }

        $presences = $this->entityManager->getRepository(Presence::class)
            ->createQueryBuilder('p')
            ->join('p.child', 'child')
            ->join('p.calendar', 'calendar')
            ->where('calendar.date BETWEEN :start AND :end')
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->getQuery()
            ->getResult();

        $presenceMap = [];
        foreach ($presences as $presence){
            $presenceMap[$presence->getChild()->getId()][$presence->getCalendar()->getId()] = $presence->isPresent();
        }

        $dayCalendar = [];
        foreach ($currentDayWeek as $day) {
            $dayCalendar[] = [
                'id' => $day->getId(),
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
            'children' => $children,
            'presenceMap' => $presenceMap,
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'previousWeekStartDate' => $previousWeekStartDate,
            'nextWeekStartDate' => $nextWeekStartDate,
        ]);
    }

    #[Route('/admin/presence/update', name: 'admin_presence_update', methods: ['POST'])]
    public function updatePresences(Request $request): Response
    {
        $presences = $request->request->all()['presences'] ?? [];

        foreach ($presences as $childId => $days) {
            $child = $this->entityManager->getRepository(Child::class)->find($childId);
            
            foreach ($days as $calendarId => $isPresent) {
                $calendar = $this->entityManager->getRepository(Calendar::class)->find($calendarId);
                
                $presence = $this->entityManager->getRepository(Presence::class)->findOneBy([
                    'child' => $child,
                    'calendar' => $calendar
                ]);

                if (!$presence) {
                    $presence = new Presence();
                    $presence->setChild($child);
                    $presence->setCalendar($calendar);
                    $this->entityManager->persist($presence);
                }

                $presence->setPresent(true);
            }
        }

        $this->entityManager->flush();
        
        $this->addFlash('success', 'Les présences ont été mises à jour');
        return $this->redirectToRoute('admin');
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
        yield MenuItem::linkToCrud('Enfants', 'fas fa-child', Child::class);
        if($this->isGranted('ROLE_ADMIN')){
            yield MenuItem::linkToCrud('Responsables', 'fas fa-user-tie', User::class)
            ->setController(ResponsableCrudController::class);
            yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
            yield MenuItem::linkToCrud('Calendrier', 'fas fa-calendar-days', Calendar::class);
        }
    }

    

    // public function configureAssets(): Assets 
    // {
    //     return Assets::new()
    //         ->addJsFile('build/app.js')
    //         ;
    // }
}


