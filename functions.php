<?php

defined('WPINC') || die();

function has_fancy_product_page(int $product_id): bool
{
    return !empty(\Fancy_Product_Page\get_fancy_product_page_id($product_id));
}
