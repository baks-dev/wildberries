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

namespace BaksDev\Wildberries\Repository\AllProfileToken;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use Doctrine\ORM\EntityManagerInterface;

final class AllProfileToken implements AllProfileTokenInterface
{

    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * Метод возвращает профили пользователей, добавленных токенов авторизации
     */
    public function fetchAllWbTokenProfileAssociative(): ?array
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(token.id)', UserProfileUid::class);
        $qb->select($select);

        $qb->from(WbToken::class, 'token');

        $qb->join(
            WbTokenEvent::class,
            'event',
            'WITH',
            'event.id = token.event AND event.profile = token.id',
        );

        $qb->join(
            UserProfileInfo::class,
            'users_profile_info',
            'WITH',
            'users_profile_info.profile = token.id AND users_profile_info.status = :status',
        );

        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);

        return $qb->getQuery()->getResult();
    }
}