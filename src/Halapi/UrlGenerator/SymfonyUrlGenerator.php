<?php

/*
 * This file is part of the Make.org project.
 *
 * (c) Make.org 2016
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        $this->generator->generate($name, $parameters, $referenceType);
    }
}
