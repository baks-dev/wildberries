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

namespace BaksDev\Wildberries\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Wildberries\Entity\Event\WbTokenEvent;
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\UseCase\Admin\Delete\WbTokenDeleteDTO;
use BaksDev\Wildberries\UseCase\Admin\Delete\WbTokenDeleteForm;
use BaksDev\Wildberries\UseCase\Admin\Delete\WbTokenDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_WB_TOKEN_DELETE')]
final class DeleteController extends AbstractController
{

    #[Route('/admin/wb/token/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] WbTokenEvent $WbTokenEvent,
        WbTokenDeleteHandler $WbTokenDeleteHandler,
    ): Response
    {

        $WbTokenDeleteDTO = new WbTokenDeleteDTO();
        $WbTokenEvent->getDto($WbTokenDeleteDTO);
        $form = $this->createForm(WbTokenDeleteForm::class, $WbTokenDeleteDTO, [
            'action' => $this->generateUrl('Wildberries:admin.delete', ['id' => $WbTokenDeleteDTO->getEvent()]),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('wb_token_delete'))
        {
            $WbToken = $WbTokenDeleteHandler->handle($WbTokenDeleteDTO);

            if($WbToken instanceof WbToken)
            {
                $this->addFlash('admin.breadcrumb.delete', 'admin.success.delete', 'admin.wb.token');

                return $this->redirectToRoute('Wildberries:admin.index');
            }

            $this->addFlash(
                'admin.breadcrumb.delete',
                'admin.danger.delete',
                'admin.wb.token',
                $WbToken,
            );

            return $this->redirectToRoute('Wildberries:admin.index', status: 400);
        }

        return $this->render([
            'form' => $form->createView(),
        ]);
    }
}
