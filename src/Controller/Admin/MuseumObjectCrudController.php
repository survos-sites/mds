<?php

namespace App\Controller\Admin;

use App\Entity\MuseumObject;
use App\Workflow\IMuseumObjectWorkflow;
use App\Workflow\SourceWorkflowInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Workflow\WorkflowInterface;

class MuseumObjectCrudController extends CrudController
{

    public function __construct(
        #[Target(IMuseumObjectWorkflow::WORKFLOW_NAME)] protected WorkflowInterface $workflow
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return MuseumObject::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
