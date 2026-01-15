<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\UseCase\Admin\NewEdit;

use BaksDev\Wildberries\UseCase\Admin\NewEdit\Active\WbTokenActiveForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Card\WbTokenCardForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Orders\WbTokenOrdersForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Percent\WbTokenPercentForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Profile\WbTokenProfileForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Sales\WbTokenSalesForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Stocks\WbTokenStockForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Token\WbTokenValueForm;
use BaksDev\Wildberries\UseCase\Admin\NewEdit\Warehouse\WbTokenWarehouseForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WbTokenForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('active', WbTokenActiveForm::class, ['required' => false, 'label' => false]);

        $builder->add('card', WbTokenCardForm::class, ['required' => false, 'label' => false]);

        $builder->add('orders', WbTokenOrdersForm::class, ['required' => false, 'label' => false]);

        $builder->add('stock', WbTokenStockForm::class, ['required' => false, 'label' => false]);

        $builder->add('sales', WbTokenSalesForm::class, ['required' => false, 'label' => false]);

        $builder->add('profile', WbTokenProfileForm::class, ['required' => false, 'label' => false]);

        $builder->add('percent', WbTokenPercentForm::class, ['required' => false, 'label' => false]);

        $builder->add('token', WbTokenValueForm::class, ['required' => false, 'label' => false]);

        $builder->add('warehouse', WbTokenWarehouseForm::class, ['required' => false, 'label' => false]);


        /* Сохранить ******************************************************/
        $builder->add(
            'wb_token', SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WbTokenDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}