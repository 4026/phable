<?php
namespace Four026\Phable;

/**
 * A single word loaded from the dictionary.
 *
 * @package Four026\Phable
 * @property-read string $text The (literal, unparsed) text content of the node.
 */
class Node
{
    /**
     * The (literal, unparsed) text content of the node.
     *
     * @var string
     */
    private $text;

    /**
     * Associative array of variants associated with this node.
     *
     * @var string[]
     */
    public $variants;

    /**
     * @param string   $word
     * @param string[] $variants
     */
    public function __construct($word, $variants = [])
    {
        $this->text     = $word;
        $this->variants = $variants;
    }

    public function __get($var_name)
    {
        switch ($var_name) {
            case 'text':
                return $this->text;
            default:
                $trace = debug_backtrace();
                trigger_error(
                    'Undefined property via __get(): ' . $var_name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
                    E_USER_NOTICE
                );

                return null;
        }
    }

    public function __toString()
    {
        return $this->text;
    }

    /**
     * @param string $variant_name
     *
     * @return string
     */
    public function getVariant($variant_name)
    {
        //Use the defined variant, if there was one...
        if (array_key_exists($variant_name, $this->variants)) {
            return $this->variants[$variant_name];
        }

        //Otherwise, use built-in variants.
        switch ($variant_name) {
            case 'capitalize':
            case 'capitalise':
                return ucfirst($this->text);

            case 's':
            case 'plural':
                return $this->text.'s';

            case 'ed':
            case 'past':
                return $this->text.'ed';

            case 'a':
                return in_array($this->text[0], ['a', 'e', 'i', 'o', 'u']) ? 'an '.$this->text : 'a '.$this->text;
        }

        return $this->text;
    }
}