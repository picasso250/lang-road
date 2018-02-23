# abstract syntax tree

## program

the program is a tree of files.

the depdency is in .dep file in the root

## file

file is a list of statments.

the file's value is the last statement.

if there's no statement in file, than the value is nil.

## statment

statment is expression.

## expr

every expr has a value.

### type of expr

1. literal like `4`
2. var like `a`
3. function call like `a(b)`
4. def like `a=4`
5. lambda like `\a.a+1`

### function

there are 2 kinds of functions

1. buildin functions
2. user defined functions

#### build in functions

3. quote
4. build-atom
6. build-pair
7. is-atom
8. is-pair
9. equal
10. car
11. cdr
12. cond
13. let
14. arith type


