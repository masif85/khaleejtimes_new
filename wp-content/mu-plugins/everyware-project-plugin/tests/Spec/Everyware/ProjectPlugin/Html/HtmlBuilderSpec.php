<?php

namespace Spec\Everyware\ProjectPlugin\Html;

use Everyware\ProjectPlugin\Html\HtmlBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HtmlBuilderSpec extends ObjectBehavior
{
    private function formatHtml($html)
    {
        return trim(preg_replace('~>\s+<~', '><', $html));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(HtmlBuilder::class);
    }

    function it_throws_exception_on_missin_method()
    {
        $this->shouldThrow(\BadMethodCallException::class)->during('noMethod');
    }

    function it_throws_exception_on_generating_other_than_html()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('toHtmlString', [1]);
    }

    function it_is_macroable()
    {
        $this->shouldThrow(\BadMethodCallException::class)->during('noMethod');
        HtmlBuilder::macro('noMethod', function () {
            return 'Method exists!';
        });
        $this->noMethod()->shouldReturn('Method exists!');
    }

    function it_can_convert_array_to_string_of_tag_attributes()
    {
        $this->attributes([
            'title' => 'Title text',
            'id' => 'identifier',
            'class' => 'class-1 class-2 class-3'
        ])->shouldReturn(' title="Title text" id="identifier" class="class-1 class-2 class-3"');

        $this->attributes([
            'title' => 'Title text',
            'id' => 'identifier',
            'class' => ['class-1', 'class-2', 'class-3']
        ])->shouldReturn(' title="Title text" id="identifier" class="class-1 class-2 class-3"');
    }

    function it_can_convert_an_HTML_string_to_entities()
    {
        $paragraph = '<p>Nöje & Kultur</p>';
        $encoded = '&lt;p&gt;N&ouml;je &amp; Kultur&lt;/p&gt;';
        $this->entities($paragraph)->shouldReturn($encoded);
    }

    function it_can_convert_entities_to_HTML_characters()
    {
        $paragraph = '<p>Nöje & Kultur</p>';
        $encoded = '&lt;p&gt;N&ouml;je &amp; Kultur&lt;/p&gt;';
        $this->decode($encoded)->shouldReturn($paragraph);
    }

    function it_can_obfuscate_an_email()
    {
        $email = 'test_mail@example.com';
        $this->email($email)->shouldNotReturn($email);
    }

    function it_can_create_a_number_of_nbsps()
    {
        $this->nbsp()->shouldReturn('&nbsp;');
        $this->nbsp(3)->shouldReturn('&nbsp;&nbsp;&nbsp;');
    }

    function it_can_create_tags()
    {
        $this->tag('p', 'Nöje & Kultur')->shouldReturn('<p>Nöje & Kultur</p>');
    }

    function it_can_create_single_tags()
    {
        $this->singleTag('br')->shouldReturn('<br>');
    }

    function it_can_add_attributes_to_tags()
    {
        $this->tag('p', 'Nöje & Kultur', ['id' => 'paragraph-id', 'class' => 'p-1'])->shouldReturn('<p id="paragraph-id" class="p-1">Nöje & Kultur</p>');
        $this->singleTag('br', ['id' => 'br-id', 'class' => 'br-class'])->shouldReturn('<br id="br-id" class="br-class">');
    }

    function it_allows_class_in_tag_attributes_to_be_array()
    {
        $this->tag('p', 'Nöje & Kultur', ['id' => 'p-id', 'class' => ['p-class-1', 'p-class-2']])->shouldReturn('<p id="p-id" class="p-class-1 p-class-2">Nöje & Kultur</p>');
        $this->singleTag('br', ['id' => 'br-id', 'class' => ['br-class-1', 'br-class-2']])->shouldReturn('<br id="br-id" class="br-class-1 br-class-2">');
    }

    function it_has_helper_function_for_script()
    {
        $this->script('http://example.com/src.js', ['type' => 'text/javascript'])->shouldReturn('<script src="http://example.com/src.js" type="text/javascript"></script>');
    }

    function it_has_helper_function_for_style_links()
    {
        $this->style('http://example.com/src.js')->shouldReturn('<link media="all" type="text/css" rel="stylesheet" href="http://example.com/src.js">');
    }

    function it_has_helper_function_for_image_tags()
    {
        $this->image('http://example.com/img.png')->shouldReturn('<img src="http://example.com/img.png">');
        $this->image('http://example.com/img.png', 'Alt Text')->shouldReturn('<img src="http://example.com/img.png" alt="Alt Text">');
    }

    function it_has_helper_function_for_meta_tags()
    {
        $this->meta('viewport', 'initial-scale=1.1')->shouldReturn('<meta name="viewport" content="initial-scale=1.1">');
    }

    function it_has_helper_function_for_links()
    {
        $this->link('http://example.com/')->shouldReturn('<a href="http://example.com/">http://example.com/</a>');
        $this->link('http://example.com/', 'Link Title')->shouldReturn('<a href="http://example.com/">Link Title</a>');
    }

    function it_has_helper_function_for_creating_entity_coded_mailto_links()
    {
        $email = 'test_mail@example.com';
        $this->decode($this->mailto($email))->shouldReturn('<a href="mailto:test_mail@example.com">test_mail@example.com</a>');
        $this->decode($this->mailto($email, 'John Doe'))->shouldReturn('<a href="mailto:test_mail@example.com">John Doe</a>');
        $this->decode($this->mailto($email, 'John Doe', ['title' => 'Send a mail to John Doe']))->shouldReturn('<a href="mailto:test_mail@example.com" title="Send a mail to John Doe">John Doe</a>');
    }

    function it_can_create_select_box_from_list()
    {
        $list = [
            ['value' => 1, 'text' => 'Page 1'],
            ['value' => 2, 'text' => 'Page 2'],
            ['value' => 3, 'text' => 'Page 3'],
            ['value' => 4, 'text' => 'Page 4'],
        ];

        $this->select($list)->shouldReturn($this->formatHtml('
            <select>
                <option value="1">Page 1</option>
                <option value="2">Page 2</option>
                <option value="3">Page 3</option>
                <option value="4">Page 4</option>
            </select>
        '));
    }

    function it_has_helper_function_for_ol_list()
    {
        $list = [];
        for ($i = 0; $i <= 4; $i++) {
            $list[] = 'tag content ' . $i;
        }

        $result = $this->formatHtml('
            <ol>
                <li>tag content 0</li>
                <li>tag content 1</li>
                <li>tag content 2</li>
                <li>tag content 3</li>
                <li>tag content 4</li>
            </ol>'
        );

        $this->ol($list)->shouldReturn($result);

        $list = array_map(function ($content) {
            return $this->tag('p', $content);
        }, $list);

        $result = $this->formatHtml('
            <ol class="ol-list">
                <li><p>tag content 0</p></li>
                <li><p>tag content 1</p></li>
                <li><p>tag content 2</p></li>
                <li><p>tag content 3</p></li>
                <li><p>tag content 4</p></li>
            </ol>'
        );
        $this->ol($list, ['class' => ['ol-list']])->shouldReturn($result);
    }

    function it_has_helper_function_for_ul_list()
    {
        $list = [];
        for ($i = 0; $i <= 4; $i++) {
            $list[] = 'tag content ' . $i;
        }
        $result = $this->formatHtml('
            <ul>
                <li>tag content 0</li>
                <li>tag content 1</li>
                <li>tag content 2</li>
                <li>tag content 3</li>
                <li>tag content 4</li>
            </ul>'
        );

        $this->ul($list)->shouldReturn($result);

        $list = array_map(function ($content) {
            return $this->tag('p', $content);
        }, $list);

        $result = $this->formatHtml('
            <ul class="ul-list">
                <li><p>tag content 0</p></li>
                <li><p>tag content 1</p></li>
                <li><p>tag content 2</p></li>
                <li><p>tag content 3</p></li>
                <li><p>tag content 4</p></li>
            </ul>'
        );
        $this->ul($list, ['class' => ['ul-list']])->shouldReturn($result);
    }

    function it_has_helper_function_for_dl_list()
    {
        $list = [];
        for ($i = 0; $i <= 4; $i++) {
            $list['list title: ' . $i] = 'list description: ' . $i;
        }

        $result = $this->formatHtml('
            <dl>
                <dt>list title: 0</dt>
                    <dd>list description: 0</dd>
                <dt>list title: 1</dt>
                    <dd>list description: 1</dd>
                <dt>list title: 2</dt>
                    <dd>list description: 2</dd>
                <dt>list title: 3</dt>
                    <dd>list description: 3</dd>
                <dt>list title: 4</dt>
                    <dd>list description: 4</dd>
            </dl>
        ');

        $this->dl($list)->shouldReturn($result);

        $list = array_map(function ($content) {
            return [$content, $content];
        }, $list);

        $result = $this->formatHtml('
            <dl>
                <dt>list title: 0</dt>
                    <dd>list description: 0</dd>
                    <dd>list description: 0</dd>
                <dt>list title: 1</dt>
                    <dd>list description: 1</dd>
                    <dd>list description: 1</dd>
                <dt>list title: 2</dt>
                    <dd>list description: 2</dd>
                    <dd>list description: 2</dd>
                <dt>list title: 3</dt>
                    <dd>list description: 3</dd>
                    <dd>list description: 3</dd>
                <dt>list title: 4</dt>
                    <dd>list description: 4</dd>
                    <dd>list description: 4</dd>
            </dl>
        ');
        $this->dl($list)->shouldReturn($result);
    }

    function it_allows_for_inlinescript_in_script_tags()
    {
        $this->script('', ['type' => 'text/javascript'], 'var JsVariable = {};')->shouldReturn('<script type="text/javascript">var JsVariable = {};</script>');

    }

    function it_can_remove_defaults_for_style_attributes()
    {
        $this->style('http://example.com/src.js', ['media' => false])->shouldReturn('<link type="text/css" rel="stylesheet" href="http://example.com/src.js">');
    }

    function it_can_create_alternative_meta_tags()
    {
        $this->singleTag('meta', ['http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8'])->shouldReturn('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
    }

    function it_supports_correct_attributes_without_values()
    {
        $this->singleTag('input', [
            'type' => 'checkbox',
            'name' => 'input-name',
            'value' => 'on',
            'checked' => 'checked',
            'required' => true
        ])->shouldReturn('<input type="checkbox" name="input-name" value="on" checked required>');
    }
}
