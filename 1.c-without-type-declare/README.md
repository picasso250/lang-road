C like language, and have GC

    import stdio  // import borrowed from go-lang
    func main() { // func is a key word for function
        stdio.printf("hello world!") // no ; (semicolon)
        0 // last statement is return value
    }

no need to specify a type, it can be a dynamic language

    func swap(&a, &b) { // & means by ref
        t = a
        a = b
        b = t
    }

but it can be compile-time typed

    fact::(int)->int // type decl :: borrowed from Haskell
    fact(n) = if (n==0) { 1 } else { n * fact(n-1) } } // function decl borrow from Haskell

for compile simplicity, every line is a statement.

for compile simplicity, there are no operator priority.

    a = 3*4+1 // error
    a = (3*4)+1 // ok

operators at least include below

    + - * / | & ^ = == != -> =>

you can define new operators

    op || (a,b) = a|b 

for compile simplicity, operators expcept for (){}[] must be seperated by space.

    i-->0 // parsed as 'i' '-->' '0'

and (){}[] can not be re-defined.

keywords are import, return, if, for (only this 4)

    for (i=0;i<4;i+=1)
    for (lst as i=>e) // borrowed from php

struct can have method

    name::(A,any,any)->any
    a.name (b,c) = b+c
    a.name(b,c) // invoke
