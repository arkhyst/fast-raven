<?php

namespace FastRaven\Types;

/**
 * Enum QueryType
 *
 * QueryType is an enum that defines the type of SQL query that can be executed.
 */
enum QueryType: string {
    case SELECT = "SELECT";
    case INSERT = "INSERT";
    case UPDATE = "UPDATE";
    case DELETE = "DELETE";
    case COUNT = "COUNT";
}