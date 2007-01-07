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
    // for each document in the result without iteration
    /*for ($i = 0; $i < $nres->docNum(); $i++) {
        // get a result document object
        $rdoc = $nres->getDocument($i);
        // display attributes
        if (($value = $rdoc->getAttribute('@uri')) !== null) {
            fprintf(STDOUT, "URI: %s\n", $value);
        }
        if (($value = $rdoc->getAttribute('@title')) !== null) {
            fprintf(STDOUT, "Title: %s\n", $value);
        }
        // display the snippet text (with getter method)
        fprintf(STDOUT, "%s", $rdoc->getSnippet());
    }*/
    // for each document in the result as an iterator
    $j = 0;
    foreach ($nres as $rdoc) {
        $j++;
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
    // debug output
    //var_dump($i, $j, $nres->docNum());
} else {
    fprintf(STDERR, "error: %d\n", $node->status);
    if (Services_HyperEstraier::getErrorStack()->hasErrors()) {
        fputs(STDERR, print_r(Services_HyperEstraier::getErrorStack()->getErrors(), true));
    }
}
