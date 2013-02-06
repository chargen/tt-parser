<?php

$val = 'banaan12';
$c = crc32($val);
$h = hash("crc32b", $val);


print('CRC32: ' . $c . "\n");
print('hex: ' . sprintf('0x%08x', $c) . "\n");
print('DecHex: ' .dechex($c) . "\n");
print('Dec: ' . $c . "\n");
print("\n");

print('Via Hash: ' . $h . "\n");
$i = intval($h, 16);
print('hex: ' . sprintf('0x%08x', $i) . "\n");
print('DecHex: ' .dechex($i) . "\n");
print('Dec: ' . $i . "\n");

