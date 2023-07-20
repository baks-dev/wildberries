<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Wildberries\Entity;



use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/* Event */

#[ORM\Entity]
#[ORM\Table(name: 'wb_token')]
class WbToken
{
    const TABLE = 'wb_token';

    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $id;

    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: WbTokenEventUid::TYPE, unique: true)]
    private WbTokenEventUid $event;


    public function __construct(UserProfile|UserProfileUid $profile)
    {
        $this->id = $profile instanceof UserProfile ? $profile->getId() : $profile;
    }
    
    public function getId(): UserProfileUid
    {
        return $this->id;
    }


    public function getEvent(): WbTokenEventUid
    {
        return $this->event;
    }


    public function setEvent(WbTokenEventUid|WbTokenEvent $event): void
    {
        $this->event = $event instanceof WbTokenEvent ? $event->getId() : $event;
    }

}