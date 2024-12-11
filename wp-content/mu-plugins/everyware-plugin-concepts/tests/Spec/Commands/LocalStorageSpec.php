<?php

namespace Spec\Everyware\Concepts\Commands;

use Everyware\Concepts\Commands\LocalFilesystem;
use Everyware\Concepts\Commands\LocalStorage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class LocalStorageSpec
 * @package Spec\Everyware\Concepts\Commands
 * @method shouldBeCalled
 * @method setStorageDir(string $localStoragePath)
 * @method addFile(string $filename, string $content)
 * @method updateFile(string $filename, string $content)
 * @method removeFile(string $filename)
 * @method readFile(string $filename)
 * @method exists(string $filename)
 * @method createDirectory(string $localStoragePath)
 */
class LocalStorageSpec extends ObjectBehavior
{
    /**
     * @var LocalFilesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $localStoragePath = 'commandline/storage/path';

    public function let(LocalFilesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
        $this->beConstructedWith($this->filesystem, $this->localStoragePath);

    }

    public function it_offers_a_way_to_setup_storage_dir_exist()
    {
        $this->filesystem
            ->exists($this->localStoragePath)
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filesystem
            ->mkdir($this->localStoragePath, 0755)
            ->shouldBeCalled();

        $this->setStorageDir($this->localStoragePath);
    }

    public function it_can_add_files_to_local_storage()
    {
        $filename = 'filename.txt';
        $content = 'content';

        $this->ifFileExist($filename, false);
        $this->simulateAddingFile($filename, $content);

        $this->addFile($filename, $content);
    }

    public function it_will_only_add_file_in_none_exists_in_local_storage()
    {
        $filename = 'filename.txt';
        $content = 'content';

        $this->ifFileExist($filename);

        $this->addFile($filename, $content);
    }

    public function it_will_updte_file_to_local_storage_if_update_is_set()
    {
        $filename = 'filename.txt';
        $content = 'content';

        $this->ifFileExist($filename);
        $this->simulateUpdatingFile($filename, $content);

        $this->addFile($filename, $content, true);
    }


    public function it_can_update_files_to_local_storage()
    {
        $filename = 'filename.txt';
        $content = 'content';

        $this->ifFileExist($filename);

        $this->simulateUpdatingFile($filename, $content);

        $this->updateFile($filename, $content);
    }

    public function it_will_add_instead_of_update_if_file_dont_exist()
    {
        $filename = 'filename.txt';
        $content = 'content';

        $this->ifFileExist($filename, false);

        $this->simulateAddingFile($filename, $content);

        $this->updateFile($filename, $content);
    }

    public function it_can_remove_files_from_local_storage()
    {
        $filename = 'filename.txt';

        $this->ifFileExist($filename);

        $this->simulateRemovingFile($filename);

        $this->removeFile($filename);
    }

    public function it_will_not_attempt_to_remove_non_existing_files_from_local_storage()
    {
        $filename = 'filename.txt';

        $this->ifFileExist($filename, false);

        $this->removeFile($filename);
    }

    public function it_can_read_the_content_of_a_file_in_local_storage()
    {
        $filename = 'filename.txt';

        $this->ifFileExist($filename);
        $this->simulateReadingFile($filename);

        $this->readFile($filename);
    }

    public function it_will_not_attempt_to_read_non_existing_files_from_local_storage()
    {
        $filename = 'filename.txt';

        $this->ifFileExist($filename, false);

        $this->readFile($filename);
    }

    public function it_can_determine_if_a_file_exists_in_storage()
    {
        $filename = 'filename.txt';

        $this->ifFileExist($filename, false);

        $this->exists($filename)->shouldReturn(false);
    }

    public function it_offers_a_way_to_create_storage_directories()
    {
        $dirName = 'new/storageDir';
        $this->ifFileExist($dirName, false);

        $this->filesystem
            ->mkdir($this->getStoragePath($dirName), 0755)
            ->shouldBeCalled();

        $this->createDirectory($dirName);
    }

    // Simulations
    // ======================================================

    private function ifFileExist(string $filename, bool $exist = true)
    {
        $this->filesystem
            ->exists($this->getStoragePath($filename))
            ->shouldBeCalled()
            ->willReturn($exist);
    }

    private function simulateAddingFile(string $filename, string $content)
    {
        $this->filesystem->touch($this->getStoragePath($filename))->shouldBeCalled();
        $this->filesystem->chmod($this->getStoragePath($filename), 0777)->shouldBeCalled();
        $this->filesystem->dumpFile($this->getStoragePath($filename), $content)->shouldBeCalled();
    }

    private function simulateUpdatingFile(string $filename, string $content)
    {
        $this->filesystem->dumpFile($this->getStoragePath($filename), $content)->shouldBeCalled();
    }

    private function simulateRemovingFile(string $filename)
    {
        $this->filesystem->remove($this->getStoragePath($filename))->shouldBeCalled();
    }

    private function simulateReadingFile(string $filename)
    {
        $this->filesystem->getFileContent($this->getStoragePath($filename))->shouldBeCalled();
    }
}
