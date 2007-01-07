<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common.php';

// create and configure the node connecton object
$node = new Services_HyperEstraier_Node;
$node->setUrl($uri);
$node->setAuth($user, $pass);

// remove the document specified by URI.
if (!$node->outDocumentByUri('http://estraier.example.com/example.txt')) {
    fprintf(STDERR, "error: %d\n", $node->status);
    if (Services_HyperEstraier_Utility::getErrorStack()->hasErrors()) {
        fputs(STDERR, print_r(Services_HyperEstraier_Utility::getErrorStack()->getErrors(), true));
    }
} else {
    fputs(STDOUT, "success.\n");
}
