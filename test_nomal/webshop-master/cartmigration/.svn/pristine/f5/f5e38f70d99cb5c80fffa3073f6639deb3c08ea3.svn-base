<?php

class LECM_Model_Seo_Shopify_Prestashop_Default
{    

    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $path = '';
        $result = array();
        $notice = $cart->getNotice();
        $lang_def = $notice['src']['language_default'];
        $category_lang = $cart->getListFromListByField($categoriesExt['data']['category_lang'], 'id_category', $category['id_category']);
        $category_def = $cart->getRowFromListByField($category_lang, 'id_lang', $lang_def);
        if($category_def){
            $path = $category['id_category'] . '-' . $category_def['link_rewrite'];
        }
        $result[] = array(
            'request_path' => $path
        );
        return $result;
    }
      
    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $notice = $cart->getNotice();
        $path = '';
        $lang_def = $notice['src']['language_default'];
        $product_lang = $cart->getListFromListByField($productsExt['data']['product_lang'], 'id_product', $product['id_product']);
        $product_def = $cart->getRowFromListByField($product_lang, 'id_lang', $lang_def);
        if($product_def){
            $path = $product['id_product'] . '-' . $product_def['link_rewrite'];
            if($product['ean13']){
                $path = $path . '-' . $product['ean13'];
            }
            $result[] = array(
                'request_path' => $path . '.html'
            );
        }
        $category_product = $cart->getListFromListByField($productsExt['data']['category_product'], 'id_product', $product['id_product']);
        if($category_product){
            foreach ($category_product as $row){
                $category_lang = $cart->getListFromListByField($productsExt['data']['category_lang'], 'id_category', $row['id_category']);
                $category_def = $cart->getRowFromListByField($category_lang, 'id_lang', $lang_def);
                if($category_def){
                    $result[] = array(
                        'request_path' => $category_def['link_rewrite'] . '/' . $path . '.html'
                    );
                }
            }
        }
        return $result;
    }
    
}