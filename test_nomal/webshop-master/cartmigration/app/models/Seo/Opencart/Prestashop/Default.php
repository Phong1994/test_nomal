<?php
class LECM_Model_Seo_Opencart_Prestashop_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        
        $result = array();
        $cat_desc = $cart->getRowFromListByField($categoriesExt['data']['category_lang'], 'id_category', $category['id_category']);
        if($cat_desc){
            $path = $category['id_category'] . "-" .$cat_desc['link_rewrite'];
            $result[] = array(
                'request_path' => $path
            );
        }
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $pro_desc = $cart->getRowFromListByField($productsExt['data']['product_lang'], 'id_product', $product['id_product']);
        if($pro_desc){
            $path = $product['id_product'] . "-" . $pro_desc['link_rewrite'];
            $result[] = array(
                'request_path' => $path
            );
            $proCat = $cart->getListFromListByField($productsExt['data']['category_product'], 'id_product', $product['id_product']);
            if($proCat){
                foreach ($proCat as $row){
                    $category = $cart->getRowFromListByField($productsExt['data']['category_lang'], 'id_category', $row['id_category']);
                    if($category){
                        $result[] = array(
                            'request_path' => $category['link_rewrite'] . '/' . $product['id_product'] . "-" . $pro_desc['link_rewrite']
                        );
                    }
                }
            }
        }
        return $result;
    }
    
}