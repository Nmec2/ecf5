<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ResponsableCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string 
    {
        return User::class;
    }

    
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        return $qb->andWhere('entity.roles NOT LIKE :role')
                ->setParameter('role', '%ROLE_ADMIN%')
                ->andWhere('entity.roles NOT LIKE :staff')
                ->setParameter('staff', '%ROLE_STAFF%');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Responsables')
            ->setPageTitle(Crud::PAGE_EDIT, fn (User $user) => 'Modifier ' . $user->getEmail())
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un nouveau responsable')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (User $user) => 'Détail : ' . $user->getEmail());
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('email'),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            TextField::new('phone', 'Téléphone'),
            TextField::new('link', 'Liens'),
        ];
    }
}