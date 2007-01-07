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

// {{{ load dependencies

require_once 'Services/HyperEstraier.php';

// }}}
// {{{ class Services_HyperEstraier_NodeResult

/**
 * Abstraction of result set from node.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 */
class Services_HyperEstraier_NodeResult implements IteratorAggregate
{
    // {{{ properties

    /**
     * Documents
     *
     * @var array
     * @access  private
     */
    private $_docs;

    /**
     * Hint informations
     *
     * @var array
     * @access  private
     */
    private $_hints;

    // }}}
    // {{{ constructor

    /**
     * Create a node result object.
     *
     * @param   array   $docs   Documents.
     * @param   array   $hints  Hint informations.
     * @access  public
     */
    public function __construct(array $docs, array $hints)
    {
        $this->_docs = $docs;
        $this->_hints = $hints;
    }

    // }}}
    // {{{ overloading methods

    /**
     * Allow to get private/protected property from outside of the instance.
     *
     * @param   string  $name   The name of a property.
     * @return  mixed   The value of the property.
     *                  If it does not exist, generates a user-level notice message
     *                  and returns `null'.
     * @access  private
     */
    private function __get($name)
    {
        $name = '_' . $name;
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        $errmsg = sprintf('Undefined property: %s::%s', __CLASS__, $name);
        trigger_error($errmsg, E_USER_NOTICE);
        return null;
    }

    // }}}
    // {{{ IteratorAggregate implementation

    /**
     * Get the node result iterator.
     *
     * @return  object  Services_HyperEstraier_NodeResultIterator
     *                  The iterator of this node result.
     * @access  public
     * @ignore
     */
    public function getIterator()
    {
        return new Services_HyperEstraier_NodeResultIterator($this);
    }

    // }}}
    // {{{ getter methods

    /**
     * Get the number of documents.
     *
     * @return  int     The number of documents.
     * @access  public
     */
    public function docNum()
    {
        return count($this->_docs);
    }

    /**
     * Get a document object.
     *
     * @param   int     $index  The index of a document.
     * @return  object  Services_HyperEstraier_ResultDocument
     *                  A result document object.
     *                  If the index is out of bounds, returns `null'.
     * @access  public
     */
    public function getDocument($index)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($index, 'integer')
        );
        return (isset($this->_docs[$index])) ? $this->_docs[$index] : null;
    }

    /**
     * Get the value of hint information.
     *
     * @param   string  $key    The key of a hint.
     *                          "VERSION", "NODE", "HIT", "HINT#n", "DOCNUM",
     *                          "WORDNUM", "TIME", "TIME#n", "LINK#n", and "VIEW"
     *                          are provided for keys.
     * @return  string  The hint. If the key does not exist, returns `null'.
     * @access  public
     */
    public function getHint($key)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($key, 'string')
        );
        return (isset($this->_hints[$key])) ? $this->_hints[$key] : null;
    }

    // }}}
}

// }}}
// {{{ class Services_HyperEstraier_NodeResultIterator

/**
 * Iteration of result set from node.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 * @ignore
 */
class Services_HyperEstraier_NodeResultIterator implements Iterator
{
    // {{{ properties

    /**
     * An instance of Services_HyperEstraier_NodeResult
     *
     * @var object  Services_HyperEstraier_NodeResult
     * @access  private
     */
    private $nres;

    /**
     * The current position of the iterator
     *
     * @var int
     * @access  private
     */
    private $pos;

    /**
     * The key of the last result document object
     *
     * @var int
     * @access  private
     */
    private $end;

    // }}}
    // {{{ constructor

    /**
     * Create a node result iterator.
     *
     * @param   object  $nres  Services_HyperEstraier_NodeResult
     *                         which is the node result object.
     * @access  public
     */
    public function __construct(Services_HyperEstraier_NodeResult $nres)
    {
        $this->nres = $nres;
        $this->pos = 0;
        $this->end = $this->nres->docNum() - 1;
    }

    // }}}
    // {{{ Iterator implementation

    /**
     * Rewind the iterator to the beginning of the node result.
     *
     * @return  void
     * @access  public
     */
    public function rewind()
    {
        $this->pos = 0;
    }

    /**
     * Get the key of the current position.
     *
     * @return  int     The key of the current position.
     * @access  public
     */
    public function key()
    {
        return $this->pos;
    }

    /**
     * Get the result document object of the current position.
     *
     * @return  object  Services_HyperEstraier_ResultDocument
     *                  The result document object of the current position.
     * @access  public
     */
    public function current()
    {
        return $this->nres->getDocument($this->pos);
    }

    /**
     * Move the iterator to the next key/value pair.
     *
     * @return  void
     * @access  public
     */
    public function next()
    {
        $this->pos++;
    }

    /**
     * Check whether there are more value or not.
     *
     * @return  bool    True if there are more value, else false.
     * @access  public
     */
    public function valid()
    {
        return $this->pos <= $this->end;
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
