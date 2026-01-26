<?php

namespace FastRaven\Components\Data;

use FastRaven\Types\OperatorType;

class Condition {
    #----------------------------------------------------------------------
    #\ VARIABLES

    private string $left;
        public function getLeft(): string { return $this->left; }
        public function setLeft(string $left): void { $this->left = $left; }
    private OperatorType $operator;
        public function getOperator(): OperatorType { return $this->operator; }
        public function setOperator(OperatorType $operator): void { $this->operator = $operator; }
    private string|int|float|bool|array $right;
        public function getRight(): string|int|float|bool|array { return $this->right; }
        public function setRight(string|int|float|bool|array $right): void { $this->right = $right; }

    #/ VARIABLES
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ INIT

    /**
     * Create a new Condition instance.
     *
     * @param string $left What to compare.
     * @param OperatorType $operator The operator of the condition.
     * @param string|int|float|bool|array $right What to compare against.
     *
     * @return Condition
     */
    public static function new(string $left, OperatorType $operator, string|int|float|bool|array $right): Condition {
        return new Condition($left, $operator, $right);
    }

    /**
     * Create a new Condition instance with the operator EQUAL.
     *
     * @param string $left What to compare.
     * @param string|int|float|bool $right What to compare against.
     *
     * @return Condition
     */
    public static function equals(string $left, string|int|float|bool $right): Condition {
        return new Condition($left, OperatorType::EQUAL, $right);
    }

    /**
     * Create a new Condition instance with the operator NOT_EQUAL.
     *
     * @param string $left What to compare.
     * @param string|int|float|bool $right What to compare against.
     *
     * @return Condition
     */
    public static function notEquals(string $left, string|int|float|bool $right): Condition {
        return new Condition($left, OperatorType::NOT_EQUAL, $right);
    }

    /**
     * Create a new Condition instance with the operator GREATER_THAN.
     *
     * @param string $left What to compare.
     * @param int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function greaterThan(string $left, int|float $right): Condition {
        return new Condition($left, OperatorType::GREATER_THAN, $right);
    }

    /**
     * Create a new Condition instance with the operator LESS_THAN.
     *
     * @param string $left What to compare.
     * @param int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function lessThan(string $left, int|float $right): Condition {
        return new Condition($left, OperatorType::LESS_THAN, $right);
    }

    /**
     * Create a new Condition instance with the operator GREATER_THAN_OR_EQUAL.
     *
     * @param string $left What to compare.
     * @param int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function greaterThanOrEqual(string $left, int|float $right): Condition {
        return new Condition($left, OperatorType::GREATER_THAN_OR_EQUAL, $right);
    }

    /**
     * Create a new Condition instance with the operator LESS_THAN_OR_EQUAL.
     *
     * @param string $left What to compare.
     * @param int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function lessThanOrEqual(string $left, int|float $right): Condition {
        return new Condition($left, OperatorType::LESS_THAN_OR_EQUAL, $right);
    }

    /**
     * Create a new Condition instance with the operator LIKE.
     *
     * @param string $left What to compare.
     * @param string $right What to compare against.
     *
     * @return Condition
     */
    public static function like(string $left, string $right): Condition {
        return new Condition($left, OperatorType::LIKE, $right);
    }

    /**
     * Create a new Condition instance with the operator NOT_LIKE.
     *
     * @param string $left What to compare.
     * @param string $right What to compare against.
     *
     * @return Condition
     */
    public static function notLike(string $left, string $right): Condition {
        return new Condition($left, OperatorType::NOT_LIKE, $right);
    }

    /**
     * Create a new Condition instance with the operator IN.
     *
     * @param string $target What to compare.
     * @param array $variable What to compare against.
     *
     * @return Condition
     */
    public static function in(string $left, array $right): Condition {
        return new Condition($left, OperatorType::IN, $right);
    }

    /**
     * Create a new Condition instance with the operator NOT_IN.
     *
     * @param string $left What to compare.
     * @param array $right What to compare against.
     *
     * @return Condition
     */
    public static function notIn(string $left, array $right): Condition {
        return new Condition($left, OperatorType::NOT_IN, $right);
    }

    /**
     * Create a new Condition instance with the operator BETWEEN.
     *
     * @param string $left What to compare.
     * @param string|int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function between(string $left, string|int|float $right): Condition {
        return new Condition($left, OperatorType::BETWEEN, $right);
    }

    /**
     * Create a new Condition instance with the operator NOT_BETWEEN.
     *
     * @param string $left What to compare.
     * @param string|int|float $right What to compare against.
     *
     * @return Condition
     */
    public static function notBetween(string $left, string|int|float $right): Condition {
        return new Condition($left, OperatorType::NOT_BETWEEN, $right);
    }

    /**
     * Create a new Condition instance with the operator IS.
     *
     * @param string $left What to compare.
     * @param string|bool $right What to compare against.
     *
     * @return Condition
     */
    public static function is(string $left, string|bool $right): Condition {
        return new Condition($left, OperatorType::IS, $right);
    }

    /**
     * Create a new Condition instance with the operator IS_NOT.
     *
     * @param string $left What to compare.
     * @param string|bool $right What to compare against.
     *
     * @return Condition
     */
    public static function isNot(string $left, string|bool $right): Condition {
        return new Condition($left, OperatorType::IS_NOT, $right);
    }

    private function  __construct(string $left, OperatorType $operator, string|int|float|bool|array $right) {
        $this->left = $left;
        $this->right = $right;
        $this->operator = $operator;
    }

    #/ INIT
    #----------------------------------------------------------------------
    
    #----------------------------------------------------------------------
    #\ PRIVATE FUNCTIONS



    #/ PRIVATE FUNCTIONS
    #----------------------------------------------------------------------

    #----------------------------------------------------------------------
    #\ METHODS

    public function parseToQuery(bool $offuscate = true): string {
        if(is_array($this->right)) {
            $rightValue = $offuscate ? 
                implode(", ", array_fill(0, count($this->right), "?")) : 
                implode(", ", $this->right);
            return $this->left . " " . $this->operator->value . " (" . $rightValue . ")";
        } else if(is_bool($this->right)) {
            $rightValue = $offuscate ? "?" : ($this->right ? "1" : "0");
            return $this->left . " " . $this->operator->value . " " . $rightValue;
        } else {
            $rightValue = $offuscate ? "?" : $this->right;
            return $this->left . " " . $this->operator->value . " " . $rightValue;
        }
    }

    #/ METHODS
    #----------------------------------------------------------------------
}