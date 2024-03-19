<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class ExtendedStep extends CodeceptionStep
{
    public function getArgumentsAsString(int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $result = parent::getArgumentsAsString($maxLength);

        return str_replace(',', ', ', $result);
    }

    protected function stringifyArgument(mixed $argument): string
    {
        if (is_string($argument) && preg_match('/App\\\Entity\\\[a-z\\\]+/i', $argument))
        {
            $reflection = new \ReflectionClass($argument);
            return $reflection->getShortName();
        }

        return parent::stringifyArgument($argument);
    }

    protected function getClassName(object $argument): string
    {
        if ($argument instanceof \BackedEnum) {
            return $this->enumToString($argument);
        }

        if ($this->isAppEntity($argument)) {
            return $this->entityToString($argument);
        }

        return parent::getClassName($argument);
    }

    protected function enumToString(\BackedEnum $enum): string
    {
        $reflection = new \ReflectionClass($enum);
        return $reflection->getShortName() . '::' . $enum->name;
    }

    protected function isAppEntity(object $argument): bool
    {
        return str_starts_with(get_class($argument), 'App\\Entity\\');
    }

    protected function entityToString(object $argument): string
    {
        $reflection = new \ReflectionClass($argument);
        return $reflection->getShortName();
    }
}

class Action extends ExtendedStep
{
}

class Condition extends \Codeception\Step\ExtendedStep
{
}

class Assertion extends ExtendedStep
{
    protected function entityToString(object $argument): string
    {
        // can add entity details as id, email, etc.

        return parent::entityToString($argument);
    }
}

class Comment extends ExtendedStep
{
    /**
     * CODE BELOW IS A COPY OF THE ORIGINAL CODE
     * @see vendor/codeception/codeception/src/Codeception/Step/Comment.php
     */

    public function __toString(): string
    {
        return $this->getAction();
    }

    public function toString(int $maxLength): string
    {
        return mb_strcut((string)$this, 0, $maxLength, 'utf-8');
    }

    public function getHtml(string $highlightColor = '#732E81'): string
    {
        return '<strong>' . $this->getAction() . '</strong>';
    }

    public function getPhpCode(int $maxLength): string
    {
        return '// ' . $this->getAction();
    }

    public function run(ModuleContainer $container = null): void
    {
        // don't do anything, let's rest
    }

    public function getPrefix(): string
    {
        return '';
    }
}
