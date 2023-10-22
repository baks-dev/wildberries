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

        //dd(self::generate(new \DateTimeImmutable('1996-11-30 00:00:00')));

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



    private static string $time = '';
    private static array $rand = [];
    private static string $seed;
    private static array $seedParts;
    private static int $seedIndex = 0;

    public static function generate(DateTimeInterface $time = null): string
    {
        if (0 > $time = $time->format('Uv')) {
            throw new InvalidArgumentException('The timestamp must be positive.');
        }

        if ($time > self::$time || (null !== $mtime && $time !== self::$time)) {
            randomize:
            self::$rand = unpack('n*', isset(self::$seed) ? random_bytes(10) : self::$seed = random_bytes(16));
            self::$rand[1] &= 0x03FF;
            self::$time = $time;
        } else {
            if (!self::$seedIndex) {
                $s = unpack('l*', self::$seed = hash('sha512', self::$seed, true));
                $s[] = ($s[1] >> 8 & 0xFF0000) | ($s[2] >> 16 & 0xFF00) | ($s[3] >> 24 & 0xFF);
                $s[] = ($s[4] >> 8 & 0xFF0000) | ($s[5] >> 16 & 0xFF00) | ($s[6] >> 24 & 0xFF);
                $s[] = ($s[7] >> 8 & 0xFF0000) | ($s[8] >> 16 & 0xFF00) | ($s[9] >> 24 & 0xFF);
                $s[] = ($s[10] >> 8 & 0xFF0000) | ($s[11] >> 16 & 0xFF00) | ($s[12] >> 24 & 0xFF);
                $s[] = ($s[13] >> 8 & 0xFF0000) | ($s[14] >> 16 & 0xFF00) | ($s[15] >> 24 & 0xFF);
                self::$seedParts = $s;
                self::$seedIndex = 21;
            }

            self::$rand[5] = 0xFFFF & $carry = self::$rand[5] + (self::$seedParts[self::$seedIndex--] & 0xFFFFFF);
            self::$rand[4] = 0xFFFF & $carry = self::$rand[4] + ($carry >> 16);
            self::$rand[3] = 0xFFFF & $carry = self::$rand[3] + ($carry >> 16);
            self::$rand[2] = 0xFFFF & $carry = self::$rand[2] + ($carry >> 16);
            self::$rand[1] += $carry >> 16;

            if (0xFC00 & self::$rand[1]) {
                if (PHP_INT_SIZE >= 8 || 10 > strlen($time = self::$time)) {
                    $time = (string) (1 + $time);
                } elseif ('999999999' === $mtime = substr($time, -9)) {
                    $time = (1 + substr($time, 0, -9)).'000000000';
                } else {
                    $time = substr_replace($time, str_pad(++$mtime, 9, '0', STR_PAD_LEFT), -9);
                }

                goto randomize;
            }

            $time = self::$time;
        }

        $time = dechex($time * 1);


        return substr_replace(sprintf('%012s-%04x-%04x-%04x%04x%04x',
            $time,
            0x7000 | (self::$rand[1] << 2) | (self::$rand[2] >> 14),
            0x8000 | (self::$rand[2] & 0x3FFF),
            self::$rand[3],
            self::$rand[4],
            self::$rand[5],
        ), '-', 8, 0);
    }

}