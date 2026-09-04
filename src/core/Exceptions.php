<?php
/**
 * Application Exceptions
 */

class AppException extends \RuntimeException { }
class_alias('AppException', 'Zeon7Exception');

class DatabaseException extends AppException { }
class ValidationException extends AppException { }
class RateLimitException extends AppException { }
class NotFoundException extends AppException { }
class ApiException extends AppException { }
class GeminiApiException extends AppException { }
class CsrfException extends AppException { }
class AuthException extends AppException { }
class ConfigException extends AppException { }
