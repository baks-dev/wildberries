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

namespace BaksDev\Wildberries\Repository\AllWbToken;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\Active\WbTokenActive;
use BaksDev\Wildberries\Entity\Event\Card\WbTokenCard;
use BaksDev\Wildberries\Entity\Event\Modify\WbTokenModify;
use BaksDev\Wildberries\Entity\Event\Orders\WbTokenOrders;
use BaksDev\Wildberries\Entity\Event\Profile\WbTokenProfile;
use BaksDev\Wildberries\Entity\Event\Sales\WbTokenSales;
use BaksDev\Wildberries\Entity\Event\Stocks\WbTokenStocks;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Yandex\Market\Entity\Event\Card\YaMarketTokenCard;
use BaksDev\Yandex\Market\Entity\Event\Stocks\YaMarketTokenStocks;

final class AllWbTokenRepository implements AllWbTokenInterface
{
    private ?SearchDTO $search = null;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage
    ) {}


    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    /**
     * Profile
     */
    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод возвращает пагинатор WbToken
     */
    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('token.id')
            ->addSelect('token.event')
            ->from(WbToken::class, 'token');


        /** Если не админ - только токен профиля */


        $dbal
            ->join(
                'token',
                WbTokenProfile::class,
                'profile',
                'profile.event = token.event AND profile.value = :profile',
            )
            ->setParameter(
                'profile',
                $this->profile instanceof UserProfileUid ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                UserProfileUid::TYPE,
            );


        $dbal
            ->addSelect('modify.mod_date AS modify')
            ->join(
                'token',
                WbTokenModify::class,
                'modify',
                'modify.event = token.event',
            );


        $dbal
            ->addSelect('active.value AS active')
            ->join(
                'token',
                WbTokenActive::class,
                'active',
                'active.event = token.event',
            );


        $dbal
            ->addSelect('card.value AS card')
            ->leftJoin(
                'token',
                WbTokenCard::class,
                'card',
                'card.event = token.event',
            );

        $dbal
            ->addSelect('stocks.value AS stocks')
            ->leftJoin(
                'token',
                WbTokenStocks::class,
                'stocks',
                'stocks.event = token.event',
            );

        $dbal
            ->addSelect('orders.value AS orders')
            ->leftJoin(
                'token',
                WbTokenOrders::class,
                'orders',
                'orders.event = token.event',
            );

        $dbal
            ->addSelect('sales.value AS sales')
            ->leftJoin(
                'token',
                WbTokenSales::class,
                'sales',
                'sales.event = token.event',
            );

        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal
            ->addSelect('users_profile.event as users_profile_event')
            ->leftJoin(
                'token',
                UserProfile::class,
                'users_profile',
                'users_profile.id = profile.value',
            );


        // Info
        $dbal
            ->addSelect('users_profile_info.status as users_profile_status')
            ->leftJoin(
                'token',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = token.id',
            );

        // Event
        $dbal->leftJoin(
            'users_profile',
            UserProfileEvent::class,
            'users_profile_event',
            'users_profile_event.id = users_profile.event',
        );


        // Personal
        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile_event',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile_event.id',
            );

        // Avatar

        $dbal->addSelect("
            CASE 
            WHEN users_profile_avatar.name IS NOT NULL 
            THEN CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name) 
            ELSE NULL 
            END AS users_profile_avatar
        ");

        $dbal
            ->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext")
            ->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn')
            ->leftJoin(
                'users_profile_event',
                UserProfileAvatar::class,
                'users_profile_avatar',
                'users_profile_avatar.event = users_profile_event.id',
            );

        /** ACCOUNT */

        $dbal->leftJoin(
            'users_profile_info',
            Account::class,
            'account',
            'account.id = users_profile_info.usr',
        );

        $dbal
            ->addSelect('account_event.email AS account_email')
            ->leftJoin(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event AND account_event.account = account.id',
            );

        $dbal
            ->addSelect('account_status.status as account_status')
            ->leftJoin(
                'account_event',
                AccountStatus::class,
                'account_status',
                'account_status.event = account_event.id',
            );

        /* Поиск */
        if($this->search instanceof SearchDTO)
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('token.id')
                ->addSearchEqualUid('token.event')
                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username');
        }

        return $this->paginator->fetchAllHydrate($dbal, WbTokenPaginatorResult::class);

    }
}
