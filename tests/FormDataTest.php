<?php

namespace PETest\Component\FormData;

use PE\Component\FormData\FormData;

class FormDataTest extends \PHPUnit_Framework_TestCase
{
    private $contentType = 'multipart/form-data; boundary=Asrf456BGe4h';

    private $sample0 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment"; filename="attachment-1.txt"
Content-Type: image/jpeg

TXT1
--Asrf456BGe4h
EOF;

    public function testFormDataSample0()
    {
        $files = (new FormData($this->contentType, $this->sample0))->getFILES();

        static::assertTrue(isset($files['attachment']['name']));
        static::assertTrue(isset($files['attachment']['type']));
        static::assertTrue(isset($files['attachment']['tmp_name']));
        static::assertTrue(isset($files['attachment']['error']));
        static::assertTrue(isset($files['attachment']['size']));
    }

    private $sample1 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment[foo]"; filename="attachment-1.txt"
Content-Type: image/jpeg

TXT1
--Asrf456BGe4h
EOF;

    public function testFormDataSample1()
    {
        $files = (new FormData($this->contentType, $this->sample1))->getFILES();

        static::assertTrue(isset($files['attachment']['name']['foo']));
        static::assertTrue(isset($files['attachment']['type']['foo']));
        static::assertTrue(isset($files['attachment']['tmp_name']['foo']));
        static::assertTrue(isset($files['attachment']['error']['foo']));
        static::assertTrue(isset($files['attachment']['size']['foo']));
    }

    private $sample2 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment[foo][]"; filename="attachment-1.txt"
Content-Type: image/jpeg

TXT1
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment[foo][]"; filename="attachment-2.txt"
Content-Type: image/jpeg

TXT2
--Asrf456BGe4h
EOF;

    public function testFormDataSample2()
    {
        $files = (new FormData($this->contentType, $this->sample2))->getFILES();

        static::assertTrue(isset($files['attachment']['name']['foo'][0]));
        static::assertTrue(isset($files['attachment']['type']['foo'][0]));
        static::assertTrue(isset($files['attachment']['tmp_name']['foo'][0]));
        static::assertTrue(isset($files['attachment']['error']['foo'][0]));
        static::assertTrue(isset($files['attachment']['size']['foo'][0]));

        static::assertTrue(isset($files['attachment']['name']['foo'][1]));
        static::assertTrue(isset($files['attachment']['type']['foo'][1]));
        static::assertTrue(isset($files['attachment']['tmp_name']['foo'][1]));
        static::assertTrue(isset($files['attachment']['error']['foo'][1]));
        static::assertTrue(isset($files['attachment']['size']['foo'][1]));
    }

    private $sample3 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment[foo][][bar]"; filename="attachment-1.txt"
Content-Type: image/jpeg

TXT1
--Asrf456BGe4h
Content-Disposition: form-data; name="attachment[foo][][baz]"; filename="attachment-2.txt"
Content-Type: image/jpeg

TXT2
--Asrf456BGe4h
EOF;

    public function testFormDataSample3()
    {
        $files = (new FormData($this->contentType, $this->sample3))->getFILES();

        static::assertTrue(isset($files['attachment']['name']['foo'][0]['bar']));
        static::assertTrue(isset($files['attachment']['type']['foo'][0]['bar']));
        static::assertTrue(isset($files['attachment']['tmp_name']['foo'][0]['bar']));
        static::assertTrue(isset($files['attachment']['error']['foo'][0]['bar']));
        static::assertTrue(isset($files['attachment']['size']['foo'][0]['bar']));

        static::assertTrue(isset($files['attachment']['name']['foo'][1]['baz']));
        static::assertTrue(isset($files['attachment']['type']['foo'][1]['baz']));
        static::assertTrue(isset($files['attachment']['tmp_name']['foo'][1]['baz']));
        static::assertTrue(isset($files['attachment']['error']['foo'][1]['baz']));
        static::assertTrue(isset($files['attachment']['size']['foo'][1]['baz']));
    }

    private $sample4 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="message"

MESSAGE
--Asrf456BGe4h
EOF;

    public function testFormDataSample4()
    {
        $post = (new FormData($this->contentType, $this->sample4))->getPOST();

        static::assertTrue(isset($post['message']));
    }

    private $sample5 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="message[foo]"

MESSAGE
--Asrf456BGe4h
EOF;

    public function testFormDataSample5()
    {
        $post = (new FormData($this->contentType, $this->sample5))->getPOST();

        static::assertTrue(isset($post['message']['foo']));
    }

    private $sample6 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="message[foo][]"

MESSAGE1
--Asrf456BGe4h
Content-Disposition: form-data; name="message[foo][]"

MESSAGE2
--Asrf456BGe4h
EOF;

    public function testFormDataSample6()
    {
        $post = (new FormData($this->contentType, $this->sample6))->getPOST();

        static::assertTrue(isset($post['message']['foo'][0]));
        static::assertTrue(isset($post['message']['foo'][1]));
    }

    private $sample7 = <<<EOF
--Asrf456BGe4h
Content-Disposition: form-data; name="message[foo][][bar]"

MESSAGE1
--Asrf456BGe4h
Content-Disposition: form-data; name="message[foo][][baz]"

MESSAGE2
--Asrf456BGe4h
EOF;

    public function testFormDataSample7()
    {
        $post = (new FormData($this->contentType, $this->sample7))->getPOST();

        static::assertTrue(isset($post['message']['foo'][0]['bar']));
        static::assertTrue(isset($post['message']['foo'][1]['baz']));
    }
}
