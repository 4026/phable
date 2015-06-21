<?php

namespace Four026\Phable;

/**
 * Class for loading, storing and parsing a generative grammar.
 *
 * @package Four026\Phable
 */
class Grammar
{
    /**
     * Associative array mapping symbols to arrays of Nodes that can be used in this grammar.
     *
     * @var Node[][]
     */
    private $nodes = [];

    /**
     * @param string $grammar_path The path to the file to use as the specification for this grammar.
     */
    public function __construct($grammar_path)
    {
        if (!file_exists($grammar_path)) {
            throw new \InvalidArgumentException("Could not find specified grammar file: $grammar_path");
        }

        if (($handle = fopen($grammar_path, "r")) === false) {
            throw new \RuntimeException("Unable to open grammar file: $grammar_path");
        }
        fclose($handle);

        //Read in file as JSON.
        $file_data = json_decode(file_get_contents($grammar_path), true);
        if (!isset($file_data) || json_last_error() != JSON_ERROR_NONE) {
            throw new \RuntimeException("Error parsing JSON: " . json_last_error_msg());
        }

        foreach ($file_data as $symbol => $nodes) {
            $this->nodes[$symbol] = [];
            foreach ($nodes as $node) {
                if (is_string($node)) {
                    $this->nodes[$symbol][] = new Node($node);
                } else {
                    $this->nodes[$symbol][] = new Node($node['normal'], array_diff_key($node, ['normal']));
                }
            }
        }
    }

    /**
     * @param $symbol
     *
     * @return Node[]
     */
    public function getNodesForSymbol($symbol)
    {
        if (!array_key_exists($symbol, $this->nodes)) {
            throw new \DomainException("Unknown symbol '$symbol'");
        }

        return $this->nodes[$symbol];
    }
}