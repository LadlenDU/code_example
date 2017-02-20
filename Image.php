<?php

namespace app\helpers;

use app\core\Configurable;

/**
 * Class Image для работы с изображениями.
 */
class Image implements Configurable
{
    /** Префикс названия миниатюры */
    const THUMBNAIL_PREFIX = "_thumb";

    /** @var array конфигурация */
    protected $config = [];

    public function __constructor($config)
    {
        $this->config = $config;
    }

    /**
     * Создает путь к файлу миниатюры заданного изображения.
     *
     * @param string $path путь к файлу
     * @return string
     */
    public static function getThumbName($path)
    {
        $thumbPath = '';

        $info = pathinfo($path);
        $thumbPath .= (isset($info['dirname']) && $info['dirname'] != '' && $info['dirname'] != '.')
            ? ($info['dirname'] . '/') : '';
        $thumbPath .= $info['filename'] . self::THUMBNAIL_PREFIX;
        $thumbPath .= isset($info['extension']) ? ('.' . $info['extension']) : '';

        return $thumbPath;
    }

    /**
     * Уменьшить изображение до его допустимо максимальных размеров и разместить в соответствующей директории.
     *
     * @param string $imagePath путь к оригинальному изображению
     * @param bool|true $thumb надо ли генерировать миниатюру
     * @param bool|false $temporary надо ли генерировать файлы во временную директорию
     * @return array [[new][, new_thumb]] пути к сгенерированному изображению и миниатюре
     * @throws \Exception
     */
    public function reduceImageToMaxDimensions($imagePath, $thumb = true, $temporary = false)
    {
        $ret = [];

        $newPath = $this->config['webDir'] . $this->config['image']['public_images_root'];
        $newPath .= $temporary
            ? $this->config['image']['public_images_temp_dir']
            : $this->config['image']['public_images_dir'];
        $newPath .= '/' . str_replace('.', '', uniqid('images', true));

        $maxSize = $this->config['image']['max_size'];
        if ($new = self::resizeImageReduce($imagePath, $newPath, $maxSize))
        {
            $ret['new'] = $new;
        }

        if ($thumb)
        {
            $newThumbPath = self::getThumbName($newPath);
            $maxThumbSize = $this->config['image']['max_thumb_size'];
            if ($newThumb = self::resizeImageReduce($imagePath, $newThumbPath, $maxThumbSize))
            {
                $ret['new_thumb'] = $newThumb;
            }
        }

        return $ret;
    }

    /**
     * Уменьшить пропорционально изображение (если требуется).
     *
     * @param string $currentPath путь к оригинальному изображению
     * @param string $newPath путь к новому изображению (без расширения файла,
     * расширение добавляется автоматически к названию)
     * @param array $maxSize [width, height] максимальный размер изображения
     * @return bool|array false в случае неудачи, массив ['name', 'width', 'height'] в случае удачи
     * @throws \Exception
     */
    public static function resizeImageReduce($currentPath, $newPath, $maxSize)
    {
        $ret = false;

        if ($size = getimagesize($currentPath))
        {
            if (!$extension = self::getMimeExtension($size['mime']))
            {
                throw new \Exception('Не поддерживаемый MIME тип: ' . $size['mime']);
            }

            $realNewName = "$newPath.$extension";

            $scale = min($maxSize['width'] / $size[0], $maxSize['height'] / $size[1]);

            if ($scale >= 1)
            {
                if (copy($currentPath, $realNewName))
                {
                    $ret = ['name' => basename($realNewName), 'width' => $size[0], 'height' => $size[1]];
                }
            }
            else
            {
                $width = ceil($scale * $size[0]);
                $height = ceil($scale * $size[1]);

                $src = imagecreatefromstring(file_get_contents($currentPath));
                $dst = imagecreatetruecolor($width, $height);

                imagecopyresampled($dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
                imagedestroy($src);

                switch ($extension)
                {
                    case 'jpg':
                    {
                        $ret = imagejpeg($dst, $realNewName);
                    }
                        break;
                    case 'gif':
                    {
                        $ret = imagegif($dst, $realNewName);
                    }
                        break;
                    case 'png':
                    {
                        $ret = imagepng($dst, $realNewName, 9, PNG_ALL_FILTERS);
                    }
                        break;
                }

                imagedestroy($dst);

                if ($ret)
                {
                    $ret = ['name' => basename($realNewName), 'width' => $width, 'height' => $height];
                }
            }
        }

        return $ret;
    }

    /**
     * Вернуть расширение для файла по MIME типу.
     *
     * @param string $mime
     * @return bool|string расширения для файла или false если MIME тип не поддерживается
     */
    protected static function getMimeExtension($mime)
    {
        $ret = false;

        switch ($mime)
        {
            case 'image/jpeg':
            case 'image/pjpeg':
            {
                $ret = 'jpg';
            }
                break;
            case 'image/gif':
            {
                $ret = 'gif';
            }
                break;
            case 'image/png':
            case 'image/x-png':
            {
                $ret = 'png';
            }
                break;
        }

        return $ret;
    }

    /**
     * Проверка на MIME тип.
     *
     * @param string $file путь к файлу
     * @param array $mime допустимые MIME типы
     * @return array пустой массив в случае удачи, или содержит элемент ['error'] с описанием ошибки в случае ошибки
     */
    public static function validateImageMIME($file, $mime)
    {
        $ret = [];

        if ($file)
        {
            $size = getimagesize($file);
            if (!empty($size['mime']))
            {
                if (!in_array($size['mime'], $mime))
                {
                    $ret['error'] = 'Не допустимый тип изображения: ' . $size['mime'];
                }
            }
            else
            {
                $ret['error'] = 'Не удалось определить тип изображения';
            }
        }

        return $ret;
    }
}
