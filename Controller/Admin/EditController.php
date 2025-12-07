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

namespace BaksDev\Wildberries\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\Type\Event\WbTokenEventUid;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_WB_TOKEN_EDIT')]
final class EditController extends AbstractController
{

    #[Route('/admin/wb/token/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] WbTokenEvent $WbTokenEvent,
        WbTokenHandler $WbTokenHandler,
    ): Response
    {

        $WbTokenDTO = new WbTokenDTO();

        /** Запрещаем редактировать чужой токен */
        if($this->isAdmin() === true || $this->getProfileUid()?->equals($WbTokenEvent->getProfile()->getValue()) === true)
        {
            $WbTokenEvent->getDto($WbTokenDTO);
        }

        if($request->getMethod() === 'GET')
        {
            $WbTokenDTO->getToken()->hiddenToken();
        }

        // Форма
        $form = $this
            ->createForm(WbTokenForm::class, $WbTokenDTO, [
                'action' => $this->generateUrl(
                    route: 'wildberries:admin.newedit.edit',
                    parameters: ['id' => $WbTokenDTO->getEvent() ?: new WbTokenEventUid()]),
            ])
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('wb_token'))
        {
            $this->refreshTokenForm($form);

            /** Запрещаем редактировать чужой токен */
            if($this->isAdmin() === false && $this->getProfileUid()?->equals($WbTokenDTO->getProfile()->getValue()) !== true)
            {
                $this->addFlash('admin.breadcrumb.edit', 'admin.danger.edit', 'admin.wb.token', '404');
                return $this->redirectToReferer();
            }

            $handle = $WbTokenHandler->handle($WbTokenDTO);

            $this->addFlash
            (
                'admin.breadcrumb.edit',
                $handle instanceof WbToken ? 'admin.success.edit' : 'admin.danger.edit',
                'admin.wb.token',
                $handle
            );

            return $this->redirectToReferer();

        }

        return $this->render(['form' => $form->createView()]);
    }
}
