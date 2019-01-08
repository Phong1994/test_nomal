<?php

class LECM_Model_Seo_Opencart_Bigcommerce_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        return array(
            array(
                'request_path' => '/' . ltrim($category['url'], '/')
            )
        );
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        return array(
            array(
                'request_path' => '/' . ltrim($product['custom_url'], '/')
            )
        );
    }
}