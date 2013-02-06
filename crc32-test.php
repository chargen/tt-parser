<?php

$val = 'banaan';
$c = crc32($val);
$h = hash("crc32b", $val);


print('CRC32: ' . $c . "\n");
print('hex: ' . sprintf('0x%08x', $c) . "\n");
print("\n");

print('Via Hask: ' . $h . "\n");
$i = intval($h, 16);
print('hex: ' . sprintf('0x%08x', $i) . "\n");
print('Dec: ' . $i . "\n");

// Both hex lines should have the same value
// The final 'Dec:' line should have the same value as the top 'CRC32'


