<?php

// https://en.wikipedia.org/wiki/Escape_sequences_in_C
$escape_map = [
    "a"=>	0x07,//	Alert (Beep, Bell) (added in C89)[1]
    "b"=>	0x08,//	Backspace
    "f"=>	0x0C,//	Formfeed
    "n"=>	0x0A,//	Newline (Line Feed); see notes below
    "r"=>	0x0D,//	Carriage Return
    "t"=>	0x09,//	Horizontal Tab
    "v"=>	0x0B,//	Vertical Tab
    "\\"=>	0x5C,//	Backslash
    "'"=>	0x27,//	Single quotation mark
    "\""=>	0x22,//	Double quotation mark
];

// token types are
//     word
//     string
//     number: 1 1.0 1.0f 0xFF
//     operator
//         ()[]{}
//         other
//     comment
// currently this function parses one line, but it can easily change to parse a whole file
function lex_line($line) {
    global $escape_map;
    $operator_reg = '/^[-`~!@#$%^&*+=\\\\|\'\;:\'"<>,.?\/]$/'; // no _[]{}()

    $n = strlen($line);
    $state = ""; // in word/string/number/operator/comment
    $ret = [];
    $i = 0;
    while ($i<$n){
        $c = $line[$i];
        if ($state=='') {
            if (preg_match('/^\s$/i', $c)) {
                // do nothing
            } elseif (preg_match('/^[a-z_]$/i', $c)) {
                $state='word';
                $word = $c;
            } else if (preg_match('/^"$/', $c)) {
                $word = '';
                $state = 'string';
            } else if (preg_match('/^[0-9]$/', $c)) {
                $state='number';
                $word=$c;
            } else if (preg_match('/^[\[\](){}]$/', $c)) {
                $ret[] = ["operator",$c];
            } else if (preg_match($operator_reg, $c)) {
                $state = 'operator';
                $word = $c;
            }
        } else if ($state=='word') {
            if (preg_match('/^[\w_]$/', $c)) {
                $word.=$c;
            } else {
                $ret[]=['word',$word];
                $i--; $state='';
            }
        } else if ($state=='string') {
            // \t\b\v and so on
            if ($c == '\\') {
                $i++;
                $esc=$line[$i]; // lack of error proc
                $word .= chr($escape_map[$esc]); // lack of error proc
            } else if ($c == '"') {
                $ret[] = ['string', $word];
                $state='';
            } else {
                $word .= $c;
            }
        } else if ($state=='number') {
            if (preg_match('/^[\w.]$/i', $c)) {
                $word.=$c;
            } else {
                $ret[] = ['number', $word];
                $i--; $state='';
            }
        } else if ($state=='operator') {
            if (preg_match($operator_reg, $c)) {
                $word .= $c;
                if ($word=="//") {
                    $state="comment";
                    $word = '';
                }
            } else {
                $ret[] = ['operator', $word];
                $i--; $state = '';
            }
        } else if ($state=='comment') {
            $word.=$c;
        } // lack of error proc
        $i++;
    }
    if ($word !== '' && $state)
        $ret[] = [$state, $word];

    return $ret;
}

