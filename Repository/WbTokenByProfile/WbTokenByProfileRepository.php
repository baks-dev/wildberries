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

namespace BaksDev\Wildberries\Repository\WbTokenByProfile;

use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Wildberries\Entity\Cookie\WbTokenCookie;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationCookie;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class WbTokenByProfileRepository implements WbTokenByProfileInterface
{
    private TokenStorageInterface $tokenStorage;

    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    /**
     * Токен авторизации Wildberries
     */
    public function getToken(UserProfileUid $profile): ?WbAuthorizationToken
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(token.id, event.token)', WbAuthorizationToken::class);
        $qb->select($select);

        $qb->from(WbToken::class, 'token');
        $qb->where('token.id = :profile');
        $qb->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            WbTokenEvent::class,
            'event',
            'WITH',
            'event.id = token.event AND event.active = true',
        );

        $qb->join(
            UserProfileInfo::class,
            'info',
            'WITH',
            'info.profile = token.id AND info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );


        /* Кешируем результат ORM */
        return $qb->enableCache('users-profile-group', 86400)->getOneOrNullResult();

    }


    /**
     * Cookie авторизации Wildberries
     */
    public function getTokenCookie(UserProfileUid $profile): ?WbAuthorizationCookie
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(token.id, cookie.identifier, cookie.token)', WbAuthorizationCookie::class);
        $qb->select($select);

        $qb->from(WbToken::class, 'token');
        $qb->where('token.id = :profile');
        $qb->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            WbTokenEvent::class,
            'event',
            'WITH',
            'event.id = token.event AND event.active = true',
        );

        $qb
            ->join(
                UserProfileInfo::class,
                'info',
                'WITH',
                'info.profile = token.id AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $qb->join(
            WbTokenCookie::class,
            'cookie',
            'WITH',
            'cookie.event = token.event',
        );


        /* Кешируем результат ORM */
        return $qb->enableCache('wildberries', 86400)->getOneOrNullResult();

    }


    /**
     * Текущий Активный профиль пользователя любой
     */
    public function getCurrentUserProfile(): ?UserProfileUid
    {
        /** @var UserUid $usr */
        $usr = $this->tokenStorage->getToken()
            ?->getUser()
            ?->getId();

        if(!$usr)
        {
            throw new InvalidArgumentException('Невозможно определить авторизованного пользователя');
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(profile.id)', UserProfileUid::class);
        $qb->select($select);

        $qb->from(UserProfileInfo::class, 'profile_info');
        $qb->where('profile_info.usr = :user');

        $qb->andWhere('profile_info.active = true');
        $qb->andWhere('profile_info.status = :status');

        $qb->setParameter('user', $usr, UserUid::TYPE);
        $qb->setParameter('status', new EmailStatus(EmailStatusActive::class), EmailStatus::TYPE);

        $qb->join(
            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = profile_info.profile',
        );

        /* Кешируем результат ORM */
        return $qb->enableCache('wildberries', 86400)->getOneOrNullResult();

    }

}
