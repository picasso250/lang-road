# M-expr

- quote `'`
- atom `A?(x)`
- null `Nil?(x)`
- car `head(lst)`
- cdr `tail(lst)`
- cons `[x@lst]`
- cond can be new line
- let can be new line
- lambda `\x.x+1`
- list `[ 1 2 3 ]`
- define `=`
- apply `f(x)`

## Principle

We learn from

- Math
- C or Haskell
- Our Intuitive

And:

- do not have static types yet

## `cond` can be new line

```
cond?
  a? => b;
  c? => d.
```

## `let` can be new line

```
let
  a = 3;
  b = 4;
  do(a,b).
```
