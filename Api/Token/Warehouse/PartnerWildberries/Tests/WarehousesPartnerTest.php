<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Orders\WildberriesOrdersSticker\WildberriesOrdersSticker;
use BaksDev\Wildberries\Api\Token\Supplies\SupplyInfo\WildberriesSupplyInfo;
use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\PartnerWarehouses;
use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\Warehouse;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries
 * @group wildberries-api
 */
#[When(env: 'test')]
final class WarehousesPartnerTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var PartnerWarehouses $PartnerWarehouses */
        $PartnerWarehouses = self::getContainer()->get(PartnerWarehouses::class);

        $Warehouses = $PartnerWarehouses
            ->profile(new UserProfileUid())
            ->warehouses();

        $Warehouse = $Warehouses->current();

       self::assertEquals(1, $Warehouse->getId());
       self::assertEquals("ул. Троицкая, Подольск, Московская обл.", $Warehouse->getName());
       self::assertEquals(15, $Warehouse->getWildberries());


        $Warehouses->next();
        $Warehouse = $Warehouses->current();

        self::assertEquals(2, $Warehouse->getId());
        self::assertEquals("ул. Троицкая, Химки, Московская обл.", $Warehouse->getName());
        self::assertEquals(16, $Warehouse->getWildberries());

    }
}