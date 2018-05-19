<?php

namespace match;

/**
 * this->is a helper language for parse
 * 'f          capture a node named f
 * 'f:a_type   capture a node whose type is a_type
 * ',f:a_type* capture a node list whose type is a_type and seperate by `,` and the list can be empty
 * @return true|string
 */
function match($pattern, $tokens, &$match=[]) {
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
        $p_type = $pat[$i][0];
        $p = $pat[$i][1];
        $node = new PatternNode();
        if ($p == "'") {
            $node->type = 'symbol';
            if ('word'==$pat[$i+1][0])) {
                $node->name = $pat[$i+1][1];
                $i++;
            } else {
                $node->sep = $pat[$i+1][1];
                $node->name = $pat[$i+2][1];
                $i+=2;
            }
            if (isset($pat[$i+1]) && $pat[$i+1][1] == ':') {
                $node->match_type = $pat[$i+2][1];
                $i+=2;
            }
            if (isset($pat[$i+1]) && $pat[$i+1][1] == '*') {
                $node->can_be_empty = true;
                $i+=1;
            }
        } else {
            $node->type = 'literal';
            $node->name = $p;
        }
        $lst[] = $node;
    }
    return $lst;
}

function _clear_lf_comment_lex($list) {
    $ret = [];
    $n = count($list);
    for($i=0;$i<$n;$i++) {
        $node = $list[$i];
        if ($node->type == 'lf' && $i-1>=0 && $list[$i-1]->type=='lf') {
            // do nothing
        } else if ($node->type == 'comment') {
            // do nothing
        } else {
            $ret[] = $node;
        }
    }
}
// bnf to expr
function _file_expr() { return new ConcatExpr(_statement_list_expr(), new MatchExpr('lf')); }
function _statement_list_expr() {
    return _concat_expr_list([
        new OptionalExpr(_type('lf')),
        _sep_expr(new MatchExpr('lf'), _statement_expr()),
        new OptionalExpr(_type('lf')),
    ]);
}
function _statement_expr() {
    return _alter_expr_list([
        function(){ return _expr_expr(); },
        new ConcatExpr(new MatchExpr(null,'import'), new MatchExpr('string')),
        _func_define_expr(),
        _type_decl_expr(),]);
}
function _expr_expr() {
    return _alter_expr_list([
        _concat_expr_list([_lit('('), function() {return _expr_expr(); }, _lit(')')]),
        new ConcatExpr(_operator_expr(), _primary_expr_expr()),
        _if_expr(),
        _for_expr(),
        _lit('continue'),
        _lit('break'),
        _lit('return'),
    ]);
}
function _if_expr() {
    return new ConcatExpr(
        _concat_expr_list([
            _lit('if'), _lit('('),
            function () { return _expr_expr(); },
            _lit(')'), _lit('{'),
            _statement_list_expr(),
            _lit('}'),
        ]),
        new OptionalExpr(_concat_expr_list([
            _lit('else'), _lit('{'),
            _statement_list_expr(),
            _lit('}'),
        ]))
    );
}
function _for_expr() {
    return _concat_expr_list([
        _lit('for'), _lit('('),
        new OptionalExpr(function (){ return _expr_expr(); }), _lit(';'),
        new OptionalExpr(function (){ return _expr_expr(); }), _lit(';'),
        new OptionalExpr(function (){ return _expr_expr(); }), 
        _lit('{'), _statement_list_expr(), _lit('}'),
    ]);
}
function _primary_expr_expr() {
    return _alter_expr_list([
        new ConcatExpr(new OptionalExpr(_lit('$')), _type('word')),
        _type('number'),
        _concat_expr_list([_type('word'),_lit('.'),_type('word')]),
        _concat_expr_list([
            new OptionalExpr(_lit('$')),
            _type('word'), _lit('('),
            new OptionalExpr(_sep_expr(_lit(','),function(){ return _expr_expr(); })),
            _lit(')')
        ]),
        _concat_expr_list([_type('word'),_lit('['), _lazy_expr(), _lit(']')),
    ]);
}
function _func_define_expr() {
    return _concat_expr_list([
        _lit('func'),
        new AltExpr(
            _type('word'),
            new ConcatExpr(
                new OptionalExpr(_lit('op')),
                _type('operator')
            )
        ),
        _lit('('), _param_list_expr(), _lit(')'),
        _lit('{'), _statement_list_expr(), _lit('}'),
    ]);
}
function _param_list_expr() {
    $param = _concat_expr_list([
        new OptionalExpr(_lit('&')),
        _type('word'),
        new OptionalExpr(new ConcatExpr(_lit('='), _lazy_expr())),
    ]);
    return _sep_expr(_lit(','), $param);
}
function _type_decl_expr() {
    return _concat_expr_list([
        new AltExpr(_type('word'), _type('operator')),
        _lit('::'),
        _type_decl_expr_expr(),
    ]);
}
function _type_decl_expr_expr() {
    return _alter_expr_list([
        _type('word'),
        _concat_expr_list([
            _lit('('), _param_type_list_expr(), _lit(')'), _lit('->'),
            function() { return _type_decl_expr_expr(); },
        ]),
    ]);
}
function _param_type_list_expr() {
    return _sep_expr(_lit(','), function() { return _type_decl_expr_expr(); });
}

function _lazy_expr() {
    return function() { return _expr_expr(); }
}
function _lit($lit) {
    return new MatchExpr(null, $lit);
}
function _type($type) {
    return new MatchExpr($type, null);
}
function _concat_expr_list($list) {
    if (count($list) <= 1) return $list[0];
    $a = new ConcatExpr($list[0],$list[1]);
    for ($i = 2; $i<count($list);$i++) {
        $a = new ConcatExpr($a, $list[$i]);
    }
}
function _alter_expr_list($list) {
    if (count($list) <= 1) return $list[0];
    $a = new AltExpr($list[0],$list[1]);
    for($i = 2;$i<count($list);$i++) {
        $a = new AltExpr($a,$list[$i]);
    }
    return $a;
}
function _sep_expr($sep, $sub_expr) { // a,a,a
    $sep_ = new ConcatExpr($sep, $sub_expr);
    return new ConcatExpr($sub_expr, new OptionalExpr(new RepeatExpr($sep_)));
}

// from JS version: https://gist.github.com/picasso250/0efc287080664f43eb93
class ConcatExpr { // ab
    function __construct($Left,$Right) {
        $this->Left = Left;$this->Right=Right
    }
}
class AltExpr { // a|b
    function __construct($Left,$Right) {
        $this->Left = Left;$this->Right=Right
    }
}
class RepeatExpr { // a*
    function __construct($Subexpr) {$this->SubExpr= $SubExpr;}
}
class OptionalExpr { // a?
    function __construct($Subexpr) {$this->SubExpr= $Subexpr;}
}
class MatchExpr {
    function __construct($type, $word=null) { $this->type = $type; $this->word = $word }
    function match($lex_node) {
        if ($this->type === null)
            return $this->word == $lex_node->word;
        if ($this->word === null)
            return $this->type == $lex_node->type;
    }
}

function MatchImplApply ($expr, $target, $i, $cont) {
    // for lazy value
    if (is_callable($expr))
        return MatchImplApply($expr(), $target, $i, $cont);

    // for normal
  switch (true) {
  case $expr instanceof ConcatExpr:
    return MatchImplApply($expr->Left, $target, $i, function($rest,$ri)use($expr,$cont) {
      return MatchImplApply($expr->Right, $rest, $ri, $cont);});
  case $expr instanceof AltExpr:
    return (MatchImplApply($expr->Left, $target, $i, $cont) ||
        MatchImplApply($expr->Right, $target, $i, $cont));
  case $expr instanceof RepeatExpr:
    return MatchImplApply($expr->Subexpr, $target, $i, function ($rest,$ri)use($expr,$cont) {
        return (MatchImplApply($expr, $rest, $ri, $cont) || $cont($rest,$ri));
    }) || $cont($target, $i);
  case $expr instanceof OptionalExpr:
    return MatchImplApply($expr->Subexpr, $target, $i, $cont) || $cont($target, $i)
  case $expr instanceof MatchExpr:
    return $i < count($target) && $expr->match($target) && $cont($target, $i+1) // end of string, match a char
  default:
    throw "no expression type"
  }
}

function RegexMatch ($RegExpr, $target) { // all match
    return MatchImplApply($Regexpr, $target,0, function($rest,$i) { return !isset($rest[$i]); });
}
//function RegexSearch (Regexpr->target,i) { // partial match from begining
//    return MatchImplApply(Regexpr, target,i, (rest,i) => true) ||
//    (target[i] !== undefined && RegexSearch(Regexpr-> target, i+1))
//}

