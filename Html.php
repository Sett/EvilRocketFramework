<?php
/**
 * Like SimpleXMLElement, but work with HTML.
 *
 * TODO :
 * 1) Complete all element parsers with useElementNameAsTagName;
 * 2) Make revers-operation - HTML-code into this object;
 * 3) Make possible parallel creating of unlimited HTML-fragments; 
 * @author Se#
 */
class Evil_Html
{
    /**
     * Known tags
     */
    const TEXT   = 'text';
    const DIV    = 'div';
    const LI     = 'li';
    const SELECT = 'select';
    const OPTION = 'option';
    const TABLE  = 'table';
    const TR     = 'tr';
    const TD     = 'td';
    const INPUT  = 'input';

    /**
     * Default tag
     * @var string
     */
    public static $defaultTag = 'text';

    /**
     * Use default tag if option 'tag' in element is missed. 
     * @var boolean
     */
    public static $useDefaultTagIfItMissed = true;

    /**
     * Use element name (first parameter in the __construct) as tag attribute. 
     * @var boolean
     */
    public static $useElementNameAsTagName = true;

    /**
     * Contain all information for current HTML-creation
     * @var array of arrays
     */
    protected static $_code = null;

    /**
     * Head element's name
     * @var string
     */
    protected static $_head = '';

    /**
     * Parent element's name
     * @var string
     */
    protected $_parent         = null;

    /**
     * Current element's name
     * @var string
     */
    protected $_current        = null;

    /**
     * Current element's options (attributes)
     * @var mixed
     */
    protected $_currentOptions = null;

    /**
     * Result HTML
     * @var string
     */
    protected $_result         = '';

    /**
     * If tag's type is missed, use default tag.
     * Attr $adding will be used for parallel creating of different HTML-code.
     * 
     * @param string $name
     * @param string,array $options
     * @param string $tagType default null
     * @param string $parent default null
     */
    public function __construct($name = '', $options = null, $tagType = null, $parent = null, $adding = false)
    {
        if(is_null($tagType) && empty($options)){
            $tagType = $name;
            $name    = $this->_generateName();
            $options = array();
        }

        if(is_null($tagType))
            $tagType = self::$defaultTag;

        if(empty($name))
            $name = $this->_generateName();

        if(is_string($tagType)){// we don't know how to work with not-a-string
            if(empty(self::$_head)){// check, if we are crerating head-element
                self::$_head = $name;
                self::$_code = array(); // new HTML-code is started
            }

            if(is_null($parent))// does current element is a child?
                self::$_code = array($name => array('tag' => $tagType, 'options' => $options));
            else
                self::$_code[$parent]['childs'][] = array($name => array('tag' => $tagType, 'options' => $options));

            $this->_setCurrent($name);// save current element's name
            $this->_setCurrentOptions($options); // save current element's options for comfort extracting
        }
    }

    protected function _generateName()
    {
        return sha1(mt_rand(1,9999) . time() . mt_rand(0,99999)) . md5(mt_rand(0,99999));
    }


    public function __toString()
    {
        echo ' === START === <br/>' . self::$_head . ':';
        print_r(self::$_code);
        return ' === END === ';
    }
    /**
     * Add child for current element.
     * @param string $name
     * @param string, array $options
     * @param string $htmlTagType default null
     * @return self
     */
    public function addChild($name, $options, $htmlTagType = null)
    {
        if(is_null($options) && is_null($htmlTagType)){
            $htmlTagType = $name;
            $options     = array();
            $name        = '';
        }
        $child = new self($name, $options, $htmlTagType, $this->_current, true);
        $child->_setParent($this->_current);

        return $child;
    }

    /**
     * Append child to the current parent.
     * 
     * @param string $name
     * @param mixed $options
     * @param string $htmlTagType default null
     * @return self
     */
    public function append($name, $options, $htmlTagType = null)
    {
        return new self($name, $options, $htmlTagType, $this->_parent, true);
    }

    /**
     * Generate HTML-code by current state of code
     * @return string
     */
    public function asHtml()
    {
        $this->_result = '';
        // Begin from the head
        $this->_forEach(self::$_head, self::$_code[self::$_head]);

        self::$_head = null;
        return $this->_result;
    }

    /**
     * Parses elements to create an HTML-code.
     *
     * @param string $name
     * @param array $options
     */
    protected function _forEach($name, $options)
    {
        if(isset(self::$_code[$name])){// it means current element if a child
            if($name != self::$_head){// head has already childs in it's options
                $options = array_merge($options, self::$_code[$name]);
                unset(self::$_code[$name]);// to not operate element twice
            }
        }

        if(!is_array($options))// we don't know how to operate not-an-array
            throw new Exception($name);

        if(!isset($options['tag'])){// tag is missed
            if(self::$useDefaultTagIfItMissed)// use default
                $options['tag'] = self::$defaultTag;
            else
                throw new Exception(' Incorrect element "' . $name . '" ');
        }

        $method = '_tag' . ucfirst($options['tag']);// create a method name for current tag
        if(method_exists($this, $method))
            $this->_result .= $this->$method(true, $options['options']);
        else
            throw new Exception(' Unknown tag "' . $options['tag'] . '" ');

        /**
         * TODO : make it more correct - reduce cicles
         */
        if(isset($options['childs'])){// current element has a children
            foreach($options['childs'] as $index=>$childConfig){
                foreach($options['childs'][$index] as $childName => $childOptions){
                    $this->_forEach($childName, $childOptions);
                    break;
                }
            }
        }
        $this->_result .= $this->$method(false, $options['options']);// pass options, as we can realise post-options in the future

    }

    /**
     * Realise formating and creating simple open-close tag (may with a text).
     *
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _simpleTag($name, $start, $options, $hasText = false)
    {
        if(false == $start)
            return '</' . $name . '>';

        $text = '';
        if($hasText){
            if(is_string($options))
                $text = $options;
            elseif(isset($options['text'])){
                $text = $options['text'];
                unset($options['text']);
            }
        }
        $result = '<' . $name . ' ';
        if(is_array($options)){
            if(!isset($options['name']) && self::$useElementNameAsTagName)
                $options['name'] = $name;

            foreach($options as $attr => $value){
                $attr  = (string) $attr;
                $value = (string) $value;
                $result .= $attr . '="' . $value . '" ';
            }
        }
        return $result .= '>' . $text;
    }

    /**
     * Realise formating and creating TABLE-tag.
     *
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagTable($start, $options = null)
    {
        return $this->_simpleTag(self::TABLE, $start, $options);
    }

    /**
     * Realise formating and creating TR-tag.
     *
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagTr($start, $options = null)
    {
        return $this->_simpleTag(self::TR, $start, $options);
    }

    /**
     * Realise formating and creating TD-tag.
     *
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagTd($start, $options)
    {
        return $this->_simpleTag(self::TD, $start, $options, true);
    }

    protected function _tagInput($start, $options)
    {
        if(false == $start)
            return '';

        $result = '<input ';
        foreach($options as $attr => $value){
            $attr  = (string) $attr;
            $value = (string) $value;
            if( ('readonly' == $attr) && empty($value) )
                continue;

            $result .= $attr . '="' . $value . '" ';
        }
        return $result . '/>';
    }

    /**
     * Realise formating and creating SELECT-tag.
     * 
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagSelect($start, $options = null)
    {
        if(false == $start)
            return '</select>';

        $result = '<select ';
        $hasOptions = false;
        if(!is_array($options))
            return '';

        foreach($options as $attr => $value){
            if(self::OPTION == $attr){
                $hasOptions = true;
                continue;
            }
            $attr  = (string) $attr;
            $value = (string) $value;

            $result .= $attr . '="' . $value . '" ';
        }

        $result .= '>';
        if($hasOptions){
            $list = $options['options'];
            foreach($list as $option){
                $result .= $this->_tagOption(true, $option);
                $result .= $this->_tagOption(false);
            }
       }
       return $result;
    }

    /**
     * Realise formatting and creating OPTION-tag.
     * 
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagOption($start, $options = null)
    {
        if(false == $start)
            return '</option>';

        $result = '';
        if(!is_array($options))
            return '';

        $result .= '<option ';
        if(isset($options['text'])){
            $text = $options['text'];
            unset($options['text']);
        }
        else
            $text = '';

        foreach($options as $attr => $value)
            $result .= $attr . '="' . $value . '" ';

        $result .= '>' . $text;
        return $result;
    }

    /**
     * Realise formating and creating LI-tag.
     * 
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagLi($start, $options = null)
    {
        if(false == $start)
            return '</li>';

        $text  = '';
        $attrs = '';

        if(is_string($options))
            $text = $options;
        elseif(is_array($options)){
            $attrs .= ' ';
            if(isset($options['text'])){
                $text = $options['text'];
                unset($options['text']);
            }

           foreach($options as $attr => $value)
               $attrs .= $attr . '="' . $value . '" ';
        }
        return '<li'.$attrs.'>' . $text;
    }

    /**
     * Realise formating and creating simple text.
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagText($start, $options = null)
    {
        if(false == $start)
            return '';

        $result = '';
        if(is_string($options))
            return $options;

        foreach($options as $value)
            $result .= $value;

        return $result;
    }

    /**
     * Realise formating and creating DIV-tag
     * @param boolean $start
     * @param mixed $options
     * @return string
     */
    protected function _tagDiv($start, $options = null)
    {
        if(false == $start)
            return '</div>';

        $result = '<div ';
        foreach($options as $attr => $value){
            if('childs' == $attr)
                continue;
            $result .= $attr . '="' . $value . '" ';
        }

        $result .= '>';
        return $result;
    }

    /**
     * Set parent for current element
     * @param string $name
     */
    protected function _setParent($name)
    {
        $this->_parent = $name;
    }

    /**
     * Set name of a current element
     * @param string $name
     */
    protected function _setCurrent($name)
    {
        $this->_current = $name;
    }

    /**
     * Set options (attributes) for current element
     * @param mixed $options
     */
    protected function _setCurrentOptions($options)
    {
        $this->_currentOptions = $options;
    }

    /**
     * Return options (attributes) of a current element
     * @return mixed
     */
    public function getAttrs()
    {
        return $this->_currentOptions;
    }
}