<?php

$validationLines = file('./../application/configs/attributevalidations.inc.php');
$attributes = array();
foreach ($validationLines as $validationLine) {
    if (is_numeric($validationLine[0])) {
        if (isset($attributeName)) {
            if (empty($desc)) {
                $desc = new stdClass();
            }
            $attributes[$attributeName] = $desc;
        }

        $matches = array();
        preg_match('/(\d+)\.{0,2}([\d\*]*) (\S+)/', $validationLine, $matches);
        $attributeName = $matches[3];
        $desc = array(
        );

        if (empty($matches[2])) {
            $desc['Constraints']['error']['min'] = $matches[1];
            $desc['Constraints']['error']['max'] = $matches[1];
        }
        else if ($matches[2] !== "*") {
            $desc['Constraints']['error']['max'] = $matches[2];
        }
        else if ($matches[1] !== '0') {
            $desc['Constraints']['error']['min'] = $matches[1];
        }
    }
    else if (preg_match('/Values must be no longer than (\d+) characters/i', $validationLine, $matches)) {
        $desc['Constraints']['error']['maxLength'] = $matches[1];
    }
    else if (trim($validationLine) === "Values are case insensitive.") {
        $desc['Constraints']['caseInsensitive'] = true;
    }
    else if (preg_match('/Value MUST match (.+)/', $validationLine, $matches)) {
        $desc['Constraints']['error']['validateRegex'] = $matches[1];
    }
    else if (preg_match('/Values MUST be a valid (.+)/', $validationLine, $matches)) {
        $desc['Constraints']['error']['validate'] = $matches[1];
    }
}
var_export($attributes);

/**
 * comment -> "\n#.*\n"
 * validation -> occurrences " " attributeName \n "Value"[s] operator
 * operator -> "MUST" | "SHOULD"
 * attributeName -> \S+
 * occurrences -> min ".." max
 * min -> \d+
 * max -> |d+ | wildcard
 * wildcard -> "*"
 */
class Parser
{
}