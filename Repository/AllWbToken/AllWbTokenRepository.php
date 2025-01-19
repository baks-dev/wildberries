<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

final class AllWbTokenRepository implements AllWbTokenInterface
{
    private ?SearchDTO $search = null;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
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

        $dbal->select('token.id');
        $dbal->addSelect('token.event');
        $dbal->from(WbToken::class, 'token');

        /** Если не админ - только токен профиля */

        if($this->profile)
        {
            $dbal->where('token.id = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }


        $dbal->addSelect('event.active');
        $dbal->join(
            'token',
            WbTokenEvent::class,
            'event',
            'event.id = token.event AND event.profile = token.id'
        );


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal->addSelect('users_profile.event as users_profile_event');
        $dbal->leftJoin(
            'token',
            UserProfile::class,
            'users_profile',
            'users_profile.id = token.id'
        );


        // Info
        $dbal->addSelect('users_profile_info.status as users_profile_status');
        $dbal->leftJoin(
            'token',
            UserProfileInfo::class,
            'users_profile_info',
            'users_profile_info.profile = token.id'
        );

        // Event
        $dbal->leftJoin(
            'users_profile',
            UserProfileEvent::class,
            'users_profile_event',
            'users_profile_event.id = users_profile.event'
        );


        // Personal
        $dbal->addSelect('users_profile_personal.username AS users_profile_username');

        $dbal->leftJoin(
            'users_profile_event',
            UserProfilePersonal::class,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id'
        );

        // Avatar

        $dbal->addSelect("
            CASE 
            WHEN users_profile_avatar.name IS NOT NULL 
            THEN CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name) 
            ELSE NULL 
            END AS users_profile_avatar
        ");

        $dbal->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext");
        $dbal->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $dbal->leftJoin(
            'users_profile_event',
            UserProfileAvatar::class,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );

        /** ACCOUNT */

        $dbal->leftJoin(
            'users_profile_info',
            Account::class,
            'account',
            'account.id = users_profile_info.usr'
        );

        $dbal->addSelect('account_event.email AS account_email');
        $dbal->leftJoin(
            'account',
            AccountEvent::class,
            'account_event',
            'account_event.id = account.event AND account_event.account = account.id'
        );

        $dbal->addSelect('account_status.status as account_status');
        $dbal->leftJoin(
            'account_event',
            AccountStatus::class,
            'account_status',
            'account_status.event = account_event.id'
        );

        /* Поиск */
        if($this->search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($search)
                ->addSearchEqualUid('token.id')
                ->addSearchEqualUid('token.event')
                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username');
        }

        return $this->paginator->fetchAllAssociative($dbal);

    }
}
