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

namespace BaksDev\Wildberries\Api\Token\Warehouse\WarehousesWildberries\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Orders\WildberriesOrdersSticker\WildberriesOrdersSticker;
use BaksDev\Wildberries\Api\Token\Supplies\SupplyInfo\WildberriesSupplyInfo;
use BaksDev\Wildberries\Api\Token\Warehouse\WarehousesWildberries\WildberriesWarehousesClient;
use BaksDev\Wildberries\Api\Token\Warehouse\WarehousesWildberries\WildberriesWarehouseDTO;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries
 * @group wildberries-warehouses
 */
#[When(env: 'test')]
final class WarehousesWildberriesTest extends KernelTestCase
{
    private static $tocken;

    public static function setUpBeforeClass(): void
    {
        self::$tocken = $_SERVER['TEST_WB_TOCKEN'];
    }

    public function testUseCase(): void
    {
        /** @var WildberriesWarehousesClient $PartnerWarehouses */
        $WildberriesWarehousesClient = self::getContainer()->get(WildberriesWarehousesClient::class);

        $WildberriesWarehousesClient->TokenHttpClient(new WbAuthorizationToken(new UserProfileUid(), self::$tocken));


        $WildberriesWarehouse = $WildberriesWarehousesClient
            ->profile(new UserProfileUid())
            ->warehouses();

        /** @var WildberriesWarehouseDTO $WildberriesWarehouseDTO */
        $WildberriesWarehouseDTO = $WildberriesWarehouse->current();


        self::assertNotNull($WildberriesWarehouseDTO->getId());
        self::assertIsInt($WildberriesWarehouseDTO->getId());

        self::assertNotNull($WildberriesWarehouseDTO->getName());
        self::assertIsString($WildberriesWarehouseDTO->getName());

        self::assertNotNull($WildberriesWarehouseDTO->getCity());
        self::assertIsString($WildberriesWarehouseDTO->getCity());


        self::assertNotNull($WildberriesWarehouseDTO->getCargo());
        self::assertContains($WildberriesWarehouseDTO->getCargo(), [1, 2, 3]);

        self::assertNotNull($WildberriesWarehouseDTO->getDelivery());
        self::assertContains($WildberriesWarehouseDTO->getDelivery(), [1, 2, 3]);

    }
}