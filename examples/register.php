<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'common.php';

// create and configure the node connecton object
$node = new Services_HyperEstraier_Node;
$node->setUrl($uri);
$node->setAuth($user, $pass);

// create a document object
$doc = new Services_HyperEstraier_Document;

// add attributes to the document object
$doc->addAttribute('@uri', 'http://estraier.example.com/example.txt');
$doc->addAttribute('@title', 'Bridge Over The Troubled Water');

// add the body text to the document object
$doc->addText('Like a bridge over the troubled water,');
$doc->addText('I will ease your mind.');

// register the document object to the node
if (!$node->putDocument($doc)) {
    fprintf(STDERR, "error: %d\n", $node->status);
    if (Services_HyperEstraier_Error::hasErrors()) {
        fputs(STDERR, print_r(Services_HyperEstraier_Error::getErrors(), true));
    }
} else {
    fputs(STDOUT, "success.\n");
}
