<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Model\Entity;

use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use Cake\Routing\Router;
use Cake\Utility\Text;
use SplFileObject;

/**
 * Media Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $code
 * @property string $filename
 * @property string $filetype
 * @property int $filesize
 * @property string $filepath
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 *
 * @property \FriendsOfBabba\Core\Model\Entity\User $user
 */
class Media extends BaseEntity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'user_id' => true,
        'code' => true,
        'filename' => true,
        'filetype' => true,
        'filesize' => true,
        'filepath' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'user' => true,
    ];

    protected $_virtual = ['file'];

    public function _getFile()
    {
        return parent::fireMethod('_getFile', function (Media $media) {
            if (isset($media->filepath)) {
                return [
                    'path' => Router::url(DS . $media->filepath, true),
                    'name' => $media->filename,
                    'size' => $media->filesize,
                    'type' => $media->filetype
                ];
            }
            return NULL;
        });
    }

    /**
     * Returns currently configured storage.
     *
     * @return string
     */
    public static function getStoragePath()
    {
        return Configure::read('Media.storage', WWW_ROOT . "media");
    }

    /**
     * Crea un file partendo da un oggetto di tipo File
     * inviato tramite client javascript.
     *
     * @param ArrayObject $rawFile
     * @return void
     */
    public static function createFromRawFile($rawFile)
    {
        $data = $rawFile['data'];
        $data = explode(",", $data);
        $data = count($data) > 1 ? base64_decode($data[1]) : "";
        $ext = pathinfo($rawFile['name'], PATHINFO_EXTENSION);
        $createdFile = self::createFile(null, $ext);
        // Write data into created file:
        file_put_contents($createdFile->getPathname(), $data);

        return [
            'name' => $rawFile['name'],
            'size' => $rawFile['size'],
            'type' => empty($rawFile['type']) ? "application/octet-stream" : $rawFile['type'],
            'path' => str_replace(WWW_ROOT, "", $createdFile->getPathname())
        ];
    }

    /**
     * Create empty file in to the storage.
     */
    public static function createFile($name = null, $ext = null, $date = null): SplFileObject
    {
        if (is_null($name)) {
            $name = Text::uuid();
        }
        if (is_null($date)) {
            $date = new \DateTime();
        }
        if (!is_null($ext)) {
            $name .= "." . $ext;
        }
        $storagePath = self::createFolder($date);

        touch($storagePath . DS . $name);

        $file = new SplFileObject($storagePath . DS . $name);

        return $file;
    }

    /**
     * Create folder path bassed on date.
     * The folder will be created inside the storage folder. (Media/storage config).
     *
     * @param \DateTime $date
     * @return string
     *  Returns the path of the created folder.
     */
    public static function createFolder(\DateTime $date = null): string
    {
        if (is_null($date)) {
            $date = new \DateTime();
        }

        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $storagePath = implode(DS, [
            self::getStoragePath(),
            $year,
            $month,
            $day
        ]);

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        return $storagePath;
    }
}
