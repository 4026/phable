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
    private $start_symbol = 'story';
    /**
     * @var int
     */
    private $seed = null;

    /**
     * @var string[][]
     */
    private $tokens = array();

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
        return preg_replace_callback(
            '/#([\w.]+)#/',
            function ($matches) {
                $symbol = $matches[1];
                if (strpos($symbol, '.') !== false) {
                    list($symbol_name, $symbol_variant) = explode('.', $symbol);
                } else {
                    $symbol_name = $symbol;
                    $symbol_variant = null;
                }

                $possible_symbol_nodes = $this->grammar->getNodesForSymbol($symbol_name);
                $symbol_node = $possible_symbol_nodes[mt_rand(0, count($possible_symbol_nodes) - 1)];
                return $this->processNode($symbol_node, $symbol_variant);
            },
            isset($variant) ? $node->getVariant($variant) : $node->text
        );
    }
}