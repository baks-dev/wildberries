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

namespace BaksDev\Wildberries\UseCase\Admin\Delete\Modify;

use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use BaksDev\Wildberries\Entity\Modify\WbTokenModifyInterface;

final class ModifyDTO implements WbTokenModifyInterface
{
	/**
     * Модификатор
     */
	private readonly ModifyAction $action;
	
	
	public function __construct()
	{
		$this->action = new ModifyAction(ModifyActionEnum::DELETE);
	}

	public function getAction() : ModifyAction
	{
		return $this->action;
	}
	
}
