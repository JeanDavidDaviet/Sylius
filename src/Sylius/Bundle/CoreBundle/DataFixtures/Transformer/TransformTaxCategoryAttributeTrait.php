<?php

declare(strict_types=1);

namespace Sylius\Bundle\CoreBundle\DataFixtures\Transformer;

use Sylius\Bundle\CoreBundle\DataFixtures\Event\FindOrCreateResourceEvent;
use Sylius\Bundle\CoreBundle\DataFixtures\Factory\TaxCategoryFactoryInterface;

trait TransformTaxCategoryAttributeTrait
{
    private TaxCategoryFactoryInterface $taxCategoryFactory;

    private function transformTaxCategoryAttribute(array $attributes, string $attributeKey = 'tax_category'): array
    {
        if (\is_string($attributes[$attributeKey])) {
            /** @var FindOrCreateResourceEvent $event */
            $event = $this->eventDispatcher->dispatch(
                new FindOrCreateResourceEvent(TaxCategoryFactoryInterface::class, ['code' => $attributes[$attributeKey]])
            );

            $attributes[$attributeKey] = $event->getResource();
        }

        return $attributes;
    }
}