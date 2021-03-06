<?php
define('IN_PAGE', 1);
define('BLANKSPACE',
        (int)0x89A408C6); // Cast for x86 vs x64 compatibility

$map = include 'map.php';
$drawing = include 'drawing.php';

$page = $_SERVER["QUERY_STRING"];
if (!$page || !preg_match('/^\?*[1-8][0-9][0-9](_[0-9][0-9])?$/', $page))
{
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit('Not Found');
}
$debug_level = substr_count($page, '?');
$page = str_replace('?','',$page);
if (strlen($page) == 3)
  $page .= '_01';

$img = @imagecreatefromgif('http://nos.nl/data/teletekst/gif/P' .
                          $page . '.gif');

if (!$img)
{
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  exit('Page not found');
}

header('Content-Type: text/html; charset=iso-8859-1');
print('<!DOCTYPE html><html><head><title>TT-OCR: ' . $page .
      '</title>' .
      '<link rel="stylesheet" type="text/css" href="tt.css" />' .
      "</head><body><pre>\n");

print(' ' . substr($page, 0, 3));
if (substr($page, 4) == '01')
  print('   ');
else if (substr($page, 4, 1) == '0')
  print('/' . substr($page, 5) . ' ');
else
  print('/' . substr($page, 4));

print('  <span class="fg_0F0 bg_000">NOS-TT  </span>');
$tm = time();
$t = localtime($tm, true);
$days = ['zo','ma','di','wo','do','vr','za'];
$months = ['jan', 'feb', 'maa', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep',
           'okt', 'nov', 'dev'];
$day = $days[$t['tm_wday']];
print('<span class="fg_FF0 bg_000">' .
		  sprintf('%s %2d %s %d %02d:%02d:%02d', 
			  $days[$t['tm_wday']],
		          $t['tm_mday'],
			  $months[$t['tm_mon']],
			  $t['tm_year'] + 1900,
			  $t['tm_hour'],
			  $t['tm_min'],
			  $t['tm_sec']) . "</span>\n");
     			  
$img_width = imagesx($img);
$img_height = imagesy($img);

$char_width = $img_width / 40;
$char_height = $img_height / 24;
$char_pixels = $char_width * $char_height;

function tt_charinfo($img, $y, $x)
{
  global $char_height, $char_width, $char_pixels;
  $y_off = $y * $char_height;
  $x_off = $x * $char_width;

  $c1 = imagecolorat($img, $x_off, $y_off);
  $c2 = -1;

  $c1_cnt = 0;
  $v = 0;
  $t = '';

  for ($xx = 0; $xx < $char_width; $xx++)
  {
    $v = 0;
    for ($yy = 0; $yy < $char_height; $yy++)
    {
      $c = imagecolorat($img, $x_off+$xx, $y_off+$yy);
      if ($c === $c1)
      {
        $c1_cnt++;
        $d = 0;
      }
      else
      {
        $c2 = $c;
        $d = 1;
      }
      $v = $v << 1 | $d;
    }
    $t .= sprintf('%04x', $v);
  }

  if ($c1_cnt > ($char_pixels * 20 / 11))
  {
    $tmp = $c2;
    $c2 = $c1;
    $c1 = $tmp;
    $c1_cnt = $char_pixels - $c1_cnt;
  }
  if ($c1_cnt > $char_pixels / 5)
    $fb_char = '*';
  else if ($c1_cnt > $char_pixels / 8)
    $fb_char = '.';
  else
    $fb_char = ' ';
    
  return array(
		'hash' 		=> crc32($t),
                'bg'		=> $c1,
		'fg'		=> $c2,
		'fb_char'	=> $fb_char,
              );
}

function clrstr($img, $clr)
{
  if ($clr < 0)
    return "FFF";

  $info = imagecolorsforindex($img, $clr);
  return sprintf('%X%X%X', $info['red']   / 16,
                           $info['green'] / 16,
                           $info['blue']  / 16);
}

$debug = array();
$lower_data = [];

$default_bg = imagecolorat($img, 0, 0);
$default_fg = imagecolorclosest($img, 0xFF, 0xFF, 0xFF);

if (clrstr($img, $default_fg) != 'FFF')
  $default_fg = -1;

for ($c_y = 0; $c_y < 24; $c_y++)
{
  $last_fg = $default_fg;
  $last_bg = $default_bg;
  $span_open = false;
  for ($c_x = 0; $c_x < 40; $c_x++)
  {
    $info = tt_charinfo($img, $c_y, $c_x);

    $bg = $info['bg'];
    $fg = $info['fg'];
    $key = $info['hash'];
    $fb_char = $info['fb_char'];

    unset($c);
    if (isset($map[$key]))
    {
       $r = $map[$key];

       if (is_array($r))
       {
         if ($c_y+1 < 24)
         {
           $lower_info = tt_charinfo($img, $c_y+1, $c_x);
	   $lower_key = $lower_info['hash'];
           if (isset($r[$lower_key]))
           {
             $c = $r[$lower_key];

             // Store information for when processing the next row
             $lower_data[(100*($c_y+1) + $c_x)] = [' ', $fg, $bg];
           }
           else if ($key === BLANKSPACE)
             $c = ' ';
           else if ($debug_level > 0)
	    $fb_char .= '<!--L:' . sprintf('0x%08X', $lower_key) . '-->';
         }
         else if ($key === BLANKSPACE)
           $c = ' ';
       }
       else
         $c = $r;
    }
    else if (isset($lower_data[(100 * $c_y + $c_x)]))
    {
      $r = $lower_data[(100 * $c_y + $c_x)];
      $c = $r[0];
      $fg = $r[1];
      $bg = $r[2];
    }

    if (! isset($c))
    {
      if ($debug_level > 0)
        $c = $fb_char . '<!--' . sprintf('0x%08X', $key) . '-->';
      else
	$c = $fb_char;

      if ($debug_level > 0 
          && !isset($debug[$key]) 
          && ($debug_level > 1 || !isset($drawing[$key])))
      {
         $tmp = imagecreate($char_width, $char_height);

         $clrinfo = imagecolorsforindex($img, $bg);
         $bg_index = imagecolorallocate($tmp, $clrinfo['red'],
                                              $clrinfo['green'],
                                              $clrinfo['blue']);
	 $fg_index = -1;
         if ($fg !== -1)
         {
           $clrinfo = imagecolorsforindex($img, $fg);
           $fg_index = imagecolorallocate($tmp, $clrinfo['red'],
                                                $clrinfo['green'],
                                                $clrinfo['blue']);
         }
    
         imagecopy($tmp, $img, 0, 0,
                   $c_x * $char_width, $c_y * $char_height,
                   $char_width, $char_height);
         imagecolorset($tmp, $bg_index, 0, 0, 0);
         if (isset($fg_index))
	   imagecolorset($tmp, $fg_index, 0xFF, 0xFF, 0xFF);

         ob_start();
           imagegif($tmp);
           $gifdata = ob_get_contents();
         ob_end_clean();
         imagedestroy($tmp);

         $debug[$key] = '<img title="' . sprintf('0x%X', $key) . '" '
                            . 'src="data:image/gif;base64,'
                             . base64_encode($gifdata) . '" />'
                        . sprintf(' 0x%08X', $key);
	 
      }
    }

    if ($fg !== -1 && $fg !== $last_fg)
    {
      if ($span_open)
        print('</span>');

      print('<span class="fg_' . clrstr($img, $fg) . ' bg_' . clrstr($img, $bg) . '">');
      $last_fg = $fg;
      $last_bg = $bg;
      $span_open = true;
    }
    else if ($bg !== $last_bg)
    {
      if ($span_open)
        print('</span>');

      print('<span class="fg_' . clrstr($img, $last_fg) . ' bg_' . clrstr($img, $bg) . '">');
      $last_bg = $bg;
      $span_open = true;
    }
    print($c);
  }
  if ($span_open)
    print('</span>');
  print("\n");
}
print("</pre>");

foreach ($debug as &$line)
{
  print("\n<p>" . $line . "</p>");
}
print ("</body></html>\n");