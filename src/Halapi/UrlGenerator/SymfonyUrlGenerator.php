<?php

namespace Halapi\UrlGenerator;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface as SymfonyUrlGeneratorInterface;

/**
 * Class SymfonyUrlGenerator.
 *
 * @author Romain Richard
 */
class SymfonyUrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var SymfonyUrlGeneratorInterface
     */
    private $generator;

    /**
     * SymfonyUrlGenerator constructor.
     *
     * @param SymfonyUrlGeneratorInterface $generator
     */
    public function __construct(SymfonyUrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     * @return string
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->generator->generate($name, $parameters, $referenceType);
    }
}
