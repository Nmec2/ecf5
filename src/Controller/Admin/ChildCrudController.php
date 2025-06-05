<?php

namespace App\Controller\Admin;

use App\Entity\Child;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ChildCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Child::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom'),
            DateField::new('date_of_birth', 'Date de naissance'),
            TextAreaField::new('comment', 'Commentaire'),
            AssociationField::new('Responsables', 'Responsables')
                ->setFormTypeOption('by_reference', false)
                ->setQueryBuilder(function ($qb) {
                    return $qb->andWhere('entity.roles NOT LIKE :role')
                            ->setParameter('role', '%ROLE_ADMIN%')
                            ->andWhere('entity.roles NOT LIKE :staff')
                            ->setParameter('staff', '%ROLE_STAFF%');
                })
                ->formatValue(function ($value, $entity) {
                    if ($value instanceof \Traversable) {
                        $names = [];
                        foreach ($value as $user) {
                            $names[] = (string) $user; 
                        }
                        return implode(', ', $names);
                    }
                    return '';
                })
            ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Enfants')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier les informations')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un nouvel enfant')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de l\'enfant');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add('index', Action::new('addResponsable', 'Ajouter un responsable')
                ->linkToCrudAction('edit')
                ->setIcon('fa fa-user-plus')
        );
    }
}
