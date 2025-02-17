<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Survos\Bundle\MakerBundle\SurvosMakerBundle::class => ['dev' => true, 'test' => true],
    Bizkit\VersioningBundle\BizkitVersioningBundle::class => ['all' => true],
    Survos\DeploymentBundle\SurvosDeploymentBundle::class => ['dev' => true],
    Survos\CoreBundle\SurvosCoreBundle::class => ['all' => true],
    Survos\WorkflowBundle\SurvosWorkflowBundle::class => ['all' => true],
    Symfony\UX\TwigComponent\TwigComponentBundle::class => ['all' => true],
    Survos\SimpleDatatables\SurvosSimpleDatatablesBundle::class => ['all' => true],
    Survos\CommandBundle\SurvosCommandBundle::class => ['all' => true],
    Knp\Bundle\TimeBundle\KnpTimeBundle::class => ['all' => true],
    Symfony\UX\Icons\UXIconsBundle::class => ['all' => true],
    Knp\Bundle\MenuBundle\KnpMenuBundle::class => ['all' => true],
    Survos\BootstrapBundle\SurvosBootstrapBundle::class => ['all' => true],
    Inspector\Symfony\Bundle\InspectorBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    ApiPlatform\Symfony\Bundle\ApiPlatformBundle::class => ['all' => true],
    Survos\CrawlerBundle\SurvosCrawlerBundle::class => ['all' => true],
    Pierstoval\SmokeTesting\SmokeTestingBundle::class => ['dev' => true, 'test' => true],
];
