<?php

namespace PE\Component\FormData;

class FormData
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $post = [];

    /**
     * Constructor
     *
     * <code>
     * // Parse data
     * $data = new FormData($_SERVER['CONTENT_TYPE'], file_get_contents('php://input'));
     *
     * // Get $_FILES compatible array
     * $data->getFILES()
     *
     * // Get $_POST compatible array
     * $data->getPOST()
     * </code>
     *
     * @param string $contentTypeHeader RAW request "Content-Type" header
     * @param string $body              RAW request body
     */
    public function __construct($contentTypeHeader, $body)
    {
        if (!preg_match('/boundary=(.*)$/', $contentTypeHeader, $matches)) {
            return;
        }

        $boundary = $matches[1];

        if (!count($boundary)) {
            // Parse body as query string
            parse_str(urldecode($body), $this->post);
            return;
        }

        $blocks = preg_split("/-+{$boundary}/", $body);
        array_pop($blocks);

        foreach ($blocks as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $this->parseBlock($value);

            /*if (count($block['post']) > 0) {
                array_push($results['post'], $block['post']);
            }

            if (count($block['file']) > 0) {
                array_push($results['file'], $block['file']);
            }*/
        }
    }

    /**
     * @return array
     */
    public function getFILES()
    {
        return $this->files;
    }

    /**
     * @return array
     */
    public function getPOST()
    {
        return $this->post;
    }

    /**
     * @param string $block
     */
    private function parseBlock($block)
    {
        list($headers, $body) = explode("\r\n\r\n", $block, 2);

        $headers = explode("\r\n", trim($headers));

        $tmp = [];
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);
            $tmp[$key] = $value;
        }

        $headers = array_map(function($value){
            return trim($value);
        }, $tmp);

        strpos($headers['Content-Disposition'], 'filename') !== false
            ? $this->parseFile($headers, $body)
            : $this->parsePost($headers, $body);
    }

    /**
     * @param array  $headers File headers
     * @param string $body    File binary data
     */
    private function parseFile(array $headers, $body)
    {
        list($index, $chain) = $this->parseName($headers);

        preg_match('/name=\"([^\"]*)\"; filename=\"([^\"]*)\"/', $headers['Content-Disposition'], $matches);

        $path = sys_get_temp_dir().'\php'.substr(sha1(mt_rand()), 0, 6);
        $err  = file_put_contents($path, $body);

        if (!array_key_exists($index, $this->files)) {
            $this->files[$index] = [
                'name'     => null,
                'type'     => null,
                'tmp_name' => null,
                'error'    => null,
                'size'     => null,
            ];
        }

        $this->setKey($this->files[$index]['name'], $chain, $matches[2]);
        $this->setKey($this->files[$index]['type'], $chain, $headers['Content-Type']);
        $this->setKey($this->files[$index]['tmp_name'], $chain, $path);
        $this->setKey($this->files[$index]['error'], $chain, $err === false ? $err : 0);
        $this->setKey($this->files[$index]['size'], $chain, filesize($path));
    }

    /**
     * Parse simple field
     *
     * @param array  $headers Field headers
     * @param string $body    Field body
     */
    private function parsePost(array $headers, $body)
    {
        list($index, $chain) = $this->parseName($headers);

        if (!array_key_exists($index, $this->post)) {
            $this->post[$index] = null;
        }

        $this->setKey($this->post[$index], $chain, $body);
    }

    /**
     * @param array $headers
     *
     * @return array Format [0 => $index, 1 => $chain] where $index - root key, $chain - depth keys chain
     */
    private function parseName(array $headers)
    {
        preg_match('/name=\"([^\"]*)\"/', $headers['Content-Disposition'], $matches);

        if (false !== ($pos = strpos($matches[1], '['))) {
            preg_match_all('/(?:\[([^\[\]]*)\])/', substr($matches[1], $pos), $tmp);
            return [substr($matches[1], 0, $pos), $tmp[1]];
        } else {
            return [$matches[1], []];
        }
    }

    /**
     * Set key at expected depth
     *
     * @param mixed $key
     * @param array $chain
     * @param mixed $value
     */
    private function setKey(&$key, array $chain, $value)
    {
        if (empty($chain)) {
            $key = $value;
        } else {
            $index = array_shift($chain);

            if (!is_array($key)) {
                $key = [];
            }

            if ($index === '') {
                $key[] = null;
                end($key);
                $index = key($key);
            }

            if (!isset($key[$index])) {
                $key[$index] = null;
            }

            $this->setKey($key[$index], $chain, $value);
        }
    }
}
