<?php

namespace App\Menu;

use App\Entity\Extract;
use App\Entity\Grp;
use App\Entity\Record;
use App\Entity\Source;
use Doctrine\ORM\EntityManagerInterface;
use Survos\BootstrapBundle\Event\KnpMenuEvent;
use Survos\BootstrapBundle\Service\MenuService;
use Survos\BootstrapBundle\Traits\KnpMenuHelperInterface;
use Survos\BootstrapBundle\Traits\KnpMenuHelperTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
        private ?AuthorizationCheckerInterface               $authorizationChecker = null
    )
    {
    }

    public function appAuthMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $this->menuService->addAuthMenu($menu);
    }

    #[AsEventListener(event: KnpMenuEvent::NAVBAR_MENU)]
    public function navbarMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        $options = $event->getOptions();
        $this->add($menu, 'app_homepage');

        foreach ([Record::class => 'record', Source::class => 'source', Grp::class => 'grp', Extract::class => 'extract'] as $class => $code) {
            $repo = $this->entityManager->getRepository($class);
            $this->add($menu, 'app_' . $code, label: $code, badge: $repo->count());
        }

        $this->add($menu, 'api_entrypoint');

        if ($this->isEnv('dev')) {

            $subMenu = $this->addSubmenu($menu, "Workflows");
            foreach ([Record::class => 'record', Source::class => 'source',
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
