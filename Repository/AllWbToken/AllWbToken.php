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
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\SwitcherInterface;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Groups\Group\Entity\Group;
use BaksDev\Users\Groups\Group\Entity\Trans\GroupTrans;
use BaksDev\Users\Groups\Users\Entity\CheckUsers;
use BaksDev\Users\Groups\Users\Entity\Event\CheckUsersEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllWbToken implements AllWbTokenInterface
{

    private Connection $connection;

    private PaginatorInterface $paginator;

    private SwitcherInterface $switcher;

    private TranslatorInterface $translator;


    public function __construct(
        Connection $connection,
        PaginatorInterface $paginator,
        SwitcherInterface $switcher,
        TranslatorInterface $translator
    )
    {
        $this->connection = $connection;
        $this->paginator = $paginator;
        $this->switcher = $switcher;
        $this->translator = $translator;
    }


    /**
     * Метод возвращает пагинатор WbToken
     */
    public function fetchAllWbTokenAssociative(SearchDTO $search): PaginatorInterface
    {
        $qb = $this->connection->createQueryBuilder();

        $local = new Locale($this->translator->getLocale());


        $qb->select('token.id');
        $qb->addSelect('token.event');
        $qb->from(WbToken::TABLE, 'token');

        $qb->addSelect('event.active');
        $qb->join('token', WbTokenEvent::TABLE, 'event', 'event.id = token.event AND event.profile = token.id');


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $qb->addSelect('users_profile.event as users_profile_event');
        $qb->join(
            'token',
            UserProfile::TABLE,
            'users_profile',
            'users_profile.id = token.id'
        );

        // Info
        $qb->addSelect('users_profile_info.status as users_profile_status');
        $qb->join(
            'token',
            UserProfileInfo::TABLE,
            'users_profile_info',
            'users_profile_info.profile = token.id'
        );

        // Event
        $qb->join(
            'users_profile',
            UserProfileEvent::TABLE,
            'users_profile_event',
            'users_profile_event.id = users_profile.event'
        );



        // Personal
        $qb->addSelect('users_profile_personal.username AS users_profile_username');

        $qb->join(
            'users_profile_event',
            UserProfilePersonal::TABLE,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id'
        );

        // Avatar

        $qb->addSelect("CONCAT ( '/upload/".UserProfileAvatar::TABLE."' , '/', users_profile_avatar.dir, '/', users_profile_avatar.name, '.') AS users_profile_avatar");
        $qb->addSelect("CASE WHEN users_profile_avatar.cdn THEN  CONCAT ( 'small.', users_profile_avatar.ext) ELSE users_profile_avatar.ext END AS users_profile_avatar_ext");
        $qb->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $qb->leftJoin(
            'users_profile_event',
            UserProfileAvatar::TABLE,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );

        // Группа

        $qb->join(
            'users_profile_info',
            CheckUsers::TABLE,
            'check_user',
            'check_user.user_id = users_profile_info.user_id'
        );

        $qb->join(
            'check_user',
            CheckUsersEvent::TABLE,
            'check_user_event',
            'check_user_event.id = check_user.event'
        );

        $qb->leftJoin(
            'check_user_event',
            Group::TABLE,
            'groups',
            'groups.id = check_user_event.group_id'
        );

        $qb->addSelect('groups_trans.name AS group_name'); // Название группы

        $qb->leftJoin(
            'groups',
            GroupTrans::TABLE,
            'groups_trans',
            'groups_trans.event = groups.event AND groups_trans.local = :local'
        );

        $qb->setParameter('local', $local, Locale::TYPE);



        /** ACCOUNT */

        $qb->join(
            'users_profile_info',
            Account::TABLE,
            'account',
            'account.id = users_profile_info.user_id'
        );

        $qb->addSelect('account_event.email AS account_email');
        $qb->join(
            'account',
            AccountEvent::TABLE,
            'account_event',
            'account_event.id = account.event AND account_event.account = account.id'
        );

        $qb->addSelect('account_status.status as account_status');
        $qb->join(
            'account_event',
            AccountStatus::TABLE,
            'account_status',
            'account_status.event = account_event.id'
        );

        /* Поиск */
        if($search->query)
        {
            $search->query = mb_strtolower($search->query);
            $searcher = $this->connection->createQueryBuilder();

            if($search->isUid())
            {


                $searcher->orWhere('token.id = :query');
                $searcher->orWhere('token.event = :query');

                $qb->setParameter('query', $search->query);
            }
            else
            {
                $searcher->orWhere('LOWER(account_event.email) LIKE :query');
                $searcher->orWhere('LOWER(account_event.email) LIKE :switcher');

                $searcher->orWhere('LOWER(users_profile_personal.username) LIKE :query');
                $searcher->orWhere('LOWER(users_profile_personal.username) LIKE :switcher');

                $qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
                $qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
            }
        }

        return $this->paginator->fetchAllAssociative($qb);
    }
}
