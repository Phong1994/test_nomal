<?php

class LECM_Model_Seo_Shopify_Cscart_Default
{    

    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $path = '';
        $result = array();
        $pathIds = explode('/', $category['id_path']);
        foreach ($pathIds as $id){
            $seo_names = $cart->getRowFromListByField($categoriesExt['data']['seo_names'], 'object_id', $id);
            if($seo_names){
                $path = $path . '/' . $seo_names['name'];
            }
        }
        $path = ltrim($path, '/');
        $notice = $cart->getNotice();
        $result[] = array(
            'request_path' => $path
        );
        return $result;
    }
      
    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $seo_names = $cart->getRowFromListByField($productsExt['data']['seo_names'], 'object_id', $product['product_id']);
        $path = $seo_names['name'] . '.html';
        $notice = $cart->getNotice();
        $result[] = array(
            'request_path' => $path
        );
        return $result;
    }
    
}