<?php

namespace FastRaven\Tests\Components\Data;

use PHPUnit\Framework\TestCase;
use FastRaven\Components\Data\Condition;
use FastRaven\Types\OperatorType;

class ConditionTest extends TestCase
{
    public function testNewCreatesCondition(): void
    {
        $condition = Condition::new("col", OperatorType::EQUAL, "val");
        
        $this->assertEquals("col", $condition->getLeft());
        $this->assertEquals(OperatorType::EQUAL, $condition->getOperator());
        $this->assertEquals("val", $condition->getRight());
    }

    public function testFactories(): void
    {
        $c1 = Condition::equals("a", 1);
        $this->assertEquals(OperatorType::EQUAL, $c1->getOperator());

        $c2 = Condition::notEquals("a", 1);
        $this->assertEquals(OperatorType::NOT_EQUAL, $c2->getOperator());

        $c3 = Condition::greaterThan("a", 1);
        $this->assertEquals(OperatorType::GREATER_THAN, $c3->getOperator());

        $c4 = Condition::lessThan("a", 1);
        $this->assertEquals(OperatorType::LESS_THAN, $c4->getOperator());

        $c5 = Condition::greaterThanOrEqual("a", 1);
        $this->assertEquals(OperatorType::GREATER_THAN_OR_EQUAL, $c5->getOperator());

        $c6 = Condition::lessThanOrEqual("a", 1);
        $this->assertEquals(OperatorType::LESS_THAN_OR_EQUAL, $c6->getOperator());

        $c7 = Condition::like("a", "%v%");
        $this->assertEquals(OperatorType::LIKE, $c7->getOperator());

        $c8 = Condition::notLike("a", "%v%");
        $this->assertEquals(OperatorType::NOT_LIKE, $c8->getOperator());

        $c9 = Condition::in("a", [1, 2]);
        $this->assertEquals(OperatorType::IN, $c9->getOperator());

        $c10 = Condition::notIn("a", [1, 2]);
        $this->assertEquals(OperatorType::NOT_IN, $c10->getOperator());

        $c11 = Condition::between("a", 10);
        $this->assertEquals(OperatorType::BETWEEN, $c11->getOperator());

        $c12 = Condition::notBetween("a", 10);
        $this->assertEquals(OperatorType::NOT_BETWEEN, $c12->getOperator());

        $c13 = Condition::is("a", true);
        $this->assertEquals(OperatorType::IS, $c13->getOperator());

        $c14 = Condition::isNot("a", true);
        $this->assertEquals(OperatorType::IS_NOT, $c14->getOperator());
    }

    public function testParseToQueryObfuscated(): void
    {
        $c = Condition::equals("col", "val");
        $this->assertEquals("col = ?", $c->parseToQuery(true));

        $c = Condition::in("col", [1, 2]);
        $this->assertEquals("col IN (?, ?)", $c->parseToQuery(true));

        $c = Condition::is("col", true);
        $this->assertEquals("col IS ?", $c->parseToQuery(true));
    }

    public function testParseToQueryNotObfuscated(): void
    {
        $c = Condition::equals("col", "val");
        $this->assertEquals("col = val", $c->parseToQuery(false));

        $c = Condition::in("col", [1, 2]);
        $this->assertEquals("col IN (1, 2)", $c->parseToQuery(false));

        $c = Condition::is("col", true);
        $this->assertEquals("col IS 1", $c->parseToQuery(false));
    }
}
