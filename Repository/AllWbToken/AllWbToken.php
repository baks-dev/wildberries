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

namespace BaksDev\Wildberries\Repository\AllWbToken;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;

final class AllWbToken implements AllWbTokenInterface
{
    private PaginatorInterface $paginator;
    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        PaginatorInterface $paginator,
    )
    {

        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Метод возвращает пагинатор WbToken
     */
    public function fetchAllWbTokenAssociative(SearchDTO $search, ?UserProfileUid $profile): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

        $qb->select('token.id');
        $qb->addSelect('token.event');
        $qb->from(WbToken::TABLE, 'token');

        /** сли не админ - только токен профиля */
        if($profile)
        {
            $qb->where('token.id = :profile')
                ->setParameter('profile', $profile, UserProfileUid::TYPE)
            ;
        }


        $qb->addSelect('event.active');
        $qb->join('token', WbTokenEvent::TABLE, 'event', 'event.id = token.event AND event.profile = token.id');




        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $qb->addSelect('users_profile.event as users_profile_event');
        $qb->leftJoin(
            'token',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = token.id'
        );

        //dd($qb->fetchAllAssociative());


        // Info
        $qb->addSelect('users_profile_info.status as users_profile_status');
        $qb->leftJoin(
            'token',
            UserProfileInfo::TABLE,
            'users_profile_info',
            'users_profile_info.profile = token.id'
        );

        // Event
        $qb->leftJoin(
            'users_profile',
            UserProfileEvent::TABLE,
            'users_profile_event',
            'users_profile_event.id = users_profile.event'
        );


        // Personal
        $qb->addSelect('users_profile_personal.username AS users_profile_username');

        $qb->leftJoin(
            'users_profile_event',
            UserProfilePersonal::TABLE,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id'
        );

        // Avatar

        $qb->addSelect("CASE WHEN users_profile_avatar.name IS NOT NULL THEN CONCAT ( '/upload/".UserProfileAvatar::TABLE."' , '/', users_profile_avatar.name) ELSE NULL END AS users_profile_avatar");
        $qb->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext");
        $qb->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $qb->leftJoin(
            'users_profile_event',
            UserProfileAvatar::TABLE,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );

        /** ACCOUNT */

        $qb->leftJoin(
            'users_profile_info',
            Account::TABLE,
            'account',
            'account.id = users_profile_info.usr'
        );

        $qb->addSelect('account_event.email AS account_email');
        $qb->leftJoin(
            'account',
            AccountEvent::TABLE,
            'account_event',
            'account_event.id = account.event AND account_event.account = account.id'
        );

        $qb->addSelect('account_status.status as account_status');
        $qb->leftJoin(
            'account_event',
            AccountStatus::TABLE,
            'account_status',
            'account_status.event = account_event.id'
        );

        /* Поиск */
        if($search->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($search)

                ->addSearchEqualUid('token.id')
                ->addSearchEqualUid('token.event')

                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username')
            ;
        }

        return $this->paginator->fetchAllAssociative($qb);

    }
}
