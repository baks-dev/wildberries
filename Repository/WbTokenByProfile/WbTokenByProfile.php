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

namespace BaksDev\Wildberries\Repository\WbTokenByProfile;


use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Wildberries\Entity\Cookie\WbTokenCookie;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationCookie;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class WbTokenByProfile implements WbTokenByProfileInterface
{

    private EntityManagerInterface $entityManager;

    private TokenStorageInterface $tokenStorage;


    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }


    /**
     * Токен авторизации Wildberries
     */
    public function getToken(UserProfileUid $profile): ?WbAuthorizationToken
    {
        $qb = $this->entityManager->createQueryBuilder();

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
        );

        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileUid::TYPE);

        // Кешируем результат ORM
        $cacheQueries = new ApcuAdapter('Wildberries');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);
        $query->setParameters($qb->getParameters());

        return $query->getOneOrNullResult();

    }


    /**
     * Cookie авторизации Wildberries
     */
    public function getTokenCookie(UserProfileUid $profile): ?WbAuthorizationCookie
    {
        $qb = $this->entityManager->createQueryBuilder();

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

        $qb->join(

            UserProfileInfo::class,
            'info',
            'WITH',
            'info.profile = token.id AND info.status = :status',
        );

        $qb->join(

            WbTokenCookie::class,
            'cookie',
            'WITH',
            'cookie.event = token.event',
        );

        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileUid::TYPE);

        // Кешируем результат ORM
        $cacheQueries = new ApcuAdapter('Wildberries');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);
        $query->setParameters($qb->getParameters());

        return $query->getOneOrNullResult();
    }


    /**
     * Текущий Активный профиль пользователя
     */
    public function getCurrentUserProfile(): ?UserProfileUid
    {
        $user = $this->tokenStorage->getToken()
            ?->getUser()
            ?->getId();

        if(!$user)
        {
            throw new InvalidArgumentException('Невозможно определить авторизованного пользователя');
        }

        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(profile.id)', UserProfileUid::class);
        $qb->select($select);

        $qb->from(UserProfileInfo::class, 'profile_info');
        $qb->where('profile_info.user = :user');

        $qb->andWhere('profile_info.active = true');
        $qb->andWhere('profile_info.status = :status');

        $qb->setParameter('user', $user, UserUid::TYPE);
        $qb->setParameter('status', new AccountStatus(AccountStatusEnum::ACTIVE), UserUid::TYPE);

        $qb->join(
            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = profile_info.profile',
        );


        // Кешируем результат ORM
        $cacheQueries = new ApcuAdapter('Wildberries');

        $query = $this->entityManager->createQuery($qb->getDQL());
        $query->setQueryCache($cacheQueries);
        $query->setResultCache($cacheQueries);
        $query->enableResultCache();
        $query->setLifetime(60 * 60 * 24);
        $query->setParameters($qb->getParameters());
        
        return $query->getOneOrNullResult();
        //return $query->getSingleResult();

    }

}