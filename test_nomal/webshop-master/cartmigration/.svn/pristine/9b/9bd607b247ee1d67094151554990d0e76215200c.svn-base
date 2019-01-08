<?php

class LECM_Model_Seo_Shopify_Squarespace_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        return array(
            array(
                'request_path' => '/' . ltrim($category['code'], '/')
            )
        );
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $url_prd = unserialize($product['url']);
        return array(
            array(
                'request_path' => '/' . ltrim($url_prd['fullPath'], '/')
            )
        );
    }
}