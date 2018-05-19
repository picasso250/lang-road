<?php

function test_lex() {
    assert_true(lex("a+=foo")== [new LexNode('word','a',1),new LexNode('operator','+=',1),new LexNode('word','foo',1)]);
    //print_r(lex_line("a+=foo"));
    assert_true(lex('"abc"//bc')==[new LexNode('string','abc',1),new LexNode('comment','bc',1),]);
    assert_true(lex('0.2f')==[new LexNode('number','0.2f',1),]);
    //print_r(lex_line('0.2f'));
    assert_true(lex("func main(){\n0\n}")
        ==[new LexNode("word", "func", 1),new LexNode("word","main",1),new LexNode("operator","(",1), new LexNode("operator", ")",1), new LexNode("operator","{",1),new LexNode("number","0",2),new LexNode("operator","}",3)]);
    //print_r(lex("func main(){\n0\n}"));
    assert_true(lex('"func
        main()"') == [new LexNode("string","func\n        main()",2)]);
    assert_true(lex('"ab\nc"')==[new LexNode('string',"ab\nc",1),]);
    //var_dump(lex('"ab\nc"'));
}
