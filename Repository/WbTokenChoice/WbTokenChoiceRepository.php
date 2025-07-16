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

namespace BaksDev\Wildberries\Repository\WbTokenChoice;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;

final class WbTokenChoiceRepository implements WbTokenChoiceInterface
{

    public function __construct(
        private readonly WbTokenByProfileInterface $wbTokenByProfile,
        private readonly ORMQueryBuilder $ORMQueryBuilder
    ) {}

    /**
     * Возвращает всю коллекцию идентификаторов токенов
     */
    public function getTokenCollection(): ?array
    {
        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(token.id, users_profile_personal.username)', UserProfileUid::class);
        $qb->select($select);

        $qb->from(WbToken::class, 'token');

        $qb->join(
            WbTokenEvent::class,
            'event',
            'WITH',
            'event.id = token.event AND event.profile = token.id',
        );

        $qb
            ->join(
                UserProfileInfo::class,
                'users_profile_info',
                'WITH',
                'users_profile_info.profile = token.id AND users_profile_info.status = :status',
            )
            ->setParameter(
                key: 'status',
                value: UserProfileStatusActive::class,
                type: UserProfileStatus::TYPE
            );

        $qb->leftJoin(
            UserProfile::class,
            'users_profile',
            'WITH',
            'users_profile.id = token.id',
        );

        $qb->leftJoin(
            UserProfilePersonal::class,
            'users_profile_personal',
            'WITH',
            'users_profile_personal.event = users_profile.event',
        );


        /* Кешируем результат ORM */
        return $qb
            ->enableCache('wildberries', '1 day')
            ->getResult();
    }

    /**
     * Возвращает коллекцию идентификаторов, доступных активному профилю пользователя
     */
    public function getAccessProfileTokenCollection(): ?array
    {
        $UserProfileUid = $this->wbTokenByProfile->getCurrentUserProfile();

        if(!$UserProfileUid)
        {
            return [];
        }

        $qb = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $select = sprintf('new %s(token.id, users_profile_personal.username)', UserProfileUid::class);
        $qb->select($select);

        $qb->from(WbToken::class, 'token');

        $qb->join(
            WbTokenEvent::class,
            'event',
            'WITH',
            'event.id = token.event',
        );

        /*$qb->join(
            WbTokenAccess::class,
            'access',
            'WITH',
            'access.event = token.event AND access.profile = :access',
        );*/


        //$qb->setParameter('access', $UserProfileUid, UserProfileUid::TYPE);


        $qb
            ->join(
                UserProfileInfo::class,
                'users_profile_info',
                'WITH',
                'users_profile_info.profile = token.id AND users_profile_info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $qb->leftJoin(
            UserProfile::class,
            'users_profile',
            'WITH',
            'users_profile.id = token.id',
        );

        $qb->leftJoin(
            UserProfilePersonal::class,
            'users_profile_personal',
            'WITH',
            'users_profile_personal.event = users_profile.event',
        );


        /* Кешируем результат ORM */
        return $qb->enableCache('wildberries', 86400)->getResult();

    }
}
