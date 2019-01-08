<?php
class LECM_Model_Seo_Magento_Magento_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $result = array();
        $notice = $cart->getNotice();
        $version_src = $cart->convertVersion($notice['src']['config']['version'],2);
        $field = 'entity_id';
        if($version_src<200){
            $field = 'category_id';
        }
        $cat_desc = $cart->getListFromListByField($categoriesExt['data']['core_url_rewrite'], $field, $category['entity_id']);
        
        if($cat_desc){
            foreach ($cat_desc as $row){
                $path = $row['request_path'];
                $result[] = array(
                    'request_path' => $path,
                    'store_id' => $row['store_id'],
                );
            }
        }
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = array();
        $notice = $cart->getNotice();
        $version_src = $cart->convertVersion($notice['src']['config']['version'],2);
        $field = 'entity_id';
        if($version_src<200){
            $field = 'product_id';
        }
        $pro_desc = $cart->getListFromListByField($productsExt['data']['core_url_rewrite'], $field, $product['entity_id']);
        if($pro_desc){
            foreach ($pro_desc as $row){
                $path = $row['request_path'];
                $category_id = null;
                if($version_src<200){
                    $category_id = $row['category_id'];
                }else{
                    if($version_src < 220){
                        $metadata = @unserialize($row['metadata']);
                    }else{
                        $metadata = json_decode($row['metadata'],true);
                    }
                    if($metadata && isset($metadata['category_id'])){
                        $category_id = $metadata['category_id'];
                    }
                }
                $result[] = array(
                    'request_path' => $path,
                    'store_id' => $row['store_id'],
                    'category_id' => $category_id,
                );
            }
        }
        return $result;
    }
    
}