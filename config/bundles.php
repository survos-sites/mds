<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\UX\StimulusBundle\StimulusBundle::class => ['all' => true],
    Symfony\UX\Turbo\TurboBundle::class => ['all' => true],
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
];
