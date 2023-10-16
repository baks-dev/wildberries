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

namespace BaksDev\Wildberries\Api\Token\Card\WildberriesCardByArticle\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCardByArticle\CardByArticle;
use BaksDev\Wildberries\Api\Token\Card\WildberriesCardByArticle\WildberriesCardByArticle;
use BaksDev\Wildberries\Api\Token\Orders\WildberriesOrdersSticker\WildberriesOrdersSticker;
use BaksDev\Wildberries\Api\Token\Supplies\SupplyInfo\WildberriesSupplyInfo;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries
 * @group wildberries-api
 */
#[When(env: 'test')]
final class WildberriesCardByArticleTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);

        /** @var WildberriesCardByArticle $WildberriesCardByArticle */
        $WildberriesCardByArticle = self::getContainer()->get(WildberriesCardByArticle::class);

        $CardByArticle = $WildberriesCardByArticle
            ->profile(new UserProfileUid())
            ->addArticle('ARTICLE')
            ->findByArticle();

//        /** @var CardByArticle $card */
//        foreach($CardByArticle as $card)
//        {
//            dd($card);
//        }

//
//        self::assertEquals('WB-GI-7654321', $WildberriesSupplyInfoDTO->getIdentifier());
//        self::assertEquals('Тестовая поставка', $WildberriesSupplyInfoDTO->getName());
//        self::assertTrue($WildberriesSupplyInfoDTO->isCargo());
//        self::assertFalse($WildberriesSupplyInfoDTO->isDone());
//
//        $date = new \DateTimeImmutable('2022-05-04T07:56:29Z');
//        self::assertEquals($date, $WildberriesSupplyInfoDTO->getScan());
//        self::assertEquals($date, $WildberriesSupplyInfoDTO->getCreated());
//        self::assertEquals($date, $WildberriesSupplyInfoDTO->getClosed());

    }
}