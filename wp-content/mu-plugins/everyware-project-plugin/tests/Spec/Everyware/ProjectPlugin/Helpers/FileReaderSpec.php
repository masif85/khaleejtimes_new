<?php

namespace Spec\Everyware\ProjectPlugin\Helpers;

use Everyware\ProjectPlugin\Helpers\FileReader;
use InvalidArgumentException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Name: Test name
 * Description: Header dock for testing FileReader class
 * Version: 1.0.0
 * Author: Infomaker Scandinavia AB
 * Author URI: https://infomaker.se
 */
class FileReaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(FILEREADER_TEST_FILE);
        $this->shouldHaveType(FileReader::class);
    }

    function it_throws_exeption_if_file_dose_not_exist()
    {
        $file = 'some_file.php';
        $this->beConstructedWith($file);

        $this->shouldThrow(new InvalidArgumentException("File {$file} dose not exist."))->duringInstantiation();
    }

    function it_can_extract_meta_data_from_header_dockblock()
    {
        $this->beConstructedWith(FILEREADER_TEST_FILE);
        $this->extractHeaders([
            'name' => 'Name',
            'description' => 'Description',
            'version' => 'Version',
            'author' => 'Author',
            'author_uri' => 'Author URI'
        ])->shouldReturn([
            'name' => 'Test name',
            'description' => 'Header dock for testing FileReader class',
            'version' => '1.0.0',
            'author' => 'Infomaker Scandinavia AB',
            'author_uri' => 'https://infomaker.se'
        ]);
    }

    function it_accepts_html_as_metadata()
    {
        $this->beConstructedWith(FILEREADER_TEST_FILE);
        $this->extractHeaders([
            'html' => 'HTML'
        ])->shouldReturn([
            'html' => '<p> Here is a paragraph</p>'
        ]);
    }

    function it_returns_empty_values_if_meta_key_cant_be_found()
    {
        $this->beConstructedWith(FILEREADER_TEST_FILE);
        $this->extractHeaders([
            'name' => 'Some header '
        ])->shouldReturn([
            'name' => ''
        ]);
    }

    function it_offers_a_way_to_fetch_single_header()
    {
        $this->beConstructedWith(FILEREADER_TEST_FILE);
        $this->getHeader('Version')->shouldReturn('1.0.0');
        $this->getHeader('Name')->shouldReturn('Test name');
    }
}
