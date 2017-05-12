<?php

namespace Akatsuki\Component\Bencode;

use Akatsuki\Component\Bencode\Exception\InvalidSourceException;

/**
 * Class Decoder
 *
 * @package Akatsuki\Component\Bencode
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Decoder
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var int
     */
    private $sourceLength;

    /**
     * @var int
     */
    private $offset;

    public function decode(string $source)
    {
        $this->source       = $source;
        $this->sourceLength = strlen($this->source);
        $this->offset       = 0;

        $decoded = $this->doDecode();

        if ($this->offset !== $this->sourceLength) {
            throw new InvalidSourceException('Found multiple entities outside list or dict definitions');
        }

        return $decoded;
    }

    private function doDecode()
    {
        switch ($this->getChar()) {

            case 'i':
                ++$this->offset;

                return $this->decodeInteger();

            case 'l':
                ++$this->offset;

                return $this->decodeList();

            case 'd':
                ++$this->offset;

                return $this->decodeDict();

            default:
                if (ctype_digit($this->getChar())) {
                    return $this->decodeString();
                }

        }

        throw new InvalidSourceException("Unknown entity found at offset $this->offset");
    }

    private function decodeInteger(): int
    {
        $offsetOfE = strpos($this->source, 'e', $this->offset);
        if (false === $offsetOfE) {
            throw new InvalidSourceException("Unterminated integer entity at offset $this->offset");
        }

        $currentOffset = $this->offset;
        if ('-' === $this->getChar($currentOffset)) {
            ++$currentOffset;
        }

        if ($offsetOfE === $currentOffset) {
            throw new InvalidSourceException("Empty integer entity at offset $this->offset");
        }

        while ($currentOffset < $offsetOfE) {
            if (!ctype_digit($this->getChar($currentOffset))) {
                throw new InvalidSourceException(
                    "Non-numeric character found in integer entity at offset $this->offset"
                );
            }
            ++$currentOffset;
        }

        $value = substr($this->source, $this->offset, $offsetOfE - $this->offset);

        $absoluteValue = (string)abs($value);
        if (1 < strlen($absoluteValue) && '0' === $value[0]) {
            throw new InvalidSourceException("Illegal zero-padding found in integer entity at offset $this->offset");
        }

        $this->offset = $offsetOfE + 1;

        return (int)$value + 0;
    }

    private function decodeString(): string
    {
        if ('0' === $this->getChar() && ':' !== $this->getChar($this->offset + 1)) {
            throw new InvalidSourceException(
                "Illegal zero-padding in string entity length declaration at offset $this->offset"
            );
        }

        $offsetOfColon = strpos($this->source, ':', $this->offset);
        if (false === $offsetOfColon) {
            throw new InvalidSourceException("Unterminated string entity at offset $this->offset");
        }

        $contentLength = (int)substr($this->source, $this->offset, $offsetOfColon);
        if (($contentLength + $offsetOfColon + 1) > $this->sourceLength) {
            throw new InvalidSourceException("Unexpected end of string entity at offset $this->offset");
        }

        $value        = substr($this->source, $offsetOfColon + 1, $contentLength);
        $this->offset = $offsetOfColon + $contentLength + 1;

        return $value;
    }

    private function decodeList(): array
    {
        $list       = [];
        $terminated = false;
        $listOffset = $this->offset;

        while (null !== $this->getChar()) {
            if ('e' === $this->getChar()) {
                $terminated = true;
                break;
            }

            $list[] = $this->doDecode();
        }

        if (false === $terminated && null === $this->getChar()) {
            throw new InvalidSourceException("Unterminated list definition at offset $listOffset");
        }

        $this->offset++;

        return $list;
    }

    private function decodeDict(): array
    {
        $dict       = [];
        $terminated = false;
        $dictOffset = $this->offset;

        while (null !== $this->getChar()) {
            if ('e' === $this->getChar()) {
                $terminated = true;
                break;
            }

            $keyOffset = $this->offset;
            if (false === ctype_digit($this->getChar())) {
                throw new InvalidSourceException("Invalid dictionary key at offset $keyOffset");
            }

            $key = $this->decodeString();
            if (isset($dict[$key])) {
                throw new InvalidSourceException("Duplicate dictionary key at offset $keyOffset");
            }

            $dict[$key] = $this->doDecode();
        }

        if (false === $terminated && null === $this->getChar()) {
            throw new InvalidSourceException("Unterminated dictionary definition at offset $dictOffset");
        }

        $this->offset++;

        return $dict;
    }

    private function getChar(int $offset = null): ?string
    {
        if (null === $offset) {
            $offset = $this->offset;
        }

        if (empty($this->source) || $this->offset >= $this->sourceLength) {
            return null;
        }

        return $this->source[$offset];
    }
}