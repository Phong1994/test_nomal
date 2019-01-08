<?php

class LECM_Model_Cart_Magento14
    extends LECM_Model_Cart
{

    /**
     * TODO: DISPLAY
     */

    public function displayConfigSource()
    {
        $parent = parent::displayConfigSource();
        if ($parent['result'] != 'success') {
            return $parent;
        }
        $response       = $this->_defaultResponse();
        $default_config = $this->getConnectorData($this->getConnectorUrl('query',null,'src'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'languages'       => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin' AND is_active = 1"
                ),
                'currencies'      => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/default'"
                ),
                'eav_entity_type' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_eav_entity_type",
                )
            )),
        ));
        if (!$default_config || $default_config['result'] != "success") {
            return $this->errorConnector(false);
        }
        $default_config_data = $default_config['data'];
        if ($default_config_data && $default_config_data['languages'] && $default_config_data['currencies'] && $default_config_data['eav_entity_type']) {
            $this->_notice['src']['language_default'] = $this->_getDefaultLanguage($default_config_data['languages']);
            $this->_notice['src']['currency_default'] = isset($default_config_data['currencies'][0]['value']) ? $default_config_data['currencies'][0]['value'] : 'USD';
            foreach ($default_config_data['eav_entity_type'] as $eav_entity_type) {
                $this->_notice['src']['extends'][$eav_entity_type['entity_type_code']] = $eav_entity_type['entity_type_id'];
            }
        }
        $this->_notice['src']['category_root'] = 1;
//        $this->_notice['src']['site']          = array(
//            1 => 'Default Shop'
//        );
        $this->_notice['src']['categoryData']  = array(
            1 => 'Default Category',
        );
        $this->_notice['src']['attributes']    = array(
            1 => 'Default Attribute'
        );
        $config                                = $this->getConnectorData($this->getConnectorUrl('query',null,'src'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'languages'      => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin'"
                ),
                'currencies'     => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/allow'"
                ),
                'orders_status'  => array(
                    'type'  => 'select',
                    'query' => "SELECT DISTINCT status FROM _DBPRF_sales_flat_order_status_history"
                ),
                'customer_group' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_customer_group"
                ),
                'attributes_set' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_eav_attribute_set WHERE entity_type_id = '" . $this->_notice['src']['extends']['catalog_product'] . "'"
                ),
                "category_root"  => array(
                    'type'  => 'select',
                    'query' => "SELECT a.entity_id, b.value FROM _DBPRF_catalog_category_entity a, _DBPRF_catalog_category_entity_varchar b, _DBPRF_eav_attribute c
                                                WHERE a.level = '1'
                                                AND b.entity_id = a.entity_id
                                                AND b.attribute_id = c.attribute_id
                                                AND b.store_id = 0
                                                AND c.attribute_code = 'name'
                                                AND c.entity_type_id = '" . $this->_notice['src']['extends']['catalog_category'] . "' 
                                                "
                ),
                "eav_attribute_group" => array(
                    'type' => 'select',
                    'query' => "SELECT * FROM  _DBPRF_eav_attribute_group " ,
                )
            )),
        ));
        if (!$config || $config['result'] != "success") {
            return $this->errorConnector(false);
        }
        $config_data   = $config['data'];
        $language_data = $currency_data = $order_status_data = $customer_group_data = $eav_attribute_group_data=array();

        $langexist     = array();
        foreach ($config_data['languages'] as $language_row) {
            $lang_id   = $language_row['store_id'];
            $lang_name = $language_row['name'];
            $website_id = $language_row['website_id'];
            $this->_notice['src']['site'][$lang_id] = $website_id;
            if (!in_array($lang_name, $langexist)) {
                $langexist[]             = $lang_name;
                $language_data[$lang_id] = $lang_name;
            }
        }
//        print_r($language_data);exit;
        foreach ($config_data['orders_status'] as $order_status_row) {
            if (!$order_status_row['status']) continue;
            $order_status_id                     = $order_status_row['status'];
            $order_status_name                   = $order_status_row['status'];
            $order_status_data[$order_status_id] = $order_status_name;
        }
        $currencies = $config_data['currencies'][0]['value'];
        if ($currencies) {
            $currencies_array = explode(',', $currencies);
            foreach ($currencies_array as $currency_row) {
                if ($currency_row) {
                    $currency_id                 = $currency_row;
                    $currency_name               = $currency_row;
                    $currency_data[$currency_id] = $currency_name;
                }
            }
        } else {
            $currency_data['USD'] = 'USD';
        }
        foreach ($config_data['customer_group'] as $customer_group) {
            $customer_group_id                       = $customer_group['customer_group_id'];
            $customer_group_name                     = $customer_group['customer_group_code'];
            $customer_group_data[$customer_group_id] = $customer_group_name;
        }

        $attributes_set_data = array();

        foreach ($config_data['attributes_set'] as $attributes_set_row) {
            $attributes_set_id                       = $attributes_set_row['attribute_set_id'];
            $attributes_set_name                     = $attributes_set_row['attribute_set_name'];
            $attributes_set_data[$attributes_set_id] = $attributes_set_name;
        }

        if (count($attributes_set_data) > 0) {
            $this->_notice['src']['attributes'] = $attributes_set_data;
        }

        $category_root_data = array();

        foreach ($config_data['category_root'] as $category_root_row) {
            $category_root_id                      = $category_root_row['entity_id'];
            $category_root_name                    = $category_root_row['value'];
            $category_root_data[$category_root_id] = $category_root_name;
        }

        if (count($category_root_data) > 0) {
            $this->_notice['src']['categoryData'] = $category_root_data;
        }
        $eav_attribute_group = $config_data['eav_attribute_group'];
        foreach ($eav_attribute_group as $key=>$value){
            $eav_attribute_group_data[$value['attribute_group_id']] = $value['attribute_group_name'];
        }

        //=======================================================
        $this->_notice['src']['languages']              = $language_data;

        $this->_notice['src']['attribute_group_name'] = $eav_attribute_group_data;
        $this->_notice['src']['order_status']           = $order_status_data;
        $this->_notice['src']['currencies']             = $currency_data;
        $this->_notice['src']['customer_group']         = $customer_group_data;
        $this->_notice['src']['support']['country_map'] = false;
//        $this->_notice['support']['pages']              = true;
//        $this->_notice['support']['blocks']             = true;
//        $this->_notice['support']['transactions']       = true;
        $this->_notice['support']['rules']              = true;
//        $this->_notice['support']['cartrules']          = true;
        $response['result']                             = 'success';

        return $response;
    }

    public function displayConfigTarget()
    {
        $parent = parent::displayConfigTarget();
        if ($parent['result'] != 'success') {
            return $parent;
        }
        $response           = $this->_defaultResponse();
        $response['result'] = 'success';

        return $response;
    }

    public function displayConfirmSource()
    {

        return array(
            'result' => "success"
        );
    }

    public function displayConfirmTarget()
    {
        $this->_notice['target']['clear']['function'] = '_clearTargetTaxes';

        return array(
            'result' => "success"
        );
    }

    public function displayImportSource()
    {
        $parent = parent::displayImportSource();
        if ($parent['result'] != 'success') {
            return $parent;
        }
        $response = $this->_defaultResponse();
        $count    = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'taxes'         => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_tax_class WHERE class_type = 'PRODUCT' AND class_id > {$this->_notice['process']['taxes']['id_src']}",
                ),
                'manufacturers' => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_eav_attribute as ea 
                                    LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                WHERE ea.attribute_code = 'manufacturer' AND eao.option_id > {$this->_notice['process']['manufacturers']['id_src']}",
                ),
                'categories'    => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_catalog_category_entity WHERE level > 1 AND entity_id > {$this->_notice['process']['categories']['id_src']}",
                ),
                'products'      => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) as count FROM _DBPRF_catalog_product_entity 
                                WHERE entity_id > {$this->_notice['process']['products']['id_src']}",
                ),
                'customers'     => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_customer_entity WHERE entity_id > {$this->_notice['process']['customers']['id_src']}",
                ),
                'orders'        => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_sales_flat_order WHERE entity_id > {$this->_notice['process']['orders']['id_src']}",
                ),
                'reviews'       => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_review WHERE review_id > {$this->_notice['process']['reviews']['id_src']}",
                ),
                'pages'         => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_cms_page WHERE page_id > {$this->_notice['process']['pages']['id_src']}",
                ),
                'blocks'        => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_cms_block WHERE block_id > {$this->_notice['process']['blocks']['id_src']}",
                ),
                'transactions'  => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_core_email_template WHERE template_id > {$this->_notice['process']['transactions']['id_src']}",
                ),
                'rules'         => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_salesrule WHERE rule_id > {$this->_notice['process']['rules']['id_src']}",
                ),
                'cartrules'     => array(
                    'type'  => 'select',
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_catalogrule WHERE rule_id > {$this->_notice['process']['cartrules']['id_src']}",
                ),

            )),
        ));
        if (!$count || $count['result'] != 'success') {
            return $this->errorConnector(false);
        }
        $real_totals = array();
        foreach ($count['data'] as $type => $row) {
            $total              = $this->arrayToCount($row, 'count');
            $real_totals[$type] = $total;
        }

        foreach ($real_totals as $type => $count) {
            $this->_notice['process'][$type]['total'] = $count;
        }

        $response['result'] = 'success';

        return $response;
    }

    public function displayImportTarget()
    {

        return array(
            'result' => 'success'
        );
    }

    public function displayFinishSource()
    {

        return array(
            'result' => 'success'
        );
    }

    public function displayFinishTarget()
    {

        return array(
            'result' => 'success'
        );
    }

    public function prepareImportSource()
    {

        return array(
            'result' => 'success'
        );
    }

    public function prepareImportTarget()
    {

        return array(
            'result' => "success"
        );
    }

    public function clearData()
    {
        return array(
            'result' => "success",
            'msg'    => $this->getMsgStartImport('taxes'),
        );
    }

    /**
     * TODO: CLEAR
     */

    protected function _clearTargetTaxes()
    {
        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetManufacturers()
    {
        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetCategories()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetProducts()
    {
        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetCustomers()
    {
        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetOrders()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetReviews()
    {
        return $this->_notice['target']['clear'];
    }

    /**
     * TODO: PROCESS
     */

    public function prepareTaxesImport()
    {
        return $this;
    }

    public function prepareTaxesExport()
    {
        return $this;
    }

    public function getTaxesMainExport()
    {
        $map    = $this->_notice['map'];
        $id_src = $this->_notice['process']['taxes']['id_src'];
        $limit  = $this->_notice['setting']['taxes'];
        $taxes  = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_tax_class WHERE class_type = 'PRODUCT' AND class_id > " . $id_src . " ORDER BY class_id ASC LIMIT " . $limit
            )),
        ));
        if (!$taxes || $taxes['result'] != 'success') {
            return $this->errorConnector();
        }

        return $taxes;
    }

    public function getTaxesExtExport($taxes)
    {
        $taxProductClassIds       = $this->duplicateFieldValueFromList($taxes['data'], 'class_id');
        $tax_product_class_id_con = $this->arrayToInCondition($taxProductClassIds);
        $taxes_ext_queries        = array(
            'tax_calculation' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_tax_calculation WHERE product_tax_class_id IN {$tax_product_class_id_con}",
            )
        );

        $taxesExt = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($taxes_ext_queries),
        ));

        if (!$taxesExt || $taxesExt['result'] != 'success') {
            return $this->errorConnector();
        }

        $taxCustomerClassIds = $this->duplicateFieldValueFromList($taxesExt['data']['tax_calculation'], 'customer_tax_class_id');
        $taxRuleIds          = $this->duplicateFieldValueFromList($taxesExt['data']['tax_calculation'], 'tax_calculation_rule_id');
        $taxRateIds          = $this->duplicateFieldValueFromList($taxesExt['data']['tax_calculation'], 'tax_calculation_rate_id');

        $tax_customer_class_id_con = $this->arrayToInCondition($taxCustomerClassIds);
        $tax_rule_id_con           = $this->arrayToInCondition($taxRuleIds);
        $tax_rate_id_con           = $this->arrayToInCondition($taxRateIds);

        $taxes_ext_rel_queries = array(
            'tax_class'            => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_tax_class WHERE class_id IN {$tax_customer_class_id_con}",
            ),
            'tax_calculation_rate' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_tax_calculation_rate WHERE tax_calculation_rate_id IN {$tax_rate_id_con}",
            ),
            'tax_calculation_rule' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_tax_calculation_rule WHERE tax_calculation_rule_id IN {$tax_rule_id_con}",
            ),
            'customer_group'       => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_group WHERE tax_class_id IN {$tax_customer_class_id_con}"
            ),
        );

        $taxesExtRel = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($taxes_ext_rel_queries),
        ));

        if (!$taxesExtRel || $taxesExtRel['result'] != 'success') {
            return $this->errorConnector();
        }
        $taxesExt = $this->syncConnectorObject($taxesExt, $taxesExtRel);

        return $taxesExt;
    }


    public function convertTaxExport($tax, $taxesExt)
    {
        $tax_data = array();
        // var_dump($tax);exit;
        $tax_data['id'] = $tax['class_id'];
        $tax_data['product_class']        = $tax;
        $tax_data['tax_calculation']      = $this->getListFromListByField($taxesExt['data']['tax_calculation'],'product_tax_class_id',$tax['class_id']);
        $customer_tax_class_ids = $this->duplicateFieldValueFromList($tax_data['tax_calculation'],'customer_tax_class_id');
        $tax_data['customer_class']       = $this->getListFromListByField($taxesExt['data']['tax_class'],'class_id',$customer_tax_class_ids);
        $rate_ids = $this->duplicateFieldValueFromList($tax_data['tax_calculation'],'tax_calculation_rate_id');
        $rule_ids = $this->duplicateFieldValueFromList($tax_data['tax_calculation'],'tax_calculation_rule_id');
        $tax_data['tax_calculation_rate'] = $this->getListFromListByField($taxesExt['data']['tax_calculation_rate'],'tax_calculation_rate_id',$rate_ids);
        $tax_data['tax_calculation_rule'] = $this->getListFromListByField($taxesExt['data']['tax_calculation_rule'],'tax_calculation_rule_id',$rule_ids);
        $tax_customer_ids                 = $this->duplicateFieldValueFromList($tax_data['customer_class'], 'class_id');
        $tax_data['customer_group']       = $this->getListFromListByField($taxesExt['data']['customer_group'], 'tax_class_id', $tax_customer_ids);
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $tax_data,
        );
    }

    public function getTaxIdImport($convert, $tax, $taxesExt)
    {
        return $convert['id'];
    }

    public function checkTaxImport($convert, $tax, $taxesExt)
    {
        return $this->getMapFieldBySource(self::TYPE_TAX, $convert['id'], $convert['code']) ? true : false;
    }

    public function routerTaxImport($convert, $tax, $taxesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'taxImport', //taxImport - beforeTaxImport - additionTaxImport
        );
    }

    public function beforeTaxImport($convert, $tax, $taxesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function taxImport($convert, $tax, $taxesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 1
        );
    }

    public function afterTaxImport($tax_id, $convert, $tax, $taxesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionTaxImport($tax_id, $convert, $tax, $taxesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareManufacturersImport()
    {
        return $this;
    }

    public function prepareManufacturersExport()
    {
        return $this;
    }

    public function getManufacturersMainExport()
    {
        $id_src        = $this->_notice['process']['manufacturers']['id_src'];
        $limit         = $this->_notice['setting']['manufacturers'];
        $manufacturers = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT eao.* FROM _DBPRF_eav_attribute as ea
                            LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                        WHERE ea.attribute_code = 'manufacturer' AND eao.option_id > " . $id_src . " ORDER BY eao.option_id ASC LIMIT " . $limit
            ))
        ));
        if (!$manufacturers || $manufacturers['result'] != 'success') {
            return $this->errorConnector();
        }

        return $manufacturers;
    }

    public function getManufacturersExtExport($manufacturers)
    {
        $url_query                 = $this->getConnectorUrl('query');
        $optionIds                 = $this->duplicateFieldValueFromList($manufacturers['data'], 'option_id');
        $option_id_in_query        = $this->arrayToInCondition($optionIds);
        $manufacturers_ext_queries = array(
            'eav_attribute_option_value' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN " . $option_id_in_query
            )
        );
        // add custom
        $manufacturersExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($manufacturers_ext_queries)
        ));
        if (!$manufacturersExt || $manufacturersExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $manufacturers_ext_rel_queries = array();
        // add custom
        if ($manufacturers_ext_rel_queries) {
            $manufacturersExtRel = $this->getConnectorData($url_query, array(
                'serialize' => true,
                'query'     => serialize($manufacturers_ext_rel_queries)
            ));
            if (!$manufacturersExtRel || $manufacturersExtRel['result'] != 'success') {
                return $this->errorConnector();
            }
            $manufacturersExt = $this->syncConnectorObject($manufacturersExt, $manufacturersExtRel);
        }

        return $manufacturersExt;
    }

    public function convertManufacturerExport($manufacturer, $manufacturersExt)
    {
        $manufacturer_data         = $this->constructManufacturer();
        $manufacturer_data         = $this->addConstructDefault($manufacturer_data);
        $manufacturerDesc          = $this->getListFromListByField($manufacturersExt['data']['eav_attribute_option_value'], 'option_id', $manufacturer['option_id']);
        $manufacturer_data['id']   = $manufacturer['option_id'];
        $manufacturer_data['name'] = $this->getRowValueFromListByField($manufacturerDesc, 'store_id', 0, 'value');

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $manufacturer_data,
        );
    }

    public function getManufacturerIdImport($convert, $manufacturer, $manufacturersExt)
    {
        return $convert['id'];
    }

    public function checkManufacturerImport($convert, $manufacturer, $manufacturersExt)
    {
        return $this->getMapFieldBySource(self::TYPE_MANUFACTURER, $convert['id'], $convert['code']) ? true : false;
    }

    public function routerManufacturerImport($convert, $manufacturer, $manufacturersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'manufacturerImport',
        );
    }

    public function beforeManufacturerImport($convert, $manufacturer, $manufacturersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function manufacturerImport($convert, $manufacturer, $manufacturersExt)
    {

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => ''
        );
    }

    public function afterManufacturerImport($manufacturer_id, $convert, $manufacturer, $manufacturersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array()
        );

    }

    public function additionManufacturerImport($manufacturer_id, $convert, $manufacturer, $manufacturersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareCategoriesExport()
    {
        return $this;
    }

    public function prepareCategoriesImport()
    {
        return $this;
    }

    public function getCategoriesMainExport()
    {
        $id_src     = $this->_notice['process']['categories']['id_src'];
        $limit      = $this->_notice['setting']['categories'];
        $categories = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity WHERE level > 1 AND entity_id > " . $id_src . " ORDER BY entity_id ASC LIMIT " . $limit
            ))
        ));
        if (!$categories || $categories['result'] != 'success') {
            var_dump(1);exit;
            return $this->errorConnector();
        }

        return $categories;
    }


    public function getCategoriesExtExport($categories)
    {
        $url_query              = $this->getConnectorUrl('query');
        $categoryIds            = $this->duplicateFieldValueFromList($categories['data'], 'entity_id');
        $category_id_query      = $this->arrayToInCondition($categoryIds);
        $categories_ext_queries = array(
            'catalog_category_entity_varchar'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity_varchar WHERE entity_id IN " . $category_id_query
            ),
            'catalog_category_entity_text'     => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity_text WHERE entity_id IN {$category_id_query}"
            ),
            'catalog_category_entity_int'      => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity_int WHERE entity_id IN {$category_id_query}"
            ),
            'catalog_category_entity_decimal'  => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity_decimal WHERE entity_id IN {$category_id_query}",
            ),
            'catalog_category_entity_datetime' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity_datetime WHERE entity_id IN {$category_id_query}"
            ),
            'eav_attribute'                    => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['src']['extends']['catalog_category']}"
            ),
        );

        $categoriesExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($categories_ext_queries)
        ));

        if (!$categoriesExt || $categoriesExt['result'] != 'success') {
            var_dump(1);exit;
            return $this->errorConnector();
        }
        if($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']){
            $categories_ext_queries['core_url_rewrite'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_core_url_rewrite WHERE product_id IS NULL  AND category_id IN {$category_id_query} AND is_system = 1"
            );
        }
        $categories_ext_rel_queries = array();
        //add custom
        if ($categories_ext_rel_queries) {
            $categoriesExtRel = $this->getConnectorData($url_query, array(
                'serialize' => true,
                'query'     => serialize($categories_ext_rel_queries)
            ));
            if (!$categoriesExtRel || $categoriesExtRel['result'] != 'success') {
                return $this->errorConnector();
            }
            $categoriesExt = $this->syncConnectorObject($categoriesExt, $categoriesExtRel);
        }

        return $categoriesExt;
    }

    public function convertCategoryExport($category, $categoriesExt)
    {
        $category_data = $this->constructCategory();
        $category_data = $this->addConstructDefault($category_data);
        $parent        = $this->constructCategoryParent();
        $parent        = $this->addConstructDefault($parent);
        $code_parent = '';

        if ($category['parent_id'] && $category['level'] > 2) {
            $parent = $this->getCategoryParent($category['parent_id']);
            if ($parent['result'] != 'success') {
                $response           = $this->_defaultResponse();
                $response['result'] = 'error';
                $response['msg']    = $this->consoleWarning("Could not convert.");

                return $response;
            }
            $parent = $parent['data'];
            $code_parent = $parent['url_key'];
        } else {
            $parent['id']    = $category['parent_id'];
            $parent['level'] = 1;
        }
        $eav_attribute = array();
        foreach ($categoriesExt['data']['eav_attribute'] as $row) {
            $eav_attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $entity_varchar = $this->getListFromListByField($categoriesExt['data']['catalog_category_entity_varchar'], 'entity_id', $category['entity_id']);
        $entity_text    = $this->getListFromListByField($categoriesExt['data']['catalog_category_entity_text'], 'entity_id', $category['entity_id']);
        $entity_int     = $this->getListFromListByField($categoriesExt['data']['catalog_category_entity_int'], 'entity_id', $category['entity_id']);

        $is_active                      = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['is_active']);
        $is_active_def                  = $this->getRowValueFromListByField($is_active, 'store_id', 0, 'value');
        $images                         = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['image']);
        $image_def_path                 = $this->getRowValueFromListByField($images, 'store_id', 0, 'value');
        $category_data['id']            = $category['entity_id'];
        $category_data['level']         = $category['level'];
        $category_data['parent']        = $parent;
        $category_data['active']        = $is_active_def ? true : false;
        $category_data['image']['url']  = $this->getUrlSuffix($this->_notice['src']['config']['image_category']);
        $category_data['image']['path'] = $image_def_path;
        $category_data['sort_order']    = 1;
        $category_data['created_at']    = $category['created_at'];
        $category_data['updated_at']    = $category['updated_at'];
        $category_data['category']      = $category;
        $category_data['categoriesExt'] = $categoriesExt;

        $names                             = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['name']);
        $name_def                          = $this->getRowValueFromListByField($names, 'store_id', 0, 'value');
        $descriptions                      = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['description']);
        $description_def                   = $this->getRowValueFromListByField($descriptions, 'store_id', 0, 'value');
        $meta_titles                       = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['meta_title']);
        $meta_title_def                    = $this->getRowValueFromListByField($meta_titles, 'store_id', 0, 'value');
        $meta_keywords                     = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['meta_keywords']);
        $meta_keywords_def                 = $this->getRowValueFromListByField($meta_keywords, 'store_id', 0, 'value');
        $meta_descriptions                 = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['meta_description']);
        $meta_description_def              = $this->getRowValueFromListByField($meta_descriptions, 'store_id', 0, 'value');
        $is_anchor                         = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['is_anchor']);
        $is_anchor_def                     = $this->getRowValueFromListByField($is_anchor, 'store_id', 0, 'value');
        $display_mode = $this->getListFromListByField($entity_varchar,'attribute_id',$eav_attribute['display_mode']);
        $display_mode_def = $this->getRowValueFromListByField($display_mode,'store_id',0,'value');
        $url_key = $this->getListFromListByField($entity_varchar,'attribute_id',$eav_attribute['url_key']);
        $url_key_def = $this->getRowValueFromListByField($url_key,'store_id',0,'value');
        $url_path = $this->getListFromListByField($entity_varchar,'attribute_id',$eav_attribute['url_path']);
        $url_path_def = $this->getRowValueFromListByField($url_path,'store_id',0,'value');
        $category_data['url_key'] = $url_key_def?$url_key_def:'';
        $category_data['code']             = $code_parent?$code_parent.'/'.$url_key_def:$url_key_def;

        $category_data['is_anchor']        = $is_anchor_def ? $is_anchor_def : 0;
        $category_data['url_path'] = $url_path_def?$url_path_def:'';
        $category_data['display_mode'] = $display_mode_def? $display_mode_def:'';
        $category_data['name']             = $name_def;
        $category_data['description']      = $description_def;
        $category_data['meta_title']       = $meta_title_def;
        $category_data['meta_keyword']     = $meta_keywords_def;
        $category_data['meta_description'] = $meta_description_def;

        foreach ($this->_notice['src']['languages'] as $language_id => $language_label) {
            $category_language_data                     = $this->constructCategoryLang();
            $name_lang                                  = $this->getRowValueFromListByField($names, 'store_id', $language_id, 'value');
            $description_lang                           = $this->getRowValueFromListByField($descriptions, 'store_id', $language_id, 'value');
            $meta_title_lang                            = $this->getRowValueFromListByField($meta_titles, 'store_id', $language_id, 'value');
            $meta_keywords_lang                         = $this->getRowValueFromListByField($meta_keywords, 'store_id', $language_id, 'value');
            $meta_description_lang                      = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $language_id, 'value');
            $display_mode_lang = $this->getRowValueFromListByField($display_mode,'store_id',$language_id,'value');
            $url_key_lang = $this->getRowValueFromListByField($url_key,'store_id',$language_id,'value');
            $url_path_lang = $this->getRowValueFromListByField($url_path,'store_id',$language_id,'value');
            $is_anchor_lang                             = $this->getRowValueFromListByField($is_anchor,'store_id',$language_id,'value');
            $category_language_data['is_anchor']        = $is_anchor_lang?$is_anchor_lang:$category_data['is_anchor'];
            $category_language_data['url_path'] = $url_path_lang?$url_path_lang:$category_data['url_path'];
            $category_language_data['url_key'] = $url_key_lang?$url_key_lang:$category_data['url_key'];
            $category_language_data['display_mode'] = $description_lang? $display_mode_lang:$category_data['display_mode'];
            $category_language_data['name']             = $name_lang ? $name_lang : $name_def;
            $category_language_data['description']      = $description_lang ? $description_lang : $description_def;
            $category_language_data['meta_title']       = $meta_title_lang ? $meta_title_lang : $meta_title_def;
            $category_language_data['meta_keyword']     = $meta_keywords_lang ? $meta_keywords_lang : $meta_keywords_def;
            $category_language_data['meta_description'] = $meta_description_lang ? $meta_description_lang : $meta_description_def;
            $category_data['languages'][$language_id]   = $category_language_data;
        }
        $url_rewrite                  = $this->getListFromListByField($categoriesExt['data']['core_url_rewrite'], 'category_id', $category['entity_id']);
        $category_data['url_rewrite'] = array();
        foreach ($url_rewrite as $rewrite) {
            $rewrite_data                   = array();
            $rewrite_data['store_id']       = $rewrite['store_id'];
            $rewrite_data['request_path']       = $rewrite['request_path'];
            $rewrite_data['description']    = $rewrite['description'];
            $category_data['url_rewrite'][] = $rewrite_data;
        }

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $category_data,
        );
    }

    public function getCategoryIdImport($convert, $category, $categoriesExt)
    {
        return $convert['id'];
    }

    public function checkCategoryImport($convert, $category, $categoriesExt)
    {
        return $this->getMapFieldBySource(self::TYPE_CATEGORY, $convert['id'], $convert['code']) ? true : false;
    }

    public function routerCategoryImport($convert, $category, $categoriesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'categoryImport',
        );
    }

    public function beforeCategoryImport($convert, $category, $categoriesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }


    public function categoryImport($convert, $category, $categoriesExt)
    {
        return array(
            'result' => "success",
            'msg'    => '',
            'data'   => 1
        );
    }

    public function afterCategoryImport($category_id, $convert, $category, $categoriesExt)
    {

        return array(
            'result' => "success",
            'msg'    => '',
            'data'   => array()
        );
    }

    public function additionCategoryImport($category_id, $convert, $category, $categoriesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareProductsExport()
    {
        return $this;
    }

    public function prepareProductsImport()
    {
        return $this;
    }

    public function getProductsMainExport()
    {
        $id_src   = $this->_notice['process']['products']['id_src'];
        $limit    = $this->_notice['setting']['products'];
        $products = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalog_product_entity
                                WHERE entity_id > "' . $id_src . '" ORDER BY entity_id ASC LIMIT ' . $limit,
            )),
        ));
        if (!$products || $products['result'] != 'success') {
            var_dump(1);exit;
            return $this->errorConnector();
        }

        return $products;
    }


    public function getProductsExtExport($products)
    {
        $url_query           = $this->getConnectorUrl('query');
        $productIds          = $this->duplicateFieldValueFromList($products['data'], 'entity_id');
        $productType         = $this->duplicateFieldValueFromList($products['data'], "type_id");
        $product_id_con      = $this->arrayToInCondition($productIds);
        $product_ext_queries = array(
            'catalog_product_website' => array(
                'type' => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_website WHERE product_id IN " . $product_id_con,
            ),
            'catalog_product_relation'             => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_relation WHERE child_id IN " . $product_id_con,
            ),
            'eav_attribute'                        => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['src']['extends']['catalog_product']}"
            ),
            'tag_relation'                         => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_tag_relation WHERE product_id IN {$product_id_con}"
            ),
            'eav_attribute_option_value'           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option_value"
            ),
            'eav_attribute_option'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option"
            ),
            'catalog_product_super_link'           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_super_link WHERE product_id IN " . $product_id_con,
            ),
            'catalog_product_link'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_link WHERE product_id IN " . $product_id_con . " OR linked_product_id IN" . $product_id_con,
            ),
            'catalog_product_link_grouped_product' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_link WHERE link_type_id = 3 and linked_product_id IN " . $product_id_con,
            ),
            'catalog_product_option'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option WHERE product_id IN {$product_id_con}"
            ),
        );
        if (in_array("downloadable", $productType)) {
            $product_ext_queries['downloadable_link']   = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_downloadable_link WHERE product_id IN " . $product_id_con,
            );
            $product_ext_queries['downloadable_sample'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_downloadable_sample WHERE product_id IN " . $product_id_con,
            );
        }
        if (in_array("bundle", $productType)) {
            $product_ext_queries['catalog_product_bundle_option'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_bundle_option WHERE parent_id IN " . $product_id_con,
            );
        }
        if($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']){
            $product_ext_queries['core_url_rewrite'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_core_url_rewrite WHERE product_id IN {$product_id_con}"
            );
        }
        // add custom
        $productsExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_ext_queries),
        ));
        if (!$productsExt || $productsExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $downloadable_link_id   = null;
        $downloadable_sample_id = null;
        if (isset($product_ext_queries['downloadable_link'])) {

            $downloadable_link_id           = $this->duplicateFieldValueFromList($productsExt['data']['downloadable_link'], 'link_id');
            $downloadable_link_id_condition = $this->arrayToInCondition($downloadable_link_id);

            $downloadable_sample_id           = $this->duplicateFieldValueFromList($productsExt['data']['downloadable_sample'], 'sample_id');
            $downloadable_sample_id_condition = $this->arrayToInCondition($downloadable_sample_id);
        }

        $parentIds           = array();
        $parentIds           = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_super_link'], 'parent_id');
        $parent_id_query     = $this->arrayToInCondition($parentIds);
        $allproduct_id_query = $this->arrayToInCondition(array_merge($productIds, $parentIds));
        $tagIds              = $this->duplicateFieldValueFromList($productsExt['data']['tag_relation'], 'tag_id');
        $tag_id_query        = $this->arrayToInCondition($tagIds);

        $linkIds       = array();
        $linkIds       = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_link'], 'link_id');
        $link_id_query = $this->arrayToInCondition($linkIds);
        $optionIds       = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_option'], 'option_id');
        $option_id_query = $this->arrayToInCondition($optionIds);
        $product_ext_rel_queries = array(
            'catalog_product_link_attribute_decimal' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_link_attribute_decimal WHERE product_link_attribute_id = 3 and link_id IN {$link_id_query}"
            ),
            'catalog_product_super_attribute'        => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_super_attribute WHERE product_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity'                 => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_datetime'        => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_datetime WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_decimal'         => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_decimal WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_gallery'         => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_gallery WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_int'             => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_int WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_text'            => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_text WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_varchar'         => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_varchar WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_media_gallery'   => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_product_entity_tier_price'      => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_tier_price WHERE entity_id IN {$allproduct_id_query}"
            ),

            'catalog_category_product'               => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_product WHERE product_id IN {$allproduct_id_query}"
            ),
            'tag'                                    => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_tag WHERE tag_id IN {$tag_id_query}"
            ),
            'cataloginventory_stock_item'            => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_cataloginventory_stock_item WHERE product_id IN {$allproduct_id_query}"
            ),
            'catalog_product_bundle_parent'          => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_bundle_selection WHERE product_id IN " . $product_id_con,
            ),
            'catalog_product_option_title'               => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option_title WHERE option_id IN {$option_id_query}",
            ),
            'catalog_product_option_price'               => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option_price WHERE option_id IN {$option_id_query}"
            ),
            'catalog_product_option_type_value'          => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option_type_value as cpotv
                            WHERE cpotv.option_id IN {$option_id_query}"
            ),
        );

        //bundle option
        if (isset($productsExt['data']['catalog_product_bundle_option']) && count($productsExt['data']['catalog_product_bundle_option'])) {
            $bundle_option_ids                                              = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_bundle_option'], "option_id");
            $bundle_option_ids_con                                          = $this->arrayToInCondition($bundle_option_ids);
            $product_ext_rel_queries['catalog_product_bundle_option_value'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_bundle_option_value WHERE option_id IN " . $bundle_option_ids_con,
            );
            $product_ext_rel_queries['catalog_product_bundle_selection']    = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_bundle_selection WHERE option_id IN " . $bundle_option_ids_con,
            );
        }


        if ($downloadable_link_id && count($downloadable_link_id) > 0) {
            $product_ext_rel_queries['downloadable_link_title'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_downloadable_link_title WHERE link_id IN {$downloadable_link_id_condition}"
            );
            $product_ext_rel_queries['downloadable_link_price'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_downloadable_link_price WHERE link_id IN {$downloadable_link_id_condition}"
            );
        }

        if ($downloadable_sample_id && count($downloadable_sample_id) > 0) {
            $product_ext_rel_queries['downloadable_sample_title'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_downloadable_sample_title WHERE sample_id IN {$downloadable_sample_id_condition}"
            );
        }
        // add custom
        $productsExtRel = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_ext_rel_queries),
        ));
        if (!$productsExtRel || $productsExtRel['result'] != 'success') {
            return $this->errorConnector();
        }
        $productsExt = $this->syncConnectorObject($productsExt, $productsExtRel);
        $option_type_ids = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_option_type_value'],'option_type_id');
        $option_type_id_con = $this->arrayToInCondition($option_type_ids);
        $valueIds        = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_entity_media_gallery'], 'value_id');
        $value_id_query  = $this->arrayToInCondition($valueIds);
        $optionAttrIds   = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_entity_int'], 'value');
        $option_attr_id  = $this->arrayToInCondition($optionAttrIds);
        //Addition
        $multi      = $this->getListFromListByField($productsExt['data']['eav_attribute'], 'frontend_input', 'multiselect');
        $multi_ids  = $this->duplicateFieldValueFromList($multi, 'attribute_id');
        $all_option = array();
        if ($multi_ids) {
            $multi_opt = $this->getListFromListByListField($productsExt['data']['catalog_product_entity_varchar'], 'attribute_id', $multi_ids);
            foreach ($multi_opt as $row) {
                $new_options = explode(',', $row['value']);
                $all_option  = array_merge($all_option, $new_options);
            }
        }
        $all_option_query = $this->arrayToInCondition($all_option);

        $super_attribute_id       = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_super_attribute'], 'product_super_attribute_id');
        $super_attribute_id_query = $this->arrayToInCondition($super_attribute_id);


        $product_ext_rel_rel_queries = array(
            'catalog_product_super_attribute_label'      => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_label WHERE store_id = 0 AND product_super_attribute_id IN {$super_attribute_id_query}"
            ),
            'catalog_product_super_attribute_pricing'    => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_pricing WHERE product_super_attribute_id IN {$super_attribute_id_query}"
            ),
            'eav_attribute_option_value'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_attr_id} OR option_id IN {$all_option_query}",
            ),
            'catalog_product_entity_media_gallery_value' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery_value WHERE value_id IN {$value_id_query}",
            ),

            'all_option'                                 => array(
                'type'  => "select",
                'query' => "SELECT a.option_id,a.attribute_id,b.value FROM _DBPRF_eav_attribute_option as a, _DBPRF_eav_attribute_option_value as b WHERE a.option_id = b.option_id and b.store_id = 0"
            ),
            'catalog_product_option_type_title' => array(
                'type' => 'select',
                'query'=> 'SELECT * FROM _DBPRF_catalog_product_option_type_title WHERE option_type_id IN '.$option_type_id_con,
            ),
            'catalog_product_option_type_price' => array(
                'type' => 'select',
                'query'=> 'SELECT * FROM _DBPRF_catalog_product_option_type_price WHERE option_type_id IN '.$option_type_id_con,
            ),
        );
        $productsExtRelRel           = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_ext_rel_rel_queries),
        ));
        if (!$productsExtRelRel || $productsExtRelRel['result'] != 'success') {
            return $this->errorConnector();
        }
        $productsExt = $this->syncConnectorObject($productsExt, $productsExtRelRel);

        return $productsExt;
    }


    public function getProductParent($parent_id)
    {
        $response  = $this->_defaultResponse();
        $url_query = $this->getConnectorUrl('query');
        $products  = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity WHERE entity_id = " . $parent_id
            ))
        ));
        if (!$products || $products['result'] != 'success') {
            $response['result'] = 'warning';

            return $response;
        }
        $productsExt = $this->getproductsExtExport($products);
        if (!$productsExt || $productsExt['result'] != "success") {
            $response['result'] = 'warning';

            return $response;
        }
        $product = $products['data'][0];

        return $this->convertProductExport($product, $productsExt);
    }

    public function convertProductExport($product, $productsExt)
    {
        $product_data = $this->constructProduct();
        $product_data = $this->addConstructDefault($product_data);

        $entity_decimal    = $this->getListFromListByField($productsExt['data']['catalog_product_entity_decimal'], 'entity_id', $product['entity_id']);
        $entity_int        = $this->getListFromListByField($productsExt['data']['catalog_product_entity_int'], 'entity_id', $product['entity_id']);
        $entity_text       = $this->getListFromListByField($productsExt['data']['catalog_product_entity_text'], 'entity_id', $product['entity_id']);
        $entity_varchar    = $this->getListFromListByField($productsExt['data']['catalog_product_entity_varchar'], 'entity_id', $product['entity_id']);
        $entity_datetime   = $this->getListFromListByField($productsExt['data']['catalog_product_entity_datetime'], 'entity_id', $product['entity_id']);
        $manage_stock_data = $this->getListFromListByField($productsExt['data']['cataloginventory_stock_item'], 'product_id', $product['entity_id']);

        $eav_entity_attribute = $productsExt['data']['eav_entity_attribute'];
        $eav_attribute_group = array();
        foreach ($eav_entity_attribute as $key=>$value){
            $eav_attribute_group[$value['attribute_id']] = $value['attribute_group_id'];
        }
//        print_r($eav_attribute_group);exit;
        $product_data['eav_attribute_group'] = $eav_attribute_group;
        $eav_attribute     = array();
        foreach ($productsExt['data']['eav_attribute'] as $row) {
            $eav_attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $product_link_parent   = $this->getListFromListByField($productsExt['data']['catalog_product_link'], "product_id", $product['entity_id']);
        $product_link_children = $this->getListFromListByField($productsExt['data']['catalog_product_link'], "linked_product_id", $product['entity_id']);
        if (count($product_link_parent) > 0) {
            $product_data['product_link_parent'] = $product_link_parent;
        }
        if (count($product_link_children) > 0) {
            $product_data['product_link_children'] = $product_link_children;
        }
        $price    = $this->getRowValueFromListByField($entity_decimal, 'attribute_id', $eav_attribute['price'], 'value');
        $weight   = $this->getRowValueFromListByField($entity_decimal, 'attribute_id', $eav_attribute['weight'], 'value');
        $status   = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['status'], 'value');
        $quantity = $this->getRowValueFromListByField($productsExt['data']['cataloginventory_stock_item'], 'product_id', $product['entity_id'], 'qty');

//        $product_data['price_type']    = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['price_type'], 'value');
//
//        $product_data['weight_type'] = $this->getRowValueFromListByField($entity_int,'attribute_id',$eav_attribute['weight_type'],'value');
//        $product_data['sku_type'] = $this->getRowValueFromListByField($entity_int,'attribute_id',$eav_attribute['sku_type'],'value');
//        $product_data['price_type']             = $price_type ? $price_type : null;
//        $weight_type = $this->getRowValueFromListByField($entity_int,'attribute_id',$eav_attribute['weight_type'],'value');
//        $product_data['weight_type'] = $weight_type? $weight_type:null;

        $product_data['id']               = $product['entity_id'];
        $product_data['attribute_set_id'] = $product['attribute_set_id'];
        $product_data['type_id']          = $product['type_id'];
        $product_data['code']             = $product['sku'];

        // $product_data['type'] = $product['type_id'];
        $product_data['sku']    = $product['sku'];
        $product_data['price']  = $price ? $price : 0;
        $product_data['weight'] = $weight ? $weight : 0;
        $product_data['status'] = $status == 1 ? true : false;
        $product_data['qty']    = intval($quantity);
        if ($manage_stock_data && count($manage_stock_data) > 0) {
            $manage_stock_data                              = $manage_stock_data[0];
            $manage_stock_data['stock_status_changed_auto'] = $manage_stock_data['stock_status_changed_automatically'];
            $manage_stock_data['use_config_enable_qty_inc'] = $manage_stock_data['use_config_enable_qty_increments'];
            unset($manage_stock_data['stock_status_changed_automatically']);
            unset($manage_stock_data['use_config_enable_qty_increments']);
            $product_data['manage_stock_data'] = $manage_stock_data;
        }
        if ($quantity) {
            $product_data['manage_stock'] = true;
        }
        $product_data['created_at'] = $product['created_at'];
        $product_data['updated_at'] = $product['updated_at'];

        $names                 = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['name']);
        $name_def              = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $descriptions          = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['description']);
        $description_def       = $this->getRowValueFromListByField($descriptions, 'store_id', 0, 'value');
        $short_descriptions    = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['short_description']);
        $short_description_def = $this->getRowValueFromListByField($short_descriptions, 'store_id', 0, 'value');
        $meta_titles           = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['meta_title']);
        $meta_title_def        = $this->getRowValueFromListByField($meta_titles, 'store_id', 0, 'value');
        $meta_keywords         = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['meta_keyword']);
        $meta_keyword_def      = $this->getRowValueFromListByField($meta_keywords, 'store_id', 0, 'value');
        $meta_descriptions     = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['meta_description']);
        $meta_description_def  = $this->getRowValueFromListByField($meta_descriptions, 'store_id', 0, 'value');

        $status     = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['status']);
        $url_key                = $this->getListFromListByField($entity_varchar,'attribute_id',$eav_attribute['url_key']);
        $url_path               = $this->getListFromListByField($entity_varchar,'attribute_id',$eav_attribute['url_path']);
        $visibility = $this->getListFromListByField($entity_int,'attribute_id',$eav_attribute['visibility']);
        $visibility_def = $this->getRowValueFromListByField($visibility,'store_id',0,'value');
        $url_key_def = $this->getRowValueFromListByField($url_key,'store_id',0,'value');
        $url_path_def = $this->getRowValueFromListByField($url_path,'store_id',0,'value');
        $status_def = $this->getRowValueFromListByField($status,'store_id',0,'value');
        $product_data['url_key'] = $url_key_def?$url_key_def:'';
        $product_data['url_path'] = $url_path_def?$url_path_def:'';
        $product_data['status'] = $status_def?$status_def:0;
        $product_data['visibility'] = $visibility_def;
        $product_data['name']              = $name_def ? $name_def : '';
        $product_data['description']       = $description_def ? $description_def : '';
        $product_data['short_description'] = $short_description_def ? $short_description_def : '';
        $product_data['meta_title']        = $meta_title_def ? $meta_title_def : '';
        $product_data['meta_keyword']      = $meta_keyword_def ? $meta_keyword_def : '';
        $product_data['meta_description']  = $meta_description_def ? $meta_description_def : '';
        $image                             = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['image'], 'value');
        $image_label                       = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['image_label'], 'value');
        $url_product_image                 = $this->getUrlSuffix($this->_notice['src']['config']['image_product']);
//        var_dump($this->_notice['src']['config']);exit;
        $product_data['image']['url']   = $url_product_image;
        $product_data['image']['path']  = $image;
        $product_data['image']['label'] = $image_label;
        $productImage                   = $this->getListFromListByField($productsExt['data']['catalog_product_entity_media_gallery'], 'entity_id', $product['entity_id']);
        if ($productImage) {
            foreach ($productImage as $product_image) {
                $product_image_data          = $this->constructProductImage();
                $product_image_data['label'] = $this->getRowValueFromListByField($productsExt['data']['catalog_product_entity_media_gallery_value'], 'value_id', $product_image['value_id'], 'label');
                $product_image_data['position'] = $this->getRowValueFromListByField($productsExt['data']['catalog_product_entity_media_gallery_value'], 'value_id', $product_image['value_id'], 'position');
                $product_image_data['url']   = $url_product_image;
                $product_image_data['path']  = $product_image['value'];
                $product_data['images'][]    = $product_image_data;
            }
        }

        $special_price     = $this->getRowValueFromListByField($entity_decimal, 'attribute_id', $eav_attribute['special_price'], 'value');
        $special_from_date = $this->getRowValueFromListByField($entity_datetime, 'attribute_id', $eav_attribute['special_from_date'], 'value');
        $special_to_date   = $this->getRowValueFromListByField($entity_datetime, 'attribute_id', $eav_attribute['special_to_date'], 'value');
        if ($special_price) {
            $product_data['special_price']['price']      = $special_price;
            $product_data['special_price']['start_date'] = $special_from_date;
            $product_data['special_price']['end_date']   = $special_to_date;
        }
        $tiers_price = $this->getListFromListByField($productsExt['data']['catalog_product_entity_tier_price'], 'entity_id', $product['entity_id']);
        if ($tiers_price) {
            foreach ($tiers_price as $tier_price) {
                $tier_price_data          = $this->constructProductTierPrice();
                $tier_price_data          = $this->addConstructDefault($tier_price_data);
                $tier_price_data['id']    = $tier_price['value_id'];
                $tier_price_data['qty']   = $tier_price['qty'];
                $tier_price_data['price'] = $tier_price['value'];
                $tier_price_data['customer_group_id'] = $tier_price['customer_group_id'];
                $tier_price_data['all_groups'] = $tier_price['all_groups'];
                $tier_price_data['website_id']        = $tier_price['website_id'];

                $product_data['tier_prices'][] = $tier_price_data;
            }
        }


        $product_data['tax']['id'] = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['tax_class_id'], 'value');

        if (isset($eav_attribute['manufacturer'])) {
            $product_data['manufacturer']['id']   = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['manufacturer'], 'value');
            $manu_name                            = $this->getRowValueFromListByField($productsExt['data']['eav_attribute_option_value'], 'option_id', $product_data['manufacturer']['id'], 'value');
            $product_data['manufacturer']['name'] = $manu_name ? $manu_name : "";
        }


        $productCategory = $this->getListFromListByField($productsExt['data']['catalog_category_product'], 'product_id', $product['entity_id']);
        if ($productCategory) {
            foreach ($productCategory as $product_category) {
                $product_category_data        = $this->constructProductCategory();
                $product_category_data['id']  = $product_category['category_id'];
                $product_data['categories'][] = $product_category_data;
            }
        }
        $language    = $this->_notice['src']['languages'];
        foreach ($language as $lang_id => $lang_name) {
            $product_language_data                      = $this->constructProductLang();
            $product_language_data['name']              = $this->getRowValueFromListByField($names, 'store_id', $lang_id, 'value');
            $product_language_data['description']       = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['short_description'] = $this->getRowValueFromListByField($short_descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['meta_title']        = $this->getRowValueFromListByField($meta_titles, 'store_id', $lang_id, 'value');
            $product_language_data['meta_keyword']      = $this->getRowValueFromListByField($meta_keywords, 'store_id', $lang_id, 'value');
            $product_language_data['meta_description']  = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['status']            = $this->getRowValueFromListByField($status, 'store_id', $lang_id, 'value');
            $product_language_data['url_key']            = $this->getRowValueFromListByField($url_key,'store_id',$lang_id,'value');
            $product_language_data['url_path']            = $this->getRowValueFromListByField($url_path,'store_id',$lang_id,'value');
            $product_data['languages'][$lang_id]        = $product_language_data;
        }
//        var_dump($this->getRowValueFromListByField($names, 'store_id', 1, 'value'));exit;
//        var_dump($product_data['languages']);exit;
        /**
         * get custom option product
         */
        $productOption = $this->getListFromListByField($productsExt['data']['catalog_product_option'], 'product_id', $product['entity_id']);

        if ($productOption) {
            foreach ($productOption as $product_option) {

                $option_title               = $this->getListFromListByField($productsExt['data']['catalog_product_option_title'], 'option_id', $product_option['option_id']);
                $option_price               = $this->getListFromListByField($productsExt['data']['catalog_product_option_price'], 'option_id', $product_option['option_id']);
                $productOptionTypeValue = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_value'], 'option_id', $product_option['option_id']);
                $option = array();
                $option_data                          = array();
                $option_data['id']                    = $product_option['option_id'];
                $option_data['option_type']           = $product_option['type'];
                $option_data['option_is_require']     = $product_option['is_require'];
                $option_data['option_sku']            = $product_option['sku'];
                $option_data['option_max_characters'] = $product_option['max_characters'];
                $option_data['option_file_extension'] = $product_option['file_extension'];
                $option_data['option_image_size_x']   = $product_option['image_size_x'];
                $option_data['option_image_size_y']   = $product_option['image_size_y'];
                $option['value'] = $option_data;
                if($option_title && count($option_title)>0){
                    $option['title'] = $option_title;
                }
                if ($option_price && count($option_price) > 0) {
                    $option['price'] = $option_price;
                }

                $optionTypeIds          = $this->duplicateFieldValueFromList($productOptionTypeValue, 'option_type_id');
                if($productOptionTypeValue && count($productOptionTypeValue)>0){
                    $type = array();
                    foreach ($productOptionTypeValue as $typeValue) {
                        $temp_type['value'] = $typeValue;
                        $option_type_title = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_title'],'option_type_id',$typeValue['option_type_id']);
                        $option_type_price = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_price'],'option_type_id',$typeValue['option_type_id']);
                        $temp_type['title'] = $option_type_title;
                        $temp_type['price'] = $option_type_price;
                        $type[] = $temp_type;
                    }
                    $option['type'] = $type;
                }

                $product_data['options'][] = $option;
            }
        }
//        $product_options = $this->getListFromListByField($productsExt['data']['catalog_product_option'],'product_id',$product['entity_id']);
//        foreach ($product_options as $key=>$value){
//            $option_title = $this->getListFromListByField($productsExt['data']['catalog_product_option_title'],'option_id',$value['option_id']);
//            $option_value = $this->getListFromListByField($productsExt['data']['catalog_product_option_price'],'option_id',$value['option_id']);
//            $temp_options = array();
//            $temp_options['option'] = $value;
//            $temp_options['title'] = $option_title;
//            $temp_options['value'] = $option_value;
//            $product_data['options'][] = $temp_options;
//        }
        /**
         * Get parent product simple
         */
        if ($product['type_id'] == 'simple' || $product['type_id'] == 'virtual') {


            //configurable
            $configurable_parent = $this->getListFromListByField($productsExt['data']['catalog_product_super_link'], 'product_id', $product['entity_id']);
            if (is_array($configurable_parent)) {
                if ($configurable_parent && count($configurable_parent) > 0) {
                    $configurable_parent_ids = $this->duplicateFieldValueFromList($configurable_parent, 'parent_id');
                    if (is_array($configurable_parent_ids)) {
                        foreach ($configurable_parent_ids as $item) {
                            $parent = $this->getProductParent($item);
                            if ($parent['result'] != 'success') {
                                $response           = $this->_defaultResponse();
                                $response['result'] = 'error';
                                $response['msg']    = $this->consoleWarning("Could not convert.");

                                return $response;
                            }
                            $product_data['parent_configurable'][] = $parent['data'];
                        }

                    }
                }
            }

            //group
            $grouped_parent = $this->getListFromListByField($productsExt['data']['catalog_product_link_grouped_product'], 'linked_product_id', $product['entity_id']);
            if (is_array($grouped_parent)) {
                if ($grouped_parent && count($grouped_parent) > 0) {
                    $grouped_parent_ids = $this->duplicateFieldValueFromList($grouped_parent, 'product_id');
                    if (is_array($grouped_parent_ids)) {
                        foreach ($grouped_parent_ids as $item) {
                            $parent = $this->getProductParent($item);
                            if ($parent['result'] != 'success') {
                                $response           = $this->_defaultResponse();
                                $response['result'] = 'error';
                                $response['msg']    = $this->consoleWarning("Could not convert.");

                                return $response;
                            }
                            $product_data['parent_grouped'][] = $parent['data'];
                        }

                    }
                }
            }

            //bundle
            $bundle_parent = $this->getListFromListByListField($productsExt['data']['catalog_product_bundle_parent'], "product_id", $product['entity_id']);
            if (is_array($bundle_parent)) {
                if ($bundle_parent && count($bundle_parent) > 0) {
                    $bundle_parent_ids = $this->duplicateFieldValueFromList($bundle_parent, 'parent_product_id');
                    if (is_array($bundle_parent_ids)) {
                        foreach ($bundle_parent_ids as $item) {
                            $parent = $this->getProductParent($item);
                            if ($parent['result'] != 'success') {
                                $response           = $this->_defaultResponse();
                                $response['result'] = 'error';
                                $response['msg']    = $this->consoleWarning("Could not convert.");

                                return $response;
                            }
                            $product_data['parent_bundle'][] = $parent['data'];
                        }

                    }
                }
            }

        }


        /**
         * Get option product bundle
         */
        if ($product['type_id'] == "bundle") {
            $product_data['price_type'] = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['price_type'], 'value');

            $product_data['weight_type'] = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['weight_type'], 'value');
            $product_data['sku_type']    = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['sku_type'], 'value');
            $product_data['bundle_option'] = array();
            $bundle_option                 = $this->getListFromListByListField($productsExt['data']['catalog_product_bundle_option'], "parent_id", $product['entity_id']);
            if($bundle_option && count($bundle_option)>0){
                foreach ($bundle_option as $value) {
                    $bundle_option_value             = $this->getListFromListByField($productsExt['data']['catalog_product_bundle_option_value'], "option_id", $value['option_id']);
                    $temp_bundle_option              = array();
                    $temp_bundle_option['option']    = $value;
                    $temp_bundle_option['value']     = $bundle_option_value;
                    $product_data['bundle_option'][] = $temp_bundle_option;
                }
                $bundle_selection                 = $this->getListFromListByField($productsExt['data']['catalog_product_bundle_selection'], "parent_product_id", $product['entity_id']);
                $product_data['bundle_selection'] = $bundle_selection;
            }
        }
        /**
         *  Get parent product configurable
         */
        if ($product['type_id'] == 'configurable') {
            $configurable_data_attributes = array();
            $superAttribute               = $this->getListFromListByField($productsExt['data']['catalog_product_super_attribute'], 'product_id', $product['entity_id']);
            foreach ($superAttribute as $super_attribute) {

                $attribute_data                          = array();
                $attribute_option_data                   = array();
                $attribute_option_item_data              = array();
                $catalog_product_super_attribute_label   = null;
                $catalog_product_super_attribute_pricing = array();


                $catalog_product_super_attribute_label   = $this->getRowValueFromListByField($productsExt['data']['catalog_product_super_attribute_label'], 'product_super_attribute_id', $super_attribute['product_super_attribute_id'], 'value');
                $catalog_product_super_attribute_pricing = $this->getListFromListByField($productsExt['data']['catalog_product_super_attribute_pricing'], 'product_super_attribute_id', $super_attribute['product_super_attribute_id']);


                $eav_attribute_configurable = $this->getRowFromListByField($productsExt['data']['eav_attribute'], 'attribute_id', $super_attribute['attribute_id']);

                $attribute_data['attribute_id']          = $eav_attribute_configurable['attribute_id'];
                $attribute_data['entity_type_id']        = $eav_attribute_configurable['entity_type_id'];
                $attribute_data['attribute_code']        = $eav_attribute_configurable['attribute_code'];
                $attribute_data['backend_type']          = $eav_attribute_configurable['backend_type'];
                $attribute_data['frontend_input']        = $eav_attribute_configurable['frontend_input'];
                $attribute_data['frontend_label']        = $eav_attribute_configurable['frontend_label'];
                $attribute_data['is_required']           = $eav_attribute_configurable['is_required'];
                $attribute_data['is_user_defined']       = $eav_attribute_configurable['is_user_defined'];
                $attribute_data['is_unique']             = $eav_attribute_configurable['is_unique'];
                $attribute_data['super_attribute_label'] = $catalog_product_super_attribute_label;

                if (is_array($catalog_product_super_attribute_pricing)) {
                    foreach ($catalog_product_super_attribute_pricing as $key => $value) {
                        $option_value                               = $this->getListFromListByField($productsExt['data']['eav_attribute_option_value'], 'option_id', $value['value_index']);
                        $option_value_def                           = $this->getRowValueFromListByField($option_value, 'store_id', 0, 'value');
                        $attribute_option_item_data['option_id']    = $value['value_index'];
                        $attribute_option_item_data['option_value'] = $option_value_def;

                        $attribute_option_item_data['is_percent']    = $value['is_percent'];
                        $attribute_option_item_data['pricing_value'] = $value['pricing_value'];
                        $attribute_option_item_data['website_id']    = $value['website_id'];

                        $attribute_option_data[] = $attribute_option_item_data;
                    }
                }

                $attribute_data['option_data']  = $attribute_option_data;
                $configurable_data_attributes[] = $attribute_data;
            }
            $product_data['attribute_configurable'] = $configurable_data_attributes;
        }

        //get link download able
        if ($product['type_id'] === 'downloadable') {
            $product_data['downloadable'] = array();
            $downloadable_link            = array();
            $links                        = $this->getListFromListByField($productsExt['data']['downloadable_link'], 'product_id', $product['entity_id']);
            if ($links && count($links) > 0) {
                foreach ($links as $link) {
                    $title               = $this->getListFromListByField($productsExt['data']['downloadable_link_title'], 'link_id', $link['link_id']);
                    $price               = $this->getListFromListByField($productsExt['data']['downloadable_link_price'], 'link_id', $link['link_id']);

                    $temp_link           = array();
                    $temp_link['link']   = $link;
                    $temp_link['title']  = $this->getValue($title, '0', null);
                    $temp_link['price']  = $this->getValue($price, '0', null);
                    $downloadable_link[] = $temp_link;
                }
//            var_dump($downloadable_link);exit;
                $product_data['downloadable']['link'] = $downloadable_link;
                $product_data['downloadable']['links_title'] = $this->getRowValueFromListByField($entity_varchar,'attribute_id',$eav_attribute['links_title'],'value');
                $product_data['downloadable']['links_purchased_separately'] = $this->getRowValueFromListByField($entity_int,'attribute_id',$eav_attribute['links_purchased_separately'],'value');
            }


            $downloadable_sample = array();
            $sample              = $this->getListFromListByField($productsExt['data']['downloadable_sample'], 'product_id', $product['entity_id']);
            if ($sample && count($sample) > 0) {
                foreach ($sample as $value) {
                    $title                 = $this->getListFromListByField($productsExt['data']['downloadable_sample_title'], 'sample_id', $value['sample_id']);
                    $temp_sample           = array();
                    $temp_sample['sample'] = $value;
                    $temp_sample['title']  = $this->getValue($title, '0');
                    $downloadable_sample[] = $temp_sample;
                }
                $product_data['downloadable']['samples'] = $downloadable_sample;
                $product_data['downloadable']['samples_title'] =$this->getRowValueFromListByField($entity_varchar,'attribute_id',$eav_attribute['samples_title'],'value');
            }
        }
//        var_dump($product_data['downloadable']);exit;
        /**
         *  Get parent product grouped
         */
        if ($product['type_id'] == 'grouped') {
        }

        //tags
        $tag_relation = $this->getListFromListByField($productsExt['data']['tag_relation'], 'product_id', $product['entity_id']);
        if ($tag_relation) {
            $tags = array();
            foreach ($tag_relation as $product_tag) {
                $tag    = $this->getRowFromListByField($productsExt['data']['tag'], 'tag_id', $product_tag['tag_id']);
                $tags[] = $tag['name'];
            }
            $product_data['tags'] = implode(',', $tags);
        }

        //Attribute remain
        $attribute_remain = array();
        foreach ($productsExt['data']['eav_attribute'] as $row) {
            $attribute_data = array();
            if ($row['backend_type'] == 'static' || $row['is_user_defined'] != 1) continue;
            $attribute_data['attribute_id']    = $row['attribute_id'];
            $attribute_data['entity_type_id']  = $row['entity_type_id'];
            $attribute_data['attribute_code']  = $row['attribute_code'];
            $attribute_data['backend_type']    = $row['backend_type'];
            $attribute_data['frontend_input']  = $row['frontend_input'];
            $attribute_data['frontend_label']  = $row['frontend_label'];
            $attribute_data['is_required']     = $row['is_required'];
            $attribute_data['is_user_defined'] = $row['is_user_defined'];
            $attribute_data['is_unique']       = $row['is_unique'];

            $data       = null;
            $data_array = 'entity_' . $row['backend_type'];
            $data       = $this->getRowValueFromListByField($$data_array, 'attribute_id', $row['attribute_id'], 'value');
            if ($data != null) {
                $attribute_data['product_value'] = $data;
            }

            if ($row['frontend_input'] == 'select' && isset($attribute_data['product_value'])) {
                $option_value                    = $this->getRowValueFromListByField($productsExt['data']['all_option'], 'option_id', $attribute_data['product_value'], 'value');
                $attribute_data['product_value'] = $option_value;
            }

            if ($row['frontend_input'] == 'multiselect' && isset($attribute_data['product_value'])) {
                $option_array = explode(',', $attribute_data['product_value']);
                if (is_array($option_array)) {
                    $new_array = array();
                    foreach ($option_array as $item) {
                        $option_value = $this->getRowValueFromListByField($productsExt['data']['all_option'], 'option_id', $item, 'value');
                        $new_array[]  = $option_value;
                    }
                    $attribute_data['product_value'] = implode(',', $new_array);
                }
            }

            if ($row['frontend_input'] == 'multiselect' || $row['frontend_input'] == 'select') {
                $option_data = $this->getListFromListByField($productsExt['data']['all_option'], 'attribute_id', $row['attribute_id']);
                if ($option_data) {
                    $attribute_data['option_data'] = $option_data;
                }
            }

            $attribute_remain[] = $attribute_data;
        }

        // $pro_data_add = array();
        // foreach ($attribute_remain as $key => $value) {
        //     if ($attribute_type[$key] == 'static' || !$attribute_defined[$key]) {
        //         continue;
        //     }
        //     $data = null;
        //     $data_array = 'entity_'.$attribute_type[$key];
        //     $data = $this->getRowValueFromListByField($$data_array,'attribute_id',$value,'value');
        //     if($data){
        //        $pro_data_add[$key]['type'] = $attribute_type[$key];
        //        $pro_data_add[$key]['value'] = $data;
        //     }
        // }


        $product_data['add_data'] = $attribute_remain;
        $product_website = $this->getListFromListByField($productsExt['data']['catalog_product_website'],'product_id',$product['entity_id']);
        $product_data['product_website'] = $this->duplicateFieldValueFromList($product_website,'website_id');
        //url_rewrite multi store
        $url_rewrite                 = $this->getListFromListByField($productsExt['data']['core_url_rewrite'], 'product_id', $product['entity_id']);
        $product_data['url_rewrite'] = array();

        foreach ($url_rewrite as $rewrite) {
            $rewrite_data                  = array();
            $rewrite_data['store_id']      = $rewrite['store_id'];
            $rewrite_data['category_id']   = $rewrite['category_id'];
            $rewrite_data['request_path']   = $rewrite['request_path'];
            $rewrite_data['description']   = $rewrite['description'];
            $product_data['url_rewrite'][] = $rewrite_data;
        }

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $product_data,
        );
    }


    public function getProductIdImport($convert, $product, $productsExt)
    {
        return $convert['id'];
    }

    public function checkProductImport($convert, $product, $productsExt)
    {
        return $this->getMapFieldBySource(self::TYPE_PRODUCT, $convert['id'], $convert['code']) ? true : false;
    }

    public function routerProductImport($convert, $product, $productsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'productImport',
        );
    }

    public function beforeProductImport($convert, $product, $productsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function productImport($convert, $product, $productsExt)
    {
        return array(
            'result' => "success",
            'msg'    => '',
            'data'   => 1
        );

    }

    public function afterProductImport($product_id, $convert, $product, $productsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array()
        );
    }

    public function additionProductImport($product_id, $convert, $product, $productsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareCustomersExport()
    {
        return $this;
    }

    public function prepareCustomersImport()
    {
        return $this;
    }


    public function getCustomersMainExport()
    {
        $id_src    = $this->_notice['process']['customers']['id_src'];
        $limit     = $this->_notice['setting']['customers'];
        $customers = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity WHERE entity_id > " . $id_src . " ORDER BY entity_id ASC LIMIT " . $limit
            ))
        ));
        if (!$customers || $customers['result'] != 'success') {

            return $this->errorConnector();
        }

        return $customers;
    }

    public function getCustomersExtExport($customers)
    {
        $query_url            = $this->getConnectorUrl('query');
        $customerIds          = $this->duplicateFieldValueFromList($customers['data'], 'entity_id');
        $customer_id_con      = $this->arrayToInCondition($customerIds);
        $customer_ext_queries = array(
            'customer_entity_datetime' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity_datetime WHERE entity_id IN " . $customer_id_con
            ),
            'customer_entity_decimal'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity_decimal WHERE entity_id IN " . $customer_id_con
            ),
            'customer_entity_int'      => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity_int WHERE entity_id IN " . $customer_id_con
            ),
            'customer_entity_text'     => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity_text WHERE entity_id IN " . $customer_id_con
            ),
            'customer_entity_varchar'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_entity_varchar WHERE entity_id IN " . $customer_id_con
            ),
            'customer_address_entity'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_address_entity WHERE parent_id IN " . $customer_id_con
            ),
            'eav_attribute'            => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['src']['extends']['customer']} OR entity_type_id = {$this->_notice['src']['extends']['customer_address']}"
            ),
            'newsletter_subscriber'    => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_newsletter_subscriber WHERE customer_id IN " . $customer_id_con
            ),
//            'ewrewardpoints_account' => array(
//                'type' => "select",
//                'query' => "SELECT * FROM _DBPRF_rewardpoints_account WHERE customer_id IN {$customer_id_con}"
//            )
        );
        // add custom
        $customersExt = $this->getConnectorData($query_url, array(
            'serialize' => true,
            'query'     => serialize($customer_ext_queries),
        ));
        if (!$customersExt || $customersExt['result'] != 'success') {
//            var_dump();exit;
            return $this->errorConnector();
        }
        $addressIds               = $this->duplicateFieldValueFromList($customersExt['data']['customer_address_entity'], 'entity_id');
        $address_id_query         = $this->arrayToInCondition($addressIds);
        $customer_ext_rel_queries = array(
            'customer_address_entity_datetime' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_address_entity_datetime WHERE entity_id IN " . $address_id_query
            ),
            'customer_address_entity_decimal'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_address_entity_decimal WHERE entity_id IN " . $address_id_query
            ),
            'customer_address_entity_int'      => array(
                'type'  => 'select',
                'query' => "SELECT caei.*, dcr.* FROM _DBPRF_customer_address_entity_int as caei LEFT JOIN _DBPRF_directory_country_region as dcr ON caei.value = dcr.region_id  WHERE caei.entity_id IN " . $address_id_query
            ),
            'customer_address_entity_text'     => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_address_entity_text WHERE entity_id IN " . $address_id_query
            ),
            'customer_address_entity_varchar'  => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_customer_address_entity_varchar WHERE entity_id IN " . $address_id_query
            ),
        );
        // add custom
        $customersExtRel = $this->getConnectorData($query_url, array(
            'serialize' => true,
            'query'     => serialize($customer_ext_rel_queries),
        ));
        if (!$customersExtRel || $customersExtRel['result'] != 'success') {
            return $this->errorConnector();
        }
        $customersExt = $this->syncConnectorObject($customersExt, $customersExtRel);

        return $customersExt;
    }

    public function convertCustomerExport($customer, $customersExt)
    {
        $eav_attribute_cus = $eav_attribute_cusadd = array();
        foreach ($customersExt['data']['eav_attribute'] as $attribute) {
            if ($attribute['entity_type_id'] == $this->_notice['src']['extends']['customer']) {
                $eav_attribute_cus[$attribute['attribute_code']] = $attribute['attribute_id'];
            } elseif ($attribute['entity_type_id'] == $this->_notice['src']['extends']['customer_address']) {
                $eav_attribute_cusadd[$attribute['attribute_code']] = $attribute['attribute_id'];
            }
        }
        $entity_int      = $this->getListFromListByField($customersExt['data']['customer_entity_int'], 'entity_id', $customer['entity_id']);
        $entity_varchar  = $this->getListFromListByField($customersExt['data']['customer_entity_varchar'], 'entity_id', $customer['entity_id']);
        $entity_datetime = $this->getListFromListByField($customersExt['data']['customer_entity_datetime'], 'entity_id', $customer['entity_id']);
        $gender_id       = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute_cus['gender'], 'value');

        $customer_data                = $this->constructCustomer();
        $customer_data                = $this->addConstructDefault($customer_data);
        $customer_data['id']          = $customer['entity_id'];
        $customer_data['increment_id']=$customer['increment_id'];
        $customer_data['username']    = trim($customer['email']);
        $customer_data['email']       = trim($customer['email']);
        $customer_data['password']    = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['password_hash'], 'value');
        $customer_data['first_name']  = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['firstname'], 'value');
        $customer_data['middle_name'] = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['middlename'], 'value');
        $customer_data['last_name']   = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['lastname'], 'value');
        $customer_data['group_id']    = $customer['group_id'];
        //LDV94begin
        $customer_data['gender'] = $gender_id ? $gender_id : 3;
        //LDV94end

        $customer_data['dob']           = $this->getRowValueFromListByField($entity_datetime, 'attribute_id', $eav_attribute_cus['dob'], 'value');
        $customer_data['is_subscribed'] = $this->getListFromListByField($customersExt['data']['newsletter_subscriber'], 'customer_id', $customer['entity_id']);

        $customer_data['active'] = $customer['is_active'];

        $customer_data['created_at'] = $customer['created_at'];
        $customer_data['updated_at'] = $customer['updated_at'];

        $customer_data['taxvat']    = $this->getRowValueFromListByField($entity_varchar,'attribute_id',$eav_attribute_cus['taxvat'],'value');
        $customer_data['suffix']     = $this->getRowValueFromListByField($entity_varchar,'attribute_id',$eav_attribute_cus['suffix'],'value');
        $customer_data['prefix']     = $this->getRowValueFromListByField($entity_varchar,'attribute_id',$eav_attribute_cus['prefix'],'value');
        /**
         * Quang is gods
         */
        $idAddress_billing  = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute_cus['default_billing'], 'value');
        $idAddress_shipping = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute_cus['default_shipping'], 'value');
        $addressEntity      = $this->getListFromListByField($customersExt['data']['customer_address_entity'], 'parent_id', $customer['entity_id']);
        if ($addressEntity) {
            foreach ($addressEntity as $address) {
                $address_entity_int     = $this->getListFromListByField($customersExt['data']['customer_address_entity_int'], 'entity_id', $address['entity_id']);
                $address_entity_text    = $this->getListFromListByField($customersExt['data']['customer_address_entity_text'], 'entity_id', $address['entity_id']);
                $address_entity_varchar = $this->getListFromListByField($customersExt['data']['customer_address_entity_varchar'], 'entity_id', $address['entity_id']);
                $address_data           = $this->constructCustomerAddress();
                $address_data           = $this->addConstructDefault($address_data);
                $address_data['id']     = $address['entity_id'];
                if ($address['entity_id'] == $idAddress_billing) {
                    $address_data['default']['billing'] = true;
                }
                if ($address['entity_id'] == $idAddress_shipping) {
                    $address_data['default']['shipping'] = true;
                }

                $address_data['first_name'] = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['firstname'], 'value');
                $address_data['last_name']  = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['lastname'], 'value');
                $street                     = $this->getRowValueFromListByField($address_entity_text, 'attribute_id', $eav_attribute_cusadd['street'], 'value');
                $street_explode             = explode('\n', $street);
                $address_data['address_1']  = isset($street_explode[0]) ? $street_explode[0] : '';
                $address_data['address_2']  = isset($street_explode[1]) ? $street_explode[1] : '';
                $address_data['city']       = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['city'], 'value');
                $address_data['postcode']   = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['postcode'], 'value');
                $address_data['telephone']  = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['telephone'], 'value');
                $address_data['company']    = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['company'], 'value');
                $address_data['fax']        = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['fax'], 'value');
                $address_data['suffix']  = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['suffix'], 'value');
                $address_data['prefix']  = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['prefix'], 'value');
                if ($country_code = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['country_id'], 'value')) {
                    $address_data['country']['country_code'] = $country_code;
                    $address_data['country']['name']         = $this->getCountryNameByCode($country_code);
                } else {
                    $address_data['country']['country_code'] = 'US';
                    $address_data['country']['name']         = 'United States';
                }
                $state = $this->getRowFromListByField($address_entity_int, 'attribute_id', $eav_attribute_cusadd['region_id']);
                if ($state && $state['code'] && $state['default_name']) {
                    $address_data['state']['state_code'] = $state['code'];
                    $address_data['state']['name']       = $state['default_name'];
                } else {
                    $address_data['state']['state_code'] = '';// 'AL';
                    $address_data['state']['name']       = '';// 'Alabama';//$address_data['state']['name'] = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute['region'], 'value');
                }
                $customer_data['address'][] = $address_data;
            }
        }
//        var_dump($customer_data['address'][0]);
        /** rewads points */
//        $customer_data['rewads_points'] = array();
//        $ewrewardpoints_account = $this->getListFromListByField($customersExt['data']['ewrewardpoints_account'], 'customer_id', $customer['entity_id']);
//        if($ewrewardpoints_account){
//            foreach ($ewrewardpoints_account as $rewadpoints){
//                $customer_data['rewads_points'][] = $rewadpoints;
//            }
//        }
        $points_balance = $this->getRowValueFromListByField($entity_int, 'attribute_id', '636', 'value');
        if ($points_balance) {
            $customer_data['points_balance'] = $points_balance;
        }

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $customer_data,
        );
    }

    public function getCustomerIdImport($convert, $customer, $customersExt)
    {
        return $convert['id'];
    }

    public function checkCustomerImport($convert, $customer, $customersExt)
    {
        return $this->getMapFieldBySource(self::TYPE_CUSTOMER, $convert['id'], $convert['code']);
    }

    public function routerCustomerImport($convert, $customer, $customersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function beforeCustomerImport($convert, $customer, $customersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function customerImport($convert, $customer, $customersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 1,
        );
    }

    public function afterCustomerImport($customer_id, $convert, $customer, $customersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionCustomerImport($customer_id, $convert, $customer, $customersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareOrdersExport()
    {
        return $this;
    }

    public function prepareOrdersImport()
    {
        return $this;
    }

    public function getOrdersMainExport()
    {
        $id_src = $this->_notice['process']['orders']['id_src'];
        $limit  = $this->_notice['setting']['orders'];
        $orders = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_order WHERE entity_id > " . $id_src . " ORDER BY entity_id ASC LIMIT " . $limit
            ))
        ));
        if (!$orders || $orders['result'] != 'success') {
            return $this->errorConnector();
        }

        return $orders;
    }

    public function getOrdersExtExport($orders)
    {
        $orderIds           = $this->duplicateFieldValueFromList($orders['data'], 'entity_id');
        $order_id_con       = $this->arrayToInCondition($orderIds);
        $url_query          = $this->getConnectorUrl('query');
        $orders_ext_queries = array(
            'sales_flat_order_item'           => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_order_item WHERE order_id IN " . $order_id_con
            ),
            'sales_flat_order_address'        => array(
                'type'  => 'select',
                'query' => "SELECT sfoa.*,sdcr.code,sdcr.default_name FROM _DBPRF_sales_flat_order_address as sfoa
                                LEFT JOIN _DBPRF_directory_country_region as sdcr ON sfoa.region_id = sdcr.region_id
                            WHERE sfoa.parent_id IN " . $order_id_con,
            ),
            'sales_flat_order_status_history' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_order_status_history WHERE parent_id IN " . $order_id_con
            ),
            'sales_flat_order_payment'        => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_order_payment WHERE parent_id IN " . $order_id_con
            ),
            'sales_flat_invoice'              => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_invoice WHERE order_id IN " . $order_id_con
            ),
            'sales_flat_shipment'             => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_shipment WHERE order_id IN " . $order_id_con
            ),
            'sales_flat_creditmemo'           => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_creditmemo WHERE order_id IN " . $order_id_con
            ),

        );
        // add custom
        $ordersExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($orders_ext_queries)
        ));
        if (!$ordersExt || $ordersExt['result'] != 'success') {

            return $this->errorConnector();
        }
        $shipment_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_flat_shipment'], 'entity_id');
        $shipment_item_condition = $this->arrayToInCondition($shipment_ids);

        $creditmemo_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_flat_creditmemo'], 'entity_id');
        $creditmemo_item_condition = $this->arrayToInCondition($creditmemo_ids);

        $invoice_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_flat_invoice'], 'entity_id');
        $invoice_item_condition = $this->arrayToInCondition($invoice_ids);
        $orders_ext_rel_queries = array();
        if ($shipment_ids && count($shipment_ids) > 0) {
            $orders_ext_rel_queries['sales_flat_shipment_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_shipment_item WHERE parent_id IN " . $shipment_item_condition,
            );
            $orders_ext_rel_queries['sales_flat_shipment_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_shipment_comment WHERE parent_id IN " . $shipment_item_condition,
            );
        }
        if ($invoice_ids && count($invoice_ids) > 0) {
            $orders_ext_rel_queries['sales_flat_invoice_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_invoice_item WHERE parent_id IN " . $invoice_item_condition,
            );
            $orders_ext_rel_queries['sales_flat_invoice_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_invoice_comment WHERE parent_id IN " . $invoice_item_condition,
            );
        }
        if ($creditmemo_ids && count($creditmemo_ids) > 0) {
            $orders_ext_rel_queries['sales_flat_creditmemo_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_creditmemo_item WHERE parent_id IN " . $creditmemo_item_condition,
            );
            $orders_ext_rel_queries['sales_flat_creditmemo_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_flat_creditmemo_comment WHERE parent_id IN " . $creditmemo_item_condition,
            );
        }

//         add custom
        if ($orders_ext_rel_queries && count($orders_ext_rel_queries > 0)) {
            $ordersExtRel = $this->getConnectorData($url_query, array(
                'serialize' => true,
                'query'     => serialize($orders_ext_rel_queries)
            ));
            if (!$ordersExtRel || $ordersExtRel['result'] != 'success') {
                var_dump($orders_ext_rel_queries);
                exit;

                return $this->errorConnector();
            }
            $ordersExt = $this->syncConnectorObject($ordersExt, $ordersExtRel);
        }


        return $ordersExt;
    }

    public function convertOrderExport($order, $ordersExt)
    {
        $order_data                 = $this->constructOrder();
        $order_data                 = $this->addConstructDefault($order_data);
        $order_data['id']           = $order['entity_id'];
        $order_data['status']       = $order['status'];
        $order_data['tax']['title'] = "Taxes";
        $order_data['increment_id'] =$order['increment_id'];

        $order_data['tax']['amount']      = $order['base_tax_amount'];
        $order_data['shipping']['title']  = "Shipping";
        $order_data['shipping']['amount'] = $order['shipping_amount'];
        $order_data['subtotal']['title']  = 'Total products';
        $order_data['subtotal']['amount'] = $order['subtotal'];
        $order_data['total']['title']     = 'Total';
        $order_data['total']['amount']    = $order['grand_total'];
        $order_data['currency']           = $order['store_currency_code'];
        $order_data['created_at']         = $order['created_at'];
        $order_data['updated_at']         = $order['updated_at'];
        $order_data['base_subtotal_incl_tax']         = $order['base_subtotal_incl_tax'];
        $order_data['subtotal_incl_tax']         = $order['subtotal_incl_tax'];
        $order_data['discount']['amount'] = $order['discount_amount'];

        $order_customer                = $this->constructOrderCustomer();
        $order_customer                = $this->addConstructDefault($order_customer);
        $order_customer['id']          = $order['customer_id'];
        $order_customer['email']       = trim($order['customer_email']);
        $order_customer['first_name']  = $order['customer_firstname'];
        $order_customer['last_name']   = $order['customer_lastname'];
        $order_customer['middle_name'] = $order['customer_middlename'];
        $order_customer['group_id']    = $order['customer_group_id'];
        $order_data['customer']        = $order_customer;

        $customer_address               = $this->constructOrderAddress();
        $customer_address               = $this->addConstructDefault($customer_address);
        $order_data['customer_address'] = $customer_address;

        $order_address  = $this->getListFromListByField($ordersExt['data']['sales_flat_order_address'], 'parent_id', $order['entity_id']);
        $billingAddress = $this->getRowFromListByField($order_address, 'address_type', 'billing');
        $order_billing  = $this->constructOrderAddress();
        $order_billing  = $this->addConstructDefault($order_billing);
        if ($billingAddress) {
            $order_billing['first_name'] = $billingAddress['firstname'];
            $order_billing['last_name']  = $billingAddress['lastname'];
            $street                      = explode('\n', $billingAddress['street']);
            $order_billing['address_1']  = isset($street[0]) ? $street[0] : '';
            $order_billing['address_2']  = isset($street[1]) ? $street[1] : '';
            $order_billing['city']       = $billingAddress['city'];
            $order_billing['postcode']   = $billingAddress['postcode'];
            $order_billing['telephone']  = $billingAddress['telephone'];
            $order_billing['company']    = $billingAddress['company'];
            if ($billingAddress['country_id']) {
                $order_billing['country']['country_code'] = $billingAddress['country_id'];
                $order_billing['country']['name']         = $this->getCountryNameByCode($billingAddress['country_id']);
            } else {
                $order_billing['country']['country_code'] = 'US';
                $order_billing['country']['name']         = 'United States';
            }
            if ($billingAddress['region_id']) {
                $order_billing['state']['name']       = $billingAddress['default_name'];
                $order_billing['state']['state_code'] = $billingAddress['code'];
            } else {
                $order_billing['state']['name']       = 'Alabama';
                $order_billing['state']['state_code'] = 'AL';
            }
        }
        $order_data['billing_address'] = $order_billing;
        $deliveryAddress               = $this->getRowFromListByField($order_address, 'address_type', 'shipping');
        $order_delivery                = $this->constructOrderAddress();
        $order_delivery                = $this->addConstructDefault($order_delivery);
        if ($deliveryAddress) {
            $order_delivery['first_name'] = $deliveryAddress['firstname'];
            $order_delivery['last_name']  = $deliveryAddress['lastname'];
            $street_deli                  = explode('\n', $deliveryAddress['street']);
            $order_delivery['address_1']  = $street_deli[0];
            $order_delivery['address_2']  = isset($street_deli[1]) ? $street_deli[1] : '';
            $order_delivery['city']       = $deliveryAddress['city'];
            $order_delivery['postcode']   = $deliveryAddress['postcode'];
            $order_delivery['telephone']  = $deliveryAddress['telephone'];
            $order_delivery['company']    = $deliveryAddress['company'];
            if ($deliveryAddress['country_id']) {
                $order_delivery['country']['country_code'] = $deliveryAddress['country_id'];
                $order_delivery['country']['name']         = $this->getCountryNameByCode($deliveryAddress['country_id']);
            } else {
                $order_delivery['country']['country_code'] = 'US';
                $order_delivery['country']['name']         = 'United States';
            }
            if ($deliveryAddress['region_id']) {
                $order_delivery['state']['name']       = $deliveryAddress['default_name'];
                $order_delivery['state']['state_code'] = $deliveryAddress['code'];
            } else {
                $order_delivery['state']['name']       = 'Alabama';
                $order_delivery['state']['state_code'] = 'AL';
            }
        }
        $order_data['shipping_address'] = $order_delivery;

//        $order_payment          = $this->constructOrderPayment();
//        $order_payment          = $this->addConstructDefault($order_payment);
//        $order_payment['title'] = $this->getRowValueFromListByField($ordersExt['data']['sales_flat_order_payment'], 'parent_id', $order['entity_id'], 'method');
//        $order_data['payment']  = $order_payment;
        $order_payment = $this->getListFromListByField($ordersExt['data']['sales_flat_order_payment'], 'parent_id', $order['entity_id']);
        $order_data['payment']  = $order_payment;
        if(isset($order_payment[0])){
            $order_data['payment_method'] = $order_payment[0]['method'];
        }

        /**
         * Get product in order
         */
        $orderProduct = $this->getListFromListByField($ordersExt['data']['sales_flat_order_item'], 'order_id', $order['entity_id']);

        $orderItem      = array();
        $orderChildItem = array();
        foreach ($orderProduct as $order_product) {
            $order_item       = $this->constructOrderItem();
            $order_item       = $this->addConstructDefault($order_item);
            $order_item['id'] = $order_product['item_id'];
            if ($order_product['parent_item_id']) {
                $map_parent                = array();
                $map_parent['parent_id']   = $order_product['parent_item_id'];
                $map_parent['children_id'] = $order_product['item_id'];
                $orderChildItem[]          = $map_parent;
            }
            $order_item['weight']        = $order_product['weight'];
            $order_item['is_qty_decimal']        = $order_product['is_qty_decimal'];
            $order_item['no_discount']        = $order_product['no_discount'];
            $order_item['product_type']        = $order_product['product_type'];
            $order_item['quote_item_id'] = $order_product['quote_item_id'];

            $order_item['parent_item_id']           = $order_product['parent_item_id'];
            $order_item['qty_canceled']             = $order_product['qty_canceled'];
            $order_item['qty_ordered']              = $order_product['qty_ordered'];
            $order_item['qty_backordered']          = $order_product['qty_backordered'];
            $order_item['qty_invoiced']             = $order_product['qty_invoiced'];
            $order_item['qty_refunded']             = $order_product['qty_refunded'];
            $order_item['qty_shipped']              = $order_product['qty_shipped'];
            $order_item['base_cost']                = $order_product['base_cost'];
            $order_item['base_discount_amount']     = $order_product['base_discount_amount'];
            $order_item['base_tax_before_discount'] = $order_product['base_tax_before_discount'];
            $order_item['tax_before_discount']      = $order_product['tax_before_discount'];
            $order_item['locked_do_invoice']        = $order_product['locked_do_invoice'];
            $order_item['locked_do_ship']           = $order_product['locked_do_ship'];
            $order_item['base_price_incl_tax']      = $order_product['base_price_incl_tax'];
            $order_item['base_row_total_incl_tax']  = $order_product['base_row_total_incl_tax'];
            $order_item['product']['id']    = $order_product['product_id'];
            $order_item['product']['name']  = $order_product['name'];
            $order_item['product']['sku']   = $order_product['sku'];
            $order_item['qty']              = intval($order_product['qty_ordered']);
            $order_item['price']            = $order_product['price'];
            $order_item['original_price']   = $order_product['original_price'];
            $order_item['tax_amount']       = $order_product['tax_amount'];
            $order_item['tax_percent']      = $order_product['tax_percent'];
            $order_item['tax_invoiced']      = $order_product['tax_invoiced'];
            $order_item['discount_amount']  = $order_product['discount_amount'];
            $order_item['discount_percent'] = $order_product['discount_percent'];
            $order_item['discount_invoiced'] = $order_product['discount_invoiced'];
            $order_item['row_total'] = $order_product['row_total'];
            $order_item['row_invoiced']         = $order_product['row_invoiced'];
            $order_item['row_weight']         = $order_product['row_weight'];
            $order_item['price_incl_tax']         = $order_product['price_incl_tax'];
            $order_item['row_total_incl_tax']         = $order_product['row_total_incl_tax'];
            $order_item['free_shipping']         = $order_product['free_shipping'];
            $order_item['tax_canceled']         = $order_product['tax_canceled'];
            $order_item['tax_refunded']         = $order_product['tax_refunded'];
//            $order_item['discount_refunded']         = $order_product['discount_refunded'];
            if ($order_product['product_options']) {
                $options         = unserialize($order_product['product_options']);
                $item_options = array();
                if (isset($options['options'])) {
                    $orderItemOption = array();
                    foreach ($options['options'] as $option) {
                        $order_item_option                      = $this->constructOrderItemOption();
                        $order_item_option['option_name']       = $option['label'];
                        $order_item_option['option_value_name'] = $option['value'];
                        $orderItemOption[]                      = $order_item_option;
                    }
                    $item_options['options'] = $orderItemOption;
                }
                if (isset($options['attributes_info'])) {
                    $orderItemOption = array();
                    foreach ($options['attributes_info'] as $attr) {
                        $order_item_option                      = $this->constructOrderItemOption();
                        $order_item_option['option_name']       = $attr['label'];
                        $order_item_option['option_value_name'] = $attr['value'];
                        $orderItemOption[]                      = $order_item_option;
                    }
                    $item_options['attributes_info'] = $orderItemOption;

                }
                $order_item['options'] = $item_options;
            }
            $orderItem[] = $order_item;
        }
        $order_data['items'] = $orderItem;
        if ($orderChildItem && count($orderChildItem) > 0) {
            $order_data['order_child_item'] = $orderChildItem;
        }
        /**
         * Get order history
         */
        $orderStatusHistory = $this->getListFromListByField($ordersExt['data']['sales_flat_order_status_history'], 'parent_id', $order['entity_id']);
        $orderHistory       = array();
        foreach ($orderStatusHistory as $status_history) {
            $order_history               = $this->constructOrderHistory();
            $order_history               = $this->addConstructDefault($order_history);
            $order_history['id']         = $status_history['entity_id'];
            $order_history['status']     = $status_history['status'];
            $order_history['comment']    = $status_history['comment'];
            $order_history['created_at'] = $status_history['created_at'];
            $order_history['notified']   = $status_history['is_customer_notified'] == 1 ? true : false;
            $orderHistory[]              = $order_history;
        }
        $order_data['histories'] = $orderHistory;

        $shipment      = $this->getListFromListByField($ordersExt['data']['sales_flat_shipment'], 'order_id', $order['entity_id']);

        if (count($shipment) > 0) {
            $order_data['shipment']['data'] = $shipment[0];
            $shipment_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_flat_shipment_item'], 'parent_id', $shipment[0]['entity_id']);
            if (count($shipment_grid_item) > 0) {
                $order_data['shipment']['item'] = $shipment_grid_item;
            }
            $shipment_grid_comment = $this->getListFromListByField($ordersExt['data']['sales_flat_shipment_comment'],'parent_id',$shipment[0]['entity_id']);
            if (count($shipment_grid_comment) > 0) {
                $order_data['shipment']['comment'] = $shipment_grid_comment;
            }
        }

        $creditmemo = $this->getListFromListByField($ordersExt['data']['sales_flat_creditmemo'], 'order_id', $order['entity_id']);

        if (count($creditmemo) > 0) {
            $order_data['creditmemo']['data'] = $creditmemo[0];
            $creditmemo_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_flat_creditmemo_item'], 'parent_id', $creditmemo[0]['entity_id']);
            if (count($creditmemo_grid_item) > 0) {
                $order_data['creditmemo']['item'] = $creditmemo_grid_item;
            }
            $creditmemo_grid_comment             = $this->getListFromListByField($ordersExt['data']['sales_flat_creditmemo_comment'], 'parent_id', $creditmemo[0]['entity_id']);
            if (count($creditmemo_grid_comment) > 0) {
                $order_data['creditmemo']['comment'] = $creditmemo_grid_comment;
            }
        }

        $invoice = $this->getListFromListByField($ordersExt['data']['sales_flat_invoice'], 'order_id', $order['entity_id']);

        if (count($invoice) > 0) {
            $order_data['invoice']['data'] = $invoice[0];
            $invoice_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_flat_invoice_item'], 'parent_id', $invoice[0]['entity_id']);
            if (count($invoice_grid_item) > 0) {
                $order_data['invoice']['item'] = $invoice_grid_item;
            }
            $invoice_grid_comment             = $this->getListFromListByField($ordersExt['data']['sales_flat_invoice_comment'], 'parent_id', $invoice[0]['entity_id']);
            if (count($invoice_grid_comment) > 0) {
                $order_data['invoice']['comment'] = $invoice_grid_comment;
            }
        }

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $order_data,
        );
    }

    public function getOrderIdImport($convert, $order, $ordersExt)
    {
        return false;
    }

    public function checkOrderImport($convert, $order, $ordersExt)
    {
        return false;
    }

    public function routerOrderImport($convert, $order, $ordersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'orderImport',
        );
    }

    public function beforeOrderImport($convert, $order, $ordersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function orderImport($convert, $order, $ordersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterOrderImport($order_id, $convert, $order, $ordersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionOrderImport($order_id, $convert, $order, $ordersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareReviewsExport()
    {
        return $this;
    }

    public function prepareReviewsImport()
    {
        return $this;
    }

    public function getReviewsMainExport()
    {
        $id_src  = $this->_notice['process']['reviews']['id_src'];
        $limit   = $this->_notice['setting']['reviews'];
        $reviews = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_review WHERE review_id > " . $id_src . " ORDER BY review_id ASC LIMIT " . $limit
            ))
        ));
        if (!$reviews || $reviews['result'] != 'success') {
            return $this->errorConnector();
        }

        return $reviews;
    }

    public function getReviewsExtExport($reviews)
    {
        $reviewIds           = $this->duplicateFieldValueFromList($reviews['data'], 'review_id');
        $review_id_query     = $this->arrayToInCondition($reviewIds);
        $url_query           = $this->getConnectorUrl('query');
        $reviews_ext_queries = array(
            'review_detail'      => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_review_detail WHERE review_id IN " . $review_id_query
            ),
            'rating_option_vote' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_rating_option_vote WHERE review_id IN " . $review_id_query
            ),
            'review_store' => array(
                'type' => 'select',
                'query'=> "SELECT * FROM _DBPRF_review_store WHERE store_id != 0 AND review_id IN ".$review_id_query,
            )
        );
        // add custom
        $reviewsExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($reviews_ext_queries)
        ));
        if (!$reviewsExt || $reviewsExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $reviews_ext_rel_queries = array();
        // add custom
        if ($reviews_ext_rel_queries) {
            $reviewsExtRel = $this->getConnectorData($url_query, array(
                'serialize' => true,
                'query'     => serialize($reviews_ext_rel_queries)
            ));
            if (!$reviewsExtRel || $reviewsExtRel['result'] != 'success') {
                return $this->errorConnector();
            }
            $reviewsExt = $this->syncConnectorObject($reviewsExt, $reviewsExtRel);
        }

        return $reviewsExt;
    }

    public function convertReviewExport($review, $reviewsExt)
    {
        $review_data                  = $this->constructReview();
        $review_data                  = $this->addConstructDefault($review_data);
        $review_detail                = $this->getRowFromListByField($reviewsExt['data']['review_detail'], 'review_id', $review['review_id']);
        $review_data['id']            = $review['review_id'];
        $review_data['language_id']   = $this->_notice['src']['language_default'];
        $review_data['product']['id'] = $review['entity_pk_value'];
        if ($review_detail) {
            $review_data['customer']['id']   = $review_detail['customer_id'];
            $review_data['customer']['name'] = $review_detail['nickname'];
            $review_data['title']            = $review_detail['title'];
            $review_data['content']          = $review_detail['detail'];
        }
        $review_data['status']     = $review['status_id'];
        $review_data['created_at'] = $review['created_at'];
        $review_data['updated_at'] = $review['created_at'];

        $rating             = $this->constructReviewRating();
        $rate_value         = 0;
        $rating_option_vote = $this->getListFromListByField($reviewsExt['data']['rating_option_vote'], 'review_id', $review['review_id']);
        if($rating_option_vote && count($rating_option_vote)>0){
            $rating_vote = array();
            foreach ($rating_option_vote as $vote){
                $rating_vote_data = array();
                $rating_vote_data['remote_ip'] = $vote['remote_ip'];
                $rating_vote_data['percent']    = $vote['percent'];
                $rating_vote_data['rating_id']  = $vote['rating_id'];
                $rate_value                    += ($vote['percent'] / 10);
                $rating_vote[]                 = $rating_vote_data;
            }
            $review_data['rating_vote'] = $rating_vote;
        }

        $rating['rate_code'] = 'default';
        if ($rate_value) {
            $rating['rate'] = round($rate_value / count($rating_option_vote)) * 10 ;
        } else {
            $rating['rate'] = 0;
        }
        $review_data['rating'][] = $rating;
        $multi_review = $this->getListFromListByField($reviewsExt['data']['review_store'],'review_id',$review['review_id']);
        if($multi_review && count($multi_review)>0){
            $review_data['multi_store'] = $multi_review;
        }
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $review_data,
        );
    }

    public function getReviewIdImport($convert, $review, $reviewsExt)
    {
        return $convert['id'];
    }

    public function checkReviewImport($convert, $review, $reviewsExt)
    {
        return $this->getMapFieldBySource(self::TYPE_REVIEW, $convert['id'], $convert['code']) ? true : false;
    }

    public function routerReviewImport($convert, $review, $reviewsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'reviewImport',
        );
    }

    public function beforeReviewImport($convert, $review, $reviewsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function reviewImport($convert, $review, $reviewsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterReviewImport($review_id, $convert, $review, $reviewsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionReviewImport($review_id, $convert, $review, $reviewsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function preparePagesExport()
    {
        return $this;
    }

    public function preparePagesImport()
    {
        return $this;
    }

    public function getPagesMainExport()
    {
        $id_src = $this->_notice['process']['pages']['id_src'];
        $limit  = $this->_notice['setting']['pages'];
        $pages  = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_cms_page WHERE  page_id > " . $id_src . " ORDER BY page_id ASC LIMIT " . $limit
            )),
        ));
        if (!$pages || $pages['result'] != 'success') {
            return $this->errorConnector();
        }

        return $pages;
    }

    public function getPagesExtExport($pages)
    {
        $pageIds           = $this->duplicateFieldValueFromList($pages['data'], 'page_id');
        $pageIdCondition   = $this->arrayToInCondition($pageIds);
        $pages_ext_queries = array(
            'cms_page_store' => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_cms_page_store WHERE page_id IN ' . $pageIdCondition,
            ),

        );
        $pagesExt          = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($pages_ext_queries),
        ));
        if (!$pagesExt || $pagesExt['result'] != 'success') {
            return $this->errorConnector();
        }

        return $pagesExt;
    }

    public function convertPageExport($page, $pagesExt)
    {
        $page_data       = $page;
        $page_data['id'] = $page['page_id'];
        unset($page_data['page_id']);
        $page_data['page_layout'] = $page['root_template'];
        unset($page_data['root_template']);

        $stores              = $this->getListFromListByField($pagesExt['data']['cms_page_store'], 'page_id', $page['page_id']);
        $page_data['stores'] = array();
        foreach ($stores as $store) {
            $page_data['stores'][] = $store['store_id'];
        }

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $page_data,
        );
    }

    public function getPageIdImport($convert, $page, $pagesExt)
    {
        return false;
    }

    public function checkPageImport($convert, $page, $pagesExt)
    {
        return false;
    }

    public function routerPageImport($convert, $page, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'pageImport',
        );
    }

    public function beforePageImport($convert, $page, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function pageImport($convert, $page, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterPageImport($page_id, $convert, $page, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionPageImport($page_id, $convert, $page, $pagesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareBlocksExport()
    {
        return $this;
    }

    public function prepareBlocksImport()
    {
        return $this;
    }

    public function getBlocksMainExport()
    {
        $id_src = $this->_notice['process']['blocks']['id_src'];
        $limit  = $this->_notice['setting']['blocks'];
        $blocks = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_cms_block WHERE  block_id > " . $id_src . " ORDER BY block_id ASC LIMIT " . $limit
            )),
        ));
        if (!$blocks || $blocks['result'] != 'success') {
            return $this->errorConnector();
        }

        return $blocks;
    }

    public function getBlocksExtExport($blocks)
    {
        $blockIds           = $this->duplicateFieldValueFromList($blocks['data'], 'block_id');
        $blockIdCondition   = $this->arrayToInCondition($blockIds);
        $blocks_ext_queries = array(
            'cms_block_store' => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_cms_block_store WHERE block_id IN ' . $blockIdCondition,
            ),
            'eav_attribute' => array(
                'type' => "select",
                'query' => 'SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = '.$this->_notice['src']['extends']['catalog_category'],
            ),

        );
        $blocksExt          = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($blocks_ext_queries),
        ));
        if (!$blocksExt || $blocksExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $attribute_landing_page = $this->getRowValueFromListByField($blocksExt['data']['eav_attribute'],'attribute_code','landing_page','attribute_id');
        $block_ext_rel_query = array(
            'category_block' => array(
                'type' => 'select',
                'query' => "SELECT value as block_id,entity_id as category_id,store_id FROM _DBPRF_catalog_category_entity_int WHERE attribute_id = '".$attribute_landing_page."' AND value IN ".$blockIdCondition,
            ),
        );
        $blocksExtRel = $this->getConnectorData($this->getConnectorUrl('query'),array(
            'serialize' => true,
            'query' => serialize($block_ext_rel_query),
        ));
        if(!$blocksExtRel || $blocksExtRel['result'] != 'success'){
            return $this->errorConnector();
        }
        $blocksExt = $this->syncConnectorObject($blocksExt,$blocksExtRel);
        return $blocksExt;
    }

    public function convertBlockExport($block, $blocksExt)
    {
        $block_data       = $block;
        $block_data['id'] = $block['block_id'];
        unset($block_data['block_id']);

        $stores               = $this->getListFromListByField($blocksExt['data']['cms_block_store'], 'block_id', $block['block_id']);
        $block_data['stores'] = array();
        foreach ($stores as $store) {
            $block_data['stores'][] = $store['store_id'];
        }
        $block_data['category_block'] = $this->getListFromListByField($blocksExt['data']['category_block'],'block_id',$block['block_id']);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $block_data,
        );
    }
    public function getBlockIdImport($convert, $block, $blocksExt)
    {
        return false;
    }

    public function checkBlockImport($convert, $block, $blocksExt)
    {
        return false;
    }

    public function routerBlockImport($convert, $block, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'blockImport',
        );
    }

    public function beforeBlockImport($convert, $block, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function blockImport($convert, $block, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterBlockImport($block_id, $convert, $block, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionBlockImport($block_id, $convert, $block, $blocksExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }


    public function prepareWidgetsExport()
    {
        return $this;
    }

    public function prepareWidgetsImport()
    {
        return $this;
    }

    public function getWidgetsMainExport()
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getWidgetsExtExport($widgets)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function convertWidgetExport($widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getWidgetIdImport($convert, $widget, $widgetsExt)
    {
        return false;
    }

    public function checkWidgetImport($convert, $widget, $widgetsExt)
    {
        return false;
    }

    public function routerWidgetImport($convert, $Widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'WidgetImport',
        );
    }

    public function beforeWidgetImport($convert, $widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function widgetImport($convert, $widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterWidgetImport($widget_id, $convert, $widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionWidgetImport($widget_id, $convert, $widget, $widgetsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function preparePollsExport()
    {
        return $this;
    }

    public function preparePollsImport()
    {
        return $this;
    }

    public function getPollsMainExport()
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getPollsExtExport($polls)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function convertPollExport($poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getPollIdImport($convert, $poll, $pollsExt)
    {
        return false;
    }

    public function checkPollImport($convert, $poll, $pollsExt)
    {
        return false;
    }

    public function routerPollImport($convert, $poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'pollImport',
        );
    }

    public function beforePollImport($convert, $poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function pollImport($convert, $poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterPollImport($poll_id, $convert, $poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionPollImport($poll_id, $convert, $poll, $pollsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }


    public function prepareTransactionsExport()
    {
        return $this;
    }

    public function prepareTransactionsImport()
    {
        return $this;
    }

    public function getTransactionsMainExport()
    {
        $id_src      = $this->_notice['process']['transactions']['id_src'];
        $limit       = $this->_notice['setting']['transactions'];
        $transaction = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_core_email_template WHERE template_id > ' . $id_src . '
                            ORDER BY template_id ASC LIMIT ' . $limit,
            )),
        ));
        if (!$transaction || $transaction['result'] != 'success') {
            return $this->errorConnector();
        }

        return $transaction;
    }

    public function getTransactionsExtExport($transactions)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function convertTransactionExport($transaction, $transactionsExt)
    {
        $transaction_data       = $transaction;
        $transaction_data['id'] = $transaction['template_id'];
        unset($transaction_data['template_id']);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $transaction_data,
        );
    }

    public function getTransactionIdImport($convert, $transaction, $transactionsExt)
    {
        return false;
    }

    public function checkTransactionImport($convert, $transaction, $transactionsExt)
    {
        return false;
    }

    public function routerTransactionImport($convert, $transaction, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'transactionImport',
        );
    }

    public function beforeTransactionImport($convert, $transaction, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function transactionImport($convert, $transaction, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterTransactionImport($transaction_id, $convert, $transaction, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionTransactionImport($transaction_id, $convert, $transaction, $transactionsExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareNewslettersExport()
    {
        return $this;
    }

    public function prepareNewslettersImport()
    {
        return $this;
    }

    public function getNewslettersMainExport()
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getNewslettersExtExport($newsletters)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function convertNewsletterExport($newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getNewsletterIdImport($convert, $newsletter, $newslettersExt)
    {
        return false;
    }

    public function checkNewsletterImport($convert, $newsletter, $newslettersExt)
    {
        return false;
    }

    public function routerNewsletterImport($convert, $newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'newsletterImport',
        );
    }

    public function beforeNewsletterImport($convert, $newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function newsletterImport($convert, $newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterNewsletterImport($newsletter_id, $convert, $newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionNewsletterImport($newsletter_id, $convert, $newsletter, $newslettersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareUsersExport()
    {
        return $this;
    }

    public function prepareUsersImport()
    {
        return $this;
    }

    public function getUsersMainExport()
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getUsersExtExport($users)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function convertUserExport($user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function getUserIdImport($convert, $user, $usersExt)
    {
        return false;
    }

    public function checkUserImport($convert, $user, $usersExt)
    {
        return false;
    }

    public function routerUserImport($convert, $user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'UserImport',
        );
    }

    public function beforeUserImport($convert, $user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function userImport($convert, $user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterUserImport($user_id, $convert, $user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionUserImport($user_id, $convert, $user, $usersExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareRulesExport()
    {
        return $this;
    }

    public function prepareRulesImport()
    {
        return $this;
    }

    public function getRulesMainExport()
    {
        $id_src = $this->_notice['process']['rules']['id_src'];
        $limit = $this->_notice['setting']['rules'];
        $rules = $this->getConnectorData($this->getConnectorUrl('query'),array(
            'query' => serialize(array(
                'type' => 'select',
                'query' => 'SELECT * FROM _DBPRF_salesrule 
                WHERE rule_id > '.$id_src.' ORDER BY rule_id ASC LIMIT '.$limit,
            )),
        ));
        if(!$rules || $rules['result'] != 'success'){
            return $this->errorConnector();
        }
        return $rules;
    }

    public function getRulesExtExport($rules)
    {
        $url_query = $this->getConnectorUrl('query');
        $rule_ids = $this->duplicateFieldValueFromList($rules['data'],'rule_id');
        $rule_idCon = $this->arrayToInCondition($rule_ids);
        $rulesExt = $this->getConnectorData($url_query,array(
            'serialize' => true,
            'query'     => serialize(array(
                'salesrule_coupon' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_coupon WHERE rule_id IN '.$rule_idCon,
                ),
                'salesrule_customer' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_customer WHERE rule_id IN '.$rule_idCon,
                ),
//                'salesrule_customer_group' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_salesrule_customer_group WHERE rule_id IN '.$rule_idCon,
//                ),
                'salesrule_label' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_label WHERE rule_id IN '.$rule_idCon,
                ),
//                'salesrule_website' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_salesrule_website WHERE rule_id IN '.$rule_idCon,
//                ),
                'salesrule_product_attribute' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_product_attribute WHERE rule_id IN '.$rule_idCon,
                ),

            )),
        ));
        $coupon_ids = $this->duplicateFieldValueFromList($rulesExt['data']['salesrule_coupon'],'coupon_id');
        $coupon_id_con = $this->arrayToInCondition($coupon_ids);
        $rulesRelExtQueries = array(
            'salesrule_coupon_usage' => array(
                'type' => 'select',
                'query' => 'SELECT * FROM _DBPRF_salesrule_coupon_usage WHERE coupon_id IN '.$coupon_id_con,
            ),
        );
//        $customer_group = $this->duplicateFieldValueFromList($rulesExt['data']['salesrule'],'customer_group_ids');
//        if(!$customer_group){
//            $rulesRelExtQueries['salesrule_customer_group'] = array(
//                'type' => 'select',
//                'query' => 'SELECT * FROM _DBPRF_salesrule_customer_group WHERE rule_id IN '.$rule_idCon,
//
//            );
//        }
        $rulesRelExt = $this->getConnectorData($url_query,array(
            'serialize' => true,
            'query' => serialize($rulesRelExtQueries),
        ));
        if(!$rulesRelExt || $rulesRelExt['result'] != 'success'){
            return $this->errorConnector();
        }
        $rulesExt = $this->syncConnectorObject($rulesExt,$rulesRelExt);
        return $rulesExt;
    }

    public function convertRuleExport($rule, $rulesExt)
    {
        $rule_id = $rule['rule_id'];
        $rule_data = $rule;
        $rule_data['id'] = $rule_id;
        unset($rule_data['rule_id']);
        $coupons = $this->getListFromListByField($rulesExt['data']['salesrule_coupon'],'rule_id',$rule_id);
        $rule_data['label'] = $this->getListFromListByField($rulesExt['data']['salesrule_label'],'rule_id',$rule_id);
        $customers = $this->getListFromListByField($rulesExt['data']['salesrule_customer'],'rule_id',$rule_id);
        $rule_data['customer'] = array();
        foreach ($customers as $customer){
            unset($customer['rule_customer_id']);
            unset($customer['rule_id']);
            $rule_data['customer'][] = $customer;
        }
        $rule_data['customer_group'] = array();
        $customer_group = explode(',',$rule['customer_group_ids']);
        $customer_group_ids = array();
        foreach($customer_group as $group){
            $group_id   = $this->_notice['map']['customer_group'][$group];
            if(!in_array($group_id,$customer_group_ids)){
                $customer_group_ids[] = $group;
            }
        }
        $rule_data['customer_group'] = $customer_group_ids;
        $rule_data['rule_website'] = explode(',',$rule['website_ids']);
        $rule_data['conditions'] = unserialize($rule['conditions_serialized']);
        unset($rule_data['conditions_serialized']);
        $rule_data['actions'] = unserialize($rule['actions_serialized']);
        unset($rule_data['actions_serialized']);
        $rule_data['coupon'] =array();
        foreach ($coupons as $coupon){
            $usage = $this->getListFromListByField($rulesExt['data']['salesrule_coupon_usage'],'coupon_id',$coupon['coupon_id']);
            unset($usage['coupon_id']);
            unset($coupon['coupon_id']);
            unset($coupon['rule_id']);
            $coupon_data = array();
            $coupon_data['data'] = $coupon;
            $coupon_data['usage'] = $usage;
            $rule_data['coupon'][] = $coupon_data;
        }
//        var_dump($rule_data);exit;
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $rule_data,
        );
    }

    public function getRuleIdImport($convert, $rule, $rulesExt)
    {
        return false;
    }

    public function checkRuleImport($convert, $rule, $rulesExt)
    {
        return false;
    }

    public function routerRuleImport($convert, $rule, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'ruleImport',
        );
    }

    public function beforeRuleImport($convert, $rule, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function ruleImport($convert, $rule, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterRuleImport($rule_id, $convert, $rule, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionRuleImport($rule_id, $convert, $rule, $rulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function prepareCartrulesExport()
    {
        return $this;
    }

    public function prepareCartrulesImport()
    {
        return $this;
    }

    public function getCartrulesMainExport()
    {
        $id_src = $this->_notice['process']['cartrules']['id_src'];
        $limit = $this->_notice['setting']['cartrules'];
        $cartrules = $this->getConnectorData($this->getConnectorUrl('query'),array(
            'query' => serialize(array(
                'type' => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule WHERE rule_id > '.$id_src.'
                            ORDER BY rule_id ASC LIMIT '.$limit,
            )),

        ));
        if(!$cartrules || $cartrules['result'] != 'success'){
            return $this->errorConnector();
        }
        return $cartrules;
    }

    public function getCartrulesExtExport($cartrules)
    {
        $rule_ids = $this->duplicateFieldValueFromList($cartrules['data'],'rule_id');
        $rule_id_con = $this->arrayToInCondition($rule_ids);
        $cartrulesExt = $this->getConnectorData($this->getConnectorUrl('query'),array(
            'serialize' => true,
            'query' => serialize(array(
//                'catalogrule_customer_group' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_catalogrule_customer_group WHERE rule_id IN '.$rule_id_con,
//                ),
                'catalogrule_group_website' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_catalogrule_group_website WHERE rule_id IN '.$rule_id_con,
                ),
                'catalogrule_product' => array(
                    'type' => 'select',
                    'query' => 'SELECT * FROM _DBPRF_catalogrule_product WHERE rule_id IN '.$rule_id_con,
                ),
//                'catalogrule_website' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_catalogrule_website WHERE rule_id IN '.$rule_id_con,
//                ),
            )),
        ));
        if(!$cartrulesExt || $cartrulesExt['result'] != 'success'){
            return $this->errorConnector();
        }
        $rule_product_ids = $this->duplicateFieldValueFromList($cartrulesExt['data']['catalogrule_product'],'product_id');
        $cartrulesExtQueries = array();
        if($rule_product_ids && count($rule_product_ids)>0){
            $cartrulesExtQueries['catalogrule_product_price'] = array(
                'type' => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule_product_price WHERE product_id IN '.$this->arrayToInCondition($rule_product_ids),
            );

        }
//        $customer_group = $this->duplicateFieldValueFromList($cartrules['data'],'customer_group_ids');
//        if(!$customer_group){
//            $rulesRelExtQueries['catalogrule_customer_group'] = array(
//                'type' => 'select',
//                'query' => 'SELECT * FROM _DBPRF_catalogrule_customer_group WHERE rule_id IN '.$rule_id_con,
//
//            );
//        }
        if(count($cartrulesExtQueries)>0){
            $cartrulesRelExt = $this->getConnectorData($this->getConnectorUrl('query'),array(
                'serialize' => true,
                'query' => serialize($cartrulesExtQueries),
            ));
            if(!$cartrulesRelExt || $cartrulesRelExt['result'] != 'success'){
                return $this->errorConnector();
            }
            $cartrulesExt = $this->syncConnectorObject($cartrulesExt,$cartrulesRelExt);
        }
        return $cartrulesExt;
    }

    public function convertCartruleExport($cartrule, $cartrulesExt)
    {
        $cartrule_data = $cartrule;
        $id = $cartrule['rule_id'];

        $cartrule_data['id'] = $id;
        unset($cartrule_data['rule_id']);

        $cartrule_data['customer_group'] = array();
        $customer_group = explode(',',$cartrule['customer_group_ids']);
        $customer_group_ids = array();
        foreach($customer_group as $group){
            $group_id   = $this->_notice['map']['customer_group'][$group];
            if($group_id && !in_array($group_id,$customer_group_ids)){
                $customer_group_ids[] = $group;
            }
        }
        $cartrule_data['customer_group'] = $customer_group_ids;
        $cartrule_data['cartrule_website'] = explode(',',$cartrule['website_ids']);
        $cartrule_data['product'] = array();
        $cartrule_data['product_price'] = array();
        $cartrule_data['product'] = $this->getListFromListByField($cartrulesExt['data']['catalogrule_product'],'rule_id',$id);
//        if($cartrule_product && count($cartrule_product)>0){
//            foreach ($cartrule_product as $product){
//                $product_id = $product['product_id'];
//                unset($product['product_id']);
//                unset($product['rule_id']);
//                unset($product['rule_product_id']);
//                $product_data = array();
//                $product_data['data'] = $product;
//                $product_price = $this->getListFromListByField($cartrulesExt['data']['catalogrule_product_price'],'product_id',$product_id);
//                unset($product_price['rule_product_price_id']);
//                $cartrule_data['product_price'] = array_merge($cartrule_data['product_price'],$product_price);
//            }
//        }
        $cartrule_data['conditions'] = unserialize($cartrule['conditions_serialized']);
        $cartrule_data['actions'] = unserialize($cartrule['actions_serialized']);
        unset($cartrule_data['actions_serialized']);
        unset($cartrule_data['conditions_serialized']);
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $cartrule_data,
        );
    }

    public function getCartruleIdImport($convert, $cartrule, $cartrulesExt)
    {
        return false;
    }

    public function checkCartruleImport($convert, $cartrule, $cartrulesExt)
    {
        return false;
    }

    public function routerCartruleImport($convert, $cartrule, $cartrulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 'cartruleImport',
        );
    }

    public function beforeCartruleImport($convert, $cartrule, $cartrulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function cartruleImport($convert, $cartrule, $cartrulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => 0,
        );
    }

    public function afterCartruleImport($cartrule_id, $convert, $cartrule, $cartrulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    public function additionCartruleImport($cartrule_id, $convert, $cartrule, $cartrulesExt)
    {
        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => array(),
        );
    }

    /**
     * TODO: EXTENDS
     */

    public function addConstructDefault($construct)
    {
        $construct['site_id']     = 1;
        $construct['language_id'] = $this->_notice['src']['language_default'];

        return $construct;
    }

    public function getCategoryParent($parent_id)
    {
        $response   = $this->_defaultResponse();
        $url_query  = $this->getConnectorUrl('query');
        $categories = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity WHERE entity_id = " . $parent_id
            ))
        ));
        if (!$categories || $categories['result'] != 'success') {
            $response['result'] = 'warning';

            return $response;
        }
        $categoriesExt = $this->getCategoriesExtExport($categories);
        if (!$categoriesExt || $categoriesExt['result'] != "success") {
            $response['result'] = 'warning';

            return $response;
        }
        $category = $categories['data'][0];

        return $this->convertCategoryExport($category, $categoriesExt);
    }

    protected function _getDefaultLanguage($stores)
    {
        if (isset($stores['0']['sort_order'])) {
            $sort_order = $stores['0']['sort_order'];
        } else {
            return 1;
        }
        foreach ($stores as $store) {
            if ($store['sort_order'] < $sort_order) {
                $sort_order = $store['sort_order'];
            }
        }
        $default_lang = 1;
        foreach ($stores as $store) {
            if ($store['sort_order'] == $sort_order) {
                $default_lang = $store['store_id'];
                break;
            }
        }

        return $default_lang;
    }
}
