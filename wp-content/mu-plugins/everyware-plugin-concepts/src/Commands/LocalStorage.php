<?php

namespace Everyware\Concepts\Commands;

use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LocalStorage
{
    /**
     * @var string
     */
    private $storagePath;

    /**
     * @var LocalFilesystem
     */
    private $filesystem;

    /**
     * ConceptStorage constructor.
     *
     * @param LocalFilesystem $filesystem
     * @param string|null     $storagePath
     */
    public function __construct(LocalFilesystem $filesystem, string $storagePath = null)
    {
        $this->filesystem = $filesystem;

        if ($storagePath) {
            $this->setStorageDir($storagePath);
        }
    }

    /**
     * @param string $filename
     * @param string $content
     *
     * @param bool   $update
     *
     * @return LocalStorage
     */
    public function addFile(string $filename, string $content, bool $update = false): LocalStorage
    {
        try {
            $filePath = $this->getStoragePath($filename);

            if ( ! $this->filesystem->exists($filePath)) {
                $this->filesystem->touch($filePath);
                $this->filesystem->chmod($filePath, 0777);
                $this->filesystem->dumpFile($filePath, $content);
            } elseif ($update) {
                $this->filesystem->dumpFile($filePath, $content);
            }

        } catch (IOExceptionInterface $exception) {
            throw new RuntimeException('Error creating file at ' . $exception->getPath());
        }

        return $this;
    }

    /**
     *
     * @param string $path
     *
     * @return LocalStorage
     */
    public function createDirectory(string $path): LocalStorage
    {
        return $this->makeDir($this->getStoragePath($path));
    }

    /**
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool
    {
        return $this->filesystem->exists($this->getStoragePath($path));
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws RuntimeException
     */
    public function getStoragePath($path = ''): string
    {
        if ( ! $this->storagePath) {
            throw new RuntimeException('Local storage directory has not been set');
        }

        if (empty($path)) {
            return $this->storagePath;
        }

        return "{$this->storagePath}/" . ltrim($path, '/');
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function readFile(string $filename): string
    {
        $filePath = $this->getStoragePath($filename);

        if ( ! $this->filesystem->exists($filePath)) {
            return '';
        }

        $content = $this->filesystem->getFileContent($filePath);

        if ( ! $content) {
            return '';
        }

        return $content;
    }

    /**
     * @param $filename
     *
     * @return LocalStorage
     */
    public function removeFile($filename): LocalStorage
    {
        try {
            $filePath = $this->getStoragePath($filename);

            if ($this->filesystem->exists($filePath)) {
                $this->filesystem->remove($filePath);
            }

        } catch (IOExceptionInterface $exception) {
            throw new RuntimeException('Error Removing file at ' . $exception->getPath());
        }

        return $this;
    }

    /**
     * @param string $path
     */
    public function setStorageDir(string $path): void
    {
        $this->storagePath = $path;
        $this->makeDir($path);
    }

    /**
     * @param $filename
     * @param $content
     *
     * @return LocalStorage
     */
    public function updateFile($filename, $content): LocalStorage
    {
        try {
            $filePath = $this->getStoragePath($filename);

            if ( ! $this->filesystem->exists($filePath)) {
                return $this->addFile($filename, $content);
            }

            $this->filesystem->dumpFile($filePath, $content);
        } catch (IOExceptionInterface $exception) {
            throw new RuntimeException('Error updating file at ' . $exception->getPath());
        }

        return $this;
    }

    /**
     * @param string $path
     *
     * @return LocalStorage
     */
    private function makeDir(string $path): LocalStorage
    {
        try {
            if ( ! $this->filesystem->exists($path)) {
                $old = umask(0);
                $this->filesystem->mkdir($path, 0755);
                umask($old);
            }
        } catch (IOExceptionInterface $exception) {
            throw new RuntimeException('Error creating directory at ' . $exception->getPath());
        }

        return $this;
    }
}
