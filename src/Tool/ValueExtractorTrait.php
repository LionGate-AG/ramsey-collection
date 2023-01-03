<?php

/**
 * This file is part of the ramsey/collection library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey <ben@benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

declare(strict_types=1);

namespace Ramsey\Collection\Tool;

use Ramsey\Collection\Exception\InvalidPropertyOrMethod;
use Ramsey\Collection\Exception\UnsupportedOperationException;
use Throwable;

use function is_array;
use function is_object;
use function method_exists;
use function sprintf;

/**
 * Provides functionality to extract the value of a property or method from an object.
 */
trait ValueExtractorTrait
{
    /**
     * Extracts the value of the given property, method, or array key from the
     * element.
     *
     * If `$propertyOrMethod` is `null`, we return the element as-is.
     *
     * @param mixed $element The element to extract the value from.
     * @param string | null $propertyOrMethod The property or method for which the
     *     value should be extracted.
     *
     * @return mixed the value extracted from the specified property, method,
     *     or array key, or the element itself.
     *
     * @throws InvalidPropertyOrMethod
     * @throws UnsupportedOperationException
     */
    protected function extractValue(mixed $element, ?string $propertyOrMethod): mixed
    {
        if ($propertyOrMethod === null) {
            return $element;
        }

        if (!is_object($element) && !is_array($element)) {
            throw new UnsupportedOperationException(sprintf(
                'The collection type "%s" does not support the $propertyOrMethod parameter',
                $this->getType(),
            ));
        }

        if (is_array($element)) {
            return $element[$propertyOrMethod] ?? throw new InvalidPropertyOrMethod(sprintf(
                'Key or index "%s" not found in collection elements',
                $propertyOrMethod,
            ));
        }

        /**
         * Access property of collected class directly or
         * trigger calls to getter/accessor and fail silently
         * so we can continue checking for a method.
         */
        // phpcs:disable
        try {
            return $element->$propertyOrMethod;
            // @phpstan-ignore-next-line
        } catch (Throwable $e) {
            // @ignoreException
        }
        // phpcs:enable

        // @phpstan-ignore-next-line
        if (method_exists($element, $propertyOrMethod)) {
            return $element->{$propertyOrMethod}();
        }

        throw new InvalidPropertyOrMethod(sprintf(
            'Method or property "%s" not defined in %s',
            $propertyOrMethod,
            $element::class,
        ));
    }
}
