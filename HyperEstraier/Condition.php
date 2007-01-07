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
// {{{ class Services_HyperEstraier_Condition

/**
 * Abstraction of search condition.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 */
class Services_HyperEstraier_Condition
{
    // {{{ constants

    /**
     * option: check every N-gram key
     */
    const SURE = 1; // 1 << 0

    /**
     * option: check N-gram keys skipping by one
     */
    const USUAL = 2; // 1 << 1

    /**
     * option: check N-gram keys skipping by two
     */
    const FAST = 4; // 1 << 2

    /**
     * option: check N-gram keys skipping by three
     */
    const AGITO = 8; // 1 << 3

    /**
     * option: without TF-IDF tuning
     */
    const NOIDF = 16; // 1 << 4

    /**
     * option: with the simplified phrase
     */
    const SIMPLE = 1024; // 1 << 10

    /**
     * option: with the rough phrase
     */
    const ROUGH = 2048; // 1 << 11

    /**
     * option: with the union phrase
     */
    const UNION = 32768; // 1 << 15

    /**
     * option: with the intersection phrase
     */
    const ISECT = 65536; // 1 << 16

    // }}}
    // {{{ properties

    /**
     * The search phrase
     *
     * @var string
     * @access  private
     */
    private $_phrase;

    /**
     * The order of a condition object
     *
     * @var string
     * @access  private
     */
    private $_order;

    /**
     * The maximum number of retrieval
     *
     * @var int
     * @access  private
     */
    private $_max;

    /**
     * The number of documents to be skipped.
     *
     * @var int
     * @access  private
     */
    private $_skip;

    /**
     * Options of retrieval
     *
     * @var int
     * @access  private
     */
    private $_options;

    /**
     * Permission to adopt result of the auxiliary index
     *
     * @var int
     * @access  private
     */
    private $_auxiliary;

    /**
     * The name of the distinct attribute
     *
     * @var string
     * @access  private
     */
    private $_distinct;

    /**
     * The mask of targets of meta search
     *
     * @var int
     * @access  private
     */
    private $_mask;

    // }}}
    // {{{ constructor

    /**
     * Create a search condition object.
     *
     * @access  public
     */
    public function __construct()
    {
        $this->_phrase = null;
        $this->_attrs = array();
        $this->_order = null;
        $this->_max = -1;
        $this->_skip = 0;
        $this->_options = 0;
        $this->_auxiliary = 32;
        $this->_distinct = null;
        $this->_mask = 0;
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
    // {{{ setter methods

    /**
     * Set the search phrase.
     *
     * @param   string  $phrase     A search phrase.
     * @return  void
     * @access  public
     */
    public function setPhrase($phrase)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($phrase, 'string')
         );
        $this->_phrase = Services_HyperEstraier_Utility::sanitize($phrase);
    }

    /**
     * Add an expression for an attribute.
     *
     * @param   string  $expr   A search expression.
     * @return  void
     * @access  public
     */
    public function addAttribute($expr)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($expr, 'string')
        );
        $this->_attrs[] = Services_HyperEstraier_Utility::sanitize($expr);
    }

    /**
     * Set the order of a condition object.
     *
     * @param   string  $order  An expression for the order.
     *                          By default, the order is by score descending.
     * @return  void
     * @access  public
     */
    public function setOrder($order)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($order, 'string')
        );
        $this->_order = Services_HyperEstraier_Utility::sanitize($order);
    }

    /**
     * Set the maximum number of retrieval.
     *
     * @param   int     $max  The maximum number of retrieval.
     *                        By default, the number of retrieval is not limited.
     * @return  void
     * @access  public
     */
    public function setMax($max)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($max, 'integer')
        );
        if ($max >= 0) {
            $this->_max = $max;
        }
    }

    /**
     * Set the number of documents to be skipped.
     *
     * @param   int     $skip   The number of documents to be skipped.
     *                          By default, it is 0.
     * @return  void
     * @access  public
     */
    public function setSkip($skip)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($skip, 'integer')
        );
        if ($skip >= 0) {
            $this->_skip = $skip;
        }
    }

    /**
     * Set options of retrieval.
     *
     * @param   int     $options    Options:
     * - `Services_HyperEstraier_Condition::SURE' specifies that it checks every N-gram key.
     * - `Services_HyperEstraier_Condition::USUAL', which is the default,
     *      specifies that it checks N-gram keys with skipping one key.
     * - `Services_HyperEstraier_Condition::FAST' skips two keys.
     * - `Services_HyperEstraier_Condition::AGITO' skips three keys.
     * - `Services_HyperEstraier_Condition::NOIDF' specifies not to perform TF-IDF tuning.
     * - `Services_HyperEstraier_Condition::SIMPLE' specifies to use simplified phrase.
     * - `Services_HyperEstraier_Condition::ROUGH' specifies to use rough phrase.
     * - `Services_HyperEstraier_Condition::UNION' specifies to use union phrase.
     * - `Services_HyperEstraier_Condition::ISECT' specifies to use intersection phrase.
     *  Each option can be specified at the same time by bitwise or.
     *  If keys are skipped, though search speed is improved, the relevance ratio grows less.
     * @return  void
     * @access  public
     */
    public function setOptions($options)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($options, 'integer')
        );
        $this->_options |= $options;
    }

    /**
     * Set permission to adopt result of the auxiliary index.
     *
     * @param   int     $min    The minimum hits to adopt result of the auxiliary index.
     *                          If it is not more than 0, the auxiliary index is not used.
     *                          By default, it is 32.
     * @return  void
     * @access  public
     */
    public function setAuxiliary($min)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($min, 'integer')
        );
        $this->_auxiliary = $min;
    }

    /**
     * Set the attribute distinction filter.
     *
     * @param   string  $name   The name of an attribute to be distinct.
     * @return  void
     * @access  public
     */
    public function setDistinct($name)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string')
        );
        $this->_distinct = Services_HyperEstraier_Utility::sanitize($name);
    }

    /**
     * Set the mask of targets of meta search.
     *
     * @param   int     $mask   A masking number.
     *                          1 means the first target, 2 means the second target,
     *                          4 means the third target, and power values of 2 and
     *                          their summation compose the mask.
     * @return  void
     * @access  public
     */
    public function setMask($mask)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($mask, 'integer')
        );
        $this->_mask = $mask;
    }

    // }}}
    // {{{ getter methods

    /**
     * Get the search phrase.
     *
     * @return  string  The search phrase.
     * @access  public
     */
    public function getPhrase()
    {
        return $this->_phrase;
    }

    /**
     * Get expressions for attributes.
     *
     * @return  array   Expressions for attributes.
     * @access  public
     */
    public function getAttributes()
    {
        return $this->_attrs;
    }

    /**
     * Get the order expression.
     *
     * @return  string  The order expression.
     * @access  public
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get the maximum number of retrieval.
     *
     * @return  int     The maximum number of retrieval.
     * @access  public
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Get the number of documents to be skipped.
     *
     * @return  int     The number of documents to be skipped.
     * @access  public
     */
    public function getSkip()
    {
        return $this->_skip;
    }

    /**
     * Get options of retrieval.
     *
     * @return  int     Options by bitwise or.
     * @access  public
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get permission to adopt result of the auxiliary index.
     *
     * @return  int     Permission to adopt result of the auxiliary index.
     * @access  public
     */
    public function getAuxiliary()
    {
        return $this->_auxiliary;
    }

    /**
     * Get the attribute distinction filter.
     *
     * @return  string  The name of the distinct attribute.
     * @access  public
     */
    public function getDistinct()
    {
        return $this->_distinct;
    }

    /**
     * Get the mask of targets of meta search.
     *
     * @return  int     The mask of targets of meta search.
     * @access  public
     */
    public function getMask()
    {
        return $this->_mask;
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
