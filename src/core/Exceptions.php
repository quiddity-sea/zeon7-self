<?php
/**
 * Exception Hierarchy for Zeon7 Application
 */

// Base exception for all Zeon7-specific exceptions
class Zeon7Exception extends Exception {}

// Database-related exceptions
class DatabaseException extends Zeon7Exception {}

// Validation failures
class ValidationException extends Zeon7Exception {}

// Rate limiting violations
class RateLimitException extends Zeon7Exception {}

// Resource not found
class NotFoundException extends Zeon7Exception {}

// Gemini API failures
class GeminiApiException extends Zeon7Exception {}

// Generic API failures
class ApiException extends Zeon7Exception {}

// File operation failures
class FileException extends Zeon7Exception {}

// CSRF token validation failure
class CsrfException extends Zeon7Exception {}
