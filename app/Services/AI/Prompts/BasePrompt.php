<?php

namespace App\Services\AI\Prompts;

abstract class BasePrompt
{
    /**
     * Get the system prompt for the entity
     *
     * @param array $context Additional context
     * @return string
     */
    abstract public static function getSystemPrompt(array $context = []): string;

    /**
     * Safely extract a value from context with fallback
     *
     * @param array $context
     * @param string $key
     * @param string $default
     * @return string
     */
    protected static function contextValue(array $context, string $key, string $default = ''): string
    {
        return $context[$key] ?? $default;
    }

    /**
     * Truncate text to prevent excessive token usage
     *
     * @param string $text
     * @param int $maxLength
     * @return string
     */
    protected static function truncate(string $text, int $maxLength = 1000): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        return substr($text, 0, $maxLength - 3) . '...';
    }

    /**
     * Format an array of items as a string with a max number of items
     *
     * @param array $items
     * @param int $maxItems
     * @param string $prefix
     * @return string
     */
    protected static function formatList(array $items, int $maxItems = 5, string $prefix = '- '): string
    {
        if (empty($items)) {
            return '';
        }

        $items = array_slice($items, 0, $maxItems);
        $result = implode("\n", array_map(fn($item) => "{$prefix}{$item}", $items));

        return $result;
    }

    /**
     * Extract the most relevant parts of code
     *
     * @param string $code
     * @param int $maxLines
     * @return string
     */
    protected static function extractRelevantCode(string $code, int $maxLines = 100): string
    {
        if (empty($code)) {
            return '';
        }

        $lines = explode("\n", $code);

        if (count($lines) <= $maxLines) {
            return $code;
        }

        // Extract most relevant parts (e.g., classes, function signatures)
        // This is a simple approach - in production you might use more sophisticated code parsing
        $relevantLines = [];
        $inRelevantBlock = false;
        $currentBlock = [];

        foreach ($lines as $line) {
            // Look for class or function declarations, imports, etc.
            if (preg_match('/(class|function|public|private|protected|import|use|interface|trait|abstract|final|@api)/i', $line)) {
                if (!empty($currentBlock)) {
                    $relevantLines = array_merge($relevantLines, $currentBlock);
                }
                $currentBlock = [$line];
                $inRelevantBlock = true;
                continue;
            }

            // Add to current block if we're capturing
            if ($inRelevantBlock) {
                $currentBlock[] = $line;

                // End block when we hit closing bracket at beginning of line
                if (preg_match('/^\s*}\s*$/', $line)) {
                    $inRelevantBlock = false;
                }

                // Limit block size
                if (count($currentBlock) > 20) {
                    $currentBlock[] = "// ... truncated ...";
                    $inRelevantBlock = false;
                }
            }

            // Check if we've collected enough
            if (count($relevantLines) >= $maxLines) {
                break;
            }
        }

        // Add the last block if needed
        if (!empty($currentBlock)) {
            $relevantLines = array_merge($relevantLines, $currentBlock);
        }

        // If we didn't get enough relevant parts, use the beginning and end of the file
        if (count($relevantLines) < $maxLines / 2) {
            $beginning = array_slice($lines, 0, $maxLines / 2);
            $ending = array_slice($lines, -$maxLines / 2);
            $relevantLines = array_merge($beginning, ["// ... middle of file truncated ..."], $ending);
        }

        return implode("\n", array_slice($relevantLines, 0, $maxLines));
    }

    /**
     * Format metadata to include in prompts
     *
     * @param array $metadata
     * @return string
     */
    protected static function formatMetadata(array $metadata): string
    {
        if (empty($metadata)) {
            return '';
        }

        $result = "Metadata:\n";
        foreach ($metadata as $key => $value) {
            if (is_scalar($value)) {
                $result .= "- {$key}: {$value}\n";
            }
        }

        return $result;
    }
}
