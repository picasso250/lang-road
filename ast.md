# abstract syntax tree

## program

Program is a tree of files.

Depdencies is in `.dep` file in the root

## file

file is a list of statements.

the file's value is the last statement or nil when there is no statement.

## statement

statement is expression.

## expr

every expr has a value.

### type of expr

1. literal like `4`
2. var like `a`
3. function call like `a(b)`
4. def like `func f() { }`
5. lambda like `(a) => a+1`

### type of data

1. int
2. float
3. byte
4. string
5. array
6. slice
7. map

### function

there are 2 kinds of functions

1. buildin functions
2. user defined functions

#### build in functions

## language structure

1. var-def
1. struct-def
2. enum-def
3. union-def
3. member
6. array-def
7. array-deref
8. mem-alloc
9. mem-free
4. assign
5. function-define
6. function-call
7. if
8. for
9. for-in
10. while
11. do-while

## mem map

    struct s {
        int type; // object type
        int ref_count;
        ...
    }

