<?php
include ("db.php");

function CheckEmail ($Email = "")
{
    if (ereg("[[:alnum:]]+@[[:alnum:]]+\.[[:alnum:]]+", $Email)) {
        return true;
    } else {
        return false;
    }
}

function RandomString ($length = 32)
{
    $randstr = '';
    srand((double) microtime() * 1000000);
    
    $chars = array('a' , 'b' , 'c' , 'd' , 'e' , 'f' , 'g' , 'h' , 'i' , 'j' , 'k' , 'l' , 'm' , 'n' , 'o' , 'p' , 'q' , 'r' , 's' , 't' , 'u' , 'v' , 'w' , '1' , '2' , '3' , '4' , '5' , '6' , '7' , '8' , '9' , '0' , '!' , '@' , '#' , '$' , '%' , '&');
    for ($rand = 0; $rand <= $length; $rand ++) {
        $random = rand(0, count($chars) - 1);
        $randstr .= $chars[$random];
    }
    return $randstr;
}

function encrypt_decrypt ($string)
{
    $length = strlen($string);
    $str_encrypted_message = "";
    for ($pos = 0; $pos < $length; $pos ++) {
        $key_to_use = (($length + $pos) + 1); // (+5 or *3 or ^2)
        //after that we need a module division because can´t be greater than 255
        $key_to_use = (255 + $key_to_use) % 255;
        $byte_to_be_encrypted = substr($string, $pos, 1);
        $ascii_num_byte_to_encrypt = ord($byte_to_be_encrypted);
        $xored_byte = $ascii_num_byte_to_encrypt ^ $key_to_use; //xor operation
        $encrypted_byte = chr($xored_byte);
        $str_encrypted_message .= $encrypted_byte;
    }
    
    return $str_encrypted_message;
}

function deleteDir ($dir)
{
    $dhandle = opendir($dir);
    
    if ($dhandle) {
        while (false !== ($fname = readdir($dhandle))) {
            if (is_dir("{$dir}/{$fname}")) {
                if (($fname != '.') && ($fname != '..'))
                    deleteDir("$dir/$fname");
            } else
                unlink("{$dir}/{$fname}");
        }
        closedir($dhandle);
    }
    rmdir($dir);
}

function notBlank($vars, $source, &$message) {
    global ${$source};
    $blank = false;
    
    foreach ($vars as $key => $variable) {
        if(!trim(${$source}[$variable])) {
            $blank = true;
            $message .= " - {$variable};" . '\n';
        }
    }
    
    return $blank;
}

function verificaThumb ($path, $imagine, $dimensiune)
{
    $literalSize = array(
        50 => 'small',
        100 => 'medium',
        200 => 'big'
    );
    $_thumb = "../public/thumbs/{$literalSize[$dimensiune]}/$imagine";
    if ($imagine && file_exists($path . $imagine)) {
        if (! file_exists($_thumb)) {
            $thumb = new thumbnail($path . $imagine);
            $thumb->size_width($dimensiune);
            $thumb->jpeg_quality(100);
            $thumb->save($_thumb);
            chmod($_thumb, 0777);
        }
        $config = MyShop_Config::getInstance();
        return array('thumb' => str_replace('../public/', "http://{$config->DOMENIU_SITE}/", $_thumb), 'details' => getimagesize($_thumb));
    } else {
        return null;
    }
}

function pageListing ($total, $span, $currentPage)
{
    $i = 0;
    $j = 1; //minimum radius
    $pages = array($currentPage); //put current page
    $cate = min($total, (2 * $span) + 1) - 1;
    
    while ($i < $cate) {
        if (($currentPage - $j) > 0) {
            $pages[] = $currentPage - $j;
            $i ++;
        }
        
        if (($currentPage + $j) <= $total) {
            $pages[] = $currentPage + $j;
            $i ++;
        }
        
        $j ++;
    }
    
    sort($pages);
    return $pages;
}
?>