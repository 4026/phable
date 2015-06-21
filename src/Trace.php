<?php
namespace Four026\Phable;

/**
 * A single interpretation of a provided grammar.
 */
class Trace
{
    /**
     * @var Grammar
     */
    private $grammar;

    /**
     * @var string
     */
    private $start_symbol = 'origin';
    /**
     * @var int
     */
    private $seed = null;

    /**
     * An associative array mapping a symbol defined at runtime to the node that represents that symbol's value.
     * @var Node[]
     */
    private $runtime_symbols = [];

    private $text;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * @param string $start_symbol
     * @return $this
     */
    public function setStartSymbol($start_symbol)
    {
        $this->start_symbol = $start_symbol;
        return $this;
    }

    /**
     * @param int $seed
     * @return $this
     */
    public function setSeed($seed)
    {
        $this->seed = $seed;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        if (isset($this->text)) {
            return $this->text;
        }

        mt_srand($this->seed);

        //It is assumed that the starting symbol contains exactly one Node.
        $starting_node = $this->grammar->getNodesForSymbol($this->start_symbol)[0];

        $this->text = $this->processNode($starting_node);

        return $this->text;
    }

    /**
     * @param Node $node
     * @param string|null $variant
     * @return string
     */
    private function processNode(Node $node, $variant = null)
    {
        $node_string = isset($variant) ? $node->getVariant($variant) : $node->text;

        //Process the node string for any runtime symbol definitions, and remove them.
        $node_string = preg_replace_callback(
            '/\[(\w+?:.+?)\]/',
            function($matches) {
                list($runtime_symbol_name, $runtime_symbol_value) = explode(':', $matches[1]);

                $runtime_symbol_node = new Node($this->processString($runtime_symbol_value));

                $this->runtime_symbols[$runtime_symbol_name] = $runtime_symbol_node;

                echo "Defined new runtime node: $runtime_symbol_name = {$runtime_symbol_node->text}";

                return '';
            },
            $node_string
        );

        //Render any symbols in the remainder of the node string.
        return $this->processString($node_string);
    }

    /**
     * @param string $string
     * @return string
     */
    private function processString($string)
    {
        return preg_replace_callback(
            '/#[\w.]+?#/',
            function ($matches) { return $this->processSymbol($matches[0]); },
            $string
        );
    }

    /**
     * @param string $symbol A symbol identifier, with or without surrounding '#' characters.
     *
     * @return string
     */
    private function processSymbol($symbol)
    {
        $symbol = str_replace('#', '', $symbol);

        if (strpos($symbol, '.') !== false) {
            list($symbol_name, $symbol_variant) = explode('.', $symbol);
        } else {
            $symbol_name = $symbol;
            $symbol_variant = null;
        }

        if (array_key_exists($symbol_name, $this->runtime_symbols)) {
            $symbol_node = $this->runtime_symbols[$symbol_name];
        } else {
            $possible_symbol_nodes = $this->grammar->getNodesForSymbol($symbol_name);
            $symbol_node = $possible_symbol_nodes[mt_rand(0, count($possible_symbol_nodes) - 1)];
        }

        return $this->processNode($symbol_node, $symbol_variant);
    }
}