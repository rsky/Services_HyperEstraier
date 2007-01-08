<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common.php';

// create and configure the node connecton object
$node = new Services_HyperEstraier_Node;
$node->setUrl($uri);

// create a search condition object
$cond = new Services_HyperEstraier_Condition;
$cond->setPhrase('water AND mind');
$cond->setMax(10);
$cond->setSkip(0);

// get the result of search
$nres = $node->search($cond, 0);
if ($nres) {
    if ($nres->docNum() == 0) {
        fprintf(STDOUT, "%s: not found.\n", $cond->getPhrase());
    } else {
        foreach ($nres as $rdoc) {
            // display attributes
            if (($value = $rdoc->getAttribute('@uri')) !== null) {
                fprintf(STDOUT, "URI: %s\n", $value);
            }
            if (($value = $rdoc->getAttribute('@title')) !== null) {
                fprintf(STDOUT, "Title: %s\n", $value);
            }
            // display the snippet text (with property overloading)
            fprintf(STDOUT, "%s", $rdoc->snippet);
        }
    }
} else {
    fprintf(STDERR, "error: %d\n", $node->status);
    if (Services_HyperEstraier::getErrorStack()->hasErrors()) {
        fputs(STDERR, print_r(Services_HyperEstraier::getErrorStack()->getErrors(), true));
    }
}
