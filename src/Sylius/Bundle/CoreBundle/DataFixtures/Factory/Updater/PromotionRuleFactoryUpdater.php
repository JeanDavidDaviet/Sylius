<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\CoreBundle\DataFixtures\Factory\Updater;

use Sylius\Component\Promotion\Model\PromotionRuleInterface;

final class PromotionRuleFactoryUpdater implements PromotionRuleFactoryUpdaterInterface
{
    public function update(PromotionRuleInterface $promotionRule, array $attributes): void
    {
        $promotionRule->setType($attributes['type']);
        $promotionRule->setConfiguration($attributes['configuration']);
    }
}
