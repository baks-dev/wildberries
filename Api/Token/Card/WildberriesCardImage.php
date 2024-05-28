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

declare(strict_types=1);

namespace BaksDev\Wildberries\Api\Token\Card;

//use App\Module\Products\Product\Type\Offers\Id\ProductOfferUid;
//use App\Module\Wildberries\Rest\OpenApi\Cards\WbImage\WbImageInterface;
use DateTimeInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionParameter;
use RuntimeException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function strlen;
use const PHP_INT_SIZE;
use const STR_PAD_LEFT;

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