<?php

function test_lex_line() {
    assert_true(lex_line("a+=foo")==[['word','a'],['operator','+='],['word','foo']]);
    //print_r(lex_line("a+=foo"));
    assert_true(lex_line('"abc"//bc')==[['string','abc'],['comment','bc'],]);
    assert_true(lex_line('0.2f')==[['number','0.2f'],]);
    //print_r(lex_line('0.2f'));
}
