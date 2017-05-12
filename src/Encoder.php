<?php

namespace Akatsuki\Component\Bencode;

use JsonSerializable;

/**
 * Class Encoder
 *
 * @package Akatsuki\Component\Bencode
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Encoder
{
    private $data;

    public function encode($data): string
    {
        if (is_object($data)) {
            if ($data instanceof BencodeSerializable) {
                $data = $data->bencodeSerialize();
            } else {
                $data = (array)$data;
            }
        }

        $this->data = $data;

        return $this->doEncode();
    }

    private function doEncode($data = null): string
    {
        $data = $data ?? $this->data;

        if (is_array($data) && (isset ($data[0]) || empty ($data))) {
            return $this->encodeList($data);
        }

        if (is_array($data)) {
            return $this->encodeDict($data);
        }

        if (is_int($data) || is_float($data)) {
            $data = sprintf('%.0f', round($data, 0));

            return $this->encodeInteger($data);
        }

        return $this->encodeString($data);
    }

    private function encodeInteger($data = null): string
    {
        $data = $data ?? $this->data;

        return sprintf("i%.0fe", $data);
    }

    private function encodeString($data = null): string
    {
        $data = $data ?? $this->data;

        return sprintf("%d:%s", strlen($data), $data);
    }

    private function encodeList(array $data = null): string
    {
        $data = $data ?? $this->data;

        $list = '';
        foreach ($data as $value) {
            $list .= $this->doEncode($value);
        }

        return 'l' . $list . 'e';
    }

    private function encodeDict(array $data = null): string
    {
        $data = $data ?? $this->data;
        ksort($data);

        $dict = '';
        foreach ($data as $key => $value) {
            $dict .= $this->encodeString($key) . $this->doEncode($value);
        }

        return 'd' . $dict . 'e';
    }
}