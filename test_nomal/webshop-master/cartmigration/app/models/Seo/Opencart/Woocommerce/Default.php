<?php

class LECM_Model_Seo_Opencart_Woocommerce_Default
{
    const SEO_ENABLE = false;// Enable Search Engine Friendly URLs
    const HYPER_LINK = false;//Use Hyphens (-) instead of Underscores (_)

    public function getCategoriesSeoExport($cart, $category, $categoriesExt)
    {
        $result = array();
        $notice = $cart->getNotice();
        $store_id = 1;
        if (self::SEO_ENABLE) {
            $path = $category['slug'] ? $category['slug'] : $category['name'];
            if (self::HYPER_LINK) {
                $path = str_replace(' ', '-', strtolower($path));
            }
        } else {
            $path = 'product-category/' . $category['slug'];
        }
        $result[] = array(
            'store_id' => $store_id,
            'request_path' => $path
        );
        return $result;
    }

    public function categorySeoImport($cart, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function afterCategorySeoImport($cart, $category_id, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function getProductSeoExport($cart, $product, $productsExt)
    {
        $result = array();
        $notice = $cart->getNotice();
        $store_id = 1;
        if (self::SEO_ENABLE) {
            $path = $product['post_name'] ? $product['post_name'] : $product['post_title'];
            // change special character to space
            $path = preg_replace('/[^a-zA-Z0-9 _-]+/', ' ', $path);
            // change multi space to single space
            $path = preg_replace('/\s+/', ' ', $path);
            // change space to hyphen
            $path = str_replace(' ', '-', $path);
            // change multi hyphen to single hyphen
            $path = preg_replace('/-+/', '-', $path);
            if (self::HYPER_LINK) {
                $path = str_replace('_', '-', $path);
            }
            $result[] = array(
                'store_id' => $store_id,
                'request_path' => $path
            );
        } else {
            $path = 'product/' . $product['post_name'];
            $result[] = array(
                'store_id' => $store_id,
                'request_path' => $path
            );
        }

        return $result;
    }

    public function productSeoImport($cart, $convert, $product, $productsExt)
    {
        return false;
    }

    public function afterProductSeoImport($cart, $product_id, $convert, $product, $productsExt)
    {
        return false;
    }
}