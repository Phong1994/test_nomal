<?php
class LECM_Model_Seo_Opencart_Magento_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $result = array();
        $cat_desc = $cart->getListFromListByField($categoriesExt['data']['core_url_rewrite'], 'category_id', $category['entity_id']);
        
        if($cat_desc){
            foreach ($cat_desc as $row){
                $path = $row['request_path'];
                $result[] = array(
                    'request_path' => $path
                );
            }
        }
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $pro_desc = $cart->getListFromListByField($productsExt['data']['core_url_rewrite'], 'product_id', $product['entity_id']);
        if($pro_desc){
            foreach ($pro_desc as $row){
                $path = $row['request_path'];
                $result[] = array(
                    'request_path' => $path
                );
            }
        }
        return $result;
    }
    
}