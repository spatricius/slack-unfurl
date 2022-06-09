<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function build(ContainerBuilder $containerBuilder)
    {
        parent::build($containerBuilder);

        $childDefinition = $containerBuilder->registerForAutoconfiguration(
            '\App\Service\SlackRequestParser\SlackRequestParserInterface'
        );
        $childDefinition->addTag('app.slack.request.parser');

        $childDefinition = $containerBuilder->registerForAutoconfiguration(
            '\App\Service\GitlabTextResolver\SlackResponseRendererInterface'
        );
        $childDefinition->addTag('app.slack.response.renderer');
    }
}
