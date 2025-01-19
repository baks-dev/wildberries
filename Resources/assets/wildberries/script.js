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

$addButtonProfile = document.getElementById('add-collection-access');

if($addButtonProfile)
{
    /* Блок для новой коллекции */
    //let $blockCollectionCall = document.getElementById('collection-call');

    //if ($blockCollectionCall) {

    $addButtonProfile.addEventListener('click', function()
    {

        let $addButtonProfile = this;

        let $blockCollection = document.getElementById($addButtonProfile.dataset.collection);


        /* получаем прототип коллекции  */
        let newForm = $addButtonProfile.dataset.prototype;
        let index = $addButtonProfile.dataset.index * 1;

        /* Замена '__name__' в HTML-коде прототипа
         вместо этого будет число, основанное на том, сколько коллекций */
        newForm = newForm.replace(/__access__/g, index);

        /* Вставляем новую коллекцию */
        let div = document.createElement('div');
        div.id = $addButtonProfile.dataset.item.replace(/__access__/g, index);


        div.innerHTML = newForm;
        $blockCollection.append(div);

        /* Удаляем контактный номер телефона */
        (div.querySelector('.del-item'))?.addEventListener('click', removeElement);

        /* применяем select2 */
        new NiceSelect(div.querySelector('[data-select="select2"]'), {searchable: true});

        /* Увеличиваем data-index на 1 после вставки новой коллекции */
        $addButtonProfile.dataset.index = (index + 1).toString();

        /* Плавная прокрутка к элементу */
        div.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});

    });
    //}
}

document.querySelectorAll('.del-item').forEach(function(item)
{
    item.addEventListener('click', removeElement);
});

function removeElement()
{
    document.getElementById(this.dataset.delete).remove();
}

