<?php
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

class PatternNode {
    public $Left;
    public $Right;
    public $Subexpr->

    public $type;
    public $name;
    public $sep = null;
    public $match_type = null;
    public $can_be_empty = false;
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
    function __construct($Subexpr-> {$this->SubExpr= $SubExpr;}
}
class OptionalExpr { // a?
    function __construct($Subexpr-> {$this->SubExpr= $Subexpr;}
}
class MatchExpr {
    function __construct($ch) { $this->ch= ch; }
}
class _Match_literal {
    function __construct($word) { $this->word = $word; }
}
class _Match

function MatchImplApply ($expr, $target, $i, $cont) {
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
    return target[i] !== undefined && target[i] == expr->ch && cont(target, i+1) // end of string, match a char
  default:
    throw "no expression type"
  }
}

function RegexMatch (Regexpr->target) { // all match
    return MatchImplApply(Regexpr, target,0, (rest,i) => rest[i] === undefined)
}
function RegexSearch (Regexpr->target,i) { // partial match from begining
    return MatchImplApply(Regexpr, target,i, (rest,i) => true) ||
    (target[i] !== undefined && RegexSearch(Regexpr-> target, i+1))
}

console.assert(RegexMatch(new Concatexpr->new Matchexpr->'a'), new Matchexpr->'b')), "ab"));
console.assert(RegexMatch(new Altexpr->new Matchexpr->'a'), new Matchexpr->'b')), "a"));
console.assert(RegexMatch(new Altexpr->new Matchexpr->'a'), new Matchexpr->'b')), "b"));
console.assert(RegexMatch(new Repeatexpr->new Matchexpr->'a')), "aaaaa"));
console.assert(RegexMatch(new Concatexpr->new Repeatexpr->new Matchexpr->'a')), new Matchexpr->'b')),
  "aaaaab"));
console.assert(RegexMatch(new Concatexpr->new Repeatexpr->new Matchexpr->'a')), new Matchexpr->'b')),
  "b"));
console.assert(RegexSearch(new Concatexpr->new Repeatexpr->new Matchexpr->'a')), new Matchexpr->'b')),
      "aaaaabb", 0));
console.assert(RegexMatch(new Optionalexpr->new Matchexpr->'a')), "a"));
console.assert(RegexMatch(new Optionalexpr->new Matchexpr->'a')), ""));
console.assert(RegexMatch(new Optionalexpr->new Concatexpr->new Matchexpr->'a'), new Matchexpr->'b'))),
    "ab"));
console.assert(RegexMatch(new Optionalexpr->new Concatexpr->new Matchexpr->'a'), new Matchexpr->'b'))),
    ""));
