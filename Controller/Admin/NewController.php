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
use BaksDev\Wildberries\Entity\WbToken;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenDTO;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\WbTokenHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity("ROLE_WB_TOKEN_NEW")]
final class NewController extends AbstractController
{
    /**
     * Обрабатывает создание нового токена Wildberries
     *
     * @see WbTokenHandler
     */
    #[
        Route(
            "/admin/wb/token/new",
            name: "admin.newedit.new",
            methods: ["GET", "POST"],
        ),
    ]
    public function news(
        Request $request,
        WbTokenHandler $WbTokenHandler,
    ): Response {
        $WbTokenDTO = new WbTokenDTO();

        if (false === $this->isAdmin()) {
            $WbTokenDTO->getProfile()->setValue($this->getProfileUid());
        }

        // Форма
        $form = $this->createForm(
            type: WbTokenForm::class,
            data: $WbTokenDTO,
            options: [
                "action" => $this->generateUrl(
                    route: "wildberries:admin.newedit.new",
                ),
            ],
        )->handleRequest($request);

        if (
            $form->isSubmitted() &&
            $form->isValid() &&
            $form->has("wb_token")
        ) {
            $this->refreshTokenForm($form);

            $WbToken = $WbTokenHandler->handle($WbTokenDTO);

            if ($WbToken instanceof WbToken) {
                $this->addFlash(
                    type: "admin.breadcrumb.new",
                    message: "admin.success.new",
                    subject: "admin.wb.token",
                );

                return $this->redirectToRoute(route: "wildberries:admin.index");
            }

            $this->addFlash(
                type: "admin.breadcrumb.new",
                message: "admin.danger.new",
                subject: "admin.wb.token",
                context: $WbToken,
            );

            return $this->redirectToReferer();
        }

        return $this->render(
            view: ["form" => $form->createView()],
        );
    }
}
