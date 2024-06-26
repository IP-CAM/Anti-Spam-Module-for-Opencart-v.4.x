<?php

namespace Cleantalk\Common;

/**
 * Class ServerVariables
 * Safety handler for ${_SOMETHING}
 *
 * @usage \Cleantalk\Common\{SOMETHING}::get( $name );
 *
 * @package Cleantalk\Common
 */
class ServerVariables
{
    public static $instance;
    public $variables = [];

    public function __construct()
    {
    }

    public function __wakeup()
    {
    }

    public function __clone()
    {
    }

    /**
     * Constructor
     * @return $this
     */
    public static function getInstance()
    {
        if ( !isset(static::$instance) ) {
            static::$instance = new static();
            static::$instance->init();
        }
        return static::$instance;
    }

    /**
     * Alternative constructor
     */
    protected function init()
    {
    }

    /**
     * Gets variable from ${_SOMETHING}
     *
     * @param $name
     *
     * @return string ${_SOMETHING}[ $name ]
     */
    public static function get($name)
    {
        return static::getInstance()->getVariable($name);
    }

    /**
     * BLUEPRINT
     * Gets given ${_SOMETHING} variable and seva it to memory
     * @param $name
     *
     * @return mixed|string
     */
    protected function getVariable($name)
    {
        return true;
    }

    /**
     * Save variable to $this->variables[]
     *
     * @param string $name
     * @param string $value
     */
    protected function remebmerVariable($name, $value)
    {
        static::$instance->variables[$name] = $value;
    }

    /**
     * Checks if variable contains given string
     *
     * @param string $var Haystack to search in
     * @param string $string Needle to search
     *
     * @return bool|int
     */
    public static function hasString($var, $string)
    {
        return stripos(self::get($var), $string) !== false;
    }
}
