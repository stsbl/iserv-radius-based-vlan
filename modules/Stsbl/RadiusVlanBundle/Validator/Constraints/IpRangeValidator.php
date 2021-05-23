<?php

declare(strict_types=1);

namespace Stsbl\RadiusVlanBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

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
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 */
final class IpRangeValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IpRange) {
            throw new UnexpectedValueException($constraint, IpRange::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!(bool)\preg_match('#^[^/]+/[^/]+$#', $value)) {
            $this->context->addViolation($constraint->getMessage());

            return;
        }

        [$ip, $cidr] = \explode('/', $value);

        $ipViolations = $this->context->getValidator()->validate($ip, new Ip(['version' => $constraint->getVersion()]));

        if ($ipViolations->count() > 0) {
            $this->context->addViolation($constraint->getMessage());

            return;
        }

        if ((bool)\preg_match('/^[0-9]{1,2}$/', $cidr)) {
            $cidr = (int)$cidr;
        } else {
            $this->context->addViolation($constraint->getMessage());

            return;
        }

        if (\filter_var($ip, FILTER_FLAG_IPV4) && !($cidr >= 0 && $cidr <= 32)) {
            $this->context->addViolation($constraint->getMessage());
        } elseif (\filter_var($ip, FILTER_FLAG_IPV6) && !($cidr >= 0 && $cidr <= 64)) {
            $this->context->addViolation($constraint->getMessage());
        }
    }
}
