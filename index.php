<?php
$page = $_SERVER["QUERY_STRING"];
if (!$page || !preg_match('/^[1-8][0-9][0-9](_[0-9][0-9])?$/', $page))
{
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit('Not Found');
}
if (strlen($page) == 3)
  $page .= '_01';

$img = @imagecreatefromgif('http://nos.nl/data/teletekst/gif/P' .
                          $page . '.gif');

if (!$img)
{
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit('Page not found');
}

//if (!$image)
//  header('HTTP/1.0 404 Not found');

header('Content-Type: text/html; charset=iso-8859-1');
print('<!DOCTYPE html><html><head><title>TT-OCR: ' . $page .
      '</title>' .
      '<link rel="stylesheet" type="text/css" href="tt.css" />' .
      "</head><body><pre>\n");

$img_width = imagesx($img);
$img_height = imagesy($img);

$width = $img_width / 40;
$height = $img_height / 24;

$default_bg = imagecolorat($img, 0, 0);
$white = imagecolorexact($img, 255, 255, 255);

$map = array(
		3534201612	=> '0',
		2235723926	=> '1',
		2329124304	=> '2',
		595964101	=> '3',
		3397878754	=> '4',
		1295270320	=> '5',
		4029715854	=> '6',
		2650246530	=> '7',
		2970398899	=> '8',
		3804151092	=> '9',

		1601299259	=> 'a',
		1606857371	=> 'b',
		1624669055	=> 'c',
		891381801	=> 'd',
		21449581	=> 'e',
		3761489114	=> 'f',
		3797368716	=> 'g',
		2627774917	=> 'h',
		1616125723	=> 'i',
		3191861198	=> 'j',
		1879122060	=> 'k',
		2081484705	=> 'l',
		1618099120	=> 'm',
		3061040568	=> 'n',
		2587406942	=> 'o',
		2393273215	=> 'p',
		1910007434	=> 'q',
		2371038366	=> 'r',
		388093892	=> 's',
		3777625267	=> 't',
		235221980	=> 'u',
		4187802139	=> 'v',
		4117684883	=> 'w',
		3399911844	=> 'x',
		727964424	=> 'y',
		3707866732	=> 'z',

		2022465125	=> 'A',
		2747550388	=> 'B',
		1059698095	=> 'C',
		389073583	=> 'D',
		551612243	=> 'E',
		1006166086	=> 'F',
		1187307283	=> 'G',
		2774752771	=> 'H',
		234704744	=> 'I',
		3777662778	=> 'J',
		4201598871	=> 'K',
		793243503	=> 'L',
		3491154039	=> 'M',
		1998964204	=> 'N',
		64510916	=> 'O',
		1569695062	=> 'P',
		2131267931	=> 'Q',
		3271437817	=> 'R',
		1145680575	=> 'S',
		361443753	=> 'T',
		1914321993	=> 'U',
		4236332421	=> 'V',
		4294095100	=> 'W',
		2707061704	=> 'X',
		4232076185	=> 'Y',
		2020628638	=> 'Z',

		// Accenten
		598866701	=> 'A', // A`
		2078918337	=> 'A', // A'
		200394543	=> 'A', // A^
		2295066833	=> 'A', // A~
		1557746877	=> 'A', // A"
		1355724745	=> 'A', // Ao

		456947221	=> 'a', // a`
		6819758		=> 'a', // a'
		739015281	=> 'a', // a^
		2939571599	=> 'a', // a~
		1266440680	=> 'a', // a"
		2818446585	=> 'a', // ao

		2796177511	=> 'C', // C,
		2046047862	=> 'c', // c,

		270981835	=> 'E', // E`
		595949559	=> 'E',	// E' en E^
	        79029643	=> 'E', // E"

		1845299908	=> 'e', // e`
		1583261688	=> 'e', // e'
		1916457511	=> 'e', // e^
		3097041203	=> 'e', // e"

		1455483392	=> 'I', // I`
		244897740	=> 'I', // I'
		1027308272	=> 'I', // I^
		698893744	=> 'I', // I"

		3706810004	=> 'i', // i`
		4015178664	=> 'i', // i'
		3275142775	=> 'i', // i^
		2756089326	=> 'i', // i"

		299678710	=> 'N', // N~
		1177840396	=> 'n', // n~


		1491467948	=> 'O', // O`
		12436320	=> 'O', // O'
		857632348	=> 'O', // O^
		2955172258	=> 'O', // O~
		663572764	=> 'O', // O"


		3732219760	=> 'o', // o`
		3307298507	=> 'o', // o'
		3913624340	=> 'o', // o^
		1786715370	=> 'o', // o~
		2385840269	=> 'o', // o"
		3153423524	=> 'o', // o/


		1525541070	=> 'U', // U`
		45470978	=> 'U', // U'
		12717237	=> 'U', // U^
		1448031889	=> 'U', // U"


		// Leestekens
		733607649	=> ',',
		4057946933	=> '.',
		3456988079	=> '?',
		1526085451	=> ':',
		4234077017	=> '-',
		3589110848	=> '@',
		2471052989	=> '@', // Section Sign (misbruikt voor @)
		1462658754	=> '(',
		757107923	=> ')',
		1708095885	=> '/',
		112795658	=> '\'', // variant 1
		820081149	=> '\'', // variant 2
		3946750139	=> '`',
		2382245462	=> '"',
		2800407841	=> '!',
		1458717149	=> '+',
		2359107608	=> '%',
		2366078462	=> '*',
                2670402817	=> '=',
		3288699018	=> '\'', // Degree sign
		3283145428	=> '*', // (big) middot

		// HTML Special
		923248255	=> '&amp;',
		3289748683	=> '&gt;',

		// Special layout
		1848220626 => ' ',
		544517146  => ' ',
		3005453317 => '=',
		2774347164 => '-',

		2434459971 => ' ', // Blokje. Waarschijnlijk NOS fout

		// Dubbelhoog (boven, onder):
		2303330837	=> 'a',		1871365943	=> '_',
		1553042905	=> 'b',		1660741547	=> '_',
		111131200	=> 'c',		2633084922	=> '_',
		2111243400	=> 'd',		2804931794	=> '_',
		3558099829	=> 'e',		920189195	=> '_',
		2049325299	=> 'f',		3600288543	=> '_',
		1844284599	=> 'g',		2779137480	=> '_',
		3386445281	=> 'h',		/* dubbel */
		667875755	=> 'i',		2415782771	=> '_',
		3593084993	=> 'j',		2140645774	=> '_',
		2768013269	=> 'k',		2430345007	=> '_',
		1579717012	=> 'l',		2415782771	=> '_',
		3100060838	=> 'm',		1269614032	=> '_',
		987280161	=> 'n',		4251263503	=> '_',
		3882511460	=> 'o',		1891312526	=> '_',
		2945463065	=> 'p',		1760815684	=> '_',

		430451505	=> 'r',		91748890	=> '_',
		1618045528	=> 's',		2065700877	=> '_',
		2363339845	=> 't',		1462553170	=> '_',
		1318126267	=> 'u',		2167595504	=> '_',
		2533221982	=> 'v',		2235084808	=> '_',
		2408739356	=> 'w',		2559406702	=> '_',

		1483543161	=> 'y',		1479956870	=> '_',
		1267219180	=> 'z',		1398012093	=> '_',


		1371273178	=> 'A',		1397357680	=> '_',

		1072536011	=> 'C',		3833747684	=> '_',
		4068645470	=> 'D',		2523085319	=> '_',
		343830190	=> 'E',		2766905296	=> '_',
		221390984	=> 'F',		/* dubbel */		
		1213201136	=> 'G',		3029486539	=> '_',
		1521157565	=> 'H',		2854099499	=> '_',
		999010000	=> 'I',		376696587	=> '_',

		610986023	=> 'K',		1330120699	=> '_',
		2277814841	=> 'L',		2959542162	=> '_',
		4249161253	=> 'M',		3314112451	=> '_',
		291836899	=> 'N',		4005510657	=> '_',
		3900554684	=> 'O',		921255146	=> '_',
		1152207128	=> 'P',		3948807141	=> '_',

		962130330	=> 'R',		2154220368	=> '_',
		3303148246	=> 'S',		1350720031	=> '_',
		3427240991	=> 'T',		3200574446	=> '_',
		3594318532	=> 'U',		1896191416	=> '_',
		2804691015	=> 'V',		1244038579	=> '_',
		2807615982	=> 'W',		614074234	=> '_',
		3649136027	=> 'Z',		2150175376	=> '_',
		

		1465935156	=> 'e', // e"

		2746607677	=> '-',		3682803836	=> '_',

		2922836892	=> '0',		1709140618	=> '_',
		1101032990	=> '1',		4191979695	=> '_',
		731502949	=> '2',		2477268762	=> '_',
		1202529696	=> '9',		2062464750	=> '_',

		3211974510	=> '\'',
		2072273752	=> ':',		4136218993	=> '_',
            );

$clr_swap = array(
		1269614032 => '1', // Onderkant dubbele m
		3314112451 => '1', // Onderkant dubbele M
		921255146  => '1', // Onderkant dubbele O
	);

for ($c_y = 0; $c_y < 24; $c_y++)
{
  $y_off = $c_y * $height;
  $last_fg = $white;
  $last_bg = $default_bg;
  $span_open = false;
  for ($c_x = 0; $c_x < 40; $c_x++)
  {
    $x_off = $c_x * $width;

    $cmp = imagecolorat($img, $x_off, $y_off);
    $alt = -1;
    $s = '';
    
    for ($x = 0; $x < $width; $x++)
      for ($y = 0; $y < $height; $y++)
      {
        $c = imagecolorat($img, $x_off+$x, $y_off+$y);
        if ($c === $cmp)
          $s .= '0';
        else
        {
          $s .= '1';
          $alt = $c;
        }
      }
    $key = sprintf('%u', crc32($s));

    if (isset($map[$key]))
    {
       $r = $map[$key];

       if (isset($clr_swap[$key]))
       {
         $tmp = $alt;
         $alt = $cmp;
         $cmp = $tmp;
       }

       if ($r !== ' ' && $alt !== $last_fg)
       {
         if ($span_open)
           print('</span>');
         $clrinfo = imagecolorsforindex($img, $alt);
         $clrtxt = sprintf('%X%X%X', $clrinfo['red']   / 16,
                                     $clrinfo['green'] / 16,
                                     $clrinfo['blue']  / 16);

         $clrinfo = imagecolorsforindex($img, $cmp);
         $clrbg = sprintf('%X%X%X', $clrinfo['red']   / 16,
                                    $clrinfo['green'] / 16,
                                    $clrinfo['blue']  / 16);
         print('<span class="fg_' . $clrtxt . ' bg_' . $clrbg . '">');
         $last_fg = $alt;
	 $last_bg = $cmp;
         $span_open = true;
       }
       else if ($cmp !== $last_bg)
       {
         if ($span_open)
           print('</span>');
         $clrinfo = imagecolorsforindex($img, $last_fg);
         $clrtxt = sprintf('%X%X%X', $clrinfo['red']   / 16,
                                     $clrinfo['green'] / 16,
                                     $clrinfo['blue']  / 16);

         $clrinfo = imagecolorsforindex($img, $cmp);
         $clrbg = sprintf('%X%X%X', $clrinfo['red']   / 16,
                                    $clrinfo['green'] / 16,
                                    $clrinfo['blue']  / 16);
         print('<span class="fg_' . $clrtxt . ' bg_' . $clrbg . '">');
         $last_bg = $cmp;
         $span_open = true;
       }

       print($r);
    }
    else
    {
       print('.');

       print('<!--' . $key . '-->');

//       $tmp = imagecreatetruecolor($width, $height);
//       imagecopy($tmp, $img, 0, 0, $x_off, $y_off, $width, $height);
//       imagepng($tmp, 'letters/' . $key . '.png');

//       imagedestroy($tmp);
    }
  }
  if ($span_open)
    print('</span>');
  print("\n");
}
print("</pre></body></html>\n");