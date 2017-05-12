<?php

namespace Akatsuki\Component\Bencode;

/**
 * Interface BencodeSerializable
 *
 * @package Akatsuki\Component\Bencode
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface BencodeSerializable
{
    public function bencodeSerialize(): array;
}