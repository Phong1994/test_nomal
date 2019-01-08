<?php
class LECM_Model_Seo_Opencart_Opencart_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $result = array();
        $cat_desc = $cart->getRowFromListByField($categoriesExt['data']['url_alias'], 'query', 'category_id='.$category['category_id']);
        
        if($cat_desc){
            $path = $cat_desc['keyword'];
            $result[] = array(
                'request_path' => $path
            );
        }
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $pro_desc = $cart->getRowFromListByField($productsExt['data']['url_alias'], 'query', 'product_id='.$product['product_id']);
        if($pro_desc){
            $path = $pro_desc['keyword'];
            $result[] = array(
                'request_path' => $path
            );
        }
        return $result;
    }
    
}