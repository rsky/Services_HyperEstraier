<?php
/**
 * PHP interface of Hyper Estraier
 *
 * A porting of estraierpure.rb which is a part of Hyper Estraier.
 *
 * Hyper Estraier is a full-text search system. You can search lots of
 * documents for some documents including specified words. If you run a web
 * site, it is useful as your own search engine for pages in your site.
 * Also, it is useful as search utilities of mail boxes and file servers.
 *
 * PHP version 5
 *
 * Copyright (c) 2005-2007 Ryusuke SEKIYAMA. All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any personobtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @copyright   2005-2007 Ryusuke SEKIYAMA
 * @license     http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version     SVN: $Id:$
 * @link        http://page2.xrea.jp/  (Project web site)
 * @link        http://hyperestraier.sourceforge.net/  (Hyper Estraier)
 * @since       File available since Release 0.1.0
 * @filesource
 */

// {{{ constants

/**
 * The version number of Services_HyperEstraier.
 */
define('SERVICES_HYPERESTRAIER_VERSION', '@package_version@');

/**
 * Specifies debug mode.
 *
 * If set to `1', every methods check their argument datatype.
 */
if (!defined('SERVICES_HYPERESTRAIER_DEBUG')) {
    define('SERVICES_HYPERESTRAIER_DEBUG', 0);
}

// }}}
// {{{ load dependencies

require_once 'Services/HyperEstraier/Node.php';

// }}}
// {{{ class Services_HyperEstraier

/**
 * Class for simple document handling and searching.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @since       Class available since Release 0.6.0
 * @static
 */
class Services_HyperEstraier
{
    // {{{ private methods

    /**
     * Get a node object.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @return  object  Services_HyperEstraier_Node
     * @throws  InvalidArgumentException
     * @access  private
     * @static
     * @ignore
     */
    private static function _getNode($url)
    {
        static $node = null;
        static $checksum = '';

        // parse the url
        if (!is_string($url)) {
            throw new InvalidArgumentException(sprintf(
                    'Argument#2 should be a kind of integer or string, %s given.',
                    gettype($id)));
        }
        if (!($purl = @parse_url($url)) ||
            !isset($purl['scheme']) || strcasecmp($purl['scheme'], 'http') != 0 ||
            !isset($purl['host']) || !isset($purl['path']) ||
            (isset($purl['user']) xor isset($purl['pass'])))
        {
            throw new InvalidArgumentException('Invalid URL given.');
        }

        // check if the node object is cached
        $newchecksum = md5($url);
        if ($checksum != $newchecksum) {
            $node = new Services_HyperEstraier_Node;
            $nurl = 'http://' . $purl['host'];
            if (isset($purl['port'])) {
                $nurl .= ':' . $purl['port'];
            }
            $nurl .= $purl['path'];
            $node->setUrl($nurl);
            if (isset($purl['user']) && isset($purl['pass'])) {
                $node->setAuth($purl['user'], $purl['pass']);
            }
            $checksum = $newchecksum;
        }

        return $node;
    }

    // }}}
    // {{{ public methods

    /**
     * Register the text.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @param   string  $text       A text data.
     * @param   array   $attrs      Associated array of the attributes.
     *                              At least, requires `@uri' attribute.
     * @param   array   $keywords   A list of keywords. (optional)
     * @return  bool    True if success, else false.
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     */
    public static function register($url, $text,
                                    array $attrs,
                                    array $keywords = null)
    {
        $node = self::_getNode($url);
        $doc = new Services_HyperEstraier_Document;
        foreach (preg_split('/(?:\\r\\n|\\r|\\n)+/', $text) as $line) {
            if (strlen($line)) {
                if (substr($line, 0, 1) == "\n") {
                    $doc->addText($line);
                } else {
                    $doc->addHiddenText($line);
                }
            }
        }
        foreach ($attributes as $name => $value) {
            $doc->addAttribute($name, $value);
        }
        if ($keywords) {
            $doc->setKeywords($keywords);
        }
        return $node->putDocument($doc);
    }

    /**
     * Update the registered document.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @param   int|string  $id     The ID number of a registered document
     *                              or the URI of a registered document.
     * @param   string  $text       An additional text data. (optional)
     * @param   array   $attrs      Associated array of the attributes. (optional)
     * @param   array   $keywords   A list of keywords. (optional)
     * @return  bool    True if success, else false.
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     */
    public static function update($url, $id, $text = '',
                                  array $attrs = null,
                                  array $keywords = null)
    {
        $node = self::_getNode($url);
        if (is_int($id)) {
            $doc = $node->getDocument($id);
        } else if (is_string($id)) {
            $doc = $node->getDocumentByUri($id);
        } else {
            throw new InvalidArgumentException(sprintf(
                    'Argument#2 should be a kind of integer or string, %s given.',
                    gettype($id)));
        }
        if (!$doc) {
            return false;
        }
        if (strlen($text)) {
            foreach (preg_split('/(?:\\r\\n|\\r|\\n)+/', $text) as $line) {
                if (strlen($line)) {
                    if (substr($line, 0, 1) == "\n") {
                        $doc->addText($line);
                    } else {
                        $doc->addHiddenText($line);
                    }
                }
            }
        }
        if ($attributes) {
            foreach ($attributes as $name => $value) {
                $doc->addAttribute($name, $value);
            }
        }
        if ($keywords) {
            $doc->setKeywords($keywords);
        }
        return $doc->editDocument($doc);
    }

    /**
     * Replace the registered document.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @param   int|string  $id     The ID number of a registered document
     *                              or the URI of a registered document.
     * @param   string  $text       A text data.
     * @param   array   $attrs      Associated array of the attributes.
     *                              At least, requires `@uri' attribute.
     * @param   array   $keywords   A list of keywords. (optional)
     * @return  bool    True if success, else false.
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     * @see Services_HyperEstraier::register()
     * @see Services_HyperEstraier::purge()
     */
    public static function replace($url, $id, $text,
                                   array $attrs,
                                   array $keywords = null)
    {
        $node = self::_getNode($url);
        if (is_int($id)) {
            $doc = $node->getDocument($id);
        } else if (is_string($id)) {
            $doc = $node->getDocumentByUri($id);
        } else {
            throw new InvalidArgumentException(sprintf(
                    'Argument#2 should be a kind of integer or string, %s given.',
                    gettype($id)));
        }
        if ($doc && !self::purge($url, $id)) {
            return false;
        }
        return self::register($url, $text, $attributes, $keywords);
    }

    /**
     * Purge the registered document.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @param   int|string  $id     The ID number of a registered document
     *                              or the URI of a registered document.
     * @return  bool    True if success, else false.
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     */
    public static function purge($url, $id)
    {
        $node = self::_getNode($url);
        if (is_int($id)) {
            return $node->outDocument($id);
        } else if (is_string($id)) {
            return $node->outDocumentByUri($id);
        } else {
            throw new InvalidArgumentException(sprintf(
                    'Argument#1 should be a kind of integer or string, %s given.',
                    gettype($id)));
        }
    }

    /**
     * Search for documents corresponding a phrase.
     *
     * @param   string  $url    The url of a node server.
     *                          Also includes the username and the password.
     * @param   string  $phrase A search phrase.
     * @param   int     $limit  The maximum number of retrieval.
     *                          By default, the number of retrieval is not limited.
     * @param   int     $offset The number of documents to be skipped.
     *                          By default, it is 0.
     * @return  object  Services_HyperEstraier_NodeResult
     *                  A node result object.
     *                  On error, returns `null'.
     * @throws  InvalidArgumentException
     * @access  public
     * @static
     */
    public static function search($url, $phrase, $limit = -1, $offset = 0)
    {
        $node = self::_getNode($url);
        $cond = new Services_HyperEstraier_Condition;
        $cond->setPhrase($phrase);
        $cond->setMax($limit);
        $cond->setSkip($offset);
        return $node->search($cond, 0);
    }

    // }}}
}

// }}}
/*
 * Local variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=iso-8859-1 ai et ts=4 sw=4 sts=4 fdm=marker:
