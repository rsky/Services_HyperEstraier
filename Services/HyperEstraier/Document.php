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

require_once 'Services/HyperEstraier/Utility.php';

// }}}
// {{{ class Services_HyperEstraier_Document

/**
 * Abstraction of document.
 *
 * @category    Web Services
 * @package     Services_HyperEstraier
 * @author      Ryusuke SEKIYAMA <rsky0711@gmail.com>
 * @version     Release: @package_version@
 */
class Services_HyperEstraier_Document
{
    // {{{ properties

    /**
     * The ID number
     *
     * @var int
     * @access  private
     */
    private $_id;

    /**
     * Attributes
     *
     * @var array
     * @access  private
     */
    private $_attrs;

    /**
     * Sentences of text
     *
     * @var array
     * @access  private
     */
    private $_dtexts;

    /**
     * Hidden sentences of text
     *
     * @var array
     * @access  private
     */
    private $_htexts;

    /**
     * Keywords
     *
     * @var array
     * @access  private
     */
    private $_kwords;

    /**
     * Substiture score
     *
     * @var int
     * @access  private
     */
    private $_score;

    // }}}
    // {{{ constructor

    /**
     * Create a document object.
     *
     * @param   string  $draft  A string of draft data.
     * @access  public
     */
    public function __construct($draft = '')
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($draft, 'string')
        );
        $this->_id = -1;
        $this->_attrs = array();
        $this->_dtexts = array();
        $this->_htexts = array();
        $this->_kwords = null;
        $this->_score = -1;
        if (strlen($draft)) {
            $lines = explode("\n", $draft);
            $num = 0;
            $len = count($lines);
            while ($num < $len) {
                $line = $lines[$num];
                $num++;
                if (strlen($line) == 0) {
                    break;
                }
                if (substr($line, 0, 1) == '%') {
                    if (substr($line, 8) == "%VECTOR\t") {
                        $fields = explode("\t", $line);
                        $i = 1;
                        $flen = count($fields) - 1;
                        while ($i < $flen) {
                            $this->_kwords[$fields[$i]] = $fields[$i+1];
                            $i += 2;
                        }
                    } elseif (substr($line, 7) == "%SCORE\t") {
                        $fields = explode("\t", $line);
                        $this->_score = (int)$fields[1];
                    }
                    continue;
                }
                $line = Services_HyperEstraier_Utility::sanitize($line);
                if (strpos($line, '=')) {
                    list($key, $value) = explode('=', $line, 2);
                    $this->_attrs[$key] = $value;
                }
            }
            while ($num < $len) {
                $line = $lines[$num];
                $num++;
                if (strlen($line) == 0) {
                    continue;
                }
                if (substr($line, 0, 1) == "\t") {
                    if (strlen($line) > 1) {
                        $this->_htexts[] = substr($line, 1);
                    }
                } else {
                    $this->_dtexts[] = $line;
                }
            }
        }
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
     * Add an attribute.
     *
     * @param   string  $name   The name of an attribute.
     * @param   string  $value  The value of the attribute.
     *                          If it is `null', the attribute is removed.
     * @return  void
     * @access  public
     */
    public function addAttribute($name, $value = null)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string'), array($value, 'string', 'NULL')
        );
        $name = Services_HyperEstraier_Utility::sanitize($name);
        $value = Services_HyperEstraier_Utility::sanitize($value);
        if (!is_null($value)) {
            $this->_attrs[$name] = $value;
        } else {
            unset($this->_attrs[$name]);
        }
    }

    /**
     * Add a sentence of text.
     *
     * @param   string  $text   A sentence of text.
     * @return  void
     * @access  public
     */
    public function addText($text)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($text, 'string')
        );
        $text = Services_HyperEstraier_Utility::sanitize($text);
        if (strlen($text)) {
            $this->_dtexts[] = $text;
        }
    }

    /**
     * Add a hidden sentence.
     *
     * @param   string  $text   A hidden sentence.
     * @return  void
     * @access  public
     */
    public function addHiddenText($text)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($text, 'string')
        );
        $text = Services_HyperEstraier_Utility::sanitize($text);
        if (strlen($text)) {
            $this->_htexts[] = $text;
        }
    }

    /**
     * Attach keywords.
     *
     * @param   array   $kwords A list of keywords.
     *                          Keys of the map should be keywords of the document
     *                          and values should be their scores in decimal string.
     * @return  void
     * @access  public
     */
    public function setKeywords(array $kwords)
    {
        $this->_kwords = $kwords;
    }

    /**
     * Set the substitute score.
     *
     * @param   int     $score  The substitute score.
     *                          It it is negative, the substitute score setting is nullified.
     * @return  void
     * @access  public
     */
    public function setScore($score)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($score, 'integer')
        );
        $this->_score = $score;
    }

    // }}}
    // {{{ getter methods

    /**
     * Get the ID number.
     *
     * @return  int     The ID number of the document object.
     *                  If the object has never beenregistered, returns -1.
     * @access  public
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get an array of attribute names of a document object.
     *
     * @return  array   Attribute names.
     * @access  public
     */
    public function getAttributeNames()
    {
        $names = array_keys($this->_attrs);
        sort($names);
        return $names;
    }

    /**
     * Get the value of an attribute.
     *
     * @param   string  $name  The name of an attribute.
     * @return  string  The value of the attribute. If it does not exist, returns `null'.
     * @access  public
     */
    public function getAttribute($name)
    {
        SERVICES_HYPERESTRAIER_DEBUG && Services_HyperEstraier_Utility::checkTypes(
            array($name, 'string')
        );
        return (isset($this->_attrs[$name])) ? $this->_attrs[$name] : null;
    }

    /**
     * Get an array of sentences of the text.
     *
     * @return  array   Sentences of the text.
     * @access  public
     */
    public function getTexts()
    {
        return $this->_dtexts;
    }

    /**
     * Concatenate sentences of the text of a document object.
     *
     * @return  string  Concatenated sentences.
     * @access  public
     */
    public function catTexts()
    {
        return implode(' ', $this->_dtexts);
    }

    /**
     * Dump draft data of a document object.
     *
     * @return  string  The draft data.
     * @access  public
     */
    public function dumpDraft()
    {
        $buf = '';
        foreach ($this->getAttributeNames() as $name) {
            $buf .= sprintf("%s=%s\n", $name, $this->_attrs[$name]);
        }
        if ($this->_kwords) {
            $buf .= '%VECTOR';
            foreach ($this->_kwords as $key => $value) {
                $buf .= sprintf("\t%s\t%s", $key, $value);
            }
            $buf .= "\n";
        }
        $buf .= "\n";
        if ($this->_dtexts) {
            $buf .= implode("\n", $this->_dtexts) . "\n";
        }
        if ($this->_htexts) {
            $buf .= "\t" . implode("\n\t", $this->_htexts) . "\n";
        }
        return $buf;
    }

    /**
     * Get attached keywords.
     *
     * @return  array   A list of keywords and their scores in decimal string.
     *                  If no keyword is attached, `null' is returned.
     * @access  public
     */
    public function getKeywords()
    {
        return $this->_kwords;
    }

    /**
     * Get the substitute score.
     *
     * @return  int     The substitute score or -1 if it is not set.
     * @access  public
     */
    public function getScore()
    {
        return $this->_score;
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
