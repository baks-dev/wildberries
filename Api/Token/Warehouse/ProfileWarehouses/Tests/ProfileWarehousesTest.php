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

namespace BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Orders\WildberriesOrdersSticker\WildberriesOrdersSticker;
use BaksDev\Wildberries\Api\Token\Supplies\SupplyInfo\WildberriesSupplyInfo;
use BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses\ProfileWarehouseDTO;
use BaksDev\Wildberries\Api\Token\Warehouse\ProfileWarehouses\ProfileWarehousesClient;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries
 * @group wildberries-profile-warehouses
 */
#[When(env: 'test')]
final class ProfileWarehousesTest extends KernelTestCase
{
    private static $tocken;

    public static function setUpBeforeClass(): void
    {
        self::$tocken = $_SERVER['TEST_WB_TOCKEN'];
    }

    public function testUseCase(): void
    {
        /** @var ProfileWarehousesClient $WildberriesWarehouses */
        $WildberriesWarehouses = self::getContainer()->get(ProfileWarehousesClient::class);

        $WildberriesWarehouses->TokenHttpClient(new WbAuthorizationToken(new UserProfileUid(), self::$tocken));

        /** @var ProfileWarehouseDTO $Warehouse */
        $Warehouse = $WildberriesWarehouses->warehouses()->current();

        self::assertNotNull($Warehouse->getId());
        self::assertIsInt($Warehouse->getId());

        self::assertNotNull($Warehouse->getOffice());
        self::assertIsInt($Warehouse->getOffice());

        self::assertNotNull($Warehouse->getName());
        self::assertIsString($Warehouse->getName());

        self::assertNotNull($Warehouse->getCategory());
        self::assertContains($Warehouse->getCategory(), [1, 2, 3]);

        self::assertNotNull($Warehouse->getDelivery());
        self::assertContains($Warehouse->getDelivery(), [1, 2, 3]);

    }
}