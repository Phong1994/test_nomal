<?php

class LECM_Model_Seo_Shopify_Zencart_Default
{
    public function getCategoriesSeoExtQuery($cart, $categories){
        $result = array();
        $parentIds = $cart->duplicateFieldValueFromList($categories['data'], 'parent_id');
        $parentIds = $this->_filterParentId($parentIds);
        if($parentIds){
            $parent_id_con = $cart->arrayToInCondition($parentIds);
            $result = array(
                'seo_categories' => array(
                    'type' => 'select',
                    "query" => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}"
                )
            );
        }
        return $result;
    }

    public function getCategoriesSeoExtRelQuery($cart, $categories, $categoriesExt){
        $result = array();
        if(isset($categoriesExt['data']['seo_categories'])){
            $parentIds = $cart->duplicateFieldValueFromList($categoriesExt['data']['seo_categories'], 'parent_id');
            $parentIds = $this->_filterParentId($parentIds);
            if($parentIds){
                $parent_id_con = $cart->arrayToInCondition($parentIds);
                $result = array(
                    'seo_categories_2' => array(
                        'type' => 'select',
                        "query" => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}"
                    )
                );
            }
        }
        return $result;
    }

    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
        $result = array();
        $parent_cat_id_lv1 = $parent_cat_id_lv2 = 0;
        $parent_cat_data_all = array(
            'result' => 'success',
            'categories' => array(),
        );
        $parent_cat_path_all = array(
            'result' => 'success',
            'ip' => ''
        );
        $parent_cat_id = $category['parent_id'];
        $ip = $category['categories_id'];
        if($parent_cat_id){
            $ip = $parent_cat_id . "_" . $ip;
            $parent_cat_data = $cart->getRowFromListByField($categoriesExt['data']['seo_categories'], 'categories_id', $parent_cat_id);
            if($parent_cat_data){
                $parent_cat_id_lv1 = $parent_cat_data['parent_id'];
                if($parent_cat_id_lv1){
                    $ip = $parent_cat_id_lv1 . "_" . $ip;
                    $parent_cat_data_lv1 = $cart->getRowFromListByField($categoriesExt['data']['seo_categories_2'], 'categories_id', $parent_cat_id_lv1);
                    if($parent_cat_data_lv1){
                        $parent_cat_id_lv2 = $parent_cat_data_lv1['parent_id'];
                        if($parent_cat_id_lv2){
                            $ip = $parent_cat_id_lv2 . "_" . $ip;
                            $parent_cat_data_all = $this->_getCategoriesParent($cart, $parent_cat_id_lv2, $parent_cat_data_all);
                            if($parent_cat_data_all['result'] == 'success'){
                                $parent_cat_path_all = $this->_pathCategory($cart, $parent_cat_id_lv2, $parent_cat_data_all, $parent_cat_path_all);
                                if($parent_cat_path_all['result'] == 'success'){
                                    $ip = $parent_cat_path_all['ip'] . $ip;
                                }
                            }
                        }
                    }
                }
            }
        }
        $path = '?main_page=index&cPath=' . $ip;
        $notice = $cart->getNotice();
        $result[] = array(
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

    public function getProductsSeoExtQuery($cart, $products){
        return array();
    }

    public function getProductsSeoExtRelQuery($cart, $products, $productsExt){
        $catIds = $cart->duplicateFieldValueFromList($productsExt['data']['products_to_categories'], 'categories_id');
        $cat_id_con = $cart->arrayToInCondition($catIds);
        $result = array(
            'seo_categories' => array(
                'type' => 'select',
                "query" => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$cat_id_con}",
            )
        );
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
        $result = $data = array();
        $path = '?main_page=product_info&products_id=' . $product['products_id'];
        $data[] = $path;
        $proToCat = $cart->getListFromListByField($productsExt['data']['products_to_categories'], 'products_id', $product['products_id']);
        $catIds = $cart->duplicateFieldValueFromList($proToCat, 'categories_id');
        $cat_data_all = $this->_getCategoriesParent($cart, $catIds, array(
            'result' => 'success',
            'categories' => array()
        ));
        if($cat_data_all['result'] == 'success'){
            foreach($catIds as $cat_id){
                $ip = $cat_id;
                $seo_cat = $cart->getRowFromListByField($productsExt['data']['seo_categories'], 'categories_id', $cat_id);
                if($seo_cat){
                    $parent_cat_id = $seo_cat['parent_id'];
                    if($parent_cat_id){
                        $ip = $parent_cat_id . "_" . $ip;
                        $al = $this->_pathCategory($cart, $cat_id, $cat_data_all, array(
                            'result' => 'success',
                            'ip' => ''
                        ));
                        if($al['result'] == 'success'){
                            $ip = $al['ip'] . $cat_id;
                        }
                    }
                }
                $path = '?main_page=product_info&cPath=' . $ip . '&products_id=' . $product['products_id'];
                $data[] = $path;
            }
        }
        $data = array_unique($data);
        $notice = $cart->getNotice();
        foreach($data as $path){
            $result[] = array(
                'request_path' => $path
            );
        }
        return $result;
    }

    public function productSeoImport($cart, $convert, $product, $productsExt) {
        return false;
    }

    public function afterProductSeoImport($cart, $product_id, $convert, $product, $productsExt) {
        return false;
    }

    ############################################### Extend function ################################################

    protected function _filterParentId($catIds){
        if(!$catIds){
            return false;
        }
        $data = array();
        foreach($catIds as $cat_id){
            if($cat_id){
                $data[] = $cat_id;
            }
        }
        return $data;
    }

    protected function _getCategoriesParent($cart, $catIds, $data){
        $url_query = $cart->getConnectorUrl('query');
        if($data['result'] == 'error'){
            return $data;
        }
        if(!is_array($catIds)){
            $catIds = array($catIds);
        }
        $catIds = $this->_filterParentId($catIds);
        $catIds = array_unique($catIds);
        $cat_id_con = $cart->arrayToInCondition($catIds);
        $query = array(
            'categories' => array(
                'type' => "select",
                'query' =>  "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$cat_id_con}",
            )
        );
        $result = $cart->getConnectorData($url_query, array(
            'serialize' => true,
            'query' => serialize($query),
        ));
        if(!$result || $result['result'] != 'success'){
            return array(
                'result' => 'error'
            );
        }
        foreach($result['data']['categories'] as $row){
            $data['categories'][] = $row;
        }
        $parentCatIds = $cart->duplicateFieldValueFromList($result['data']['categories'], 'parent_id');
        $parentCatIds = $this->_filterParentId($parentCatIds);
        if($parentCatIds){
            $data = $this->_getCategoriesParent($cart, $parentCatIds, $data);
        }
        return $data;
    }

    protected function _pathCategory($cart, $cat_id, $data, $result){
        if($result['result'] == 'error'){
            return $result;
        }
        $parent_cat = $cart->getRowFromListByField($data['categories'], 'categories_id', $cat_id);
        if(!$parent_cat){
            return array(
                'result' => 'error'
            );
        }
        $parent_cat_id = $parent_cat['parent_id'];
        if($parent_cat_id == 0){
            return $result;
        }
        $result['ip'] = $parent_cat_id . "_" . $result['ip'];
        $result = $this->_pathCategory($cart, $parent_cat_id, $data, $result);
        return $result;
    }
}