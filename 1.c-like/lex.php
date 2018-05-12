<?php

class LexNode {
    function __construct($type, $word, $line) {
        $this->type = $type;
        $this->word = $word;
        $this->line = $line;
    }
}

// token types are
//     word
//     string
//     number: 1 1.0 1.0f 0xFF
//     operator
//         ()[]{}
//         other
//     lf:  new line
//     comment
// currently this function parses one line, but it can easily change to parse a whole file
function lex($code) {
    // https://en.wikipedia.org/wiki/Escape_sequences_in_C
    $escape_map = [
        "n" =>	"\n",//	Newline (Line Feed); see notes below
        "r" =>	"\r",//	Carriage Return
        "t" =>	"\t",//	Horizontal Tab
        "v" =>	"\v",//	Vertical Tab
        "\\" =>	"\\",//	Backslash
        "'" =>	"'",//	Single quotation mark
        "\"" =>	'"',//	Double quotation mark
    ];
    $operator_reg = '/^[-`~!@#$%^&*+=\\\\|\'\;:\'"<>,.?\/]$/'; // no _[]{}()

    $n = strlen($code);
    $state = ""; // in word/string/number/operator/lf/comment
    $ret = [];
    $i = 0;
    $line = 1;
    while ($i<$n) {
        $c = $code[$i];
        if ($state=='') {
            if (preg_match('/^\s$/i', $c)) {
                if ($c==="\n") $line++;
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
                $ret[] = new LexNode("operator",$c, $line);
            } else if (preg_match($operator_reg, $c)) {
                $state = 'operator';
                $word = $c;
            }
        } else if ($state=='word') {
            if (preg_match('/^[\w_]$/', $c)) {
                $word.=$c;
            } else {
                $ret[]=new LexNode('word',$word, $line);
                $i--; $state='';
            }
        } else if ($state=='string') {
            if ($c==="\n") $line++;
            // \t\b\v and so on
            if ($c == "\\") {
                $i++;
                $esc=$code[$i]; // lack of error proc
                $word .= ($escape_map[$esc]); // lack of error proc
            } else if ($c == '"') {
                $ret[] = new LexNode('string', $word, $line);
                $state='';
            } else {
                $word .= $c;
            }
        } else if ($state=='number') {
            if (preg_match('/^[\w.]$/i', $c)) {
                $word.=$c;
            } else {
                $ret[] = new LexNode('number', $word, $line);
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
                $ret[] = new LexNode('operator', $word, $line);
                $i--; $state = '';
            }
        } else if ($state=='comment') {
            if ($c=="\n") {
                $line++;
                $state='';
                $ret[] = new LexNode('comment', $word, $line);
            } else {
                $word.=$c;
            }
        } // lack of error proc
        $i++;
    }
    if ($word !== '' && $state)
        $ret[] = new LexNode($state, $word, $line);

    return $ret;
}

