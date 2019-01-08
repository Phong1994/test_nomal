<?php

class LECM_Model_Seo_Opencart_Oscommerce_Custom
{
    // config in admin menu of osCommerce: Configuration > SEO Urls
    const SEO_ADD_CID_TO_PRODUCT_URLS = false;   // Add cPath to product URLs?
    const SEO_ADD_CPATH_TO_PRODUCT_URLS = false; // Add category parent to product URLs?
    const SEO_ADD_CAT_PARENT = false;            // Add category parent to begining of URLs?
    const SEO_CHAR_CONVERT_SET = '';             // Enter special character conversions
    const SEO_URLS_FILTER_SHORT_WORDS = 0;       // Filter Short Words
    const SEO_REMOVE_ALL_SPEC_CHARS = false;     // Remove all non-alphanumeric characters?
    protected $_convertTable = array();

    public function getCategoriesSeoExtQuery($cart, $categories){
        $result = false;
        $parentIds = $cart->duplicateFieldValueFromList($categories['data'], 'parent_id');
        $parentIds = $this->_filterParentId($parentIds);
        if($parentIds){
            $parent_id_con = $cart->arrayToInCondition($parentIds);
            $result = array(
                'seo_categories' => array(
                    'type' => 'select',
                    "query" => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}"
                ),
                'seo_categories_description' => array(
                    'type' => 'select',
                    "query" => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$parent_id_con}"
                ),
            );
        }
        return $result;
    }

    public function getCategoriesSeoExtRelQuery($cart, $categories, $categoriesExt){
        $result = false;
        if(isset($categoriesExt['data']['seo_categories'])){
            $parentIds = $cart->duplicateFieldValueFromList($categoriesExt['data']['seo_categories'], 'parent_id');
            $parentIds = $this->_filterParentId($parentIds);
            if($parentIds){
                $parent_id_con = $cart->arrayToInCondition($parentIds);
                $result = array(
                    'seo_categories_2' => array(
                        'type' => 'select',
                        "query" => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}",
                    ),
                    'seo_categories_description_2' => array(
                        'type' => 'select',
                        "query" => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$parent_id_con}"
                    ),
                );
            }
        }
        return $result;
    }

    public function getCategoriesSeoExport($cart, $category, $categoriesExt){
        $result = false;
        $parent_cat_id_lv1 = $parent_cat_id_lv2 = 0;
        $parent_cat_data_all = array(
            'result' => 'success',
            'categories' => array(),
            'categories_description' => array()
        );
        $parent_cat_path_all = array(
            'result' => 'success',
            'ip' => '',
            'np' => ''
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
        $catDesc = $cart->getListFromListByField($categoriesExt['data']['categories_description'], 'categories_id', $category['categories_id']);
        foreach($catDesc as $cat_desc){
            if(self::SEO_ADD_CAT_PARENT){
                $np = $cat_desc['categories_name'];
                if($parent_cat_id){
                    $seoCatDesc = $cart->getListFromListByField($categoriesExt['data']['seo_categories_description'], 'categories_id', $parent_cat_id);
                    $seo_cat_desc = $cart->getRowFromListByField($seoCatDesc, 'language_id', $cat_desc['language_id']);
                    if($seo_cat_desc){
                        $np = $seo_cat_desc['categories_name'] . " " . $np;
                        if($parent_cat_id_lv1){
                            $seoCatDescLv1 = $cart->getListFromListByField($categoriesExt['data']['seo_categories_description_2'], 'categories_id', $parent_cat_id_lv1);
                            $seo_cat_desc_lv1 = $cart->getRowFromListByField($seoCatDescLv1, 'language_id', $cat_desc['language_id']);
                            if($seo_cat_desc_lv1){
                                $np = $seo_cat_desc_lv1['categories_name'] . " " . $np;
                                if($parent_cat_id_lv2){
                                    $seoCatDescLv2 = $cart->getListFromListByField($parent_cat_data_all['categories_description'], 'categories_id', $parent_cat_id_lv2);
                                    $seo_cat_desc_lv2 = $cart->getRowFromListByField($seoCatDescLv2, 'language_id', $cat_desc['language_id']);
                                    if($seo_cat_desc_lv2){
                                        $np = $seo_cat_desc_lv2['categories_name'] . " " . $np;
                                        $ap = $this->_pathCategory($cart, $parent_cat_id_lv2, $parent_cat_data_all, array(
                                            'result' => 'success',
                                            'ip' => '',
                                            'np' => ''
                                        ), $cat_desc['language_id']);
                                        if($ap['result'] == 'success'){
                                            if($ap['np']){
                                                $np = $ap['np'] . " " . $np;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $cat_name_seo = $this->_toUrl($np);
                $path = $cat_name_seo . "-c-" . $ip . ".html";
                $result[] = array(
                    'request_path' => $path
                );
            } else {
                $cat_name = $cat_desc['categories_name'];
                $cat_name_seo = $this->_toUrl($cat_name);
                $path = $cat_name_seo . "-c-" . $ip . '.html';
                $result[] = array(
                    'request_path' => $path
                );
            }
        }
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
        $result = false;
        if(self::SEO_ADD_CPATH_TO_PRODUCT_URLS){
            $catIds = $cart->duplicateFieldValueFromList($productsExt['data']['products_to_categories'], 'categories_id');
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_categories' => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$cat_id_con}",
                'seo_categories_description' => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$cat_id_con}"
            );
        }
        return $result;
    }

    public function getProductSeoExport($cart, $product, $productsExt){
        $result = false;
        $proToCat = $cart->getListFromListByField($productsExt['data']['products_to_categories'], 'products_id', $product['products_id']);
        $catIds = $cart->duplicateFieldValueFromList($proToCat, 'categories_id');
        $cat_data_all = array(
            'result' => 'success',
            'categories' => array(),
            'categories_description' => array()
        );
        if(self::SEO_ADD_CPATH_TO_PRODUCT_URLS){
            $cat_data_all = $this->_getCategoriesParent($cart, $catIds, $cat_data_all);
        }
        $proDesc = $cart->getListFromListByField($productsExt['data']['products_description'], 'products_id', $product['products_id']);
        foreach($proDesc as $pro_desc){
            $np = $pro_desc['products_name'];
            $catSeo = false;
            if(self::SEO_ADD_CPATH_TO_PRODUCT_URLS || self::SEO_ADD_CID_TO_PRODUCT_URLS){
                foreach($catIds as $cat_id){
                    if(self::SEO_ADD_CPATH_TO_PRODUCT_URLS){
                        $seo_cat = $cart->getRowFromListByField($productsExt['data']['seo_categories'], 'categories_id', $cat_id);
                        if($seo_cat){
                            $parent_cat_id = $seo_cat['parent_id'];
                            $seoCatDesc = $cart->getListFromListByField($productsExt['data']['seo_categories_description'], 'categories_id', $cat_id);
                            $seo_cat_desc = $cart->getRowFromListByField($seoCatDesc, 'language_id', $pro_desc['language_id']);
                            if($seo_cat_desc){
                                $catSeo = $seo_cat_desc['categories_name'];
                                if($parent_cat_id){
                                    $ap = $this->_pathCategory($cart, $cat_id, $cat_data_all, array(
                                        'result' => 'success',
                                        'ip' => '',
                                        'np' => ''
                                    ), $pro_desc['language_id']);
                                    if($ap['result'] == 'success'){
                                        $catSeo = $ap['np'] . $catSeo;
                                    }
                                }
                            }
                        }
                    }
                    if($catSeo){
                        $pro_name_seo = $this->_toUrl($catSeo) . '-' . $this->_toUrl($np);
                    }else{
                        $pro_name_seo = $this->_toUrl($np);
                    }
                    $path = $pro_name_seo . "-p-" . $product['products_id'] . ".html";
                    if(self::SEO_ADD_CID_TO_PRODUCT_URLS){
                        $path .= "?cPath=" . $cat_id;
                    }
                    $result[] = array(
                        'request_path' => $path
                    );
                }
            } else {
                $pro_name_seo = $this->_toUrl($np);
                $path = $pro_name_seo . "-p-" . $product['products_id'] . ".html";
                $result[] = array(
                    'request_path' => $path
                );
            }
        }
        return $result;
    }

############################################### Extend function ################################################

    protected function _toUrl($string){
        $convertSpecial = $this->_specialToConvertTable();
        if($convertSpecial){
            $string = strtr($string, $convertSpecial);
        }
        if($this->_convertTable){
            $string = strtr($string, $this->_convertTable);
        }
        $pattern = self::SEO_REMOVE_ALL_SPEC_CHARS ? "([^[:alnum:]]+)" : "([[:punct:]]+)";
        $string = preg_replace($pattern, '', strtolower($string));
        $string = preg_replace("/([[:space:]]|[[:blank:]])+/", '-', $string);
        if(self::SEO_URLS_FILTER_SHORT_WORDS){
            $string = $this->_shortName($string, self::SEO_URLS_FILTER_SHORT_WORDS);
        }
        return $string;
    }

    protected function _shortName($str, $limit){
        $container = array();
        $foo = @explode('-', $str);
        foreach($foo as $index => $value){
            switch (true){
                case ( strlen($value) <= $limit ):
                    continue;
                default:
                    $container[] = $value;
                    break;
            }
        }
        $container = ( sizeof($container) > 1 ? implode('-', $container) : $str );
        return $container;
    }

    protected function _specialToConvertTable(){
        if(!self::SEO_CHAR_CONVERT_SET){
            return false;
        }
        $data = array();
        $items = explode(',', self::SEO_CHAR_CONVERT_SET);
        foreach($items as $item){
            $split = explode('=>', $item);
            if(isset($split[0]) && isset($split[1])){
                $data[$split[0]] = $split[1];
            }
        }
        return $data;
    }

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

    protected function _pathCategory($cart, $cat_id, $data, $result, $lang_id = 0){
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
        if($lang_id){
            $parentCatDes = $cart->getListFromListByField($data['categories_description'], 'categories_id', $parent_cat_id);
            $parent_cat_des = $cart->getRowFromListByField($parentCatDes, 'language_id', $lang_id);
            if($parent_cat_des){
                $result['np'] = $parent_cat_des['categories_name'] . " " . $result['np'];
            }
        }
        $result = $this->_pathCategory($cart, $parent_cat_id, $data, $result, $lang_id);
        return $result;
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
            ),
            'categories_description' => array(
                'type' => "select",
                'query' =>  "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$cat_id_con}",
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
        foreach($result['data']['categories_description'] as $row){
            $data['categories_description'][] = $row;
        }
        $parentCatIds = $cart->duplicateFieldValueFromList($result['data']['categories'], 'parent_id');
        $parentCatIds = $this->_filterParentId($parentCatIds);
        if($parentCatIds){
            $data = $this->_getCategoriesParent($cart, $parentCatIds, $data);
        }
        return $data;
    }
}