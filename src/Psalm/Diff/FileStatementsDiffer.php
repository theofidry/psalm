<?php

namespace Psalm\Diff;

use PhpParser;

/**
 * @internal
 */
class FileStatementsDiffer extends Differ
{
    /**
     * Calculate diff (edit script) from $a to $b.
     *
     * @param string $a_code
     * @param string $b_code
     * @param PhpParser\Node\Stmt[] $a
     * @param PhpParser\Node\Stmt[] $b New array
     *
     * @return array{
     *      0: array<int, string>,
     *      1: array<int, string>,
     *      2: array<int, string>,
     *      3: array<int, array{0: int, 1: int, 2: int, 3: int}>
     * }
     */
    public static function diff(array $a, array $b, $a_code, $b_code)
    {
        list($trace, $x, $y, $bc) = self::calculateTrace(
            /**
             * @param string $a_code
             * @param string $b_code
             * @psalm-suppress UnusedParam
             *
             * @return bool
             */
            function (PhpParser\Node\Stmt $a, PhpParser\Node\Stmt $b, $a_code, $b_code) {
                if (get_class($a) !== get_class($b)) {
                    return false;
                }

                if (($a instanceof PhpParser\Node\Stmt\Namespace_ && $b instanceof PhpParser\Node\Stmt\Namespace_)
                    || ($a instanceof PhpParser\Node\Stmt\Class_ && $b instanceof PhpParser\Node\Stmt\Class_)
                    || ($a instanceof PhpParser\Node\Stmt\Interface_ && $b instanceof PhpParser\Node\Stmt\Interface_)
                ) {
                    return (string)$a->name === (string)$b->name;
                }

                return false;
            },
            $a,
            $b,
            $a_code,
            $b_code
        );

        $diff = self::extractDiff($trace, $x, $y, $a, $b, $bc);

        $keep = [];
        $keep_signature = [];
        $delete = [];
        $diff_map = [];

        foreach ($diff as $diff_elem) {
            if ($diff_elem->type === DiffElem::TYPE_KEEP) {
                if ($diff_elem->old instanceof PhpParser\Node\Stmt\Namespace_
                        && $diff_elem->new instanceof PhpParser\Node\Stmt\Namespace_
                ) {
                    $namespace_keep = NamespaceStatementsDiffer::diff(
                        (string) $diff_elem->old->name,
                        $diff_elem->old->stmts,
                        $diff_elem->new->stmts,
                        $a_code,
                        $b_code
                    );

                    $keep = array_merge($keep, $namespace_keep[0]);
                    $keep_signature = array_merge($keep_signature, $namespace_keep[1]);
                    $delete = array_merge($delete, $namespace_keep[2]);
                    $diff_map = array_merge($diff_map, $namespace_keep[3]);
                } elseif (($diff_elem->old instanceof PhpParser\Node\Stmt\Class_
                        && $diff_elem->new instanceof PhpParser\Node\Stmt\Class_)
                    || ($diff_elem->old instanceof PhpParser\Node\Stmt\Interface_
                        && $diff_elem->new instanceof PhpParser\Node\Stmt\Interface_)
                ) {
                    $class_keep = ClassStatementsDiffer::diff(
                        (string) $diff_elem->old->name,
                        $diff_elem->old->stmts,
                        $diff_elem->new->stmts,
                        $a_code,
                        $b_code
                    );

                    $keep = array_merge($keep, $class_keep[0]);
                    $keep_signature = array_merge($keep_signature, $class_keep[1]);
                    $delete = array_merge($delete, $class_keep[2]);
                    $diff_map = array_merge($diff_map, $class_keep[3]);
                }
            }
        }

        return [$keep, $keep_signature, $delete, $diff_map];
    }
}