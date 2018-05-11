<?php

if (!isset($argv[1])) {
    echo "Usage: $argv[0] <file>\n";
    exit(1);
}

$file = $argv[1];
if (!is_file($file)) {
    echo "$file not file\n";
    exit(1);
}

$state = "";
$line_no = 0;
$cur_node = null; // null also means root here
$stmts = [];
$if_mode = "then";
foreach(file($file) as $_line_no => $line_raw) {
    $line_no = $_line_no+1;
    $line = trim($line_raw);
    if (strlen($line) >=2 && strpos($line, '//') === 0) {
        continue;
    }
    $tokens = lex_line( $line );
    if (empty($tokens)) continue;
    if ($tokens[0] == 'func') {
        if (!isset($tokens[1])) {
            echo "$line_no: func must have a name\n";
            exit(1);
        }
        if ($tokens[1] == 'op') {
            match_func_op($tokens);
        } else {
            match_func($tokens);
        }
    } else if ($tokens[0] == 'if') {
        match_if($tokens);
    } else if ($tokens[0] == 'for') {
        match_for($tokens);
    } else if ($tokens[0] == '}' && $tokens[1] == 'else') {
        match_else($tokens);
    } else if ($tokens[0] == '}') {
        $cur_node = close_node();
    } else {
        // expr
        match_expr();
    }
}

function lex_line($line) {
    $a = preg_split('/\b/', $line);
    $ret = [];
    $word = '';
    // compose op
    foreach ($a as $key => $value) {
        if (trim($value) === '') {
            continue;
        } else if (preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            if ($word !== '') {
                $ret[] = $word;
                $word = '';
            }
            $ret[] = $value;
        } else {
            $word .= $value;
            if ($word == '//') break; // comment
        }
    }
    return $ret;
}

function close_node() {
    global $state;
    global $line_no;
    global $stmts;
    global $cur_node;
    if ($cur_node->parent == null) {
        $stmts[] = $cur_node;
        $cur_node = null;
    } else {
        $cur_node = $cur_node->parent;
    }
}

function match_func($tokens) {
    global $line_no;
    global $cur_node;
    $node = new AST_Node('func', $line_no, $cur_node);
    if (match("func 'f()", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = [];
        $node->reciever = null;
    } else if (match("func ('r) 'f()", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = [];
        $node->reciever = $m['r'];
    } else if (match("func 'f(',p+)", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = $m['p'];
        $node->reciever = null;
    } else if (match("func ('r) 'f(',p+)", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = $m['p'];
        $node->reciever = $m['r'];
    } else {
        echo "$line_no: func must be func a() or func (r)a()\n";
        exit(1);
    }
    return $node;
}

function match_func_op($tokens) {
    global $line_no;
    global $cur_node;
    $node = new AST_Node('func_op', $line_no, $cur_node);
    if (match("func op'f()", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = [];
        $node->reciever = null;
    } else if (match("func op'f(',p+)", $tokens, $m)) {
        $node->name = $m['f'];
        $node->params = $m['p'];
        $node->reciever = null;
    } else {
        echo "$line_no: func op must be func op a()\n";
        exit(1);
    }
    return $node;
}

function match_if($tokens) {
    global $line_no;
    global $cur_node;
    global $if_mode;
    $if_mode = "then";
    $node = new AST_Node('if', $line_no, $cur_node);
    if (match("if('e:expr){", $tokens, $m)) {
        $node->cond = $m['e'];
    } else {
        echo "$line_no: if must be if(e)\n";
        exit(1);
    }
    return $node;
}

function match_else($tokens) {
    global $line_no;
    global $cur_node;
    global $if_mode;
    $if_mode = "else";
    if (match("}else{", $tokens, $m)) {
    } else {
        echo "$line_no: else must be }else{\n";
        exit(1);
    }
    return $node;
}
function match_for($tokens) {
    global $line_no;
    global $cur_node;
    global $if_mode;
    $node = new AST_Node('for', $line_no, $cur_node);
    if (match("for('e1:expr;'e2:expr;'e3:expr){", $tokens, $m)) {
        $node->init = $m['e1'];
        $node->cond = $m['e2'];
        $node->post = $m['e3'];
    } else if (match("for('lst:expr as 'k=>'v){", $tokens, $m)) {
        $node->lst = $m['lst'];
        $node->k = $m['k'];
        $node->v = $m['v'];
    } else {
        echo "$line_no: for must be for('e1:expr;'e2:expr;'e3:expr){ or for('lst:expr as 'k=>'v){\n";
        exit(1);
    }
    return $node;
}
function match_expr($tokens) {
    global $line_no;
    global $cur_node;
    global $if_mode;
    if (true !== ($msg = _match_expr($tokens, $i=0, $m))) {
        echo "$line_no: $msg\n";
        exit(1);
    }
    return _build_expr_from_match($m, $cur_node);
}

function _build_expr_from_match($m, $parent) {
    global $line_no;
    $node = new AST_Node('expr', $line_no, $parent);
    if (isset($m['a'])) {
        $node->pn = isset($m['b']) ? 2 : 1;
        $node->a = $m['a'];
        $node->op = $m['o'];
        $node->b = isset($m['b']) ? $m['b'] : null;
    } else if (isset($m['f'])) {
        $node->pn = count($m['p']);
        $node->func = $m['f'];
    }
    return $node;
}

function _match_expr($tokens, &$i, &$match) {
    if (_match("'a:expr 'o:op 'b:expr", $tokens, $i, $match)) {
        return true;
    } else if (_match("'o:op 'a:expr", $tokens, $i, $match)) {
        return true;
    } else if (_match("'f(',p:expr)", $tokens, $i, $match)) {
        return true;
    } else {
        return "expr must be 'a:expr 'o:op 'b:expr or 'o:op 'a:expr or 'f(',p:expr)\n";
    }
}

function match($pattern, $tokens, &$match=[]) {
    $i = 0;
    return _match($pattern, $tokens, $i, $match);
}
function _match($pattern, $tokens, &$i, &$match=[]) {
    $pats = _build_match_pattern($pattern);
    foreach ($pats as $key => $pat) {
        $tk = $tokens[$i];
        if ($pat->type == 'literal') {
            if ($tk !== $pat->name) {
                return "$pat->name expect, $tk given";
            }
        } else if ($pat->type == 'symbol') {
            if ($pat->sep !== null) {
                if (true!==($msg=_match_sep($pat->sep, $pat, $tokens, $i)))
                    return $msg;
            } else {
                if (true!==($msg=_match_symbol($pat, $tokens, $i, $match)))
                    return $msg;
            }
        }
        $i++;
    }
    return true;
}
function _match_symbol($pat, $tokens, &$i, &$match) {
    if ($pat->match_type === null) {
        $match[$pat->name] = $tokens[$i];
        $i++;
        return true;
    } else {
        $func = "_match_".$pat->match_type;
        if (true!==($msg=$func($tokens, $i, $match))) {
            return $msg;
        }
        $func = "_build_{$pat->match_type}_from_match";
        $match[$pat->name] = $build_func($match);
    }
    return true;
}
function _build_match_pattern($pattern) {
    $pat = lex_line($pattern);
    $lst = [];
    $cur = null;
    $i = 0;
    for ($i = 0; $i < count($pat); $i++) {
        $p = $pat[$i];
        $node = new PatternNode();
        if ($p == "'") {
            $node->type = 'symbol';
            if (preg_match('/^[a-zA-Z]+[a-zA-Z0-9._]+$/', $pat[$i+1])) {
                $node->name = $pat[$i+1];
                $i++;
            } else {
                $node->sep = $pat[$i+1];
                $node->name = $pat[$i+2];
                $i+=2;
            }
            if (isset($pat[$i+1]) && $pat[$i+1] == ':') {
                $node->match_type = $pat[$i+2];
                $i+=2;
            }
        } else {
            $node->type = 'literal';
            $node->name = $p;
        }
        $lst[] = $node;
    }
    return $lst;
}

class PatternNode {
    public $type;
    public $name;
    public $sep = null;
    public $match_type = null;
    // public function __construct($type, $name) {
    //     $this->type = $type;
    //     $this->name = $name;
    // }
}

class AST_Node {
    public $line_no;
    public $type;
    public $parent = null;
    public function __construct($type, $line_no, $parent) {
        $this->line_no = $line_no;
        $this->type = $type;
        $this->parent = $parent;
    }
}