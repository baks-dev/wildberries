<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Api\Token\Prices\PricesInfo\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCards\WildberriesCards;
use BaksDev\Wildberries\Api\Token\Prices\PricesInfo\PricesInfo;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group wildberries
 * @group wildberries-prices
 */
#[When(env: 'test')]
class WbPricesInfoTest extends KernelTestCase
{
    private static string $tocken;
    private static int $nomenclature;

    public static function setUpBeforeClass(): void
    {
        self::$tocken = $_SERVER['TEST_WB_TOCKEN'];
        self::$nomenclature = (int) $_SERVER['TEST_WB_NOMENCLATURE'];
    }

    public function testUseCase(): void
    {
        /** @var PricesInfo $PricesInfo */
        $PricesInfo = self::getContainer()->get(PricesInfo::class);

        $PricesInfo->TokenHttpClient(new WbAuthorizationToken(new UserProfileUid(), self::$tocken));

        $PricesInfo->prices(self::$nomenclature);
        $Price = $PricesInfo->getPriceByNomenclature(self::$nomenclature);

        self::assertNotNull($Price->getPrice());
        self::assertInstanceOf(Money::class, $Price->getPrice());
        self::assertIsFloat($Price->getPrice()->getValue());

        self::assertNotNull($Price->getDiscount());
        self::assertIsInt($Price->getDiscount());

    }

}