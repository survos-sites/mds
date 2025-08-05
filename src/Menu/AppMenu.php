<?php

namespace App\Menu;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Entity\MuseumObject;
use App\Entity\Record;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Survos\BootstrapBundle\Event\KnpMenuEvent;
use Survos\BootstrapBundle\Service\MenuService;
use Survos\BootstrapBundle\Traits\KnpMenuHelperInterface;
use Survos\BootstrapBundle\Traits\KnpMenuHelperTrait;
use Survos\CoreBundle\Service\SurvosUtils;
use Survos\MeiliBundle\Service\MeiliService;
use Survos\WorkflowBundle\Service\WorkflowHelperService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zenstruck\Bytes;

// events are
/*
// #[AsEventListener(event: KnpMenuEvent::NAVBAR_MENU2)]
#[AsEventListener(event: KnpMenuEvent::SIDEBAR_MENU, method: 'sidebarMenu')]
#[AsEventListener(event: KnpMenuEvent::PAGE_MENU, method: 'pageMenu')]
#[AsEventListener(event: KnpMenuEvent::FOOTER_MENU, method: 'footerMenu')]
#[AsEventListener(event: KnpMenuEvent::AUTH_MENU, method: 'appAuthMenu')]
*/

final class AppMenu implements KnpMenuHelperInterface
{
    use KnpMenuHelperTrait;

    public function __construct(
        #[Autowire('%kernel.environment%')] protected string $env,
        private MenuService                                  $menuService,
        private Security                                     $security,
        private EntityManagerInterface                       $entityManager,
        private readonly MeiliService                        $meiliService, private readonly WorkflowHelperService $workflowHelperService,
        private ?AuthorizationCheckerInterface               $authorizationChecker = null,
    )
    {
    }

    public function appAuthMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $this->menuService->addAuthMenu($menu);
    }

    private function getApproxCount(string $class): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT reltuples::BIGINT FROM pg_class WHERE relname = :table',
            ['table' => $this->getTableName($class)]
        );
    }

    private function getTableName(string $entityClass): string
    {
        return $this->entityManager->getClassMetadata($entityClass)->getTableName();
    }

    #[AsEventListener(event: KnpMenuEvent::NAVBAR_MENU)]
    public function navbarMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $options = $event->getOptions();
        $this->add($menu, 'app_homepage');
        if ($this->isEnv('dev')) {
            $this->add($menu, 'zenstruck_messenger_monitor_dashboard', label: "*msg");
        }
        $this->add($menu, 'survos_workflow_entities', label: "*entities");
        $this->add($menu, 'admin', label: "ez");


//        $subMenu = $this->addSubmenu($menu, 'meili_insta');

//        foreach ($this->meiliService->)
            foreach ($this->meiliService->indexedEntities as $class) {
//                $indexName = $this->meiliService->getPrefixedIndexName($class);
                $shortClass = new \ReflectionClass($class)->getShortName();
            $this->add($menu, 'meili_insta', ['indexName' => $class],
                badge: SurvosUtils::formatLargeNumber($this->workflowHelperService->getApproxCount($class)),
                label: $shortClass);
        }


//        foreach ($this->workflowHelperService->getWorkflowsGroupedByClass() as $class=>$wf) {
////
//////        }
//////        foreach ([Extract::class, Record::class, Source::class, MuseumObject::class] as $class) {
////            $repo = $this->entityManager->getRepository($class);
////            $counts[$class] = $this->workflowHelperService->getApproxCount($class);
////
////            foreach ([MuseumObject::class => 'obj', Source::class => 'source', Grp::class => 'grp', Extract::class => 'extract'] as $class => $code) {
//////            dd($this->getApproxCount($class));
//////            $repo = $this->entityManager->getRepository($class);
//        }
//
//        foreach ($this->meiliService->indexedEntities as $class) {
//            dd($class, $this->meiliService->getPrefixedIndexName($class));
//        }
//
        $this->add($menu, 'api_entrypoint', label: 'API', external: true);

        if ($this->isEnv('dev')) {
            $this->add($menu, 'survos_command', [
                'commandName' => 'load:Grp'],
                label: "load:Grp",
            );


            $subMenu = $this->addSubmenu($menu, "Workflows");
            foreach ([Record::class => 'record',
                         MuseumObject::class => 'obj',
                         Source::class => 'source',
                         Extract::class => 'extract'] as $class => $code) {
                $shortName = new \ReflectionClass($class)->getShortName();
                $this->add($subMenu, 'survos_command', [
                    'className' => addslashes($class),
                    'commandName' => 'survos:workflow:generate'],
                    label: "make workflow " . $class);
                $this->add($subMenu, 'survos_command', [
                    'commandName' => 'workflow:iterate',
                    'className' => addslashes($class),
                    'transport' => 'sync'
                ], label: "iterate " . $shortName);
            }
            $subMenu = $this->addSubmenu($menu, "Existing Workflows");
            $this->add($subMenu, 'survos_workflows');
        }

        //        $this->add($menu, 'app_homepage');
        // for nested menus, don't add a route, just a label, then use it for the argument to addMenuItem

        $nestedMenu = $this->addSubmenu($menu, 'Credits');
        foreach (['bundles', 'javascript'] as $type) {
            // $this->addMenuItem($nestedMenu, ['route' => 'survos_base_credits', 'rp' => ['type' => $type], 'label' => ucfirst($type)]);
        }
    }
}
