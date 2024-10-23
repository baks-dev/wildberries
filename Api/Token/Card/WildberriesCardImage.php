<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Api\Token\Card;

//use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
//use App\Module\Wildberries\Rest\OpenApi\Cards\WbImage\WbImageInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WildberriesCardImage
{

    private HttpClientInterface $client;

    private KernelInterface $kernel;


    public function __construct(
        HttpClientInterface $client,
        KernelInterface $kernel
    )
    {
        $this->client = $client;
        $this->kernel = $kernel;
    }


    /**
     * @param string $url - url фото загрузки
     * @param mixed $Image - DTO для присвоения значений
     * @param string $nameDir - Директория загрузки файла (Entity::TABLE)
     */
    public function get(string $url, object $Image, string $nameDir, $reload = false,): mixed
    {
        /** Вычисляем хеш ссылки и присваиваем его к названию файла */
        $originalFilename = pathinfo($url);
        $newFilename = 'image.'.$originalFilename['extension'];

        $dir = md5($url);


        /** Полный путь к директории загрузки */
        $uploadDir = $this->kernel->getProjectDir().'/public/upload/'.$nameDir.'/'.$dir;
        $path = $uploadDir.'/'.$newFilename;


        /**
         * Если файла не существует - скачиваем
         */
        if($reload || !file_exists($path))
        {
            /* Создаем директорию для загрузки */
            if(!file_exists($uploadDir))
            {
                if(!mkdir($uploadDir) && !is_dir($uploadDir))
                {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
                }
            }

            $response = $this->client->request('GET', $url);

            // Responses are lazy: this code is executed as soon as headers are received
            if(200 !== $response->getStatusCode())
            {
                return false;
            }

            /**
             * Если файл перезагружается, и его актуальность больше 1 суток - удаляем директорию
             */
            if($reload && filemtime($path) < (time() - 86400))
            {
                self::removeDir($uploadDir);

                /* Создаем директорию для новой загрузки */
                if(!mkdir($uploadDir) && !is_dir($uploadDir))
                {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
                }
            }

            if(!file_exists($path))
            {
                // получить содержимое ответа и сохранить их в файл
                $fileHandler = fopen($path, 'w');
                foreach($this->client->stream($response) as $chunk)
                {
                    fwrite($fileHandler, $chunk->getContent());
                }
            }
        }

        /* Размер файла */
        $fileSize = filesize($path);

        $Image->setName($dir);
        //$Image->setDir($dir);
        $Image->setExt($originalFilename['extension']);
        $Image->setSize($fileSize);

        return $Image;
    }


    static function removeDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach($files as $file)
        {
            (is_dir($dir.'/'.$file)) ? self::removeDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }

        return rmdir($dir);
    }


}