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

namespace BaksDev\Wildberries\Repository\WbTokenByProfile;

use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Repository\UserTokenStorage\UserTokenStorageInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Wildberries\Entity\Cookie\WbTokenCookie;
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
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class WbTokenByProfileRepository implements WbTokenByProfileInterface
{

    private UserProfileUid|false $profile = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProfile(UserProfileUid|UserProfile $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Возвращает токен авторизации Wildberries ID по профилю
     */
    public function getToken(): WbAuthorizationToken|false
    {
        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException('Invalid Argument UserProfileUid');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(WbToken::class, 'token')
            ->where('token.id = :profile')
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE,
            );

        $dbal->join(
            'token',
            WbTokenProfile::class,
            'wb_token_profile',
            'wb_token_profile.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenActive::class,
            'wb_token_active',
            'wb_token_active.event = token.event AND wb_token_active.value IS TRUE',
        );

        $dbal->join(
            'token',
            WbTokenValue::class,
            'wb_token_value',
            'wb_token_value.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenPercent::class,
            'wb_token_percent',
            'wb_token_percent.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenCard::class,
            'wb_token_card',
            'wb_token_card.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenStocks::class,
            'wb_token_stocks',
            'wb_token_stocks.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenOrders::class,
            'wb_token_orders',
            'wb_token_orders.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenSales::class,
            'wb_token_sales',
            'wb_token_sales.event = token.event',
        );

        $dbal->join(
            'token',
            WbTokenWarehouse::class,
            'wb_token_warehouse',
            'wb_token_warehouse.event = token.event',
        );


        $dbal->join(
            'token',
            UserProfileInfo::class,
            'info',
            'info.profile = token.id AND info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        //$select = sprintf('new %s(token.id, event.token, event.percent)', WbAuthorizationToken::class);
        //$dbal->select($select);

        $dbal
            ->addSelect('wb_token_profile.value AS profile')
            ->addSelect('wb_token_value.value AS token')
            ->addSelect('wb_token_card.value AS card')
            ->addSelect('wb_token_stocks.value AS stock')
            ->addSelect('wb_token_percent.value AS percent')
            ->addSelect('wb_token_warehouse.value AS warehouse')
            ->addSelect('wb_token_orders.value AS orders')
            ->addSelect('wb_token_sales.value AS sales');

        /* Кешируем результат ORM */
        return $dbal
            ->enableCache('wildberries', '1 day')
            ->fetchHydrate(WbAuthorizationToken::class);

    }

}
