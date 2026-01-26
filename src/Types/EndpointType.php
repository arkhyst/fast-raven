<?php

namespace FastRaven\Types;

/**
 * Enum EndpointType
 *
 * EndpointType is an enum that defines the type of middleware that Routers, Endpoints and other components use.
 */
enum EndpointType: string {
    case VIEW = "VIEW";
    case API = "API";
    case CDN = "CDN";
    case ROUTER = "ROUTER";
}