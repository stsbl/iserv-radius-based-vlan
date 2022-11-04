<?php

declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @Annotation
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class IpRange extends Constraint
{
    public const V4 = '4';
    public const V6 = '6';
    public const ALL = 'all';

    /**
     * @var string|null
     */
    protected ?string $message;

    /**
     * @var string
     */
    protected string $version = self::ALL;

    /**
     * @var string[]
     */
    private static array $versions = [
        self::V4,
        self::V6,
        self::ALL,
    ];

    public function __construct($options = null)
    {
        parent::__construct($options);

        if (!\in_array($this->version, self::$versions)) {
            throw new ConstraintDefinitionException(sprintf('The option "version" must be one of "%s"', implode('", "', self::$versions)));
        }
    }

    public function getMessage(): string
    {
        return $this->message ?? _('This is not a valid IP range.');
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
