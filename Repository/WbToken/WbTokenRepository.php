<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Repository\WbToken;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Wildberries\Entity\Event\Active\WbTokenActive;
use BaksDev\Wildberries\Entity\Event\Card\WbTokenCard;
use BaksDev\Wildberries\Entity\Event\Orders\WbTokenOrders;
use BaksDev\Wildberries\Entity\Event\Percent\WbTokenPercent;
use BaksDev\Wildberries\Entity\Event\Profile\WbTokenProfile;
use BaksDev\Wildberries\Entity\Event\Sales\WbTokenSales;
use BaksDev\Wildberries\Entity\Event\Stocks\WbTokenStocks;
use BaksDev\Wildberries\Entity\Event\Token\WbTokenValue;
use BaksDev\Wildberries\Entity\Event\Warehouse\WbTokenWarehouse;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use InvalidArgumentException;


final class WbTokenRepository implements WbTokenInterface
{
    private WbTokenUid|WbToken|false $identifier = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forTokenIdentifier(WbToken|WbTokenUid|null|false $identifier): self
    {
        if(empty($identifier))
        {
            $this->identifier = false;
            return $this;
        }

        if($identifier instanceof WbToken)
        {
            $identifier = $identifier->getId();
        }

        $this->identifier = $identifier;

        return $this;
    }

    /** Метод возвращает токен авторизации по идентификатору */
    public function find(): WbAuthorizationToken|false
    {
        if(false === ($this->identifier instanceof WbTokenUid))
        {
            throw new InvalidArgumentException('Invalid Argument WbTokenUid');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(WbToken::class, 'wb_token')
            ->where('wb_token.id = :identifier')
            ->setParameter(
                key: 'identifier',
                value: $this->identifier,
                type: WbTokenUid::TYPE,
            );

        $dbal
            ->join(
                'wb_token',
                WbTokenProfile::class,
                'wb_token_profile',
                'wb_token_profile.event = wb_token.event',
            );


        $dbal
            ->join(
                'wb_token',
                WbTokenActive::class,
                'wb_token_active',
                'wb_token_active.event = wb_token.event AND wb_token_active.value IS TRUE',
            );

        $dbal
            ->join(
                'wb_token_profile',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = wb_token_profile.value AND users_profile_info.status = :status',
            )
            ->setParameter(
                key: 'status',
                value: UserProfileStatusActive::class,
                type: UserProfileStatus::TYPE,
            );


        $dbal
            ->join(
                'wb_token',
                WbTokenValue::class,
                'wb_token_value',
                'wb_token_value.event = wb_token.event',
            );


        $dbal
            ->join(
                'wb_token',
                WbTokenPercent::class,
                'wb_token_percent',
                'wb_token_percent.event = wb_token.event',
            );

        $dbal
            ->join(
                'wb_token',
                WbTokenWarehouse::class,
                'wb_token_warehouse',
                'wb_token_warehouse.event = wb_token.event',
            );


        $dbal
            ->leftJoin(
                'wb_token',
                WbTokenCard::class,
                'wb_token_card',
                'wb_token_card.event = wb_token.event',
            );

        $dbal
            ->leftJoin(
                'wb_token',
                WbTokenStocks::class,
                'wb_token_stocks',
                'wb_token_stocks.event = wb_token.event',
            );

        $dbal
            ->leftJoin(
                'wb_token',
                WbTokenOrders::class,
                'wb_token_orders',
                'wb_token_orders.event = wb_token.event',
            );
        $dbal
            ->leftJoin(
                'wb_token',
                WbTokenSales::class,
                'wb_token_sales',
                'wb_token_sales.event = wb_token.event',
            );


        $dbal
            ->select('wb_token_profile.value AS profile')
            ->addSelect('wb_token_value.value AS token')
            ->addSelect('wb_token_warehouse.value AS warehouse')
            ->addSelect('wb_token_percent.value AS percent')
            ->addSelect('wb_token_card.value AS card')
            ->addSelect('wb_token_stocks.value AS stock')
            ->addSelect('wb_token_orders.value AS orders')
            ->addSelect('wb_token_sales.value AS sales');

        return $dbal
            ->enableCache('wildberries')
            ->fetchHydrate(WbAuthorizationToken::class);

    }
}