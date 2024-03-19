<?php

namespace Codeception\Step\Decorator;

use Codeception\Lib\ModuleContainer;
use Codeception\Util\Template;

class AsJson extends \Codeception\Step\AsJson
{
    public function run(ModuleContainer $container = null)
    {
        $container->getModule('REST')->haveHttpHeader('Accept', 'application/json');

        return parent::run($container);
    }

    public static function getTemplate(Template $template): ?Template
    {
        $template = parent::getTemplate($template);

        if (!$template) {
            return null;
        }

        return $template
            ->place('step', 'Decorator\\AsJson');
    }
}
