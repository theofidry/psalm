<?php
namespace Psalm\Type\Atomic;

use Psalm\FunctionLikeParameter;
use Psalm\Type\Union;

trait CallableTrait
{
    /**
     * @var array<int, FunctionLikeParameter>|null
     */
    public $params = [];

    /**
     * @var Union|null
     */
    public $return_type;

    /**
     * Constructs a new instance of a generic type
     *
     * @param string                            $value
     * @param array<int, FunctionLikeParameter> $params
     * @param Union                             $return_type
     */
    public function __construct($value = 'callable', array $params = null, Union $return_type = null)
    {
        $this->value = $value;
        $this->params = $params;
        $this->return_type = $return_type;
    }
}
