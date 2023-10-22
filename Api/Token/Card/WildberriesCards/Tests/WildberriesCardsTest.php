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

namespace BaksDev\Wildberries\Api\Token\Card\WildberriesCards\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCards\Card;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCards\WildberriesCards;
use BaksDev\Wildberries\Api\Token\Orders\WildberriesOrdersSticker\WildberriesOrdersSticker;
use BaksDev\Wildberries\Api\Token\Stocks\GetStocks\WildberriesStocks;
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
final class WildberriesCardsTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var WildberriesCards $WildberriesCards */
        $WildberriesCards = self::getContainer()->get(WildberriesCards::class);

        /** @var Card $Cards */
        $Cards =  $WildberriesCards
            ->profile(new UserProfileUid())
            ->limit(100)
            ->nomenclature(null)
            ->updated(null)
            ->findAll()
        ;


        /** @var Card $Card */
        foreach($Cards as $Card)
        {
            foreach($Card->getOffersCollection() as $barcode => $offer)
            {
                self::assertEquals('2038811140828', $barcode);

                if(!empty($offer))
                {
                    self::assertEquals('3XL', $offer);
                }
                else
                {
                    self::assertEquals('0', $offer);
                }
            }
        }

    }
}