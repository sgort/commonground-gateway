<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class                      => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class                                => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class                        => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class                       => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class           => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class                                  => ['all' => true],
    ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle::class            => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class                              => ['dev' => true],
    Knp\Bundle\MarkdownBundle\KnpMarkdownBundle::class                         => ['all' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class               => ['dev' => true, 'test' => true],
    Conduction\CommonGroundBundle\CommonGroundBundle::class                    => ['all' => true],
    Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle::class                       => ['all' => true],
    Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle::class => ['all' => true],
    Hautelook\AliceBundle\HautelookAliceBundle::class                          => ['all' => true],
    LarpingBase\LarpingBundle\LarpingBundle::class                             => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class                  => ['all' => true],
    Setono\CronExpressionBundle\SetonoCronExpressionBundle::class              => ['all' => true],
    CommonGateway\CoreBundle\CoreBundle::class                                 => ['all' => true],
    Endroid\QrCodeBundle\EndroidQrCodeBundle::class                            => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class                          => ['all' => true],
    League\FlysystemBundle\FlysystemBundle::class                              => ['all' => true],
];
