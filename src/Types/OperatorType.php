<?php

namespace FastRaven\Types;

/**
 * Enum OperatorType
 *
 * OperatorType is an enum that defines the type of SQL operator that can be used.
 */
enum OperatorType: string {
    case EQUAL = "=";
    case NOT_EQUAL = "!=";
    case GREATER_THAN = ">";
    case LESS_THAN = "<";
    case GREATER_THAN_OR_EQUAL = ">=";
    case LESS_THAN_OR_EQUAL = "<=";
    case LIKE = "LIKE";
    case NOT_LIKE = "NOT LIKE";
    case IN = "IN";
    case NOT_IN = "NOT IN";
    case BETWEEN = "BETWEEN";
    case NOT_BETWEEN = "NOT BETWEEN";
    case IS = "IS";
    case IS_NOT = "IS NOT";
}