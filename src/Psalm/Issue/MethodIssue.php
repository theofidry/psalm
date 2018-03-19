<?php
namespace Psalm\Issue;

abstract class MethodIssue extends CodeIssue
{
    /**
     * @param string        $message
     * @param CodeLocation  $code_location
     */
    public function __construct(
        $message,
        CodeLocation $code_location
    ) {
        parent::__construct($message, $code_location);
    }
}
