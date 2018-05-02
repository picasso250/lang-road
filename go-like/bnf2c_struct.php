<?php

function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

$type_table = [];
$union_table = [];
$type_auto=1;
foreach (file($argv[1]) as $key => $line) {
    $line = trim($line);
    if ($line === "") continue;
    if ($line[0] == '#') continue; // comments
    $a = explode("=", $line);
    if (count($a)!=2) die("$line has no =\n");
    list($name,$def) = $a;
    if ($pos=strpos($name, '(')) {
        $type = substr($name, $pos+1, strlen($name)-$pos-2);
        $name = substr($name, 0, $pos);
        $union_table[$name][$type] = parse_def($a[1]);
        echo "#define ".strtoupper($name)."_TYPE_".strtoupper($type)." {$type_auto}\n";
        $type_auto++;
    } else {
        $type_table[$name] = parse_def($a[1]);
    }
}

foreach ($type_table as $name => $defs) {
    echo to_c_def_struct($defs, $name),";\n";
}

function parse_def($def) {
    $def = substr($def, 1, strlen($def)-2);
    $types = explode(',', $def);
    return $types;
}
function parse_type($type) {
    $name = $type;
    $a = explode(":", $type);
    if (count($a) == 2) {
        $name = $a[0];
        $type = $a[1];
    }
    $is_array = false;
    if ($type[0] == '[') {
        $type = substr($type, 1, strlen($type)-2);
        $name = "{$type}_list";
        $is_array = true;
    }
    $type_ = '';
    if ($pos=strpos($type, '(')) {
        $type_ = substr($type, $pos+1, strlen($type)-$pos-2);
        $type = substr($type, 0, $pos);
        $name = "{$type}_$type_";
    }
    if (count($a) == 2) {
        $name = $a[0];
    }
    return compact('is_array', 'type', 'name', 'type_');
}
function to_c_def($type_def)
{
    if ($type_def['is_array']) {
        var_dump($type_def);exit;
        $type = $type_def['type'];
        if (isset($GLOBALS['type_table']['type'])) {
            $type = "struct $type";
        } else {
            $type = "char*";
        }
        return "struct {int size; $type*data;} _$type_def[name]";
    } elseif (is_c_keyword($type_def['type'])) {
        return "{$type_def['type']} _{$type_def['name']}";
    } elseif (isset($GLOBALS['type_table'][$type_def['name']])) {
        return "struct $type_def[name] *_$type_def[name]".($type_def['type_']?"_{$type_def['type_']}":'');
    } else {
        return "char *_$type_def[name]";
    }
}
function to_c_def_struct($defs, $name="")
{
    $s = "struct $name{";
    foreach ($defs as $i => $def) {
        if ($i==0 && $def=='type') {
            $s.= "int type;";
            continue;
        }
        if ($def=='...') {
            $s.= "\n\tunion{\n\t";
            foreach ($GLOBALS['union_table'][$name] as $type => $defs_) {
                $s.= to_c_def_struct($defs_)."_$type;\n\t";
            }
            $s.= "};";
            continue;
        }
        $s.= to_c_def(parse_type($def)).";";
    }
    $s.= "}";
    return $s;
}
function is_c_keyword($def) {
    return in_array($def, ['int', 'float', 'char', 'bool']);
}
