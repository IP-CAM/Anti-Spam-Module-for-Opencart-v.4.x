<?php

namespace Cleantalk\Common;

/**
 * Class Get
 * Safety handler for $_GET
 *
 * @usage \Cleantalk\Common\Get::get( $name );
 *
 * @package Cleantalk\Common
 */
class Get extends ServerVariables
{
    public static $instance;

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
     * Gets given $_GET variable and seva it to memory
     * @param $name
     *
     * @return mixed|string
     */
    protected function getVariable($name)
    {
        // Return from memory. From $this->variables
        if ( isset(static::$instance->variables[$name]) ) {
            return static::$instance->variables[$name];
        }

        if ( function_exists('filter_input') ) {
            $value = filter_input(INPUT_GET, $name);
        }

        if ( empty($value) ) {
            $value = isset($_GET[$name]) ? $_GET[$name] : '';
        }

        // Remember for thurther calls
        static::getInstance()->remebmerVariable($name, $value);

        return $value;
    }
}
