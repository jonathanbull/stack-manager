<?php

/*
 * This file is part of the Stack Manager package.
 *
 * (c) Royal Opera House Covent Garden Foundation <website@roh.org.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ROH\Bundle\StackManagerBundle\Model;

use PHPUnit_Framework_Assert;

/**
 * Immutable model representing a CloudFormation stack.
 *
 * @author Robert Leverington <robert.leverington@roh.org.uk>
 */
class Stack
{
    /**
     * Tag to use in CloudFormation for the stack environment name.
     */
    const ENVIRONMENT_TAG = 'roh:stack-manager:environment';

    /**
     * Tag to use in CloudFormation for the stack template name.
     */
    const TEMPLATE_TAG = 'roh:stack-manager:template';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var Parameters
     */
    protected $parameters;

    public function __construct(
        $name,
        $environment,
        Template $template,
        Parameters $parameters
    ) {
        PHPUnit_Framework_Assert::assertInternalType(
            'string', $name,
            'Stack name must be a string'
        );
        PHPUnit_Framework_Assert::assertInternalType(
            'string', $environment,
            'Stack environment must be a string'
        );
        PHPUnit_Framework_Assert::assertGreaterThan(
            0, strlen($name),
            'Stack name must be at least one character long'
        );
        PHPUnit_Framework_Assert::assertLessThanOrEqual(
            255, strlen($name),
            'Stack name must be no more than 255 characters in length'
        );
        PHPUnit_Framework_Assert::assertRegExp(
            '#[a-zA-Z][-a-zA-Z0-9]*#', $name,
            'Stack name must begin with a letter and contain only alphanumeric characters and dashes'
        );

        $this->name = $name;
        $this->environment = $environment;
        $this->template = $template;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return Template
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return Tags
     */
    public function getTags()
    {
        return new Tags([
            self::ENVIRONMENT_TAG => $this->getEnvironment(),
            self::TEMPLATE_TAG => $this->getTemplate()->getName(),
        ]);
    }
}
