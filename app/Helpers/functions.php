<?php

if (! function_exists('filter')) {
    function filter($value, $type){
        $filter = \HxeVendor\Filter::instance();
        return $filter->filter($value, $type);
    }
}

?>