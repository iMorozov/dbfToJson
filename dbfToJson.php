<?php
function prettyPrint( $json, $tab = "  " ) {
  $result = '';
  $level = 0;
  $in_quotes = false;
  $in_escape = false;
  $ends_line_level = NULL;
  $json_length = strlen( $json );

  for( $i = 0; $i < $json_length; $i++ ) {
    $char = $json[$i];
    $new_line_level = NULL;
    $post = "";
    if( $ends_line_level !== NULL ) {
      $new_line_level = $ends_line_level;
      $ends_line_level = NULL;
    }
    if ( $in_escape ) {
      $in_escape = false;
    }
    elseif( $char === '"' ) {
      $in_quotes = !$in_quotes;
    }
    elseif( ! $in_quotes ) {
      switch( $char ) {
        case '}': case ']':
          $level--;
          $ends_line_level = NULL;
          $new_line_level = $level;
          break;

        case '{': case '[':
          $level++;
        case ',':
          $ends_line_level = $level;
          break;

        case ':':
          $post = " ";
          break;

        case " ": case $tab: case "\n": case "\r":
          $char = "";
          $ends_line_level = $new_line_level;
          $new_line_level = NULL;
          break;
      }
    }
    elseif ( $char === '\\' ) {
      $in_escape = true;
    }
    
    if( $new_line_level !== NULL ) {
      $result .= "\n".str_repeat( $tab, $new_line_level );
    }
    
    $result .= $char.$post;
  } // for
  return $result;
}

function decodeUnicode($s, $output = 'utf-8') { 
    return preg_replace_callback('#\\\\u([a-fA-F0-9]{4})#', function ($m) use ($output) { 
        return iconv('ucs-2be', $output, pack('H*', $m[1])); 
    }, $s); 
}

function dbfToJson($file, $from = 'cp866', $to = 'utf-8') {
  $dbf = dbase_open($file, 0);
  
  if ($dbf) {
    $records = dbase_numrecords($dbf);
    
    for ($i = 1; $i <= $records; $i++) {
      $tmp = dbase_get_record_with_names($dbf, $i);
      foreach ($tmp as $key=>$value) $tmp[$key] = iconv($from, $to, trim($value)); 
      $arr[] = $tmp;
    }
    return str_replace( '\\', '', prettyPrint(decodeUnicode(json_encode($arr))) );
  }
}
