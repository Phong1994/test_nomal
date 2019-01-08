<?php

class LECM_Model_Cart_Webshop
    extends LECM_Model_Cart
{
    /**
     * TODO: DISPLAY
     */
    const LEN_INCREMENT = 8;

    public function displayConfigSource()
    {
        $parent = parent::displayConfigSource();
        if ($parent['result'] != 'success') {
            return $parent;
        }
        $response       = $this->_defaultResponse();
        $default_config = $this->getConnectorData($this->getConnectorUrl('query', null, 'src'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'languages'       => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_store WHERE code != 'admin'"
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
            var_dump(1);
            exit;

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
        $this->_notice['src']['categoryData'] = array(
            1 => 'Default Category',
        );
        $this->_notice['src']['attributes']   = array(
            1 => 'Default Attribute'
        );
        $config                               = $this->getConnectorData($this->getConnectorUrl('query', null, 'src'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'languages'           => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'general/locale/code'"
                ),
                'currencies'          => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/allow'"
                ),
                'orders_status'       => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_sales_order_status"
                ),
                'customer_group'      => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_customer_group"
                ),
                'attributes_set'      => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_eav_attribute_set WHERE entity_type_id = '" . $this->_notice['src']['extends']['catalog_product'] . "'"
                ),
                "category_root"       => array(
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
                    'type'  => 'select',
                    'query' => "SELECT * FROM  _DBPRF_eav_attribute_group ",
                )
            )),
        ));
        if (!$config || $config['result'] != "success") {
            return $this->errorConnector(false);
        }
        $config_data   = $config['data'];
        $language_data = $currency_data = $order_status_data = $customer_group_data = $eav_attribute_group_data = array();

        $langexist = array();
        foreach ($default_config_data['languages'] as $language_row) {
            $lang_id                                = $language_row['store_id'];
            $lang_name                              = $language_row['name'];
            $website_id                             = $language_row['website_id'];
            $this->_notice['src']['site'][$lang_id] = $website_id;
            if (!in_array($lang_name, $langexist)) {
                $langexist[]             = $lang_name;
                $language_data[$lang_id] = $lang_name;
            }
        }
        foreach ($config_data['orders_status'] as $order_status_row) {
            $order_status_id                     = $order_status_row['status'];
            $order_status_name                   = $order_status_row['label'];
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

        //=======================================================
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
        foreach ($eav_attribute_group as $key => $value) {
            $eav_attribute_group_data[$value['attribute_group_id']] = $value['attribute_group_name'];
        }

        //=======================================================
        $this->_notice['src']['attribute_group_name']   = $eav_attribute_group_data;
        $this->_notice['src']['languages']              = $language_data;
        $this->_notice['src']['order_status']           = $order_status_data;
        $this->_notice['src']['currencies']             = $currency_data;
        $this->_notice['src']['customer_group']         = $customer_group_data;
        $this->_notice['src']['support']['country_map'] = false;
//        $this->_notice['support']['pages']              = true;
//        $this->_notice['support']['blocks']             = true;
//        $this->_notice['support']['transactions']       = true;
        $this->_notice['support']['rules']              = true;
//        $this->_notice['support']['cartrules']          = true;
        $response['result'] = 'success';

        return $response;
    }

    public function displayConfigTarget()
    {
        $parent = parent::displayConfigTarget();
        if ($parent['result'] != 'success') {
            return $parent;
        }
        $response       = $this->_defaultResponse();
        $this->_notice['target']['category_root'] = 0;
//        $this->_notice['target']['site']          = array(
//            1 => 'Default Shop'
//        );
        $this->_notice['target']['categoryData'] = array(
            1 => 'Default Category',
        );

        $this->_notice['target']['attributes'] = array(
            1 => 'Default Attribute'
        );

        $config = $this->getConnectorData($this->getConnectorUrl('query', null, 'target'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'orders_status'  => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_order_status"
                ),
                'customer_group' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_customer_group"
                ),
                'attributes_set' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_attribute_set"
                ),
                "products"       => array(
                    'type'  => 'select',
                    'query' => 'SELECT COUNT(1) AS count FROM _DBPRF_product'
                ),
                "categories"     => array(
                    'type'  => 'select',
                    'query' => 'SELECT COUNT(1) AS count FROM _DBPRF_category'
                ),
            )),
        ));


        if (!$config || $config['result'] != "success") {
            return $this->errorConnector(false);
        }
        $config_data   = $config['data'];
        foreach ($config_data['orders_status'] as $order_status_row) {
            $order_status_id                     = $order_status_row['status'];
            $order_status_name                   = $order_status_row['label'];
            $order_status_data[$order_status_id] = $order_status_name;
        }
        foreach ($config_data['customer_group'] as $customer_group) {
            $customer_group_id                       = $customer_group['id'];
            $customer_group_name                     = $customer_group['code'];
            $customer_group_data[$customer_group_id] = $customer_group_name;
        }

        //=======================================================
        $attributes_set_data = array();

        foreach ($config_data['attributes_set'] as $attributes_set_row) {
            $attributes_set_id                       = $attributes_set_row['id'];
            $attributes_set_name                     = $attributes_set_row['label'];
            $attributes_set_data[$attributes_set_id] = $attributes_set_name;
        }

        if (count($attributes_set_data) > 0) {
            $this->_notice['target']['attributes'] = $attributes_set_data;
        }


        //=======================================================
        $this->_notice['target']['number_of_prd']  = $config_data['products'][0]['count'];
        $this->_notice['target']['number_of_cat']  = $config_data['categories'][0]['count'];
        $this->_notice['target']['order_status']   = $order_status_data;
        $this->_notice['target']['customer_group'] = $customer_group_data;
        // $this->_notice['target']['support']['country_map'] = false;

        $this->_notice['target']['support']['attribute_map'] = true;

        $this->_notice['target']['support']['language_map'] = false;
        // $this->_notice['target']['languages'] = $language_data;
        $this->_notice['target']['support']['order_status_map'] = true;
        // $this->_notice['target']['order_status'] = $order_status_data;
        $this->_notice['target']['support']['currency_map'] = false;
        // $this->_notice['target']['currencies'] = $currency_data;
        $this->_notice['target']['support']['country_map'] = false;//
        // $this->_notice['target']['countries'] = $country_data;
        $this->_notice['target']['support']['category_map'] = true;

        $this->_notice['target']['support']['customer_group_map'] = true;//
        $this->_notice['target']['support']['taxes']              = false;
        $this->_notice['target']['support']['manufacturers']      = false;
        $this->_notice['target']['support']['categories']         = true;
        $this->_notice['target']['support']['products']           = true;
        $this->_notice['target']['support']['customers']          = true;
        $this->_notice['target']['support']['orders']             = true;
        $this->_notice['target']['support']['reviews']            = false;
        $this->_notice['target']['support']['pre_cat']            = false;

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
                    'query' => "SELECT COUNT(1) AS count FROM _DBPRF_sales_order WHERE entity_id > {$this->_notice['process']['orders']['id_src']}",
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

        $real_totals['manufacturers'] = ($real_totals['manufacturers'] > 1) ? $real_totals['manufacturers'] : 0;
//        $real_totals['products'] = 1;
        //var_dump($real_totals);exit;
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

    // public function clearData() {
    //     return array(
    //         'result' => "success",
    //         'msg' => $this->getMsgStartImport('taxes'),
    //     );
    // }

    /**
     * TODO: CLEAR
     */

    protected function _clearTargetTaxes()
    {
        if (!$this->_notice['config']['taxes']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetManufacturers';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'tax_class',
            'tax_calculation_rate',
            'tax_calculation_rule',
            'tax_calculation',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetTaxes';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetManufacturers';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetManufacturers()
    {
        // return $this->_notice['target']['clear'];
        $this->_notice['target']['clear']['result']      = 'process';
        $this->_notice['target']['clear']['function']    = '_clearTargetCategories';
        $this->_notice['target']['clear']['table_index'] = 0;

        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetCategories()
    {
        if (!$this->_notice['config']['categories']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetProducts';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables = array(
            'category',
            'url_rewrite',
        );

        $table_index           = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetCategories';
            $table                                        = $tables[$table_index];
            $where = '';
            if ($table == 'url_rewrite') {
                $where = ' WHERE type like "category"';
            }

            // if($table == 'url_alias'){
            //     $where = ' WHERE query like "category_id=%" ';
            // }
            $clear_table = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`" . $where
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetProducts';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    public function _clearTargetProducts()
    {
        if (!$this->_notice['config']['products']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetCustomers';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'url_rewrite',
            'product_category',
            'product_datetime',
            'product_decimal',
            'product_text',
            'product_varchar',
            'product_int',
            'product_media',
            'product_option',
            'product_option_value',
            'product_relation',
            'product',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetProducts';
            $table                                        = $tables[$table_index];

            $where = '';

            if ($table == 'url_rewrite') {
                $where = ' WHERE type like "product"';
            }


            $clear_table = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`" . $where
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                var_dump($clear_table);
                exit;

                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetCustomers';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetCustomers()
    {
        // return $this->_notice['target']['clear'];
        if (!$this->_notice['config']['customers']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetOrders';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'customer_address',
            'customer',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetCustomers';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetOrders';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    protected function _clearTargetOrders()
    {
        if (!$this->_notice['config']['orders']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetReviews';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'order_address',
            'order',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetOrders';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`",
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetReviews';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    public function _clearTargetReviews()
    {
        if (!$this->_notice['config']['reviews']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetPages';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

//        var_dump($this->_notice['target']['clear']);exit;

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'review',
            'review_store',
            'review_detail',
            'rating_option_vote',
            'review_entity_summary',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];
        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetReviews';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetPages';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    /**
     * TODO: PROCESS
     */

    public function _clearTargetPages()
    {

        if (!$this->_notice['config']['pages']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetBlocks';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'url_rewrite',
            'cms_page_store',
            'cms_page',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];

        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetPages';
            $table                                        = $tables[$table_index];
            $where                                        = '';
            if ($table == 'url_rewrite') {
                $where = ' WHERE entity_type like "cms-page"';
            }
            $clear_table = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`" . $where
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetBlocks';
            $this->_notice['target']['clear']['table_index'] = 0;
        }


        return $this->_notice['target']['clear'];
    }

    public function _clearTargetBlocks()
    {
        if (!$this->_notice['config']['blocks']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetTransactions';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'cms_block_store',
            'cms_block',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];

        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetBlocks';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetTransactions';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    public function _clearTargetWidgets()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetPolls()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetTransactions()
    {
        if (!$this->_notice['config']['transactions']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetRules';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'email_template',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];

        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetTransactions';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetRules';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    public function _clearTargetNewsletters()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetUsers()
    {
        return $this->_notice['target']['clear'];
    }

    public function _clearTargetRules()
    {
        if (!$this->_notice['config']['rules']) {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetCartrules';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'salesrule_label',
            'salesrule_customer_group',
            'salesrule_customer',
            'salesrule_coupon_usage',
            'salesrule_coupon',
            'salesrule',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];

        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetRules';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'process';
            $this->_notice['target']['clear']['function']    = '_clearTargetCartrules';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

    public function _clearTargetCartrules()
    {
        if (!$this->_notice['config']['cartrules']) {
            $this->_notice['target']['clear']['result']      = 'success';
            $this->_notice['target']['clear']['function']    = '_clearTargetCartrules';
            $this->_notice['target']['clear']['table_index'] = 0;
            $this->_notice['target']['clear']['msg']         = '';

            return $this->_notice['target']['clear'];
        }
        $tables      = array(
            'catalogrule_website',
            'catalogrule_customer_group',
            'catalogrule_product_price',
            'catalogrule_product',
            'catalogrule_group_website',
            'catalogrule',
        );
        $table_index = $this->_notice['target']['clear']['table_index'];

        if (isset($tables[$table_index])) {
            $this->_notice['target']['clear']['result']   = 'process';
            $this->_notice['target']['clear']['function'] = '_clearTargetCartrules';
            $table                                        = $tables[$table_index];
            $clear_table                                  = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'query' => serialize(array(
                    'type'  => 'query',
                    'query' => "DELETE FROM `_DBPRF_" . $table . "`"
                )),
            ));
            if (!$clear_table || $clear_table['result'] != 'success') {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $clear_result = $clear_table['data'];
            if ($clear_result === false) {
                return array(
                    'result' => 'error',
                    'msg'    => $this->consoleError('Clear data failed. Error: Could not empty table ' . $table)
                );
            }
            $table_index++;
            $this->_notice['target']['clear']['table_index'] = $table_index;
        } else {
            $this->_notice['target']['clear']['result']      = 'success';
            $this->_notice['target']['clear']['function']    = '_clearTargetCartrules';
            $this->_notice['target']['clear']['table_index'] = 0;
        }

        return $this->_notice['target']['clear'];
    }

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
        $tax_data                         = array();
        $tax_data['id']                   = $tax['class_id'];
        $tax_data['product_class']        = $tax;
        $tax_data['tax_calculation']      = $this->getListFromListByField($taxesExt['data']['tax_calculation'], 'product_tax_class_id', $tax['class_id']);
        $customer_tax_class_ids           = $this->duplicateFieldValueFromList($tax_data['tax_calculation'], 'customer_tax_class_id');
        $tax_data['customer_class']       = $this->getListFromListByField($taxesExt['data']['tax_class'], 'class_id', $customer_tax_class_ids);
        $rate_ids                         = $this->duplicateFieldValueFromList($tax_data['tax_calculation'], 'tax_calculation_rate_id');
        $rule_ids                         = $this->duplicateFieldValueFromList($tax_data['tax_calculation'], 'tax_calculation_rule_id');
        $tax_data['tax_calculation_rate'] = $this->getListFromListByField($taxesExt['data']['tax_calculation_rate'], 'tax_calculation_rate_id', $rate_ids);
        $tax_data['tax_calculation_rule'] = $this->getListFromListByField($taxesExt['data']['tax_calculation_rule'], 'tax_calculation_rule_id', $rule_ids);
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
        return $this->getMapFieldBySource(self::TYPE_TAX_PRODUCT, $convert['id']) ? true : false;
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
        // var_dump($convert);exit;

        $url_src    = $this->_notice['src']['cart_url'];
        $url_target = $this->_notice['target']['cart_url'];
        $url_query  = $this->getConnectorUrl('query');

        $tax_class_data = array(
            'class_name' => $convert['product_class']['class_name'] ? $convert['product_class']['class_name'] : '',
            'class_type' => $convert['product_class']['class_type'] ? $convert['product_class']['class_type'] : 'PRODUCT',
        );

//        $tax_class_query = $this->createInsertQuery('tax_class',$tax_class_data);

        $tax_class_id = $this->importData($this->createInsertQuery('tax_class',$tax_class_data),'tax');

        if (!$tax_class_id) {
            //warning
            return $this->errorConnector();
        }
//        if (!$tax_class_id) {
//            // warning
//            $response['result'] = 'warning';
//            $response['msg']    = $this->warningImportEntity('Tax Product Class', $convert['id'], $convert['product_class']['class_name']);
//
//            return $response;
//        }

        $this->insertMap($url_src, $url_target, self::TYPE_TAX_PRODUCT, $convert['id'], $tax_class_id, $convert['product_class']['class_name']);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $tax_class_id
        );
    }

    public function afterTaxImport($tax_id, $convert, $tax, $taxesExt)
    {
        $url_src                          = $this->_notice['src']['cart_url'];
        $url_target                       = $this->_notice['target']['cart_url'];
        $url_query                        = $this->getConnectorUrl('query');
        $tax_data['tax_calculation']      = $taxesExt['data']['tax_calculation'];
        $tax_data['customer_class']       = $taxesExt['data']['tax_class'];
        $tax_data['tax_calculation_rate'] = $taxesExt['data']['tax_calculation_rate'];
        $tax_data['tax_calculation_rule'] = $taxesExt['data']['tax_calculation_rule'];

        $tax_calculation_ids_array      = array();
        $customer_class_ids_array       = array();
        $tax_calculation_rate_ids_array = array();
        $tax_calculation_rule_ids_array = array();

        if (isset($convert['customer_class']) && is_array($convert['customer_class'])) {
            foreach ($convert['customer_class'] as $key => $item) {
                $customer_tax_class_exists = $this->selectMap($url_src, $url_target, self::TYPE_TAX_CUSTOMER, $item['class_id'], null, null);
                if ($customer_tax_class_exists) {
                    $customer_tax_class_id                       = $customer_tax_class_exists['id_desc'];
                    $customer_class_ids_array[$item['class_id']] = $customer_tax_class_id;
                    continue;
                }


                $tax_class_data = array(
                    'class_name' => $item['class_name'],
                    'class_type' => 'CUSTOMER',
                );

                $tax_class_customer_id = $this->importData($this->createInsertQuery("tax_class",$tax_class_data),'tax');

                if(!$tax_class_customer_id){
                    continue;
                }
                $customer_class_ids_array[$item['class_id']] = $tax_class_customer_id;

                $this->insertMap($url_src, $url_target, self::TYPE_TAX_CUSTOMER, $item['class_id'], $tax_class_customer_id, $item['class_name']);
            }
        }

        if (is_array($convert['tax_calculation_rate'])) {
            foreach ($convert['tax_calculation_rate'] as $item) {
                $tax_rate_exists = $this->selectMap($url_src, $url_target, self::TYPE_TAX_RATE, $item['tax_calculation_rate_id'], null, null);
                if ($tax_rate_exists) {
                    $tax_rate_id                                                      = $tax_rate_exists['id_desc'];
                    $tax_calculation_rate_ids_array[$item['tax_calculation_rate_id']] = $tax_rate_id;
                    continue;
                }

                $tax_calculation_rate_data = array(
                    'tax_country_id' => $item['tax_country_id'],
                    'tax_region_id'  => $item['tax_region_id'],
                    'tax_postcode'   => $item['tax_postcode'],
                    'code'           => $item['code'],
                    'rate'           => $item['rate'],
                    // 'zip_is_range' => $item['zip_is_range'],
                    // 'zip_from' => $item['zip_from'],
                    // 'zip_to' => $item['zip_to'],
                );

                $tax_calculation_rate_id = $this->importData($this->createInsertQuery('tax_calculation_rate',$tax_calculation_rate_data),'tax');

                if(!$tax_calculation_rate_id){
                    continue;
                }
                $tax_calculation_rate_ids_array[$item['tax_calculation_rate_id']] = $tax_calculation_rate_id;

                $this->insertMap($url_src, $url_target, self::TYPE_TAX_RATE, $item['tax_calculation_rate_id'], $tax_calculation_rate_id, $item['code']);
            }
        }

        if (is_array($convert['tax_calculation_rule'])) {
            foreach ($convert['tax_calculation_rule'] as $item) {
                $tax_calculation_rule_exists = $this->selectMap($url_src, $url_target, self::TYPE_TAX, $item['tax_calculation_rule_id'], null, null);
                if ($tax_calculation_rule_exists) {
                    $tax_calculation_rule_id                                          = $tax_calculation_rule_exists['id_desc'];
                    $tax_calculation_rule_ids_array[$item['tax_calculation_rule_id']] = $tax_calculation_rule_id;
                    continue;
                }


                $tax_calculation_rule_data = array(
                    'code'               => $this->getValue($item, 'code'),
                    'priority'           => $this->getValue($item, 'priority', 0),
                    'position'           => $this->getValue($item, 'position', 0),
                    'calculate_subtotal' => $this->getValue($item, 'calculate_subtotal', 0),
                );


                $tax_calculation_rule_id = $this->importData($this->createInsertQuery('tax_calculation_rule',$tax_calculation_rule_data),'tax');

                if(!$tax_calculation_rule_id){
                    continue;
                }

                $tax_calculation_rule_ids_array[$item['tax_calculation_rule_id']] = $tax_calculation_rule_id;

                $this->insertMap($url_src, $url_target, self::TYPE_TAX, $item['tax_calculation_rule_id'], $tax_calculation_rule_id, $item['code']);
            }
        }
        if (is_array($convert['tax_calculation'])) {
            foreach ($convert['tax_calculation'] as $item) {
                if (isset($customer_class_ids_array[$item['customer_tax_class_id']])
                    && isset($tax_calculation_rate_ids_array[$item['tax_calculation_rate_id']])
                    && isset($tax_calculation_rule_ids_array[$item['tax_calculation_rule_id']])
                ) {

                    if ($customer_class_ids_array[$item['customer_tax_class_id']] !== null
                        && $tax_calculation_rate_ids_array[$item['tax_calculation_rate_id']] !== null
                        && $tax_calculation_rule_ids_array[$item['tax_calculation_rule_id']] !== null
                    ) {
                        $tax_calculation_data = array(
                            'tax_calculation_rate_id' => $tax_calculation_rate_ids_array[$item['tax_calculation_rate_id']],
                            'tax_calculation_rule_id' => $tax_calculation_rule_ids_array[$item['tax_calculation_rule_id']],
                            'customer_tax_class_id'   => $customer_class_ids_array[$item['customer_tax_class_id']],
                            'product_tax_class_id'    => $tax_id,
                        );

                        $this->importData($this->createInsertQuery('tax_calculation',$tax_calculation_data),'tax');

                    }
                }
            }
        }

        if (isset($convert['customer_group']) && is_array($convert['customer_group'])) {
            foreach ($convert['customer_group'] as $key => $customer_group) {
                $customer_group_id_src = $customer_group['customer_group_id'];
                if (isset($this->_notice['map']['customer_group'][$customer_group_id_src])) {
                    $customer_group_id = $this->_notice['map']['customer_group'][$customer_group_id_src];
                    $tax_customer_id   = $this->getMapFieldBySource(self::TYPE_TAX_CUSTOMER, $customer_group['tax_class_id']);
                    if ($tax_customer_id) {
                        $update_customer_group_query = "UPDATE customer_group SET tax_class_id = '" . $tax_customer_id . "' WHERE customer_group_id = '" . $customer_group_id . "'";
                        $update_customer_group       = $this->getConnectorData($url_query, array(
                            'query' => serialize(array(
                                'type'  => 'update',
                                'query' => $update_customer_group_query,
                            )),
                        ));
                        if (!$update_customer_group || $update_customer_group['result'] != 'success') {
                            Bootstrap::logError($update_customer_group_query,'tax');

                            //warning
                        }

                    }
                }
            }
        }

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
        // var_dump($this->_notice['map']);
        // var_dump($convert);

        $url_src                       = $this->_notice['src']['cart_url'];
        $url_desc                      = $this->_notice['target']['cart_url'];
        $url_query                     = $this->getConnectorUrl('query');
        $all_query                     = array();
        $product_eav_attribute_queries = array(
            'eav_attribute' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = 4 and attribute_code = 'manufacturer'"
            ),
        );

        $product_eav_attribute = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_eav_attribute_queries)
        ));

        if (count($product_eav_attribute['data']['eav_attribute']) == 0) {
            //insert Manufacturer attribute begin
            //eav_attribute begin
            $eav_attribute_data = array(
                'entity_type_id'  => 4,
                'attribute_code'  => 'manufacturer',
                'attribute_model' => null,
                'backend_model'   => null,
                'backend_type'    => 'int',
                'backend_table'   => null,
                'frontend_model'  => null,
                'frontend_input'  => 'select',
                'frontend_label'  => 'Manufacturer',
                'frontend_class'  => null,
                'source_model'    => null,
                'is_required'     => 0,
                'is_user_defined' => 1,
                'default_value'   => null,
                'is_unique'       => 0,
                'note'            => null,
            );

            $eav_attribute_query  = "INSERT INTO _DBPRF_eav_attribute ";
            $eav_attribute_query  .= $this->arrayToInsertCondition($eav_attribute_data);
            $eav_attribute_import = $this->getConnectorData($url_query, array(
                'query' => serialize(array(
                    'type'   => 'insert',
                    'query'  => $eav_attribute_query,
                    'params' => array(
                        'insert_id' => true,
                    )
                )),
            ));


            if (!$eav_attribute_import || $eav_attribute_import['result'] != 'success') {
                if (Bootstrap::getConfig('dev_mode')) {
                    Bootstrap::logQuery($eav_attribute_query);
                    var_dump(1);
                    exit;
                }

                return $this->errorConnector();
            }
            $attribute_id = $eav_attribute_import['data'];
            if (!$attribute_id) {
                // warning
                if (Bootstrap::getConfig('dev_mode')) {
                    Bootstrap::logQuery($eav_attribute_query);
                    var_dump(1);
                    exit;
                }

                return $this->errorConnector();
            }

            // $this->insertMap($url_src, $url_desc, self::TYPE_ATTR, $item['option_id'], $attribute_id, $item['option_name']);
            //eav_attribute end

            //eav_entity_attribute begin


            $eav_entity_attribute_data = array(
                'entity_type_id'     => 4,
                'attribute_set_id'   => $this->_notice['map']['attributes'][4],
                'attribute_group_id' => 7,
                'attribute_id'       => $attribute_id,
                'sort_order'         => 15,
            );

            $eav_entity_attribute_query = "INSERT INTO _DBPRF_eav_entity_attribute ";
            $eav_entity_attribute_query .= $this->arrayToInsertCondition($eav_entity_attribute_data);

            if (Bootstrap::getConfig('dev_mode')) {
                $eav_entity_attribute_import = $this->getConnectorData($url_query, array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $eav_entity_attribute_query,
                        'params' => array(
                            'insert_id' => true,
                        )
                    )),
                ));
                if (!$eav_entity_attribute_import) {
                    var_dump($eav_entity_attribute_query);
                    exit;
                }
            } else {
                $all_query['eav_entity_attribute_query'] = array(
                    'type'   => 'insert',
                    'query'  => $eav_entity_attribute_query,
                    'params' => array(
                        'insert_id' => true,
                    )
                );
            }
            //eav_entity_attribute end

            //catalog_eav_attribute begin
            $catalog_eav_attribute_data = array(
                'attribute_id'                  => $attribute_id,
                'frontend_input_renderer'       => null,
                'is_global'                     => 1,
                'is_visible'                    => 1,
                'is_searchable'                 => 1,
                'is_filterable'                 => 1,
                'is_comparable'                 => 1,
                'is_visible_on_front'           => 1,
                'is_html_allowed_on_front'      => 1,
                'is_used_for_price_rules'       => 0,
                'is_filterable_in_search'       => 1,
                'used_in_product_listing'       => 0,
                'used_for_sort_by'              => 0,
                'apply_to'                      => null,
                'is_visible_in_advanced_search' => 1,
                'position'                      => 0,
                'is_wysiwyg_enabled'            => 0,
                'is_used_for_promo_rules'       => 0,
                'is_required_in_admin_store'    => 0,
                'is_used_in_grid'               => 0,
                'is_visible_in_grid'            => 0,
                'is_filterable_in_grid'         => 0,
                'search_weight'                 => 1,
                'additional_data'               => null,
            );

            $catalog_eav_attribute_query = "INSERT INTO _DBPRF_catalog_eav_attribute ";
            $catalog_eav_attribute_query .= $this->arrayToInsertCondition($catalog_eav_attribute_data);

            if (Bootstrap::getConfig('dev_mode')) {
                $catalog_eav_attribute_import = $this->getConnectorData($url_query, array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $catalog_eav_attribute_query,
                        'params' => array(
                            'insert_id' => true,
                        )
                    )),
                ));
                if (!$catalog_eav_attribute_import) {
                    Bootstrap::logQuery($catalog_eav_attribute_query);
                    var_dump(1);
                    exit;
                }
            } else {
                $all_query['catalog_eav_attribute_query'] = array(
                    'type'   => 'insert',
                    'query'  => $catalog_eav_attribute_query,
                    'params' => array(
                        'insert_id' => true,
                    )
                );
            }
            //catalog_eav_attribute end
            //insert Manufacturer attribute end
        } else {
            $attribute_id = $product_eav_attribute['data']['eav_attribute'][0]['attribute_id'];
        }

        if (!$this->checkOptionExists($convert['name'], 'manufacturer')) {
            //eav_attribute_option begin
            $eav_attribute_option_data   = array(
                'attribute_id' => $attribute_id,
                'sort_order'   => 0,
            );
            $eav_attribute_option_query  = "INSERT INTO _DBPRF_eav_attribute_option ";
            $eav_attribute_option_query  .= $this->arrayToInsertCondition($eav_attribute_option_data);
            $eav_attribute_option_import = $this->getConnectorData($url_query, array(
                'query' => serialize(array(
                    'type'   => 'insert',
                    'query'  => $eav_attribute_option_query,
                    'params' => array(
                        'insert_id' => true,
                    )
                )),
            ));
            $option_id                   = null;
            if (!$eav_attribute_option_import || $eav_attribute_option_import['result'] != 'success') {
                //warning
                if (Bootstrap::getConfig('dev_mode')) {
                    Bootstrap::logQuery($eav_attribute_option_query);
                    var_dump(1);
                    exit;
                }

                return $this->errorConnector();
            }
            $option_id = $eav_attribute_option_import['data'];
            if (!$option_id) {
                // warning
                if (Bootstrap::getConfig('dev_mode')) {
                    Bootstrap::logQuery($eav_attribute_option_query);
                    var_dump(1);
                    exit;
                }

                return $this->errorConnector();
            }


            $this->insertMap($url_src, $url_desc, self::TYPE_MANUFACTURER, $convert['id'], $option_id, null);
            //eav_attribute_option end

            //eav_attribute_option_value begin
            $eav_attribute_option_value_data = array(
                'option_id' => $option_id,
                'store_id'  => 0,
                'value'     => $convert['name'],
            );

            $eav_attribute_option_value_query = "INSERT INTO _DBPRF_eav_attribute_option_value ";
            $eav_attribute_option_value_query .= $this->arrayToInsertCondition($eav_attribute_option_value_data);

            if (Bootstrap::getConfig('dev_mode')) {
                $eav_attribute_option_value_import = $this->getConnectorData($url_query, array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $eav_attribute_option_value_query,
                        'params' => array(
                            'insert_id' => true,
                        )
                    )),
                ));
                if (!$eav_attribute_option_value_import) {
                    Bootstrap::logQuery($eav_attribute_option_value_query);
                    var_dump(1);
                    exit;
                }
            } else {
                $all_query['eav_attribute_option_value_query'] = array(
                    'type'   => 'insert',
                    'query'  => $eav_attribute_option_value_query,
                    'params' => array(
                        'insert_id' => true,
                    )
                );
            }

            //eav_attribute_option_value end
        }
        if (!Bootstrap::getConfig('dev_mode') && count($all_query) > 0) {
            $all_import = $this->getConnectorData($url_query, array(
                'serialize' => true,
                'query'     => serialize($all_query)
            ));
            if (!$all_import) {
                return $this->errorConnector();
            }
            if ($all_import['result'] != 'success') {
                // warning
                return $this->warningSQL($all_import);
            }
        }

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
                'query' => "SELECT * FROM _DBPRF_catalog_category_entity WHERE level > 1 
          AND entity_id > " . $id_src . " ORDER BY entity_id ASC LIMIT " . $limit
            ))
        ));
        if (!$categories || $categories['result'] != 'success') {
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

        if ($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']) {
            $categories_ext_queries['core_url_rewrite'] = array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_url_rewrite WHERE entity_type = 'category' AND entity_id IN {$category_id_query}"
            );
        }

        $categoriesExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($categories_ext_queries)
        ));

        if (!$categoriesExt || $categoriesExt['result'] != 'success') {
            var_dump(1);
            exit;

            return $this->errorConnector();
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
        $category_data['name']             = $name_def ? $name_def : '';
        $category_data['description']      = $description_def ? $description_def : '';
        $category_data['meta_title']       = $meta_title_def ? $meta_title_def : '';
        $category_data['meta_keyword']     = $meta_keywords_def ? $meta_keywords_def : '';
        $category_data['meta_description'] = $meta_description_def ? $meta_description_def : '';
        $is_anchor                         = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['is_anchor']);
        $is_anchor_def                     = $this->getRowValueFromListByField($is_anchor, 'store_id', 0, 'value');
        $display_mode                      = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['display_mode']);
        $display_mode_def                  = $this->getRowValueFromListByField($display_mode, 'store_id', 0, 'value');
        $url_path                          = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['url_path']);
        $url_path_def                      = $this->getRowValueFromListByField($url_path, 'store_id', 0, 'value');
        $url_key                           = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['url_key']);
        $url_key_def                       = $this->getRowValueFromListByField($url_key, 'store_id', 0, 'value');
        $category_data['display_mode']     = $display_mode_def ? $display_mode_def : '';
        $category_data['url_key']          = $url_key_def ? $url_key_def : '';
        $category_data['code']             = $code_parent?$code_parent.'/'.$url_key_def:$url_key_def;

        $category_data['url_path']         = $url_path_def ? $url_path_def : '';
        $category_data['is_anchor']        = $is_anchor_def ? $is_anchor_def : 0;
        foreach ($this->_notice['src']['languages'] as $language_id => $language_label) {
            $category_language_data                     = $this->constructCategoryLang();
            $name_lang                                  = $this->getRowValueFromListByField($names, 'store_id', $language_id, 'value');
            $description_lang                           = $this->getRowValueFromListByField($descriptions, 'store_id', $language_id, 'value');
            $meta_title_lang                            = $this->getRowValueFromListByField($meta_titles, 'store_id', $language_id, 'value');
            $meta_keywords_lang                         = $this->getRowValueFromListByField($meta_keywords, 'store_id', $language_id, 'value');
            $meta_description_lang                      = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $language_id, 'value');
            $is_anchor_lang                             = $this->getRowValueFromListByField($is_anchor, 'store_id', $language_id, 'value');
            $category_language_data['is_anchor']        = $is_anchor_lang ? $is_anchor_lang : $category_data['is_anchor'];
            $url_key_lang                               = $this->getRowValueFromListByField($url_key, 'store_id', $language_id, 'value');
            $url_path_lang                              = $this->getRowValueFromListByField($url_path, 'store_id', $language_id, 'value');
            $category_language_data['url_path']         = $url_path_lang ? $url_path_lang : $category_data['url_path'];
            $category_language_data['url_key']          = $url_key_lang ? $url_key_lang : $category_data['url_key'];
            $display_mode_lang                          = $this->getRowValueFromListByField($display_mode, 'store_id', $language_id, 'value');
            $category_language_data['display_mode']     = $description_lang ? $display_mode_lang : $category_data['display_mode'];
            $category_language_data['name']             = $name_lang ? $name_lang : $category_data['name'];
            $category_language_data['description']      = $description_lang ? $description_lang : $category_data['description'];
            $category_language_data['meta_title']       = $meta_title_lang ? $meta_title_lang : $category_data['meta_title'];
            $category_language_data['meta_keyword']     = $meta_keywords_lang ? $meta_keywords_lang : $category_data['meta_keyword'];
            $category_language_data['meta_description'] = $meta_description_lang ? $meta_description_lang : $category_data['meta_description'];
            $category_data['languages'][$language_id]   = $category_language_data;
        }
        $url_rewrite                  = $this->getListFromListByField($categoriesExt['data']['core_url_rewrite'], 'entity_id', $category['entity_id']);
        $category_data['url_rewrite'] = array();
        foreach ($url_rewrite as $rewrite) {
            $rewrite_data                   = array();
            $rewrite_data['store_id']       = $rewrite['store_id'];
            $rewrite_data['request_path']   = $rewrite['request_path'];
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
//        if($this->_notice['config']['add_new'] && $this->_notice['config']['seo']){
//            $this->updateSeoCate($category,$categoriesExt);
//        }

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

    protected function _importCategoryParent($parent)
    {
        $response      = $this->_defaultResponse();
        $url_src       = $this->_notice['src']['cart_url'];
        $url_desc      = $this->_notice['target']['cart_url'];
        $parent_exists = $this->selectMap($url_src, $url_desc, self::TYPE_CATEGORY, $parent['id'], null, $parent['code']);
        if ($parent_exists) {
            $response['result']    = 'success';
            $response['data']      = $parent_exists['id_desc'];
            $response['cate_path'] = $parent_exists['code_desc'];

            return $response;
        }
        $category      = $parent['category'];
        $categoriesExt = $parent['categoriesExt'];
        $parent_import = $this->categoryImport($parent, $category, $categoriesExt);
        if ($parent_import['result'] != 'success') {
            return $parent_import;
        }
        $parent_id = $parent_import['data'];
        if (!$parent_id) {
            $response['result'] = 'warning';

            return $response;
        }
        $this->afterCategoryImport($parent_id, $parent, $category, $categoriesExt);

        return $parent_import;
    }

    public function categoryImport($convert, $category, $categoriesExt)
    {
//        print_r($this->_notice['map']['categoryData'][$convert['parent']['id']]);exit;
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');
        $parent_id = null;
        if ($convert['parent'] && $convert['parent']['id'] != $convert['id'] && ($convert['parent']['id'] || $convert['parent']['code']) && $convert['parent']['level'] > 1) {

            $parent_import = $this->_importCategoryParent($convert['parent']);
            if ($parent_import['result'] != 'success') {
                $response           = $this->_defaultResponse();
                $response['result'] = 'warning';
                $response['msg']    = $this->consoleWarning('Could not import');

                return $response;
            }
            $parent_id = $parent_import['data'];

            $cate_path = $parent_import['cate_path'];
        } else {
//            if($category['entity_id'] == $category['parent_id']){
//                $parent_id = '0';
//                $cate_path = '1';
//            }else{
            $parent_id = $this->_notice['map']['categoryData'][$convert['parent']['id']];
            $cate_path = '1/' . $this->_notice['map']['categoryData'][$convert['parent']['id']];
//            }
        }
        $category_data  = array(
            'name' => $convert['name'],
            'status'        => $convert['active'],
            'parent_id'       => $parent_id,
            'level'            => isset($convert['level']) ? $convert['level']-1 : 0,
            'updated_at'        => $convert['updated_at']?$convert['updated_at']:date("Y-m-d h:i:s"),
            'created_at'        => $convert['created_at']?$convert['created_at']:date("Y-m-d h:i:s"),
            'description'        => $convert['description'],
            'url_key'        => $convert['url_key'],
            'position'         => isset($convert['category']['position']) ? $convert['category']['position'] : 0,
            'path'             => $cate_path,
            'product_count'   => isset($convert['category']['children_count']) ? $convert['category']['children_count'] : 0,
        );

        $category_id = $this->importCategoryData($this->createInsertQuery('category',$category_data));

        if (!$category_id) {
            // warning
            $response['result'] = 'warning';
            $response['msg']    = $this->warningImportEntity('Category', $convert['id'], $convert['code']);
            return $response;
//            return $this->errorConnector();
        }


        $category_update_query  = "UPDATE _DBPRF_catalog_category_entity SET `path` = '" . $cate_path . '/' . $category_id . "' WHERE `entity_id` = " . $category_id;
        $category_update_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'update',
                'query' => $category_update_query,
            )),
        ));
        if (!$category_update_import || $category_update_import['result'] != 'success') {
            //warning
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($category_update_query);
                var_dump(1);
                exit;
            }


        }

        $this->insertMap($url_src, $url_desc, self::TYPE_CATEGORY, $convert['id'], $category_id, $convert['code'], $cate_path . '/' . $category_id);
        $response = array(
            'result'    => 'success',
            'msg'       => '',
            'data'      => $category_id,
            'cate_path' => $cate_path . '/' . $category_id,
        );
//        if($parent_id == 0){
//            $response['rootCate'] = $convert['id'];
//        }
        return $response;

    }

    public function afterCategoryImport($category_id, $convert, $category, $categoriesExt)
    {


        $all_query = array();
        $url_query                      = $this->getConnectorUrl('query');
        $url_rewrite = $convert['url_rewrite'];
        foreach ($url_rewrite as $key => $rewrite) {
            $path     = $rewrite['request_path'];
            $store_id = $this->getMapStoreView($this->getValue($rewrite, 'store_id', 0));
            if (!$path) {
                continue;
            }
            $path              = $this->getRequestPath('category', $path, $store_id);
            $url_rewrite_data  = array(
                'parent_id'        => $category_id,
                'type'      => 'category',
                'url'     => $path,
                'controller'      => 'catalog/category/view/id/' . $category_id,
                'redirect_type'    => 0,
                'is_default' => 1,
            );
            $url_rewrite_query = $this->createInsertQuery('url_rewrite',$url_rewrite_data);
            if (Bootstrap::getConfig('dev_mode')) {
                $url_rewrite_import = $this->importCategoryData($url_rewrite_query);

                if (!$url_rewrite_import) {
                    //warning
                    Bootstrap::logQuery($url_rewrite_query);
                    var_dump(1);
                    exit;

                }

            } else {
                $all_query[] = $url_rewrite_query;
            }

        }

        //url_rewrite

        if (!Bootstrap::getConfig('dev_mode') && count($all_query)) {
            $this->importMultipleData($all_query,'category');
        }


        return array(
            'result' => "success",
            'msg'    => '',
            'data'   => array(),
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
            var_dump(1);
            exit;

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
            'catalog_product_relation'                             => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_relation WHERE child_id IN " . $product_id_con,
            ),
            'eav_attribute'                                        => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['src']['extends']['catalog_product']}"
            ),
            'catalog_product_website'                              => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_website WHERE product_id IN " . $product_id_con,
            ),
//            'tag_relation'                         => array(
//                'type'  => "select",
//                'query' => "SELECT * FROM _DBPRF_tag_relation WHERE product_id IN {$product_id_con}"
//            ),
            'eav_attribute_option_value'                           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option_value"
            ),
            'eav_attribute_option'                                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option"
            ),
            'catalog_product_super_link'                           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_super_link WHERE product_id IN " . $product_id_con,
            ),
            'catalog_product_link'                                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_link WHERE product_id IN " . $product_id_con . " OR linked_product_id IN" . $product_id_con,
            ),
            'catalog_product_link_grouped_product'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_link WHERE link_type_id = 3 and linked_product_id IN " . $product_id_con,
            ),
            'catalog_product_option'                               => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option WHERE product_id IN {$product_id_con}"
            ),
            'catalog_product_entity_media_gallery_value_to_entity' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery_value_to_entity WHERE entity_id IN {$product_id_con}"
            ),
            'core_url_rewrite' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_url_rewrite WHERE entity_type = 'product' AND entity_id IN {$product_id_con}"
            )
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

//        if ($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']) {
//            $product_ext_queries['core_url_rewrite'] = array(
//                'type'  => "select",
//                'query' => "SELECT * FROM _DBPRF_url_rewrite WHERE product_id IN {$product_id_con}"
//            );
//        }
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
//        $tagIds              = $this->duplicateFieldValueFromList($productsExt['data']['tag_relation'], 'tag_id');
//        $tag_id_query        = $this->arrayToInCondition($tagIds);
        $optionIds       = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_option'], 'option_id');
        $option_id_query = $this->arrayToInCondition($optionIds);
        $linkIds         = array();
        $linkIds         = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_link'], 'link_id');
        $link_id_query   = $this->arrayToInCondition($linkIds);

        $media_value_ids         = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_entity_media_gallery_value_to_entity'], 'value_id');
        $media_value_id_con      = $this->arrayToInCondition($media_value_ids);
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
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery WHERE value_id IN {$media_value_id_con}"
            ),
            'catalog_product_entity_tier_price'      => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_tier_price WHERE entity_id IN {$allproduct_id_query}"
            ),
            'catalog_category_product'               => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_category_product WHERE product_id IN {$allproduct_id_query}"
            ),
            'cataloginventory_stock_item'            => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_cataloginventory_stock_item WHERE product_id IN {$allproduct_id_query}"
            ),
            'catalog_product_bundle_parent'          => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_bundle_selection WHERE product_id IN " . $product_id_con,
            ),
            'catalog_product_option_title'           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option_title WHERE option_id IN {$option_id_query}",
            ),
            'catalog_product_option_price'           => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_option_price WHERE option_id IN {$option_id_query}"
            ),
            'catalog_product_option_type_value'      => array(
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

        if ($downloadable_sample_id && count($downloadable_link_id) > 0) {
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

        $option_type_ids    = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_option_type_value'], 'option_type_id');
        $option_type_id_con = $this->arrayToInCondition($option_type_ids);
//        $valueIds       = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_entity_media_gallery'], 'value_id');
//        $value_id_query = $this->arrayToInCondition($valueIds);
        $optionAttrIds  = $this->duplicateFieldValueFromList($productsExt['data']['catalog_product_entity_int'], 'value');
        $option_attr_id = $this->arrayToInCondition($optionAttrIds);
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
            'eav_attribute_option_value'                 => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_attr_id} OR option_id IN {$all_option_query}",
            ),
            'catalog_product_entity_media_gallery_value' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery_value WHERE value_id IN {$media_value_id_con}",
            ),
            'all_option'                                 => array(
                'type'  => "select",
                'query' => "SELECT a.option_id,a.attribute_id,b.value FROM _DBPRF_eav_attribute_option as a, _DBPRF_eav_attribute_option_value as b WHERE a.option_id = b.option_id and b.store_id = 0"
            ),
            'catalog_product_option_type_title'          => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalog_product_option_type_title WHERE option_type_id IN ' . $option_type_id_con,
            ),
            'catalog_product_option_type_price'          => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalog_product_option_type_price WHERE option_type_id IN ' . $option_type_id_con,
            ),
        );
        $productsExtRelRel           = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_ext_rel_rel_queries),
        ));
        if (!$productsExtRelRel || $productsExtRelRel['result'] != 'success') {
            var_dump(1);
            exit;

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

        $entity_decimal        = $this->getListFromListByField($productsExt['data']['catalog_product_entity_decimal'], 'entity_id', $product['entity_id']);
        $entity_int            = $this->getListFromListByField($productsExt['data']['catalog_product_entity_int'], 'entity_id', $product['entity_id']);
        $entity_text           = $this->getListFromListByField($productsExt['data']['catalog_product_entity_text'], 'entity_id', $product['entity_id']);
        $entity_varchar        = $this->getListFromListByField($productsExt['data']['catalog_product_entity_varchar'], 'entity_id', $product['entity_id']);
        $entity_datetime       = $this->getListFromListByField($productsExt['data']['catalog_product_entity_datetime'], 'entity_id', $product['entity_id']);
        $manage_stock_data     = $this->getListFromListByField($productsExt['data']['cataloginventory_stock_item'], 'product_id', $product['entity_id']);
        $eav_attribute         = array();
        $product_link_parent   = $this->getListFromListByField($productsExt['data']['catalog_product_link'], "product_id", $product['entity_id']);
        $product_link_children = $this->getListFromListByField($productsExt['data']['catalog_product_link'], "linked_product_id", $product['entity_id']);
        if (count($product_link_parent) > 0) {
            $product_data['product_link_parent'] = $product_link_parent;
        }
        if (count($product_link_children) > 0) {
            $product_data['product_link_children'] = $product_link_children;
        }
        foreach ($productsExt['data']['eav_attribute'] as $row) {
            $eav_attribute[$row['attribute_code']] = $row['attribute_id'];
        }


        $price                            = $this->getRowValueFromListByField($entity_decimal, 'attribute_id', $eav_attribute['price'], 'value');
        $weight                           = $this->getRowValueFromListByField($entity_decimal, 'attribute_id', $eav_attribute['weight'], 'value');
        $status                           = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['status'], 'value');
        $quantity                         = $this->getRowValueFromListByField($productsExt['data']['cataloginventory_stock_item'], 'product_id', $product['entity_id'], 'qty');
        $product_data['id']               = $product['entity_id'];
        $product_data['attribute_set_id'] = $product['attribute_set_id'];
        $product_data['type_id']          = $product['type_id'];
        $product_data['code']             = $product['sku'];

        $product_data['sku']              = $product['sku'];
        $product_data['price']            = $price ? $price : 0;
        $product_data['weight']           = $weight ? $weight : 0;
        $product_data['status']           = $status == 2 ? true : false;
        $product_data['qty']              = intval($quantity);
        if ($manage_stock_data && count($manage_stock_data) > 0) {
            $product_data['manage_stock_data'] = $manage_stock_data[0];
        }
        if ($quantity) {
            $product_data['manage_stock'] = true;
        }
        $product_data['created_at'] = $product['created_at'];
        $product_data['updated_at'] = $product['updated_at'];

        $names                             = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['name']);
        $name_def                          = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $descriptions                      = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['description']);
        $description_def                   = $this->getRowValueFromListByField($descriptions, 'store_id', 0, 'value');
        $short_descriptions                = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['short_description']);
        $short_description_def             = $this->getRowValueFromListByField($short_descriptions, 'store_id', 0, 'value');
        $meta_titles                       = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['meta_title']);
        $meta_title_def                    = $this->getRowValueFromListByField($meta_titles, 'store_id', 0, 'value');
        $meta_keywords                     = $this->getListFromListByField($entity_text, 'attribute_id', $eav_attribute['meta_keyword']);
        $meta_keyword_def                  = $this->getRowValueFromListByField($meta_keywords, 'store_id', 0, 'value');
        $meta_descriptions                 = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['meta_description']);
        $meta_description_def              = $this->getRowValueFromListByField($meta_descriptions, 'store_id', 0, 'value');
        $status                            = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['status']);
        $url_key                           = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['url_key']);
        $url_path                          = $this->getListFromListByField($entity_varchar, 'attribute_id', $eav_attribute['url_path']);
        $visibility                        = $this->getListFromListByField($entity_int, 'attribute_id', $eav_attribute['visibility']);
        $visibility_def                    = $this->getRowValueFromListByField($visibility, 'store_id', 0, 'value');
        $url_key_def                       = $this->getRowValueFromListByField($url_key, 'store_id', 0, 'value');
        $url_path_def                      = $this->getRowValueFromListByField($url_path, 'store_id', 0, 'value');
        $status_def                        = $this->getRowValueFromListByField($status, 'store_id', 0, 'value');
        $product_data['url_key']           = $url_key_def ? $url_key_def : '';
        $product_data['url_path']          = $url_path_def ? $url_path_def : '';
        $product_data['status']            = $status_def ? $status_def : 0;
        $product_data['visibility']        = $visibility_def;
        $product_data['name']              = $name_def ? $name_def : '';
        $product_data['description']       = $description_def ? $description_def : '';
        $product_data['short_description'] = $short_description_def ? $short_description_def : '';
        $product_data['meta_title']        = $meta_title_def ? $meta_title_def : '';
        $product_data['meta_keyword']      = $meta_keyword_def ? $meta_keyword_def : '';
        $product_data['meta_description']  = $meta_description_def ? $meta_description_def : '';

        $image             = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['image'], 'value');
        $image_label       = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['image_label'], 'value');
        $url_product_image = $this->getUrlSuffix($this->_notice['src']['config']['image_product']);

        $product_data['image']['url']   = $url_product_image;
        $product_data['image']['path']  = $image;
        $product_data['image']['label'] = $image_label;
        $product_media_ids              = $this->getListFromListByField($productsExt['data']['catalog_product_entity_media_gallery_value_to_entity'], 'entity_id', $product['entity_id']);
        $productImage                   = $this->getListFromListByField($productsExt['data']['catalog_product_entity_media_gallery'], 'value_id', $product_media_ids);
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
                $tier_price_data                      = $this->constructProductTierPrice();
                $tier_price_data                      = $this->addConstructDefault($tier_price_data);
                $tier_price_data['id']                = $tier_price['value_id'];
                $tier_price_data['qty']               = $tier_price['qty'];
                $tier_price_data['price']             = $tier_price['value'];
                $tier_price_data['customer_group_id'] = $tier_price['customer_group_id'];
                $tier_price_data['all_groups']        = $tier_price['all_groups'];
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

        $language = $this->_notice['src']['languages'];
        foreach ($language as $lang_id => $lang_name) {
            $product_language_data                      = $this->constructProductLang();
            $product_language_data['name']              = $this->getRowValueFromListByField($names, 'store_id', $lang_id, 'value');
            $product_language_data['description']       = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['short_description'] = $this->getRowValueFromListByField($short_descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['meta_title']        = $this->getRowValueFromListByField($meta_titles, 'store_id', $lang_id, 'value');
            $product_language_data['meta_keyword']      = $this->getRowValueFromListByField($meta_keywords, 'store_id', $lang_id, 'value');
            $product_language_data['meta_description']  = $this->getRowValueFromListByField($meta_descriptions, 'store_id', $lang_id, 'value');
            $product_language_data['status']            = $this->getRowValueFromListByField($status, 'store_id', $lang_id, 'value');
            $product_language_data['url_key']           = $this->getRowValueFromListByField($url_key, 'store_id', $lang_id, 'value');
            $product_language_data['url_path']          = $this->getRowValueFromListByField($url_path, 'store_id', $lang_id, 'value');

            $product_data['languages'][$lang_id] = $product_language_data;
        }


        /**
         * get custom option product
         */
        $productOption = $this->getListFromListByField($productsExt['data']['catalog_product_option'], 'product_id', $product['entity_id']);

        if ($productOption) {
            foreach ($productOption as $product_option) {

                $option_title                         = $this->getListFromListByField($productsExt['data']['catalog_product_option_title'], 'option_id', $product_option['option_id']);
                $option_price                         = $this->getListFromListByField($productsExt['data']['catalog_product_option_price'], 'option_id', $product_option['option_id']);
                $productOptionTypeValue               = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_value'], 'option_id', $product_option['option_id']);
                $option                               = array();
                $option_data                          = array();
                $option_data['id']                    = $product_option['option_id'];
                $option_data['option_type']           = $product_option['type'];
                $option_data['option_is_require']     = $product_option['is_require'];
                $option_data['option_sku']            = $product_option['sku'];
                $option_data['option_max_characters'] = $product_option['max_characters'];
                $option_data['option_file_extension'] = $product_option['file_extension'];
                $option_data['option_image_size_x']   = $product_option['image_size_x'];
                $option_data['option_image_size_y']   = $product_option['image_size_y'];
                $option['value']                      = $option_data;
                if ($option_title && count($option_title) > 0) {
                    $option['title'] = $option_title;
                }
                if ($option_price && count($option_price) > 0) {
                    $option['price'] = $option_price;
                }

                $optionTypeIds = $this->duplicateFieldValueFromList($productOptionTypeValue, 'option_type_id');
                if ($productOptionTypeValue && count($productOptionTypeValue) > 0) {
                    $type = array();
                    foreach ($productOptionTypeValue as $typeValue) {
                        $temp_type['value'] = $typeValue;
                        $option_type_title  = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_title'], 'option_type_id', $typeValue['option_type_id']);
                        $option_type_price  = $this->getListFromListByField($productsExt['data']['catalog_product_option_type_price'], 'option_type_id', $typeValue['option_type_id']);
                        $temp_type['title'] = $option_type_title;
                        $temp_type['price'] = $option_type_price;
                        $type[]             = $temp_type;
                    }
                    $option['type'] = $type;
                }

                $product_data['options'][] = $option;
            }
        }


        if ($product['type_id'] == 'simple' || $product['type_id'] == 'virtual') {


            //configurable
            $configurable_parent = $this->getListFromListByField($productsExt['data']['catalog_product_super_link'], 'product_id', $product['entity_id']);
            if (is_array($configurable_parent)) {
                if ($configurable_parent && (count($configurable_parent) > 0)) {
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
            $product_data['price_type']    = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['price_type'], 'value');
            $product_data['weight_type']   = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['weight_type'], 'value');
            $product_data['sku_type']      = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['sku_type'], 'value');
            $product_data['bundle_option'] = array();
            $bundle_option                 = $this->getListFromListByListField($productsExt['data']['catalog_product_bundle_option'], "parent_id", $product['entity_id']);
            if ($bundle_option && count($bundle_option) > 0) {

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

        /**
         *  Get parent product grouped
         */
        if ($product['type_id'] == 'grouped') {
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
                $product_data['downloadable']['link']                       = $downloadable_link;
                $product_data['downloadable']['links_title']                = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['links_title'], 'value');
                $product_data['downloadable']['links_purchased_separately'] = $this->getRowValueFromListByField($entity_int, 'attribute_id', $eav_attribute['links_purchased_separately'], 'value');
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
                $product_data['downloadable']['samples']       = $downloadable_sample;
                $product_data['downloadable']['samples_title'] = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute['samples_title'], 'value');

            }
        }

        //tags
//        $tag_relation = $this->getListFromListByField($productsExt['data']['tag_relation'], 'product_id', $product['entity_id']);
//        if ($tag_relation) {
//            $tags = array();
//            foreach ($tag_relation as $product_tag) {
//                $tag    = $this->getRowFromListByField($productsExt['data']['tag'], 'tag_id', $product_tag['tag_id']);
//                $tags[] = $tag['name'];
//            }
//            $product_data['tags'] = implode(',', $tags);
//        }

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
            $attribute_data['default_value']   = $row['default_value'];
            $data                              = null;
            $data_array                        = 'entity_' . $row['backend_type'];
            $data                              = $this->getRowValueFromListByField($$data_array, 'attribute_id', $row['attribute_id'], 'value');
//            if($row['frontend_label'] == 'Lieferzeit'){
//                print_r($row);exit;
//            }
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


        $product_data['add_data']        = $attribute_remain;
        $product_website                 = $this->getListFromListByField($productsExt['data']['catalog_product_website'], 'product_id', $product['entity_id']);
        $product_data['product_website'] = $this->duplicateFieldValueFromList($product_website, 'website_id');
        $url_rewrite                 = $this->getListFromListByField($productsExt['data']['core_url_rewrite'], 'entity_id', $product['entity_id']);
        $product_data['url_rewrite'] = array();

        foreach ($url_rewrite as $rewrite) {
            $rewrite_data                  = array();
            $rewrite_data['store_id']      = $rewrite['store_id'];
            $target_path = $rewrite['target_path'];
            $is_category = preg_match("/catalog\/product\/view\/id\/".$product['entity_id']."\/category\/(.*)/",$target_path,$match);
            if($is_category){
                $rewrite_data['category_id']   = $match[1];
            }
            $rewrite_data['request_path']   = $rewrite['request_path'];
            $rewrite_data['description']   = $rewrite['description'];
            $product_data['url_rewrite'][] = $rewrite_data;
        }
        //url_rewrite multi store
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
//        if($this->_notice['config']['add_new'] && $this->_notice['config']['seo']){
//            $this->updateSeoPrd($product,$productsExt);
//        }
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

    protected function _importProductParent($parent)
    {
        $response      = $this->_defaultResponse();
        $url_src       = $this->_notice['src']['cart_url'];
        $url_desc      = $this->_notice['target']['cart_url'];
        $parent_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $parent['id'], null, $parent['code']);
        if ($parent_exists) {
            $response['result'] = 'success';
            $response['data']   = $parent_exists['id_desc'];

            return $response;
        }
        $parent_import = $this->productImport($parent, null, null);
        if ($parent_import['result'] != 'success') {
            return $parent_import;
        }
        $parent_id = $parent_import['data'];
        if (!$parent_id) {
            $response['result'] = 'warning';

            return $response;
        }
        $this->afterProductImport($parent_id, $parent, null, null);

        return $parent_import;
    }

    // protected function _importProductChild($child)
    // {
    //     $response = $this->_defaultResponse();
    //     $url_src = $this->_notice['src']['cart_url'];
    //     $url_desc = $this->_notice['target']['cart_url'];
    //     $child_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $child['id'], null, $child['code']);
    //     if($child_exists){
    //         $response['result'] = 'success';
    //         $response['data'] = $child_exists['id_desc'];
    //         // $response['cate_path'] = $child_exists['code_desc'];
    //         return $response;
    //     }
    //     // $category = $child['category'];
    //     // $categoriesExt = $child['categoriesExt'];
    //     $child_import = $this->productImport($child, null, null);
    //     if($child_import['result'] != 'success'){
    //         return $child_import;
    //     }
    //     $child_id = $child_import['data'];
    //     if(!$child_id){
    //         $response['result'] = 'warning';
    //         return $response;
    //     }
    //     $this->afterProductImport($child_id, $child, null, null);
    //     return $child_import;
    // }

//    protected function _importProductConfigurableParent($parent)
//    {
//        $response      = $this->_defaultResponse();
//        $url_src       = $this->_notice['src']['cart_url'];
//        $url_desc      = $this->_notice['target']['cart_url'];
//        $parent_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $parent['id'], null, $parent['code']);
//        if ($parent_exists) {
//            $response['result'] = 'success';
//            $response['data']   = $parent_exists['id_desc'];
//
//            return $response;
//        }
//        $parent_import = $this->productImport($parent, null, null);
//        if ($parent_import['result'] != 'success') {
//            return $parent_import;
//        }
//        $parent_id = $parent_import['data'];
//        if (!$parent_id) {
//            $response['result'] = 'warning';
//
//            return $response;
//        }
//        $this->afterProductImport($parent_id, $parent, null, null);
//
//        return $parent_import;
//    }
//
//    protected function _importProductGroupedParent($parent)
//    {
//        $response      = $this->_defaultResponse();
//        $url_src       = $this->_notice['src']['cart_url'];
//        $url_desc      = $this->_notice['target']['cart_url'];
//        $parent_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $parent['id'], null, $parent['code']);
//        if ($parent_exists) {
//            $response['result'] = 'success';
//            $response['data']   = $parent_exists['id_desc'];
//
//            return $response;
//        }
//        $parent_import = $this->productImport($parent, null, null);
//        if ($parent_import['result'] != 'success') {
//            return $parent_import;
//        }
//        $parent_id = $parent_import['data'];
//        if (!$parent_id) {
//            $response['result'] = 'warning';
//
//            return $response;
//        }
//        $this->afterProductImport($parent_id, $parent, null, null);
//
//        return $parent_import;
//    }

    public function productImport($convert, $product, $productsExt)
    {

        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];


        $catalog_product_entity_data = array(
            'name'          => $convert['name'],
            'price'          => $convert['price'],
            'sku'              => $convert['sku'],
            'status'      => $convert['status'],
            'created_at'       => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
            'updated_at'       => $this->getValue($convert, 'updated_at', date("Y-m-d h:i:s")),
            'description'              => $convert['description'],
            'url_key'              => $convert['url_key'],
            'attribute_set' => $this->_notice['map']['attributes'][$convert['attribute_set_id']],
        );

        if ($this->_notice['config']['real_pre_prd']) {
            $catalog_product_entity_data['entity_id'] = $convert['id'];
        }
        $catalog_product_entity_query = $this->createInsertQuery('product',$catalog_product_entity_data);
        $product_id = $this->importProductData($catalog_product_entity_query);
        if (!$product_id) {
            // warning
            $response['result'] = 'warning';
            $response['msg']    = $this->warningImportEntity('Product', $convert['id'], $convert['code']);
return $response;
            return $this->errorConnector();
        }

        $this->insertMap($url_src, $url_desc, self::TYPE_PRODUCT, $convert['id'], $product_id, $convert['code'], null);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $product_id,
        );
    }

    public function afterProductImport($product_id, $convert, $product, $productsExt)
    {
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');
        $all_query = array();
        //image_begin
        $product_eav_attribute_queries = array(
            'eav_attribute' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_product_attribute WHERE entity_type_id = 4"
            ),
        );

        $product_eav_attribute       = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_eav_attribute_queries)
        ));
        $product_eav_attribute_array = array();
        $attribute_id_media          = null;
        foreach ($product_eav_attribute['data']['eav_attribute'] as $attribute) {
            if ($attribute['backend_type'] != 'static') {
                $product_eav_attribute_array[$attribute['attribute_code']]['attribute_id'] = $attribute['attribute_id'];
                $product_eav_attribute_array[$attribute['attribute_code']]['backend_type'] = $attribute['backend_type'];
            }
//            if ($attribute['attribute_code'] == 'media_gallery') {
//                $attribute_id_media = $attribute['attribute_id'];
//            }
        }
        $url_image = $this->getConnectorUrl('image');
        $image_name = null;

//        $eav_attribute_group = $this->getValue($convert, 'eav_attribute_group', array());
        if (isset($convert['images'])) {
            foreach ($convert['images'] as $key => $item) {
                if ($item['url'] && $item['path']) {
                    $item_image_name = null;
                    if (Bootstrap::getConfig('migrate_image')) {
                        $image_process = $this->processImageBeforeImport($item['url'], $item['path']);
                        $image_import  = $this->getConnectorData($url_image, array(
                            'images' => serialize(array(
                                'ci' => array(
                                    'type'   => 'download',
                                    'path'   => $this->addPrefixPath($this->makeMagentoImagePath($image_process['path']) . basename($image_process['path']), $this->_notice['target']['config']['image_product']),
                                    'params' => array(
                                        'url'    => $image_process['url'],
                                        'rename' => true,
                                    ),
                                ),
                            ))
                        ));
                        if ($image_import && $image_import['result'] == 'success') {
                            $image_import_path = $image_import['data']['ci'];
                            if ($image_import_path) {
                                // $categories_image = $this->removePrefixPath($image_import_path, $this->_notice['target']['config']['image_category']);
                                $item_image_name = $this->makeMagentoImagePath($item['path']) . basename($item['path']);
                            }
                        }
                    } else {
                        $item_image_name = $item['path'];
                    }
                    if ($item_image_name) {
                        if(isset($convert['image']['url']) && $convert['image']['path'] == $item['path']){
                            $image_name = $item_image_name;
                        }
                        $catalog_product_entity_media_gallery_data = array(
                            'value'        => $item_image_name,
                        );
                        $value_id = $this->importProductData($this->createInsertQuery('media_gallery',$catalog_product_entity_media_gallery_data));
                        if($value_id){

                            $catalog_product_entity_media_gallery_value_to_entity_data = array(
                                'product_id'  => $product_id,
                                'media_id' => $value_id,
                                'status' => 1,
                                'is_thumbnail' => 0,
                            );
                            if($item['path'] == $convert['image']['path']){
                                $catalog_product_entity_media_gallery_value_to_entity_data['is_thumbnail'] = 1;
                            }
                            $catalog_product_entity_media_gallery_value_to_entity_query = $this->createInsertQuery('product_media',$catalog_product_entity_media_gallery_value_to_entity_data);
                            if (Bootstrap::getConfig('dev_mode')) {
                                $catalog_product_entity_media_gallery_value_to_entity_import = $this->importProductData($catalog_product_entity_media_gallery_value_to_entity_query);
                                if (!$catalog_product_entity_media_gallery_value_to_entity_import) {
                                    //warning
                                    Bootstrap::logQuery($catalog_product_entity_media_gallery_value_to_entity_query);
                                    var_dump(1);
                                    exit;
                                }

                            } else {
                                $all_query[] = $catalog_product_entity_media_gallery_value_to_entity_query;
                            }


                        }

                    }

                }
            }
        }




        /**
         * End Namlv
         */
        // main image end
        //image_end
//=================================================================
        //link category and product begin

        foreach ($convert['categories'] as $key => $value) {
            $category_id = null;
            if (isset($this->_notice['map']['categoryData'][$value['id']])) {
                $category_id = $this->_notice['map']['categoryData'][$value['id']];
            } else {
                $category_id = $this->getMapFieldBySource(self::TYPE_CATEGORY, $value['id']);
                if (!$category_id) {
                    continue;
                }
            }


            $catalog_category_product_data = array(
                'product_id'  => $product_id,
                'category_id' => $category_id,
            );

            $catalog_category_product_query = $this->createInsertQuery('product_category',$catalog_category_product_data);


            if (Bootstrap::getConfig('dev_mode')) {
                $catalog_category_product_import = $this->importProductData($catalog_category_product_query);

                if (!$catalog_category_product_import) {
                    Bootstrap::logQuery($catalog_category_product_query);
                    var_dump(1);
                    exit;
                    //warning
                }

            } else {
                $all_query[] = $catalog_category_product_query;
            }



        }

        //link category and product end
//=================================================================


        //cataloginventory_stock (quality) end
//=================================================================
        //tier_price begin



        //product_website end
//=================================================================
        //insert custom option simple product begin
//
//        var_dump($convert['children']);exit;
//        if (!isset($convert['children'])  ) {
        if (isset($convert['options'])) {
            foreach ($convert['options'] as $key => $item) {
                //catalog_product_option begin
                $option_value = $this->getValue($item, 'value');
                if(!$option_value || $option_value['type'] != 'drop_down'){
                    continue;
                }
                if ($option_value) {
                    $catalog_product_option_data = array(
                        'product_id'     => $product_id,
                        'is_require'     => $this->getValue($option_value, 'option_is_require', 1),
                        'type'           => 'drop_down',
                        'title'          => $item['title'][0]['title'],
                    );

                    $catalog_product_option_query = $this->createInsertQuery('product_option',$catalog_product_option_data);



                    $catalog_product_option_id = $this->importProductData($catalog_product_option_query);
                    if ($catalog_product_option_id) {
                        $option_type  = $this->getValue($item, 'type');
                        if ($option_type) {
                            $flag = false;
                            foreach ($option_type as $key_type => $type) {
                                //catalog_product_option_type_value begin
                                $type_value = $this->getValue($type, 'value');
                                if ($type_value) {
                                    $catalog_product_option_type_value_data = array(
                                        'option_id'  => $catalog_product_option_id,
                                        'is_default'        => !$flag?1:0,
                                        'title' => $type['title'][0]['title'],
                                        'price' => $type['price'][0]['price'],
                                        'price_type' => $type['price'][0]['price'],
                                    );
                                    $flag = true;
                                    $catalog_product_option_type_value_query = $this->createInsertQuery('product_option_value',$catalog_product_option_type_value_data);



                                    $catalog_product_option_type_value_id = $this->importProductData($catalog_product_option_type_value_query);



                                }


                                /**
                                 * End Namlv
                                 */
                                //catalog_product_option_type_value end


                            }
                        }
                    }


                }

                //catalog_product_option end


            }
        }

//        }
//        elseif (count($convert['children']) < 1) {
//            foreach ($convert['options'] as $key => $item) {
//                //catalog_product_option begin
//                $catalog_product_option_data = array(
//                    'product_id'     => $product_id,
//                    'type'           => 'drop_down',
//                    'is_require'     => 1,
//                    'sku'            => null,
//                    'max_characters' => null,
//                    'file_extension' => null,
//                    'image_size_x'   => null,
//                    'image_size_y'   => null,
//                    'sort_order'     => 0,
//                );
//
//                $catalog_product_option_query  = "INSERT INTO _DBPRF_catalog_product_option ";
//                $catalog_product_option_query  .= $this->arrayToInsertCondition($catalog_product_option_data);
//                $catalog_product_option_import = $this->getConnectorData($url_query, array(
//                    'query' => serialize(array(
//                        'type'   => 'insert',
//                        'query'  => $catalog_product_option_query,
//                        'params' => array(
//                            'insert_id' => true,
//                        )
//                    )),
//                ));
//
//                if (!$catalog_product_option_import) {
//                    //warning
//                    if (Bootstrap::getConfig('dev_mode')) {
//                        Bootstrap::logQuery($catalog_product_option_query;
//                        var_dump(1);
//                        exit;
//                    }
//
//                    return $this->errorConnector();
//                }
//
//                if ($catalog_product_option_import['result'] != 'success') {
//                    //warning
//                    return $this->warningSQL($catalog_product_option_import);
//                }
//
//                $catalog_product_option_id = $catalog_product_option_import['data'];
//                if (!$catalog_product_option_id) {
//                    // warning
//                    return $this->errorConnector();
//                }
//                //catalog_product_option end
//
//                //catalog_product_option_title begin
//                $catalog_product_option_title_data = array(
//                    'option_id' => $catalog_product_option_id,
//                    'store_id'  => 0,
//                    'title'     => $item['option_name'],
//                );
//
//                $catalog_product_option_title_query = "INSERT INTO _DBPRF_catalog_product_option_title ";
//                $catalog_product_option_title_query .= $this->arrayToInsertCondition($catalog_product_option_title_data);
//                /**
//                 * Start Namlv
//                 */
//
//                if (Bootstrap::getConfig('dev_mode')) {
//                    $catalog_product_option_title_import = $this->getConnectorData($url_query, array(
//                        'query' => serialize(array(
//                            'type'   => 'insert',
//                            'query'  => $catalog_product_option_title_query,
//                            'params' => array(
//                                'insert_id' => true,
//                            )
//                        )),
//                    ));
//
//                    if (!$catalog_product_option_title_import) {
//                        //warning
//                        Bootstrap::logQuery($catalog_product_option_title_query;
//                        var_dump(1);
//                        exit;
//                    }
//
//                    if ($catalog_product_option_title_import['result'] != 'success') {
//                        //warning
//                        return $this->warningSQL($catalog_product_option_title_import);
//                    }
//                } else {
//                    $all_query['catalog_product_option_title_query_' . $key] = array(
//                        'type'   => 'insert',
//                        'query'  => $catalog_product_option_title_query,
//                        'params' => array(
//                            'insert_id' => true,
//                        )
//                    );
//                }
//
//                //catalog_product_option_title end
//                /**
//                 * End Namlv
//                 */
//                foreach ($item['values'] as $key_item => $item1) {
//                    //catalog_product_option_type_value begin
//                    $catalog_product_option_type_value_data = array(
//                        'option_id'  => $catalog_product_option_id,
//                        'sku'        => null,
//                        'sort_order' => 0,
//                    );
//
//                    $catalog_product_option_type_value_query  = "INSERT INTO _DBPRF_catalog_product_option_type_value ";
//                    $catalog_product_option_type_value_query  .= $this->arrayToInsertCondition($catalog_product_option_type_value_data);
//                    $catalog_product_option_type_value_import = $this->getConnectorData($url_query, array(
//                        'query' => serialize(array(
//                            'type'   => 'insert',
//                            'query'  => $catalog_product_option_type_value_query,
//                            'params' => array(
//                                'insert_id' => true,
//                            )
//                        )),
//                    ));
//
//                    if (!$catalog_product_option_type_value_import) {
//                        //warning
//                        if (Bootstrap::getConfig('dev_mode')) {
//                            Bootstrap::logQuery($catalog_product_option_type_value_query;
//                            var_dump(1);
//                            exit;
//                        }
//
//                        return $this->errorConnector();
//                    }
//
//                    if ($catalog_product_option_type_value_import['result'] != 'success') {
//                        //warning
//                        return $this->warningSQL($catalog_product_option_type_value_import);
//                    }
//
//                    $catalog_product_option_type_value_id = $catalog_product_option_type_value_import['data'];
//                    if (!$catalog_product_option_type_value_id) {
//                        // warning
//                        return $this->errorConnector();
//                    }
//                    //catalog_product_option_type_value end
//
//                    //catalog_product_option_type_price begin
//                    $catalog_product_option_type_price_data = array(
//                        'option_type_id' => $catalog_product_option_type_value_id,
//                        'store_id'       => 0,
//                        'price'          => $item1['price'],
//                        'price_type'     => 'fixed',
//                    );
//
//                    $catalog_product_option_type_price_query  = "INSERT INTO _DBPRF_catalog_product_option_type_price ";
//                    $catalog_product_option_type_price_query  .= $this->arrayToInsertCondition($catalog_product_option_type_price_data);
//                    $catalog_product_option_type_price_import = $this->getConnectorData($url_query, array(
//                        'query' => serialize(array(
//                            'type'   => 'insert',
//                            'query'  => $catalog_product_option_type_price_query,
//                            'params' => array(
//                                'insert_id' => true,
//                            )
//                        )),
//                    ));
//
//                    if (!$catalog_product_option_type_price_import) {
//                        //warning
//                        if (Bootstrap::getConfig('dev_mode')) {
//                            Bootstrap::logQuery($catalog_product_option_type_price_query;
//                            var_dump(1);
//                            exit;
//                        }
//
//                        return $this->errorConnector();
//                    }
//
//                    if ($catalog_product_option_type_price_import['result'] != 'success') {
//                        //warning
//                        return $this->warningSQL($catalog_product_option_type_price_import);
//                    }
//
//                    $catalog_product_option_type_price_id = $catalog_product_option_type_price_import['data'];
//                    if (!$catalog_product_option_type_price_id) {
//                        // warning
//                        return $this->errorConnector();
//                    }
//                    //catalog_product_option_type_price end
//
//                    //catalog_product_option_type_title begin
//                    $catalog_product_option_type_title_data = array(
//                        'option_type_id' => $catalog_product_option_type_value_id,
//                        'store_id'       => 0,
//                        'title'          => $item1['option_value_name'],
//                    );
//
//                    $catalog_product_option_type_title_query  = "INSERT INTO _DBPRF_catalog_product_option_type_title ";
//                    $catalog_product_option_type_title_query  .= $this->arrayToInsertCondition($catalog_product_option_type_title_data);
//                    $catalog_product_option_type_title_import = $this->getConnectorData($url_query, array(
//                        'query' => serialize(array(
//                            'type'   => 'insert',
//                            'query'  => $catalog_product_option_type_title_query,
//                            'params' => array(
//                                'insert_id' => true,
//                            )
//                        )),
//                    ));
//
//                    if (!$catalog_product_option_type_title_import) {
//                        //warning
//                        if (Bootstrap::getConfig('dev_mode')) {
//                            Bootstrap::logQuery($catalog_product_option_type_title_query;
//                            var_dump(1);
//                            exit;
//                        }
//
//                        return $this->errorConnector();
//                    }
//
//                    if ($catalog_product_option_type_title_import['result'] != 'success') {
//                        //warning
//                        return $this->warningSQL($catalog_product_option_type_title_import);
//                    }
//
//                    $catalog_product_option_type_title_id = $catalog_product_option_type_title_import['data'];
//                    if (!$catalog_product_option_type_title_id) {
//                        // warning
//                        return $this->errorConnector();
//                    }
//                    //catalog_product_option_type_title end
//                }
//
//            }
//        }
        //insert custom option simple product end
//=================================================================
        //insert product attribute begin
        $insert_attribute_array = array(
            'sale_price'             => $convert['special_price']['price'],
            'sale_from'         => $convert['special_price']['start_date'],
            'sale_to'           => $convert['special_price']['end_date'],
            'weight'                    => $convert['weight'],
            'sale_type'     => 1,
        );
        foreach ($product_eav_attribute_array as $key1 => $value1) {
            foreach ($insert_attribute_array as $key2 => $value2) {
                if (($key2 == $key1)) {

                   if (!$value2) {
                        continue;
                    }

                    $product_attr_data = array(
                        'attribute_id' => $value1['attribute_id'],
                        'product_id'    => $product_id,
                        'value'        => $value2,
                    );
                    $product_attr_query = $this->createInsertQuery("product_" . $value1['type'],$product_attr_data);

                    if (Bootstrap::getConfig('dev_mode')) {
                        $product_attr_import = $this->importProductData($product_attr_query);

                        if (!$product_attr_import) {
                            //warning
                            Bootstrap::logQuery($product_attr_query);
                            var_dump(1);
                            exit;
                        }

                    } else {
                        $all_query[] = $product_attr_query;
                    }

                }

            }
        }



        //insert attribute product end
        //configurable product end

//=================================================================
        //downloadable product begin
        if (isset($convert['downloadable'])) {
            if (isset($convert['downloadable']['link'])) {
                foreach ($convert['downloadable']['link'] as $key => $downloadable_link) {
                    $link  = $downloadable_link['link'];
                    $title = $downloadable_link['title'];
                    $price = $downloadable_link['price'];

                    $downloadable_link_data   = array(
                        'product_id'          => $product_id,
                        'sort_order'          => 0,
                        'number_of_downloads' => $this->getValue($link, 'number_of_downloads', 0),
                        'is_shareable'        => $this->getValue($link, 'is_shareable', null),
                        'link_url'            => $this->getValue($link, 'link_url', null),
                        'link_file'           => $this->getValue($link, 'link_file', null),
                        'link_type'           => $this->getValue($link, 'link_type', null),
                        'sample_url'          => $this->getValue($link, 'sample_url', null),
                        'sample_file'         => $this->getValue($link, 'sample_file', null),
                        'sample_type'         => $this->getValue($link, 'sample_type', null),
                    );
                    $downloadable_link_query  = $this->createInsertQuery('downloadable_link',$downloadable_link_data);
                    $downloadable_link_id = $this->importProductData($downloadable_link_query);

                    if($downloadable_link_id){

                        $downloadable_link_title_data  = array(
                            'link_id'  => $downloadable_link_id,
                            'store_id' => 0,
                            'title'    => $this->getValue($title, 'title', null),

                        );
                        $downloadable_link_title_query = $this->createInsertQuery('downloadable_link_title',$downloadable_link_title_data);


                        if (Bootstrap::getConfig('dev_mode')) {
                            $downloadable_link_title_import = $this->importProductData($downloadable_link_title_query);
                            if (!$downloadable_link_title_import) {
                                Bootstrap::logQuery($downloadable_link_title_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $downloadable_link_title_query;
                        }

                        $downloadable_link_price_data  = array(
                            'link_id'    => $downloadable_link_id,
                            'website_id' => 0,
                            'price'      => $this->getValue($price, 'price', null),

                        );
                        $downloadable_link_price_query = $this->createInsertQuery('downloadable_link_price',$downloadable_link_price_data);


                        if (Bootstrap::getConfig('dev_mode')) {
                            $downloadable_link_price_import = $this->importProductData($downloadable_link_price_query);
                            if (!$downloadable_link_price_import) {
                                Bootstrap::logQuery($downloadable_link_price_query);
                                exit;
                            }
                        } else {
                            $all_query[] = $downloadable_link_price_query;
                        }
                    }


                }
            }

            if (isset($convert['downloadable']['samples'])) {
                foreach ($convert['downloadable']['samples'] as $key => $downloadable_sample) {
                    $sample                     = $downloadable_sample['sample'];
                    $title                      = $this->getValue($downloadable_sample, 'title');
                    $downloadable_sample_data   = array(
                        'product_id'  => $product_id,
                        'sample_url'  => $this->getValue($sample, 'sample_url'),
                        'sample_file' => $this->getValue($sample, 'sample_file'),
                        'sample_type' => $this->getValue($sample, "sample_type"),
                        'sort_order'  => $this->getValue($sample, "sort_order", 0),
                    );
                    $downloadable_sample_query  = $this->createInsertQuery('downloadable_sample',$downloadable_sample_data);
                    $sample_id = $this->importProductData($downloadable_sample_query);
                    if (!$sample_id) {
                        if (Bootstrap::getConfig('dev_mode')) {
                            Bootstrap::logQuery($downloadable_sample_query);
                            var_dump(1);
                            exit;
                        }
                    }else{
                        $downloadable_sample_title_data  = array(
                            'sample_id' => $sample_id,
                            'store_id'  => $this->getMapStoreView($title['store_id']),
                            'title'     => $this->getValue($title, 'title'),
                        );
                        $downloadable_sample_title_query = $this->createInsertQuery('downloadable_sample_title',$downloadable_sample_title_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $downloadable_sample_title_import = $this->importProductData($downloadable_sample_title_query);
                            if (!$downloadable_sample_title_import) {
                                Bootstrap::logQuery($downloadable_sample_title_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $downloadable_sample_title_query;
                        }
                    }


                }
            }

        }


        //downloadable product end

//=================================================================
        $product_eav_attribute_queries = array(
            'eav_attribute' => array(
                'type'  => "select",
                'query' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = 4"
            ),
        );

        $product_eav_attribute = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($product_eav_attribute_queries)
        ));

        $product_eav_attribute_array = array();
        foreach ($product_eav_attribute['data']['eav_attribute'] as $attribute) {
            if ($attribute['backend_type'] != 'static') {
                $product_eav_attribute_array[$attribute['attribute_code']]['attribute_id'] = $attribute['attribute_id'];
                $product_eav_attribute_array[$attribute['attribute_code']]['backend_type'] = $attribute['backend_type'];
            }
        }

//=================================================================
        //grouped product begin
        $grouped_product_parent_id = null;
        //link grouped product and simple product begin
        if (isset($convert['parent_grouped']) && isset($convert['parent_grouped'][0]['id'])) {
            foreach ($convert['parent_grouped'] as $key => $item) {
                $grouped_product_parent_import = $this->_importProductParent($item);
                if ($grouped_product_parent_import['result'] != 'success') {
                    continue;
                }
                $grouped_product_parent_id = $grouped_product_parent_import['data'];

                if ($grouped_product_parent_id) {
                    // link grouped product and simple product begin
                    //catalog_product_relation begin
                    $catalog_product_relation_data = array(
                        'parent_id' => $grouped_product_parent_id,
                        'child_id'  => $product_id,
                    );

                    $catalog_product_relation_query = $this->createInsertQuery('catalog_product_relation',$catalog_product_relation_data);

                    if (Bootstrap::getConfig('dev_mode')) {
                        $catalog_product_relation_import = $this->importProductData($catalog_product_relation_query);

                        if (!$catalog_product_relation_import) {
                            //warning
                            Bootstrap::logQuery($catalog_product_relation_query);
                            var_dump(1);
                            exit;
                        }
                    } else {
                        $all_query[] = $catalog_product_relation_query;
                    }

                    //catalog_product_relation end

                    //catalog_product_link begin
                    $catalog_product_link_data = array(
                        'product_id'        => $grouped_product_parent_id,
                        'linked_product_id' => $product_id,
                        'link_type_id'      => 3,
                    );

                    $catalog_product_link_query = $this->createInsertQuery('catalog_product_link',$catalog_product_link_data);

                    if (Bootstrap::getConfig('dev_mode')) {
                        $catalog_product_link_import = $this->importProductData($catalog_product_link_query);

//                        if (!$catalog_product_link_import) {
//                            //warning
//                            Bootstrap::logQuery($catalog_product_link_query);
//                            var_dump(1);
//                            exit;
//                        }

                    } else {
                        $all_query[] = $catalog_product_link_query;
                    }

                }
            }
        }
        //link grouped product and simple product end

        //grouped product end

//        ===============================================================
        //bundle product finish
//        ===============================================================
        /**
         * Start Namlv
         */


//=================================================================|||
        //product relate,up-sell,cross-sell begin
        if (isset($convert['product_link_parent'])) {
            foreach ($convert['product_link_parent'] as $key => $value) {
                $children = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $value['linked_product_id'], null, null);
                if ($children && isset($children['id_desc']) && $children['id_desc'] > 0) {
                    $children_id                        = $children['id_desc'];
                    $catalog_product_link_relate_data   = array(
                        "product_id"        => $product_id,
                        "parent_id" => $children_id,
                    );
                    $catalog_product_link_relate_query  = $this->createInsertQuery('product_relation',$catalog_product_link_relate_data);
                    if(Bootstrap::getConfig('dev_mode')){
                        $catalog_product_link_relate_import = $this->importProductData($catalog_product_link_relate_query);
                    }else{
                        $all_query[] = $catalog_product_link_relate_query;
                    }

                }
            }
        }

        if (isset($convert['product_link_children'])) {
            foreach ($convert['product_link_children'] as $key => $value) {
                $parent = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $value['product_id'], null, null);
                if ($parent && isset($parent['id_desc']) && $parent['id_desc'] > 0) {
                    $parent_id                          = $parent['id_desc'];
                    $catalog_product_link_relate_data   = array(
                        "product_id"        => $parent_id,
                        "parent_id" => $product_id,
                    );
                    $catalog_product_link_relate_query  = $this->createInsertQuery('product_relation',$catalog_product_link_relate_data);
                    if(Bootstrap::getConfig('dev_mode')){
                        $catalog_product_link_relate_import = $this->importProductData($catalog_product_link_relate_query);
                    }else{
                        $all_query[] = $catalog_product_link_relate_query;
                    }
                }
            }
        }
        /**
         * End Namlv
         */
        //insert remain attribute end
//=================================================================
        //insert to catalog_product_entity_int begin
        // if(isset($convert['add_data'])){
        //     foreach ($convert['add_data'] as $key => $value) {
        //         if($value['type']!='int')continue;
        //         $attribute_exists = false;
        //         $attribute_id = null;
        //         foreach ($product_eav_attribute_array as $key1 => $value1) {
        //             if($key == $key1){
        //                 // $attribute_exists = true;
        //                 $attribute_id = $value1['attribute_id'];
        //                 break;
        //             }
        //         }

        //         if($attribute_id){
        //             $option_exists = $this->selectMap($url_src, $url_desc, self::TYPE_OPTION, $value['value'], null, null);
        //             if(isset($option_exists['id_desc'])){
        //                 $option_id = $option_exists['id_desc'];

        //                 $catalog_product_entity_int_data = array(
        //                     'attribute_id' => $attribute_id,
        //                     'store_id' => 0,
        //                     'entity_id' => $product_id,
        //                     'value' => $option_id,
        //                 );

        //                 $catalog_product_entity_int_query = "INSERT INTO _DBPRF_catalog_product_entity_int ";
        //                 $catalog_product_entity_int_query .= $this->arrayToInsertCondition($catalog_product_entity_int_data);
        //                 $catalog_product_entity_int_import = $this->getConnectorData($url_query, array(
        //                     'query' => serialize(array(
        //                         'type' => 'insert',
        //                         'query' => $catalog_product_entity_int_query,
        //                         'params' => array(
        //                             'insert_id' => true,
        //                         )
        //                     )),
        //                 ));

        //                 if(!$catalog_product_entity_int_import){
        //                     //warning
        //                     return $this->errorConnector();
        //                 }

        //                 if($catalog_product_entity_int_import['result'] != 'success'){
        //                     //warning
        //                     return $this->warningSQL($catalog_product_entity_int_import);
        //                 }
        //             }
        //         }
        //     }
        // }
        //insert to catalog_product_entity_int end

        //url_rewrite multistore
//        if ($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']) {
//            $model_seo   = Bootstrap::getModel($this->_notice['config']['seo_plugin']);
//            $url_rewrite = $model_seo->getProductSeoExport($this, $product, $productsExt);
        $url_rewrite = $convert['url_rewrite'];
        $metadata    = null;
        $target_path = null;
        foreach ($url_rewrite as $key => $rewrite) {
            $category_id = $this->getValue($rewrite, 'category_id');

            $store_id    = $this->getMapStoreView($this->getValue($rewrite, 'store_id', 0));
            if ($category_id) {
                $category_id = $this->getMapFieldBySource(self::TYPE_CATEGORY, $category_id);
                if (!$category_id) {
                    continue;
                }
                $target_path = 'catalog/product/view/id/' . $product_id . '/category/' . $category_id;
                $metadata    = $this->mySerialize(array(
                    'category_id' => $category_id,
                ));
            } else {
                $target_path = 'catalog/product/view/id/' . $product_id;
            }
            $request_path      = $this->getRequestPath('product', $rewrite['request_path'], $store_id);
            $url_rewrite_data  = array(
                'parent_id'        => $product_id,
                'type'      => 'product',
                'url'     => $request_path,
                'controller'      => $target_path,
                'redirect_type'    => 0,
                'is_default' => 1,
            );
            $url_rewrite_query = $this->createInsertQuery('url_rewrite',$url_rewrite_data);
            if (Bootstrap::getConfig('dev_mode')) {
                $url_rewrite_import = $this->importProductData($url_rewrite_query);
                if (!$url_rewrite_import) {
                    Bootstrap::logQuery($url_rewrite_query);
                    var_dump(1);
                    exit;
                }
            } else {
                $all_query[] = $url_rewrite_query;
            }
        }

//        }else{
//            foreach ($this->_notice['target']['languages'] as $store_id=>$store_name){
//                if(!$pro_url_key){
//                    continue;
//                }
//                if(strpos($pro_url_key,'.html') !== false){
//                    $path = $pro_url_key;
//                }else{
//                    $path = $pro_url_key.'.html';
//                }
//                $path = $this->getRequestPath('product',$path,$store_id);
//                $url_rewrite_data  = array(
//                    'entity_type'      => 'product',
//                    'entity_id'        => $product_id,
//                    'request_path'     => $path,
//                    'target_path'      => 'catalog/product/view/id/' . $product_id,
//                    'redirect_type'    => 0,
//                    'store_id'         => $store_id,
//                    'description'      => '',
//                    'is_autogenerated' => 1,
//                    'metadata'         => '',
//                );
//                $url_rewrite_query = $this->createInsertQuery('url_rewrite',$url_rewrite_data);
//                if (Bootstrap::getConfig('dev_mode')) {
//                    $url_rewrite_import = $this->importProductData($url_rewrite_query);
//                    if (!$url_rewrite_import) {
//                        Bootstrap::logQuery($url_rewrite_query);
//                        var_dump(1);
//                        exit;
//                    }
//                } else {
//                    $all_query[] = $url_rewrite_query;
//                }
//                $root_cate_ids = array_keys($this->_notice['target']['categoryData']);
//                foreach ($convert['categories'] as $key=> $value){
//
//                    $category_id = null;
//                    $category_code = '';
//                    if (isset($this->_notice['map']['categoryData'][$value['id']])) {
//                        $category_id = $this->_notice['map']['categoryData'][$value['id']];
//                    } else {
//                        $category_exists = $this->selectMap($url_src,$url_desc,self::TYPE_CATEGORY, $value['id']);
//                        if (!$category_exists) {
//                            continue;
//                        }
//                        $category_id = $category_exists['id_desc'];
//                        $category_code = $category_exists['code_src'];
//                    }
//                    if(in_array($category_id,$root_cate_ids) || !$category_id){
//                        continue;
//                    }
//                    $category_product_path = $category_code?$category_code.'/'.$path:$path;
//                    $category_product_path = $this->getRequestPath('product',$category_product_path,$store_id);
//                    $url_rewrite_cate_pro_data  = array(
//                        'entity_type'      => 'product',
//                        'entity_id'        => $product_id,
//                        'request_path'     => $category_product_path,
//                        'target_path'      => 'catalog/product/view/id/' . $product_id.'/category/'.$category_id,
//                        'redirect_type'    => 0,
//                        'store_id'         => $store_id,
//                        'description'      => '',
//                        'is_autogenerated' => 1,
//                        'metadata'         => '',
//                    );
//                    $url_rewrite_cate_pro_query = $this->createInsertQuery('url_rewrite',$url_rewrite_cate_pro_data);
//                    if (Bootstrap::getConfig('dev_mode')) {
//                        $url_rewrite_import = $this->importProductData($url_rewrite_cate_pro_query);
//                        if (!$url_rewrite_import) {
//                            Bootstrap::logQuery($url_rewrite_cate_pro_query);
//                            var_dump(1);
//                            exit;
//                        }
//                    } else {
//                        $all_query[] = $url_rewrite_cate_pro_query;
//                    }
//
//                }
//            }
//        }

//=================================================================
        if (!Bootstrap::getConfig('dev_mode')) {
            $all_import = $this->importMultipleData($all_query,'product');
//            if ($all_import['result'] != 'success') {
//                //warning
//                return $this->warningSQL($all_import);
//            }
        }


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

        $customer_data = $this->constructCustomer();
        $customer_data = $this->addConstructDefault($customer_data);

        $customer_data['id']           = $customer['entity_id'];
        $customer_data['increment_id'] = $customer['increment_id'];
        $customer_data['username']     = trim($customer['email']);
        $customer_data['email']        = trim($customer['email']);
        $customer_data['password']     = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['password_hash'], 'value');
        $customer_data['first_name']   = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['firstname'], 'value');
        $customer_data['middle_name']  = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['middlename'], 'value');
        $customer_data['last_name']    = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['lastname'], 'value');
        $customer_data['group_id']     = $customer['group_id'];
        //LDV94begin
        $customer_data['gender'] = $gender_id ? $gender_id : 3;
        //LDV94end

        $customer_data['dob']           = $this->getRowValueFromListByField($entity_datetime, 'attribute_id', $eav_attribute_cus['dob'], 'value');
        $customer_data['is_subscribed'] = $this->getListFromListByField($customersExt['data']['newsletter_subscriber'], 'customer_id', $customer['entity_id']);

        $customer_data['active'] = $customer['is_active'];

        $customer_data['created_at'] = $customer['created_at'];
        $customer_data['updated_at'] = $customer['updated_at'];

        $customer_data['taxvat']                    = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['taxvat'], 'value');
        $customer_data['suffix']                    = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['suffix'], 'value');
        $customer_data['prefix']                    = $this->getRowValueFromListByField($entity_varchar, 'attribute_id', $eav_attribute_cus['prefix'], 'value');
        $customer_data['disable_auto_group_change'] = $this->getValue($customer, 'disable_auto_group_change');
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

                $address_data['first_name']  = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['firstname'], 'value');
                $address_data['last_name']   = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['lastname'], 'value');
                $address_data['middle_name'] = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['middlename'], 'value');
                $address_data['suffix']      = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['suffix'], 'value');
                $address_data['prefix']      = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['prefix'], 'value');
                $address_data['vat_id']      = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['vat_id'], 'value');
                $street                      = $this->getRowValueFromListByField($address_entity_text, 'attribute_id', $eav_attribute_cusadd['street'], 'value');
                $street_explode              = explode('\n', $street);
                $address_data['address_1']   = isset($street_explode[0]) ? $street_explode[0] : '';
                $address_data['address_2']   = isset($street_explode[1]) ? $street_explode[1] : '';
                $address_data['city']        = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['city'], 'value');
                $address_data['postcode']    = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['postcode'], 'value');
                $address_data['telephone']   = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['telephone'], 'value');
                $address_data['company']     = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['company'], 'value');
                $address_data['fax']         = $this->getRowValueFromListByField($address_entity_varchar, 'attribute_id', $eav_attribute_cusadd['fax'], 'value');

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

        $response  = $this->_defaultResponse();
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');

        $default_billing  = null;
        $default_shipping = null;

        foreach ($convert['address'] as $item) {
            if ($item['default']['billing']) $default_billing = true;
        }
        foreach ($convert['address'] as $item) {
            if ($item['default']['shipping']) $default_shipping = true;
        }


        $customer_gender = 3;
        if (isset($convert['gender']) && $convert['gender'] != null) {
            switch ($convert['gender']) {
                case 'm':
                    $customer_gender = 1;
                    break;
                case 'f':
                    $customer_gender = 2;
                    break;
                case '1':
                    $customer_gender = 1;
                    break;
                case '2':
                    $customer_gender = 2;
                    break;
                default:
                    $customer_gender = null;
                    break;
            }
        }
        $group_id   = $this->getValue($this->_notice['map']['customer_group'], $convert['group_id'], 0);
        $store_id   = $this->getMapStoreView($customer['store_id']);
        $website_id = $this->getWebsiteIdByStoreId($store_id);
        if (isset($customer['website_id']) && $customer['website_id'] == 0) {
            $website_id = 0;
        }
        $customer_entity_data = array(
            'website_id'                => $website_id,
            'email'                     => $convert['email'] ? $convert['email'] : '',
            'group_id'                  => $group_id,
            'increment_id'              => null,
            'store_id'                  => $store_id,
            'created_at'                => $this->getValue($convert, 'created_at'),
            'updated_at'                => $this->getValue($convert, 'updated_at'),
            'is_active'                 => $this->getValue($convert, 'is_active', 1),
            'disable_auto_group_change' => $this->getValue($convert, 'disable_auto_group_change', 0),
            'created_in'                => $this->getValue($this->_notice['target']['languages'], $store_id, 'DEFAULT'),
            'prefix'                    => $this->getValue($convert, 'prefix'),
            'firstname'                 => $this->getValue($convert, 'first_name'),
            'middlename'                => $this->getValue($convert, 'middle_name'),
            'lastname'                  => $this->getValue($convert, 'last_name'),
            'suffix'                    => $this->getValue($convert, 'suffix'),
            'dob'                       => $this->getValue($convert, 'dob'),
            'password_hash'             => $this->getValue($convert, 'password'),
            'rp_token'                  => '',
            'rp_token_created_at'       => date("Y-m-d h:i:s"),
            'default_billing'           => $default_billing,
            'default_shipping'          => $default_shipping,
            'taxvat'                    => $this->getValue($convert, 'taxvat'),
            'confirmation'              => null,
            'gender'                    => $customer_gender,
            'failures_num'              => 0,
            'first_failure'             => null,
            'lock_expires'              => null,

        );
        if ($this->_notice['config']['pre_cus']) {
            $delete_customer                   = $this->deleteTargetCustomer($convert['id']);
            $customer_entity_data['entity_id'] = $convert['id'];
        }


        $customer_entity_query = "INSERT INTO _DBPRF_customer_entity ";
        $customer_entity_query .= $this->arrayToInsertCondition($customer_entity_data);

        $customer_entity_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $customer_entity_query,
                'params' => array(
                    'insert_id' => true,
                )
            )),
        ));

//        Bootstrap::logQuery(($customer_entity_query);exit;
//        if (!$customer_entity_import) {
//            if (Bootstrap::getConfig('dev_mode')) {
//                Bootstrap::logQuery($customer_entity_query;
//                var_dump('err');
//                exit;
//            }
//
//            //warning
//        }
//

        $customer_entity_id = $customer_entity_import['data'];
        if (!$customer_entity_import || !$customer_entity_id) {
            // warning
            Bootstrap::log($customer,'err_customer');
            $response['result'] = 'warning';
            $response['msg']    = $this->warningImportEntity('Customer', $convert['id'], $convert['code']);

            return $response;
        }

        if ($customer_entity_import['result'] != 'success') {
            // warning
            return $this->warningSQL($customer_entity_import);
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_CUSTOMER, $convert['id'], $customer_entity_id, $convert['code']);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $customer_entity_id,
        );
    }

    public function afterCustomerImport($customer_id, $convert, $customer, $customersExt)
    {
        $all_query                 = array();
        $response                  = $this->_defaultResponse();
        $url_src                   = $this->_notice['src']['cart_url'];
        $url_desc                  = $this->_notice['target']['cart_url'];
        $url_query                 = $this->getConnectorUrl('query');
        $billingDefault            = null;
        $shippingDefault           = null;
        $billing_full              = null;
        $shipping_full             = null;
        $increment_id              = null;
        $customer_update_data = array();
        $new_customer_increment_id = '' . $customer_id;
        while (strlen($new_customer_increment_id) < self::LEN_INCREMENT) {
            $new_customer_increment_id = '0' . $new_customer_increment_id;
        }
        $new_customer_increment_id = '1' . $new_customer_increment_id;
        $old_customer_increment_id = $this->getValue($convert, 'increment_id', $new_customer_increment_id);

        if ($this->_notice['config']['pre_cus']) {
            $increment_id = $old_customer_increment_id;
        } else {
            $increment_id = $new_customer_increment_id;
        }
        $customer_update_data['increment_id'] = $increment_id;
        $customer_gender = 3;
        if (isset($convert['gender']) && $convert['gender'] != null) {
            switch ($convert['gender']) {
                case 'm':
                    $customer_gender = 1;
                    break;
                case 'f':
                    $customer_gender = 2;
                    break;
                case '1':
                    $customer_gender = 1;
                    break;
                case '2':
                    $customer_gender = 2;
                    break;

                default:
                    $customer_gender = 3;
                    break;
            }
        }


        $convert_address_data = $convert['address'];
        foreach ($convert_address_data as $key => $convert_address) {
            if ($convert_address['state']['name']) {
                $region_id = $this->getIdByStateName($convert_address['state']['name']);
            } else {
                $region_id = 57;
            }
            $customer_address_entity_data = array(
                'increment_id'        => $increment_id,
                'parent_id'           => $customer_id,
                'created_at'          => $this->getValue($convert_address, 'created_at', date("Y-m-d h:i:s")),
                'updated_at'          => $this->getValue($convert_address, 'updated_at', date("Y-m-d h:i:s")),
                'is_active'           => $this->getValue($convert_address, 'is_active', 1),
                'city'                => $this->getValue($convert_address, 'city', ''),
                'company'             => $this->getValue($convert_address, 'company'),
                'country_id'          => $this->getValue($this->getValue($convert_address, 'country', array()), 'country_code', ''),
                'fax'                 => $this->getValue($convert_address, 'fax'),
                'firstname'           => $this->getValue($convert_address, 'first_name', ''),
                'lastname'            => $this->getValue($convert_address, 'last_name', ''),
                'middlename'          => $this->getValue($convert_address, 'middle_name'),
                'postcode'            => $this->getValue($convert_address, 'postcode'),
                'prefix'              => $this->getValue($convert_address, 'prefix'),
                'region'              => $convert_address['state']['name'] ? $convert_address['state']['name'] : '',
                'region_id'           => $region_id,
                'street'              => $this->getValue($convert_address, 'address_1', ''),
                'suffix'              => $this->getValue($convert, 'suffix'),
                'telephone'           => $this->getValue($convert_address, 'telephone', ''),
                'vat_id'              => $this->getValue($convert_address, 'vat_id'),
                'vat_is_valid'        => null,
                'vat_request_date'    => null,
                'vat_request_id'      => null,
                'vat_request_success' => null,
            );

            $customer_address_entity_query = $this->createInsertQuery('customer_address_entity',$customer_address_entity_data);

            if (!$convert_address['default']['billing'] && !$convert_address['default']['shipping']) {
                if (Bootstrap::getConfig('dev_mode')) {
                    $customer_address_entity_import =$this->importCustomerData($customer_address_entity_query);
                    if (!$customer_address_entity_import) {
                        Bootstrap::logQuery($customer_address_entity_query);
                        var_dump('err');
                        exit;
                    }
                }else{
                    $all_query[] = $customer_address_entity_query;
                }
            } else {
                $customer_address_id = $this->importCustomerData($customer_address_entity_query);
                if (!$customer_address_id) {
                    continue;
                }
                $temp_street = $convert_address['address_1'] . " " . $convert_address['city'] . " " . $convert_address['state']['name'] . " " . $convert_address['postcode'];
                if ($convert_address['default']['billing']) {
                    $billingDefault = $customer_address_id;
                    $billing_full   = $temp_street;

                }
                if ($convert_address['default']['shipping']) {
                    $shippingDefault = $customer_address_id;
                    $shipping_full   = $temp_street;
                }
            }

        }

        //default address
        if ($billingDefault) {
            $customer_update_data['default_billing'] = $billingDefault;
        }
        if ($shippingDefault) {
            $customer_update_data['default_shipping'] = $shippingDefault;
        }
        //end default address
        $group_id   = $this->getValue($this->_notice['map']['customer_group'], $convert['group_id'], 0);
        $store_id   = $this->getMapStoreView($customer['store_id']);
        $website_id = $this->getWebsiteIdByStoreId($store_id);
        if (isset($customer['website_id']) && $customer['website_id'] == 0) {
            $website_id = 0;
        }
        $customer_grid_flat_data = array(
            'entity_id'          => $customer_id,
            'name'               => $this->getValue($convert, 'first_name', '') . ' ' . $this->getValue($convert, 'middle_name', '') . ' ' . $this->getValue($convert, 'last_name', ''),
            'email'              => $convert['email'] ? $convert['email'] : '',
            'group_id'           => $group_id,
            'created_at'         => $convert['created_at'] ? $convert['created_at'] : null,
            'website_id'         => $website_id,
            'confirmation'       => '',
            'created_in'         => 'DEFAULT',
            'dob'                => $convert['dob'] ? $convert['dob'] : null,
            'gender'             => $customer_gender,
            'taxvat'             => $this->getValue($convert, 'taxvat'),
            'lock_expires'       => date("Y-m-d h:i:s"),
            'billing_full'       => $billing_full,
            'billing_firstname'  => $convert['first_name'] ? $convert['first_name'] : '',
            'billing_lastname'   => $convert['last_name'] ? $convert['last_name'] : '',
            'billing_telephone'  => isset($convert['address'][0]['telephone']) ? $convert['address'][0]['telephone'] : '',
            'billing_postcode'   => isset($convert['address'][0]['postcode']) ? $convert['address'][0]['postcode'] : '',
            'billing_country_id' => isset($convert['address'][0]['country']['country_code']) ? $convert['address'][0]['country']['country_code'] : '',
            'billing_region'     => isset($convert['address'][0]['state']['name']) ? $convert['address'][0]['state']['name'] : '',
            'billing_street'     => isset($convert['address'][0]['address_1']) ? $convert['address'][0]['address_1'] : '',
            'billing_city'       => isset($convert['address'][0]['city']) ? $convert['address'][0]['city'] : '',
            'billing_fax'        => isset($convert['address'][0]['fax']) ? $convert['address'][0]['fax'] : '',
            'billing_vat_id'     => '',
            'billing_company'    => isset($convert['address'][0]['company']) ? $convert['address'][0]['company'] : '',
            'shipping_full'      => $shipping_full,
            // 'date_added' => $convert['created_at'] ? $this->convertStringToDatetime($convert['created_at']) : date("Y-m-d h:i:s"),
        );

        $customer_grid_flat_query = $this->createInsertQuery('customer_grid_flat',$customer_grid_flat_data);

//        $customer_grid_flat_query = str_replace(' ,', " '',", $customer_grid_flat_query);

        if (Bootstrap::getConfig('dev_mode')) {
            $customer_grid_flat_import = $this->importCustomerData($customer_grid_flat_query);

            if (!$customer_grid_flat_import) {
                //warning
                Bootstrap::logQuery($customer_grid_flat_query);
                var_dump('err');
                exit;
            }

        } else {
            $all_query[] = $customer_grid_flat_query;
        }


        if (isset($convert['is_subscribed']) && $convert['is_subscribed'] != 0) {
            $subscribed = $convert['is_subscribed'];
            foreach ($subscribed as $key => $values) {

                $newsletter_subscriber_data = array(
                    'store_id'                => $this->getMapStoreView($this->getValue($values, 'store_id', "-1")),
                    'change_status_at'        => $this->getValue($values, 'change_status_at', null),
                    'customer_id'             => $customer_id,
                    'subscriber_email'        => isset($convert['email']) ? $convert['email'] : null,
                    'subscriber_status'       => $this->getValue($values, 'subscriber_status', 0),
                    'subscriber_confirm_code' => $this->getValue($values, 'subscriber_confirm_code', null),
                );

                $newsletter_subscriber_query = $this->createInsertQuery('newsletter_subscriber',$newsletter_subscriber_data);


                if (Bootstrap::getConfig('dev_mode')) {
                    $newsletter_subscriber_import = $this->importCustomerData($newsletter_subscriber_query);

                    if (!$newsletter_subscriber_import) {
                        Bootstrap::logQuery($newsletter_subscriber_query);
                        var_dump('err');
                        exit;
                        //warning
                    }

                } else {
                    $all_query[] =$newsletter_subscriber_query;
                }
            }

        }
        $customer_update_query = $this->createUpdateQuery('customer_entity',$customer_update_data,array('entity_id'=>$customer_id));
        if(Bootstrap::getConfig('dev_mode')){
            $update = $this->importCustomerData($customer_update_query);
            if(!$update){
                print_r($customer_update_query['query']);exit;
            }
        }else{
            $all_query[] = $customer_update_query;
            $this->importMultipleData($all_query,'customer');
        }

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
                'query' => "SELECT * FROM _DBPRF_sales_order 
                WHERE entity_id > " . $id_src . " ORDER BY entity_id ASC LIMIT " . $limit
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
            'sales_order_item'           => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_order_item WHERE order_id IN " . $order_id_con
            ),
            'sales_order_address'        => array(
                'type'  => 'select',
                'query' => "SELECT sfoa.*,sdcr.code,sdcr.default_name FROM _DBPRF_sales_order_address as sfoa
                                LEFT JOIN _DBPRF_directory_country_region as sdcr ON sfoa.region_id = sdcr.region_id
                            WHERE sfoa.parent_id IN " . $order_id_con,
            ),
            'sales_order_status_history' => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_order_status_history WHERE parent_id IN " . $order_id_con
            ),
            'sales_order_payment'        => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_order_payment WHERE parent_id IN " . $order_id_con
            ),
            'sales_invoice'              => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_invoice WHERE order_id IN " . $order_id_con
            ),
            'sales_shipment'             => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_shipment WHERE order_id IN " . $order_id_con
            ),
            'sales_creditmemo'           => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_creditmemo WHERE order_id IN " . $order_id_con
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
        $shipment_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_shipment'], 'entity_id');
        $shipment_item_condition = $this->arrayToInCondition($shipment_ids);

        $creditmemo_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_creditmemo'], 'entity_id');
        $creditmemo_item_condition = $this->arrayToInCondition($creditmemo_ids);

        $invoice_ids            = $this->duplicateFieldValueFromList($ordersExt['data']['sales_invoice'], 'entity_id');
        $invoice_item_condition = $this->arrayToInCondition($invoice_ids);
        $orders_ext_rel_queries = array();
        if ($shipment_ids && count($shipment_ids) > 0) {
            $orders_ext_rel_queries['sales_shipment_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_shipment_item WHERE parent_id IN " . $shipment_item_condition,
            );
            $orders_ext_rel_queries['sales_shipment_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_shipment_comment WHERE parent_id IN " . $shipment_item_condition,
            );
        }
        if ($invoice_ids && count($invoice_ids) > 0) {
            $orders_ext_rel_queries['sales_invoice_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_invoice_item WHERE parent_id IN " . $invoice_item_condition,
            );
            $orders_ext_rel_queries['sales_invoice_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_invoice_comment WHERE parent_id IN " . $invoice_item_condition,
            );
        }
        if ($creditmemo_ids && count($creditmemo_ids) > 0) {
            $orders_ext_rel_queries['sales_creditmemo_item'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_creditmemo_item WHERE parent_id IN " . $creditmemo_item_condition,
            );
            $orders_ext_rel_queries['sales_creditmemo_comment'] = array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_sales_creditmemo_comment WHERE parent_id IN " . $creditmemo_item_condition,
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
        $order_data                           = $this->constructOrder();
        $order_data                           = $this->addConstructDefault($order_data);
        $order_data['id']                     = $order['entity_id'];
        $order_data['status']                 = $order['status'];
        $order_data['tax']['title']           = "Taxes";
        $order_data['increment_id']           = $order['increment_id'];
        $order_data['tax']['amount']          = $order['base_tax_amount'];
        $order_data['shipping']['title']      = "Shipping";
        $order_data['shipping']['amount']     = $order['shipping_amount'];
        $order_data['subtotal']['title']      = 'Total products';
        $order_data['subtotal']['amount']     = $order['subtotal'];
        $order_data['total']['title']         = 'Total';
        $order_data['total']['amount']        = $order['grand_total'];
        $order_data['currency']               = $order['store_currency_code'];
        $order_data['created_at']             = $order['created_at'];
        $order_data['updated_at']             = $order['updated_at'];
        $order_data['base_subtotal_incl_tax'] = $order['base_subtotal_incl_tax'];
        $order_data['subtotal_incl_tax']      = $order['subtotal_incl_tax'];
        $order_data['discount']['amount']     = $order['discount_amount'];
//        $order_data['discount']['canceled'] = $order['discount_canceled'];
//        $order_data['discount']['invoiced'] = $order['discount_invoiced'];
//        $order_data['discount']['refunded'] = $order['discount_refunded'];
//
//        $order_data['base_discount']['amount'] = $order['base_discount_amount'];
//        $order_data['base_discount']['canceled'] = $order['base_discount_canceled'];
//        $order_data['base_discount']['invoiced'] = $order['base_discount_invoiced'];
//        $order_data['base_discount']['refunded'] = $order['base_discount_refunded'];
//        $order_data['protect_code']

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

        $order_address  = $this->getListFromListByField($ordersExt['data']['sales_order_address'], 'parent_id', $order['entity_id']);
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
//        $order_payment['title'] = $this->getRowValueFromListByField($ordersExt['data']['sales_order_payment'], 'parent_id', $order['entity_id'], 'method');
//        $order_data['payment']  = $order_payment;
        $order_payment = $this->getListFromListByField($ordersExt['data']['sales_order_payment'], 'parent_id', $order['entity_id']);
        $order_data['payment']  = $order_payment;
        if(isset($order_payment[0])){
            $order_data['payment_method'] = $order_payment[0]['method'];
        }

        /**
         * Get product in order
         */
        $orderProduct = $this->getListFromListByField($ordersExt['data']['sales_order_item'], 'order_id', $order['entity_id']);

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
            $order_item['weight']         = $order_product['weight'];
            $order_item['is_qty_decimal'] = $order_product['is_qty_decimal'];
            $order_item['no_discount']    = $order_product['no_discount'];
            $order_item['product_type']   = $order_product['product_type'];
            $order_item['quote_item_id']  = $order_product['quote_item_id'];

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
            $order_item['product']['id']            = $order_product['product_id'];
            $order_item['product']['name']          = $order_product['name'];
            $order_item['product']['sku']           = $order_product['sku'];
            $order_item['qty']                      = intval($order_product['qty_ordered']);
            $order_item['price']                    = $order_product['price'];
            $order_item['original_price']           = $order_product['original_price'];
            $order_item['tax_amount']               = $order_product['tax_amount'];
            $order_item['tax_percent']              = $order_product['tax_percent'];
            $order_item['tax_invoiced']             = $order_product['tax_invoiced'];
            $order_item['discount_amount']          = $order_product['discount_amount'];
            $order_item['discount_percent']         = $order_product['discount_percent'];
            $order_item['discount_invoiced']        = $order_product['discount_invoiced'];
            $order_item['row_total']                = $order_product['row_total'];
            $order_item['row_invoiced']             = $order_product['row_invoiced'];
            $order_item['row_weight']               = $order_product['row_weight'];
            $order_item['price_incl_tax']           = $order_product['price_incl_tax'];
            $order_item['row_total_incl_tax']       = $order_product['row_total_incl_tax'];
            $order_item['free_shipping']            = $order_product['free_shipping'];
            $order_item['tax_canceled']             = $order_product['tax_canceled'];
            $order_item['tax_refunded']             = $order_product['tax_refunded'];
            $order_item['discount_refunded']        = $order_product['discount_refunded'];
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
        $orderStatusHistory = $this->getListFromListByField($ordersExt['data']['sales_order_status_history'], 'parent_id', $order['entity_id']);
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

        $shipment = $this->getListFromListByField($ordersExt['data']['sales_shipment'], 'order_id', $order['entity_id']);

        if (count($shipment) > 0) {
            $order_data['shipment']['data'] = $shipment[0];
            $shipment_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_shipment_item'], 'parent_id', $shipment[0]['entity_id']);
            if (count($shipment_grid_item) > 0) {
                $order_data['shipment']['item'] = $shipment_grid_item;
            }
            $shipment_grid_comment = $this->getListFromListByField($ordersExt['data']['sales_shipment_comment'],'parent_id',$shipment[0]['entity_id']);
            if (count($shipment_grid_comment) > 0) {
                $order_data['shipment']['comment'] = $shipment_grid_comment;
            }
        }

        $creditmemo = $this->getListFromListByField($ordersExt['data']['sales_creditmemo'], 'order_id', $order['entity_id']);

        if (count($creditmemo) > 0) {
            $order_data['creditmemo']['data'] = $creditmemo[0];
            $creditmemo_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_creditmemo_item'], 'parent_id', $creditmemo[0]['entity_id']);
            if (count($creditmemo_grid_item) > 0) {
                $order_data['creditmemo']['item'] = $creditmemo_grid_item;
            }
            $creditmemo_grid_comment             = $this->getListFromListByField($ordersExt['data']['sales_creditmemo_comment'], 'parent_id', $creditmemo[0]['entity_id']);
            if (count($creditmemo_grid_comment) > 0) {
                $order_data['creditmemo']['comment'] = $creditmemo_grid_comment;
            }
        }

        $invoice = $this->getListFromListByField($ordersExt['data']['sales_invoice'], 'order_id', $order['entity_id']);

        if (count($invoice) > 0) {
            $order_data['invoice']['data'] = $invoice[0];
            $invoice_grid_item             = $this->getListFromListByField($ordersExt['data']['sales_invoice_item'], 'parent_id', $invoice[0]['entity_id']);
            if (count($invoice_grid_item) > 0) {
                $order_data['invoice']['item'] = $invoice_grid_item;
            }
            $invoice_grid_comment             = $this->getListFromListByField($ordersExt['data']['sales_invoice_comment'], 'parent_id', $invoice[0]['entity_id']);
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
        return $convert['id'];
    }

    public function checkOrderImport($convert, $order, $ordersExt)
    {

        return $this->getMapFieldBySource(self::TYPE_ORDER, $convert['id']) ? true : false;
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
        $order     = $this->getValue($convert, 'order', $order);
        $url_query = $this->getConnectorUrl('query');
        $order_id  = $order['entity_id'];
        $response  = $this->_defaultResponse();
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];

        //======================
        $customer_id = null;
        if ($convert['customer']['id']) {
            $customer_id = $this->getMapFieldBySource(self::TYPE_CUSTOMER, $convert['customer']['id'], $convert['customer']['code']);
        }
        if (!$customer_id) {
            $customer_id = null;
        }
        $customer_group = $this->getValue($this->_notice['map']['customer_group'], $convert['customer']['group_id'], 0);


        if (!$customer_group) {
            $customer_group = null;
        }
        //======================
        $order_status = 'canceled';

        if (isset($this->_notice['map']['order_status'][$convert['status']]) && $this->_notice['map']['order_status'][$convert['status']] != null) {
            $order_status = $this->_notice['map']['order_status'][$convert['status']];
        }


        $order_state = null;

        $status_state_data = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize(array(
                'status_state_data' => array(
                    'type'  => 'select',
                    'query' => "SELECT * FROM _DBPRF_sales_order_status_state",
                ),
            )),
        ));

        if ($status_state_data['result'] == 'success') {
            $order_state = $this->getRowValueFromListByField($status_state_data['data']['status_state_data'], 'status', $order_status, 'state');
        }

        if (!$order_state) {
            $order_state = 'canceled';
        }

        //======================
        $total_qty_ordered = 0;
        $subtotal          = 0;

        $ext_customer_id = $this->getMapFieldBySource(self::TYPE_CUSTOMER, $this->getValue($order, 'ext_customer_id'));
        $ext_order_id    = $this->getMapFieldBySource(self::TYPE_ORDER, $this->getValue($order, 'ext_order_id'));
        if (!$ext_customer_id) {
            $ext_customer_id = null;
        }
        if (!$ext_order_id) {
            $ext_order_id = null;
        }
        foreach ($convert['items'] as $key => $value) {
            $total_qty_ordered += intval($value['qty']);
            $subtotal          += floatval($value['subtotal']);
        }
        $store_id   = $this->getMapStoreView($this->getValue($order, 'store_id', '-1'));
        $store_name = $this->getNameStoreView($store_id);
        if ($store_name) {
            $store_name = substr($store_name, 0, 32);
        }
        $order_entity_data = array(
            'state'                                        => $order_state,
            'status'                                       => $order_status,
            'coupon_code'                                  => $this->getValue($order, 'coupon_code'),
            'protect_code'                                 => $this->getValue($order, 'protect_code'),
            'shipping_description'                         => $this->getValue($order, 'shipping_description'),
            'is_virtual'                                   => $this->getValue($order, 'is_virtual'),
            'store_id'                                     => $store_id,
            'customer_id'                                  => $customer_id,
            'base_discount_amount'                         => $convert['discount']['amount'] ? $convert['discount']['amount'] : null,
            'base_discount_canceled'                       => $this->getValue($order, 'base_discount_canceled'),
            'base_discount_invoiced'                       => $this->getValue($order, 'base_discount_invoiced'),
            'base_discount_refunded'                       => $this->getValue($order, 'base_discount_refunded'),
            'base_grand_total'                             => $convert['total']['amount'] ? $convert['total']['amount'] : null,
            'base_shipping_amount'                         => $convert['shipping']['amount'] ? $convert['shipping']['amount'] : null,
            'base_shipping_canceled'                       => $this->getValue($order, 'base_shipping_canceled'),
            'base_shipping_invoiced'                       => $this->getValue($order, 'base_shipping_invoiced'),
            'base_shipping_refunded'                       => $this->getValue($order, 'base_shipping_refunded'),
            'base_shipping_tax_amount'                     => $this->getValue($order, 'base_shipping_tax_amount'),
            'base_shipping_tax_refunded'                   => $this->getValue($order, 'base_shipping_tax_refunded'),
            'base_subtotal'                                => $convert['subtotal']['amount'] ? $convert['subtotal']['amount'] : null,
            'base_subtotal_canceled'                       => $this->getValue($order, 'base_subtotal_canceled'),
            'base_subtotal_invoiced'                       => $this->getValue($order, 'base_subtotal_invoiced'),
            'base_subtotal_refunded'                       => $this->getValue($order, 'base_subtotal_refunded'),
            'base_tax_amount'                              => $convert['tax']['amount'] ? $convert['tax']['amount'] : null,
            'base_tax_canceled'                            => $this->getValue($order, 'base_tax_canceled'),
            'base_tax_invoiced'                            => $this->getValue($order, 'base_tax_invoiced'),
            'base_tax_refunded'                            => $this->getValue($order, 'base_tax_refunded'),
            'base_to_global_rate'                          => $this->getValue($order, 'base_to_global_rate'),
            'base_to_order_rate'                           => $this->getValue($order, 'base_to_order_rate'),
            'base_total_canceled'                          => $this->getValue($order, 'base_total_canceled'),
            'base_total_invoiced'                          => $this->getValue($order, 'base_total_invoiced'),
            'base_total_invoiced_cost'                     => $this->getValue($order, 'base_total_invoiced_cost'),
            'base_total_offline_refunded'                  => $this->getValue($order, 'base_total_offline_refunded'),
            'base_total_online_refunded'                   => $this->getValue($order, 'base_total_online_refunded'),
            'base_total_paid'                              => $this->getValue($order, 'base_total_paid'),
            'base_total_qty_ordered'                       => $this->getValue($order, 'base_total_qty_ordered'),
            'base_total_refunded'                          => $this->getValue($order, 'base_total_refunded'),
            'discount_amount'                              => $convert['discount']['amount'] ? $convert['discount']['amount'] : null,
            'discount_canceled'                            => $this->getValue($order, 'discount_canceled'),
            'discount_invoiced'                            => $this->getValue($order, 'discount_invoiced'),
            'discount_refunded'                            => $this->getValue($order, 'discount_refunded'),
            'grand_total'                                  => $convert['total']['amount'] ? $convert['total']['amount'] : null,
            'shipping_amount'                              => $convert['shipping']['amount'] ? $convert['shipping']['amount'] : null,
            'shipping_canceled'                            => $this->getValue($order, 'shipping_canceled'),
            'shipping_invoiced'                            => $this->getValue($order, 'shipping_invoiced'),
            'shipping_refunded'                            => $this->getValue($order, 'shipping_refunded'),
            'shipping_tax_amount'                          => $this->getValue($order, 'shipping_tax_amount'),
            'shipping_tax_refunded'                        => $this->getValue($order, 'shipping_tax_refunded'),
            'store_to_base_rate'                           => $this->getValue($order, 'store_to_base_rate'),
            'store_to_order_rate'                          => $this->getValue($order, 'store_to_order_rate'),
            'subtotal'                                     => $convert['subtotal']['amount'] ? $convert['subtotal']['amount'] : 0,
            'subtotal_canceled'                            => $this->getValue($order, 'subtotal_canceled'),
            'subtotal_invoiced'                            => $this->getValue($order, 'subtotal_invoiced'),
            'subtotal_refunded'                            => $this->getValue($order, 'subtotal_refunded'),
            'tax_amount'                                   => $convert['tax']['amount'] ? $convert['tax']['amount'] : null,
            'tax_canceled'                                 => $this->getValue($order, 'tax_canceled'),
            'tax_invoiced'                                 => $this->getValue($order, 'tax_invoiced'),
            'tax_refunded'                                 => $this->getValue($order, 'tax_refunded'),
            'total_canceled'                               => $this->getValue($order, 'total_canceled'),
            'total_invoiced'                               => $this->getValue($order, 'total_invoiced'),
            'total_offline_refunded'                       => $this->getValue($order, 'total_offline_refunded'),
            'total_online_refunded'                        => $this->getValue($order, 'total_online_refunded'),
            'total_paid'                                   => $this->getValue($order, 'total_paid'),
            'total_qty_ordered'                            => $total_qty_ordered ? $total_qty_ordered : null,
            'total_refunded'                               => $this->getValue($order, 'total_refunded'),
            'can_ship_partially'                           => $this->getValue($order, 'can_ship_partially'),
            'can_ship_partially_item'                      => $this->getValue($order, 'can_ship_partially_item'),
            'customer_is_guest'                            => $this->getValue($order, 'customer_is_guest'),
            'customer_note_notify'                         => $this->getValue($order, 'customer_note_notify'),
            'billing_address_id'                           => $this->getValue($order, 'billing_address_id'),
            'customer_group_id'                            => $customer_group,
            'edit_increment'                               => $this->getValue($order, 'edit_increment'),
            'email_sent'                                   => $this->getValue($order, 'email_sent'),
            'send_email'                                   => $this->getValue($order, 'send_email'),
            'forced_shipment_with_invoice'                 => $this->getValue($order, 'forced_shipment_with_invoice'),
            'payment_auth_expiration'                      => $this->getValue($order, 'payment_auth_expiration'),
            'quote_address_id'                             => null,
            'quote_id'                                     => null,
            'shipping_address_id'                          => null,
            'adjustment_negative'                          => $this->getValue($order, 'adjustment_negative'),
            'adjustment_positive'                          => $this->getValue($order, 'adjustment_positive'),
            'base_adjustment_negative'                     => $this->getValue($order, 'base_adjustment_negative'),
            'base_adjustment_positive'                     => $this->getValue($order, 'base_adjustment_positive'),
            'base_shipping_discount_amount'                => $this->getValue($order, 'base_shipping_discount_amount', '0.0000'),
            'base_subtotal_incl_tax'                       => $this->getValue($order, 'base_subtotal_incl_tax', '0.0000'),
            'base_total_due'                               => $convert['total']['amount'] ? $convert['total']['amount'] : null,
            'payment_authorization_amount'                 => $this->getValue($order, 'payment_authorization_amount'),
            'shipping_discount_amount'                     => $this->getValue($order, 'shipping_discount_amount', '0.0000'),
            'subtotal_incl_tax'                            => $this->getValue($order, 'subtotal_incl_tax', '0.0000'),
            'total_due'                                    => $convert['total']['amount'] ? $convert['total']['amount'] : null,
            'weight'                                       => $this->getValue($order, 'weight'),
            'customer_dob'                                 => $this->getValue($order, 'customer_dob'),
            'increment_id'                                 => null,
            'applied_rule_ids'                             => $this->getValue($order, 'applied_rule_ids'),
            'base_currency_code'                           => $this->getValue($order, 'base_currency_code', 'USD'),
            'customer_email'                               => $convert['customer']['email'],
            'customer_firstname'                           => $convert['customer']['first_name'],
            'customer_lastname'                            => $convert['customer']['last_name'],
            'customer_middlename'                          => $convert['customer']['middle_name'],
            'customer_prefix'                              => $this->getValue($order, 'customer_prefix'),
            'customer_suffix'                              => $this->getValue($order, 'customer_suffix'),
            'customer_taxvat'                              => $this->getValue($order, 'customer_taxvat'),
            'discount_description'                         => $this->getValue($order, 'discount_description'),
            'ext_customer_id'                              => $ext_customer_id,
            'ext_order_id'                                 => $ext_order_id,
            'global_currency_code'                         => $this->getValue($order, 'global_currency_code'),
            'hold_before_state'                            => $this->getValue($order, 'hold_before_state'),
            'hold_before_status'                           => $this->getValue($order, 'hold_before_status'),
            'order_currency_code'                          => $this->getValue($order, 'order_currency_code'),
            'original_increment_id'                        => $this->getValue($order, 'original_increment_id'),
            'relation_child_id'                            => null,
            'relation_child_real_id'                       => null,
            'relation_parent_id'                           => null,
            'remote_ip'                                    => $this->getValue($order, 'remote_ip'),
            'shipping_method'                              => $this->getValue($order, 'shipping_method'),
            'store_currency_code'                          => $this->getValue($order, 'store_currency_code'),
            'store_name'                                   => $store_name ? $store_name : null,
            'x_forwarded_for'                              => $this->getValue($order, 'x_forwarded_for'),
            'customer_note'                                => $this->getValue($order, 'customer_note'),
            'created_at'                                   => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
            'updated_at'                                   => $this->getValue($convert, 'updated_at', date("Y-m-d h:i:s")),
            'total_item_count'                             => count($convert['items']),
            'customer_gender'                              => $this->getValue($order, 'customer_gender'),
            'discount_tax_compensation_amount'             => $this->getValue($order, 'discount_tax_compensation_amount'),
            'base_discount_tax_compensation_amount'        => $this->getValue($order, 'base_discount_tax_compensation_amount'),
            'shipping_discount_tax_compensation_amount'    => $this->getValue($order, 'shipping_discount_tax_compensation_amount'),
            'base_shipping_discount_tax_compensation_amnt' => $this->getValue($order, 'base_shipping_discount_tax_compensation_amnt'),
            'discount_tax_compensation_invoiced'           => $this->getValue($order, 'discount_tax_compensation_invoiced'),
            'base_discount_tax_compensation_invoiced'      => $this->getValue($order, 'base_discount_tax_compensation_invoiced'),
            'discount_tax_compensation_refunded'           => $this->getValue($order, 'discount_tax_compensation_refunded'),
            'base_discount_tax_compensation_refunded'      => $this->getValue($order, 'base_discount_tax_compensation_refunded'),
            'shipping_incl_tax'                            => $this->getValue($order, 'shipping_incl_tax', '0.0000'),
            'base_shipping_incl_tax'                       => $this->getValue($order, 'base_shipping_incl_tax', '0.0000'),
            'coupon_rule_name'                             => $this->getValue($order, 'coupon_rule_name'),
            'gift_message_id'                              => $this->getValue($order, 'gift_message_id'),
            'paypal_ipn_customer_notified'                 => $this->getValue($order, 'paypal_ipn_customer_notified'),
        );

        if ($this->_notice['config']['pre_ord']) {
            $order_delete                   = $this->deleteTargetOrder($convert['id']);
            $order_entity_data['entity_id'] = $convert['id'];
        }


        $order_entity_query = "INSERT INTO _DBPRF_sales_order ";
        $order_entity_query .= $this->arrayToInsertCondition($order_entity_data);

        $order_entity_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $order_entity_query,
                'params' => array(
                    'insert_id' => true,
                )
            )),
        ));

//         if(!$order_entity_import || $order_entity_import['result'] != 'success'){
//             $response['result'] = 'warning';
//             $response['msg'] = $this->warningImportEntity('Order', $convert['id'], $convert['code']);
//             return $response;
//         }
//         $order_entity_id = $order_entity_import['data'];
//
//         if(!$order_entity_id){
//             $response['result'] = 'warning';
//             $response['msg'] = $this->warningImportEntity('Order', $convert['id'], $convert['code']);
//             return $response;
//         }
        if (!$order_entity_import) {
            //warning
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($order_entity_query);
                var_dump(123);
                exit;
            }

            return $this->errorConnector();
        }

        if ($order_entity_import['result'] != 'success') {
            //warning
            return $this->warningSQL($order_entity_import);
        }

        $order_entity_id = $order_entity_import['data'];
        if (!$order_entity_id) {
            // warning
            $response['result'] = 'warning';
            $response['msg']    = $this->warningImportEntity('Order', $convert['id'], $convert['code']);

            return $response;
        }

        $this->insertMap($url_src, $url_desc, self::TYPE_ORDER, $convert['id'], $order_entity_id, $convert['code']);


        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $order_entity_id,
        );
    }

    /**
     * @param $order_id
     * @param $convert
     * @param $order
     * @param $ordersExt
     * @return array
     */
    public function afterOrderImport($order_id, $convert, $order, $ordersExt)
    {

        $order                  = $this->getValue($convert, 'order', $order);
        $url_src                = $this->_notice['src']['cart_url'];
        $url_desc               = $this->_notice['target']['cart_url'];
        $order_increment_id     = null;
        $new_order_increment_id = '' . $order_id;
        while (strlen($new_order_increment_id) < self::LEN_INCREMENT) {
            $new_order_increment_id = '0' . $new_order_increment_id;
        }
        $new_order_increment_id = '1' . $new_order_increment_id;
        $old_order_increment_id = $this->getValue($convert, 'increment_id', $new_order_increment_id);

        if ($this->_notice['config']['pre_ord']) {
            $order_increment_id = $old_order_increment_id;
        } else {
            $order_increment_id = $new_order_increment_id;
        }
        $url_query     = $this->getConnectorUrl('query');
        $address_query = array();
        $all_query     = array();

        $customer         = $convert['customer'];
        $customer_address = $convert['customer_address'];
        $billing_address  = $convert['billing_address'];
        $shipping_address = $convert['shipping_address'];
        $payment          = $convert['payment'];
        $items_order      = $convert['items'];

        $customer_id = null;
        if ($convert['customer']['id']) {
            $customer_id = $this->getMapFieldBySource(self::TYPE_CUSTOMER, $convert['customer']['id'], $convert['customer']['code']);
        }
        if (!$customer_id) {
            $customer_id = null;
        }
        $order_status = 'canceled';

        if (isset($this->_notice['map']['order_status'][$convert['status']]) && $this->_notice['map']['order_status'][$convert['status']] != null) {
            $order_status = $this->_notice['map']['order_status'][$convert['status']];
        }
        $customer_group     = $this->getValue($this->_notice['map']['customer_group'], $customer['group_id'], 0);
        $store_id           = $this->getMapStoreView($this->getValue($order, 'store_id', '-1'));
        $store_name         = $this->getNameStoreView($store_id);
        $customer_fullname  = $this->getValue($customer, 'first_name', '') . $this->getValue($customer, 'middle_name', ' ') . $this->getValue($customer, 'last_name');
        $customer_fullname  = ($customer_fullname && strlen($customer_fullname)) ? $customer_fullname : null;
        $billing_full_name  = $this->getValue($billing_address, 'first_name', '') . $this->getValue($billing_address, 'middle_name', ' ') . $this->getValue($billing_address, 'last_name', '');
        $billing_full_name  = ($billing_full_name && strlen($billing_full_name)) ? $billing_full_name : null;
        $shipping_full_name = $this->getValue($shipping_address, 'first_name', '') . $this->getValue($shipping_address, 'middle_name', ' ') . $this->getValue($shipping_address, 'last_name', '');
        $shipping_full_name = ($shipping_full_name && strlen($shipping_full_name)) ? $shipping_full_name : null;

        //sales_order_grid
        $sales_order_grid_data = array(
            'entity_id'             => $order_id,
            'status'                => $order_status,
            'store_id'              => $store_id,
            'store_name'            => $store_name,
            'customer_id'           => $customer_id,
            'base_grand_total'      => $convert['total']['amount'],
            'base_total_paid'       => null,
            'grand_total'           => $convert['total']['amount'],
            'total_paid'            => null,
            'increment_id'          => $order_increment_id,
            'base_currency_code'    => $this->getValue($order, 'base_currency_code', 'USD'),
            'order_currency_code'   => $this->getValue($convert, 'currency', 'USD'),
            'shipping_name'         => $shipping_full_name,
            'billing_name'          => $billing_full_name,
            'created_at'            => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
            'updated_at'            => $this->getValue($convert, 'updated_at', date("Y-m-d h:i:s")),
            'billing_address'       => $billing_address['address_1'],
            'shipping_address'      => $shipping_address['address_1'],
            'shipping_information'  => $convert['shipping']['title'],
            'customer_email'        => $customer['email'],
            'customer_group'        => $customer_group,
            'subtotal'              => $convert['subtotal']['amount'],
            'shipping_and_handling' => null,
            'customer_name'         => $customer_fullname,
            'payment_method'        => $this->getValue($convert, 'payment_method', 'cashondelivery'),
            'total_refunded'        => null,
        );

        $sales_order_grid_query = $this->createInsertQuery('sales_order_grid',$sales_order_grid_data);


        if (Bootstrap::getConfig('dev_mode')) {
            $sales_order_grid_import = $this->importOrderData($sales_order_grid_query);

            if (!$sales_order_grid_import) {
                Bootstrap::logQuery($sales_order_grid_query);
                var_dump(123);
                exit;

                //warning
            }

        } else {
            $all_query[] = $sales_order_grid_query;
        }



        //sales_order_address

        $sales_order_address_billing_data = array(
            'parent_id'           => $order_id,
            'customer_address_id' => null,
            'quote_address_id'    => null,
            'region_id'           => null,
            'customer_id'         => $customer_id,
            'fax'                 => $billing_address['fax'],
            'region'              => $billing_address['state']['name'],
            'postcode'            => $billing_address['postcode'],
            'lastname'            => $billing_address['last_name'],
            'street'              => $billing_address['address_1'],
            'city'                => $billing_address['city'],
            'email'               => $customer['email'],
            'telephone'           => $billing_address['telephone'],
            'country_id'          => $billing_address['country']['country_code'] ? $billing_address['country']['country_code'] : 'US',
            'firstname'           => $billing_address['first_name'],
            'address_type'        => 'billing',
            'prefix'              => null,
            'middlename'          => $billing_address['middle_name'],
            'suffix'              => null,
            'company'             => $billing_address['company'],
            'vat_id'              => null,
            'vat_is_valid'        => null,
            'vat_request_id'      => null,
            'vat_request_date'    => null,
            'vat_request_success' => null,
        );

        $sales_order_address_shipping_data = array(
            'parent_id'           => $order_id,
            'customer_address_id' => null,
            'quote_address_id'    => null,
            'region_id'           => null,
            'customer_id'         => $customer_id,
            'fax'                 => $shipping_address['fax'],
            'region'              => $shipping_address['state']['name'],
            'postcode'            => $shipping_address['postcode'],
            'lastname'            => $shipping_address['last_name'],
            'street'              => $shipping_address['address_1'],
            'city'                => $shipping_address['city'],
            'email'               => $customer['email'],
            'telephone'           => $shipping_address['telephone'],
            'country_id'          => $shipping_address['country']['country_code'] ? $shipping_address['country']['country_code'] : 'US',
            'firstname'           => $shipping_address['first_name'],
            'address_type'        => 'shipping',
            'prefix'              => null,
            'middlename'          => $shipping_address['middle_name'],
            'suffix'              => null,
            'company'             => $shipping_address['company'],
            'vat_id'              => null,
            'vat_is_valid'        => null,
            'vat_request_id'      => null,
            'vat_request_date'    => null,
            'vat_request_success' => null,
        );

        $sales_order_address_billing_query = $this->createInsertQuery('sales_order_address',$sales_order_address_billing_data);


        $sales_order_address_shipping_query = $this->createInsertQuery('sales_order_address',$sales_order_address_shipping_data);

        $sales_order_address_shipping_query = str_replace(' ,', " '',", $sales_order_address_shipping_query);


        $address_query['sales_order_address_shipping_query'] = array(
            'type'   => 'insert',
            'query'  => $sales_order_address_shipping_query,
            'params' => array(
                'insert_id' => true,
            )
        );

        $sales_address_shipping_id = $this->importOrderData($sales_order_address_shipping_query);
        $sales_address_billing_id  = $this->importOrderData($sales_order_address_billing_query);

        $sales_address_shipping_id = $sales_address_shipping_id?$sales_address_shipping_id:null;
        $sales_address_billing_id = $sales_address_billing_id?$sales_address_billing_id:null;

        //sales_order_payment
        $payment                  = isset($convert['payment'][0]) ? $convert['payment'][0] : array();
        $sales_order_payment_data = array(
            'parent_id'                    => $order_id,
            'base_shipping_captured'       => $this->getValue($payment, 'base_shipping_captured'),
            'shipping_captured'            => $this->getValue($payment, 'shipping_captured'),
            'amount_refunded'              => $this->getValue($payment, 'amount_refunded'),
            'base_amount_paid'             => $this->getValue($payment, 'base_amount_paid'),
            'amount_canceled'              => $this->getValue($payment, 'amount_canceled'),
            'base_amount_authorized'       => $this->getValue($payment, 'base_amount_authorized'),
            'base_amount_paid_online'      => $this->getValue($payment, 'base_amount_paid_online'),
            'base_amount_refunded_online'  => $this->getValue($payment, 'base_amount_refunded_online'),
            'base_shipping_amount'         => $this->getValue($payment, 'base_shipping_amount'),
            'shipping_amount'              => $this->getValue($payment, 'shipping_amount'),
            'amount_paid'                  => $this->getValue($payment, 'amount_paid'),
            'amount_authorized'            => $this->getValue($payment, 'amount_authorized'),
            'base_amount_ordered'          => $this->getValue($payment, 'base_amount_ordered', $convert['total']['amount']),
            'base_shipping_refunded'       => $this->getValue($payment, 'base_shipping_refunded'),
            'shipping_refunded'            => $this->getValue($payment, 'shipping_refunded'),
            'base_amount_refunded'         => $this->getValue($payment, 'base_amount_refunded'),
            'amount_ordered'               => $this->getValue($payment, 'base_amount_ordered', $convert['total']['amount']),
            'base_amount_canceled'         => $this->getValue($payment, 'base_amount_canceled'),
            'quote_payment_id'             => null,
            'additional_data'              => null,
            'cc_exp_month'                 => $this->getValue($payment, 'cc_exp_month'),
            'cc_ss_start_year'             => $this->getValue($payment, 'cc_ss_start_year'),
            'echeck_bank_name'             => $this->getValue($payment, 'echeck_bank_name'),
            'method'                       => $this->getValue($convert, 'payment_method', 'cashondelivery'),
            'cc_debug_request_body'        => null,
            'cc_secure_verify'             => null,
            'protection_eligibility'       => null,
            'cc_approval'                  => null,
            'cc_last_4'                    => null,
            'cc_status_description'        => null,
            'echeck_type'                  => null,
            'cc_debug_response_serialized' => null,
            'cc_ss_start_month'            => '0',
            'echeck_account_type'          => null,
            'last_trans_id'             => $this->getValue($payment, 'last_trans_id',''),

            'cc_cid_status'                => null,
            'cc_owner'                     => null,
            'cc_type'                      => null,
            'po_number'                    => null,
            'cc_exp_year'                  => '0',
            'cc_status'                    => null,
            'echeck_routing_number'        => null,
            'account_status'               => null,
            'anet_trans_method'            => null,
            'cc_debug_response_body'       => null,
            'cc_ss_issue'                  => null,
            'echeck_account_name'          => null,
            'cc_avs_status'                => null,
            'cc_number_enc'                => null,
            'cc_trans_id'                  => null,
            'address_status'               => null,
            'additional_information'       => null,
        );

        $sales_order_payment_query = $this->createInsertQuery('sales_order_payment',$sales_order_payment_data);



        if (Bootstrap::getConfig('dev_mode')) {
            $sales_order_payment_import = $this->importOrderData($sales_order_payment_query);

            if (!$sales_order_payment_import) {
                Bootstrap::logQuery($sales_order_payment_query);
                var_dump($sales_order_payment_query);
                exit;

                //warning
            }

        } else {
            $all_query[] = $sales_order_payment_query;
        }

        //sales_order_status_history
        foreach ($convert['histories'] as $key => $item) {
            $sales_order_status_history_data = array(
                'parent_id'            => $order_id,
                'is_customer_notified' => 1,
                'is_visible_on_front'  => 0,
                'comment'              => $item['comment'] ? $item['comment'] : '',
                'status'               => $item['status'] ? $item['status'] : 0,
                'created_at'           => $item['created_at'] ? $item['created_at'] : '',
                'entity_name'          => 'order',
            );

            $sales_order_status_history_query = $this->createInsertQuery('sales_order_status_history',$sales_order_status_history_data);

            if (Bootstrap::getConfig('dev_mode')) {
                $sales_order_status_history_import = $this->importOrderData($sales_order_status_history_query);

                if (!$sales_order_status_history_import) {
                    Bootstrap::logQuery($sales_order_status_history_query);
                    var_dump($sales_order_status_history_query);
                    exit;
                }


            } else {
                $all_query[] = $sales_order_status_history_query;
            }

        }
        $items_query  = array();
        $items_id_src = array();
        foreach ($items_order as $key => $value) {
            $product_id = $this->getMapFieldBySource(self::TYPE_PRODUCT, $value['product']['id'], $value['product']['code']);
            if (!$product_id) {
                $product_id = null;
            }
            $product_options = '';
            $options = $this->getValue($value,'options');
            if($options){
                $product_options = array();
                if(isset($options['options'])){
                    $productOptions = array();
                    foreach ($options['options'] as $option){
                        $item_option = array(
                            'label' => $option['option_name'],
                            'value' => $option['option_value_name'],
                        );
                        $productOptions[] = $item_option;
                    }
                    $product_options['options'] = $productOptions;
                }
                if(isset($options['attributes_info'])){
                    $productOptions = array();
                    foreach ($options['attributes_info'] as $attributes_info){
                        $item_option = array(
                            'label' => $attributes_info['option_name'],
                            'value' => $attributes_info['option_value_name'],
                        );
                        $productOptions[] = $item_option;
                    }
                    $product_options['attributes_info'] = $productOptions;

                }
                $product_options = $this->mySerialize($product_options);
            }
            $sales_order_item_data = array(
                'order_id'                                => $order_id,
                'parent_item_id'                          => null,
                'quote_item_id'                           => null,
                'store_id'                                => $store_id,
                'created_at'                              => $this->getValue($value, 'created_at', date("Y-m-d h:i:s")),
                'updated_at'                              => $this->getValue($value, 'updated_at', date("Y-m-d h:i:s")),
                'product_id'                              => $product_id,
                'product_type'                            => $this->getValue($value, 'product_type'),
                'product_options'                         => $product_options,
                'weight'                                  => $this->getValue($value, 'weight'),
                'is_virtual'                              => $this->getValue($value, 'is_virtual'),
                'sku'                                     => $this->getValue($this->getValue($value, 'product', array()), 'sku'),
                'name'                                    => $this->getValue($this->getValue($value, 'product', array()), 'name'),
                'description'                             => $this->getValue($value, 'description',''),
                'applied_rule_ids'                        => '',
                'additional_data'                         => '',
                'is_qty_decimal'                          => $this->getValue($value, 'is_qty_decimal'),
                'no_discount'                             => $this->getValue($value, 'no_discount', 0),
                'qty_backordered'                         => $this->getValue($value, 'qty_backordered'),
                'qty_canceled'                            => $this->getValue($value, 'qty_canceled'),
                'qty_invoiced'                            => $this->getValue($value, 'qty_invoiced'),
                'qty_ordered'                             => $this->getValue($value, 'qty_ordered'),
                'qty_refunded'                            => $this->getValue($value, 'qty_refunded'),
                'qty_shipped'                             => $this->getValue($value, 'qty_shipped'),
                'base_cost'                               => $this->getValue($value, 'base_cost'),
                'price'                                   => $this->getValue($value, 'price', '0.0000'),
                'base_price'                              => $this->getValue($value, 'price', '0.0000'),
                'original_price'                          => $this->getValue($value, 'original_price', '0.0000'),
                'base_original_price'                     => $this->getValue($value, 'original_price', '0.0000'),
                'tax_percent'                             => $this->getValue($value, 'tax_percent', 0),
                'tax_amount'                              => $this->getValue($value, 'tax_amount', 0),
                'base_tax_amount'                         => $this->getValue($value, 'tax_amount', 0),
                'tax_invoiced'                            => $this->getValue($value, 'tax_invoiced', 0),
                'base_tax_invoiced'                       => $this->getValue($value, 'tax_invoiced', 0),
                'discount_percent'                        => $this->getValue($value, 'discount_percent', 0),
                'discount_amount'                         => $this->getValue($value, 'discount_amount', 0),
                'base_discount_amount'                    => $this->getValue($value, 'base_discount_amount', 0),
                'discount_invoiced'                       => $this->getValue($value, 'discount_invoiced', 0),
                'base_discount_invoiced'                  => $this->getValue($value, 'discount_invoiced', 0),
                'amount_refunded'                         => '0.0000',
                'base_amount_refunded'                    => '0.0000',
                'row_total'                               => $this->getValue($value, 'row_total', 0),
                'base_row_total'                          => $this->getValue($value, 'base_row_total', 0),
                'row_invoiced'                            => $this->getValue($value, 'row_invoiced', 0),
                'base_row_invoiced'                       => $this->getValue($value, 'row_invoiced', 0),
                'row_weight'                              => $this->getValue($value, 'row_weight', 0),
                'base_tax_before_discount'                => $this->getValue($value, 'base_tax_before_discount', 0),
                'tax_before_discount'                     => $this->getValue($value, 'tax_before_discount', 0),
                'ext_order_item_id'                       => '',
                'locked_do_invoice'                       => $value['locked_do_invoice'],
                'locked_do_ship'                          => $value['locked_do_ship'],
                'price_incl_tax'                          => $this->getValue($value, 'price_incl_tax', 0),
                'base_price_incl_tax'                     => $this->getValue($value, 'price_incl_tax', 0),
                'row_total_incl_tax'                      => $this->getValue($value, 'row_total_incl_tax', 0),
                'base_row_total_incl_tax'                 => $this->getValue($value, 'row_total_incl_tax', 0),
                'discount_tax_compensation_amount'        => '0.0000',
                'base_discount_tax_compensation_amount'   => '0.0000',
                'discount_tax_compensation_invoiced'      => '0.0000',
                'base_discount_tax_compensation_invoiced' => '0.0000',
                'discount_tax_compensation_refunded'      => '0.0000',
                'base_discount_tax_compensation_refunded' => '0.0000',
                'tax_canceled'                            => $this->getValue($value, 'tax_canceled', 0),
                'discount_tax_compensation_canceled'      => '0.0000',
                'tax_refunded'                            => $this->getValue($value, 'tax_refunded', 0),
                'base_tax_refunded'                       => $this->getValue($value, 'tax_refunded', 0),
                'discount_refunded'                       => $this->getValue($value, 'discount_refunded', 0),
                'base_discount_refunded'                  => $this->getValue($value, 'discount_refunded', 0),
                'free_shipping'                           => $this->getValue($value, 'free_shipping', 0),
                'gift_message_id'                         => null,
                'gift_message_available'                  => null,
                'weee_tax_applied'                        => null,
                'weee_tax_applied_amount'                 => null,
                'weee_tax_applied_row_amount'             => null,
                'weee_tax_disposition'                    => null,
                'weee_tax_row_disposition'                => null,
                'base_weee_tax_applied_amount'            => null,
                'base_weee_tax_applied_row_amnt'          => null,
                'base_weee_tax_disposition'               => null,
                'base_weee_tax_row_disposition'           => null,
            );

            $sales_order_item_query = "INSERT INTO _DBPRF_sales_order_item ";
            $sales_order_item_query .= $this->arrayToInsertCondition($sales_order_item_data);
            /**
             * Start Namlv
             */

            if (Bootstrap::getConfig('dev_mode')) {
                $sales_order_item_import = $this->getConnectorData($url_query, array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $sales_order_item_query,
                        'params' => array(
                            'insert_id' => true,
                        )
                    )),
                ));

                if (!$sales_order_item_import) {
                    Bootstrap::logQuery($sales_order_item_query);
                    var_dump($sales_order_item_query);
                    exit;

                    //warning
                }
                $item_id = $sales_order_item_import['data'];
                if (!$item_id) {
                    Bootstrap::logQuery($sales_order_item_query);
                    var_dump($sales_order_item_query);
                    exit;

                    return $this->warningSQL($sales_order_item_import);
                }
                $this->insertMap($url_src, $url_desc, self::TYPE_ORDER_ITEM, $value['id'], $item_id);
            } else {
                $items_query['sales_order_item_query_' . $key]  = array(
                    'type'   => 'insert',
                    'query'  => $sales_order_item_query,
                    'params' => array(
                        'insert_id' => true,
                    ),
                );
                $items_id_src['sales_order_item_query_' . $key] = $value['id'];
            }

        }
        if (!Bootstrap::getConfig('dev_mode')) {
            $items_query_import = $this->getConnectorData($url_query, array(
                "serialize" => true,
                'query'     => serialize($items_query),
            ));
            if (!$items_query_import || $items_query_import['result'] != 'success') {
                Bootstrap::log($items_query,'order');
            }
            foreach ($items_query_import['data'] as $key => $item_id) {
                if (!$item_id) {
                    Bootstrap::log($items_query[$key]['query'],'order');
                } else {
                    $this->insertMap($url_src, $url_desc, self::TYPE_ORDER_ITEM, $items_id_src[$key], $item_id);
                }
            }
        }

        if (isset($convert['order_child_item'])) {
            foreach ($convert['order_child_item'] as $key => $value) {
                $parent_id = $this->getMapFieldBySource(self::TYPE_ORDER_ITEM, $value['parent_id']);
                $child_id  = $this->getMapFieldBySource(self::TYPE_ORDER_ITEM, $value['children_id']);
                $update_order_item_query = $this->createUpdateQuery('sales_order_item',array('parent_item_id' => $parent_id),array('item_id' => $child_id));
                if (Bootstrap::getConfig('dev_mode')) {
                    $order_item_update = $this->importOrderData($update_order_item_query);
                    if (!$order_item_update) {
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $update_order_item_query;
                }
            }
        }


        //begin invoice

        if (isset($convert['invoice'])) {

            $invoice            = $convert['invoice'];
            $invoice_data       = $convert['invoice']['data'];
            $sales_invoice_data = array(
                'store_id'                                     => $store_id,
                'base_grand_total'                             => $this->getValue($invoice_data, 'base_grand_total'),
                'shipping_tax_amount'                          => $this->getValue($invoice_data, 'shipping_tax_amount'),
                'tax_amount'                                   => $this->getValue($invoice_data, 'tax_amount'),
                'base_tax_amount'                              => $this->getValue($invoice_data, 'base_tax_amount'),
                'store_to_order_rate'                          => $this->getValue($invoice_data, 'store_to_order_rate'),
                'grand_total'                                  => $this->getValue($invoice_data, 'grand_total'),
                'shipping_amount'                              => $this->getValue($invoice_data, 'shipping_amount'),
                'subtotal_incl_tax'                            => $this->getValue($invoice_data, 'subtotal_incl_tax'),
                'base_subtotal_incl_tax'                       => $this->getValue($invoice_data, 'base_subtotal_incl_tax'),
                'store_to_base_rate'                           => $this->getValue($invoice_data, 'store_to_base_rate'),
                'total_qty'                                    => $this->getValue($invoice_data, 'total_qty'),
                'base_to_global_rate'                          => $this->getValue($invoice_data, 'base_to_global_rate'),
                'subtotal'                                     => $this->getValue($invoice_data, 'subtotal'),
                'base_subtotal'                                => $this->getValue($invoice_data, 'base_subtotal'),
                'discount_amount'                              => $this->getValue($invoice_data, 'discount_amount'),
                'billing_address_id'                           => $sales_address_billing_id,
                'is_used_for_refund'                           => $this->getValue($invoice_data, 'is_used_for_refund'),
                'order_id'                                     => $order_id,
                'email_sent'                                   => $this->getValue($invoice_data, 'email_sent'),
                'send_email'                                   => $this->getValue($invoice_data, 'send_email'),
                'can_void_flag'                                => $this->getValue($invoice_data, 'can_void_flag'),
                'state'                                        => $this->getValue($invoice_data, 'state'),
                'shipping_address_id'                          => $sales_address_shipping_id,
                'store_currency_code'                          => $this->getValue($invoice_data, 'store_currency_code'),
                'transaction_id'                               => $this->getValue($invoice_data, 'transaction_id'),
                'order_currency_code'                          => $this->getValue($invoice_data, 'order_currency_code'),
                'base_currency_code'                           => $this->getValue($invoice_data, 'base_currency_code'),
                'global_currency_code'                         => $this->getValue($invoice_data, 'global_currency_code'),
                'increment_id'                                 => null,
                'created_at'                                   => $this->getValue($invoice_data, 'created_at', date("Y-m-d h:i:s")),
                'updated_at'                                   => $this->getValue($invoice_data, 'updated_at', date("Y-m-d h:i:s")),
                'discount_tax_compensation_amount'             => null,
                'base_discount_tax_compensation_amount'        => null,
                'shipping_discount_tax_compensation_amount'    => null,
                'base_shipping_discount_tax_compensation_amnt' => null,
                'shipping_incl_tax'                            => $this->getValue($invoice_data, 'shipping_incl_tax'),
                'base_shipping_incl_tax'                       => $this->getValue($invoice_data, 'base_shipping_incl_tax'),
                'base_total_refunded'                          => $this->getValue($order, 'base_total_refunded', null),
                'discount_description'                         => $this->getValue($invoice_data, 'discount_description'),
                'customer_note'                                => null,
                'customer_note_notify'                         => null,
            );


            $sales_invoice_query  = $this->createInsertQuery('sales_invoice',$sales_invoice_data);
            $sales_invoice_id = $this->importOrderData($sales_invoice_query);
            if($sales_invoice_id){
                $increment_id_invoice     = null;
                $new_increment_id_invoice = '' . $sales_invoice_id;
                while (strlen($new_increment_id_invoice) < self::LEN_INCREMENT) {
                    $new_increment_id_invoice = '0' . $new_increment_id_invoice;
                }
                $new_increment_id_invoice = '1' . $new_increment_id_invoice;
                $old_increment_id_invoice = $this->getValue($invoice_data, 'increment_id', $new_increment_id_invoice);

                if ($this->_notice['config']['pre_ord']) {
                    $increment_id_invoice = $old_increment_id_invoice;
                } else {
                    $increment_id_invoice = $new_increment_id_invoice;
                }
                $sales_invoice_update_query = $this->createUpdateQuery('sales_invoice',array('increment_id' => $increment_id_invoice),array('entity_id' => $sales_invoice_id));

                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_invoice_update = $this->importOrderData($sales_invoice_update_query);
                    if (!$sales_invoice_update) {
                        Bootstrap::logQuery($sales_invoice_update_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $sales_invoice_update_query;
                }
                $sales_invoice_grid_data = array(
                    'entity_id'             => $sales_invoice_id,
                    'increment_id'          => $increment_id_invoice,
                    'state'                 => $this->getValue($invoice_data, 'state'),
                    'store_id'              => $store_id,
                    'store_name'            => $store_name,
                    'order_id'              => $order_id,
                    'order_increment_id'    => $order_increment_id,
                    'order_created_at'      => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
                    'customer_name'         => $customer_fullname,
                    'customer_email'        => $customer['email'],
                    'customer_group_id'     => $customer_group,
                    'payment_method'        => $this->getValue($convert, 'payment_method', 'cashondelivery'),
                    'store_currency_code'   => $this->getValue($invoice_data, 'store_currency_code'),
                    'order_currency_code'   => $this->getValue($invoice_data, 'order_currency_code'),
                    'base_currency_code'    => $this->getValue($invoice_data, 'base_currency_code'),
                    'global_currency_code'  => $this->getValue($invoice_data, 'global_currency_code'),
                    'billing_name'          => $billing_full_name,
                    'billing_address'       => $this->getValue($billing_address, 'address_1', $this->getValue($billing_address, 'address_2')),
                    'shipping_address'      => $this->getValue($shipping_address, 'address_1', $this->getValue($shipping_address, 'address_2')),
                    'shipping_information'  => null,
                    'subtotal'              => $this->getValue($invoice_data, 'subtotal'),
                    'shipping_and_handling' => null,
                    'grand_total'           => $this->getValue($invoice_data, 'grand_total'),
                    'base_grand_total'      => $this->getValue($invoice_data, 'base_grand_total'),
                    'created_at'            => $this->getValue($invoice_data, 'created_at', date("Y-m-d h:i:s")),
                    'updated_at'            => $this->getValue($invoice_data, 'updated_at', date("Y-m-d h:i:s")),

                );

                $sales_invoice_grid_query = $this->createInsertQuery('sales_invoice_grid',$sales_invoice_grid_data);

                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_invoice_grid_import = $this->importOrderData($sales_invoice_grid_query);
                    if (!$sales_invoice_grid_import) {
                        Bootstrap::logQuery($sales_invoice_grid_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $sales_invoice_grid_query;
                }

                $invoce_item = $this->getValue($invoice, 'item');
                if ($invoce_item && count($invoce_item) > 0) {
                    foreach ($invoce_item as $key => $item) {
                        $order_item_id_src = $this->getValue($item, 'order_item_id');
                        $order_item_id     = $this->getMapFieldBySource(self::TYPE_ORDER_ITEM, $order_item_id_src);
                        $product_id        = $this->getMapFieldBySource(self::TYPE_PRODUCT, $this->getValue($item, 'product_id',-1));
                        if (!$product_id) {
                            $product_id = null;
                        }
                        $sales_invoice_item_data  = array(
                            'parent_id'                             => $sales_invoice_id,
                            'base_price'                            => $this->getValue($item, 'base_price'),
                            'tax_amount'                            => $this->getValue($item, 'tax_amount'),
                            'base_row_total'                        => $this->getValue($item, 'base_row_total'),
                            'discount_amount'                       => $this->getValue($item, 'discount_amount'),
                            'row_total'                             => $this->getValue($item, 'row_total'),
                            'base_discount_amount'                  => $this->getValue($item, 'base_discount_amount'),
                            'price_incl_tax'                        => $this->getValue($item, 'price_incl_tax'),
                            'base_tax_amount'                       => $this->getValue($item, 'base_tax_amount'),
                            'base_price_incl_tax'                   => $this->getValue($item, 'base_price_incl_tax'),
                            'qty'                                   => $this->getValue($item, 'qty'),
                            'base_cost'                             => $this->getValue($item, 'base_cost'),
                            'price'                                 => $this->getValue($item, 'price'),
                            'base_row_total_incl_tax'               => $this->getValue($item, 'base_row_total_incl_tax'),
                            'row_total_incl_tax'                    => $this->getValue($item, 'row_total_incl_tax '),
                            'product_id'                            => $product_id,
                            'order_item_id'                         => $order_item_id,
                            'additional_data'                       => $this->getValue($item, 'additional_data'),
                            'description'                           => $this->getValue($item, 'description'),
                            'sku'                                   => $this->getValue($item, 'sku'),
                            'name'                                  => $this->getValue($item, 'name'),
                            'discount_tax_compensation_amount'      => null,
                            'base_discount_tax_compensation_amount' => null,
                            'tax_ratio'                             => null,
                            'weee_tax_applied'                      => null,
                            'weee_tax_applied_amount'               => null,
                            'weee_tax_applied_row_amount'           => null,
                            'weee_tax_disposition'                  => null,
                            'weee_tax_row_disposition'              => null,
                            'base_weee_tax_applied_amount'          => null,
                            'base_weee_tax_applied_row_amnt'        => null,
                            'base_weee_tax_disposition'             => null,
                            'base_weee_tax_row_disposition'         => null,
                        );
                        $sales_invoice_item_query = $this->createInsertQuery('sales_invoice_item',$sales_invoice_item_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_invoice_item_import = $this->importOrderData($sales_invoice_item_query);
                            if (!$sales_invoice_item_import) {
                                Bootstrap::logQuery($sales_invoice_item_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_invoice_item_query;
                        }
                    }
                }

                $invoice_comment = $this->getValue($invoice, 'comment');
                if ($invoice_comment && count($invoice_comment) > 0) {
                    foreach ($invoice_comment as $key => $item) {
                        $sales_invoice_comment_data  = array(
                            'parent_id'       => $sales_invoice_id,
                            'is_customer_notified'       => $this->getValue($item, 'is_customer_notified'),
                            'is_visible_on_front'           => $this->getValue($item, 'is_visible_on_front',0),
                            'comment'          => $this->getValue($item, 'comment'),
                            'created_at'             => $this->getValue($item, 'created_at',date("Y-m-d h:i:s")),
                        );
                        $sales_invoice_comment_query = $this->createInsertQuery('sales_invoice_comment',$sales_invoice_comment_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_invoice_item_import = $this->importOrderData($sales_invoice_comment_query);
                            if (!$sales_invoice_item_import) {
                                Bootstrap::logQuery($sales_invoice_comment_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_invoice_comment_query;
                        }
                    }
                }
            }

        }

        // invoice finish

//        ------------------------------------------------------------------------------------------
        //begin shipment
        if (isset($convert['shipment'])) {
            $shipment              = $convert['shipment'];
            $shipment_data         = $convert['shipment']['data'];
            $sales_shipment_data   = array(
                'store_id'            => $store_id,
                'total_weight'        => $this->getValue($shipment_data, 'total_weight'),
                'total_qty'           => $this->getValue($shipment_data, 'total_qty'),
                'email_sent'          => $this->getValue($shipment_data, 'email_sent'),
                'send_email'          => $this->getValue($shipment_data, 'send_email'),
                'order_id'            => $order_id,
                'customer_id'         => $customer_id,
                'shipping_address_id' => $sales_address_shipping_id,
                'billing_address_id'  => $sales_address_billing_id,
                'shipment_status'     => $this->getValue($shipment_data, 'shipment_status'),
                'increment_id'        => null,
                'created_at'          => $this->getValue($shipment_data, 'created_at'),
                'updated_at'          => $this->getValue($shipment_data, 'updated_at'),
                'packages'            => $this->getValue($shipment_data, 'packages'),
                'shipping_label'      => $this->getValue($shipment_data, 'shipping_label'),
            );
            $sales_shipment_query  = $this->createInsertQuery('sales_shipment',$sales_shipment_data);
            $sales_shipment_id = $this->importOrderData($sales_shipment_query);

            if($sales_shipment_id){
                $increment_id_shipment     = null;
                $new_increment_id_shipment = '' . $sales_shipment_id;
                while (strlen($new_increment_id_shipment) < self::LEN_INCREMENT) {
                    $new_increment_id_shipment = '0' . $new_increment_id_shipment;
                }
                $new_increment_id_shipment = '1' . $new_increment_id_shipment;
                $old_increment_id_shipment = $this->getValue($shipment_data, 'increment_id', $new_increment_id_shipment);

                if ($this->_notice['config']['pre_ord']) {
                    $increment_id_shipment = $old_increment_id_shipment;
                } else {
                    $increment_id_shipment = $new_increment_id_shipment;
                }
                $sales_shipment_update_query = $this->createUpdateQuery('sales_shipment',array('increment_id' => $increment_id_shipment),array('entity_id' => $sales_shipment_id));


                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_shipment_update = $this->importOrderData($sales_shipment_update_query);
                    if (!$sales_shipment_update) {
                        Bootstrap::logQuery($sales_shipment_update_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $sales_shipment_update_query;
                }

                $sales_shipment_grid_data  = array(
                    'entity_id'            => $sales_shipment_id,
                    'increment_id'         => $increment_id_shipment,
                    'store_id'             => $store_id,
                    'order_increment_id'   => $order_increment_id,
                    'order_id'             => $order_id,
                    'order_created_at'     => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
                    'customer_name'        => $this->getValue($customer, 'first_name', '') . $this->getValue($customer, 'middle_name', ' ') . $this->getValue($customer, 'last_name'),
                    'total_qty'            => $this->getValue($shipment_data, 'total_qty'),
                    'shipment_status'      => $this->getValue($shipment_data, 'shipment_status'),
                    'order_status'         => $order_status,
                    'billing_address'      => $this->getValue($billing_address, 'address_1', $this->getValue($billing_address, 'address_2')),
                    'shipping_address'     => $this->getValue($shipping_address, 'address_1', $this->getValue($shipping_address, 'address_2')),
                    'billing_name'         => $billing_full_name,
                    'shipping_name'        => $shipping_full_name,
                    'customer_email'       => $this->getValue($customer, 'email'),
                    'customer_group_id'    => $customer_group,
                    'payment_method'       => $this->getValue($convert, 'payment_method', 'cashondelivery'),
                    'shipping_information' => null,
                    'created_at'           => $this->getValue($shipment_data, 'created_at'),
                    'updated_at'           => $this->getValue($shipment_data, 'updated_at'),
                );
                $sales_shipment_grid_query = $this->createInsertQuery('sales_shipment_grid',$sales_shipment_grid_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_shipment_grid_import = $this->importOrderData($sales_shipment_grid_query);
                    if (!$sales_shipment_grid_import) {
                        Bootstrap::logQuery($sales_shipment_grid_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $sales_shipment_grid_query;
                }

                $shipment_item = $this->getValue($shipment, 'item');
                if ($shipment_item && count($shipment_item) > 0) {
                    foreach ($shipment_item as $key => $item) {
                        $order_item_id_src = $this->getValue($item, 'order_item_id');
                        $order_item_id     = $this->getMapFieldBySource(self::TYPE_ORDER_ITEM, $order_item_id_src);
                        $product_id        = $this->getMapFieldBySource(self::TYPE_PRODUCT, $this->getValue($item, 'product_id',-1));
                        if (!$product_id) {
                            $product_id = null;
                        }
                        $sales_shipment_item_data  = array(
                            'parent_id'       => $sales_shipment_id,
                            'row_total'       => $this->getValue($item, 'row_total'),
                            'price'           => $this->getValue($item, 'price'),
                            'weight'          => $this->getValue($item, 'weight'),
                            'qty'             => $this->getValue($item, 'qty'),
                            'product_id'      => $product_id,
                            'order_item_id'   => $order_item_id,
                            'additional_data' => $this->getValue($item, 'additional_data'),
                            'description'     => $this->getValue($item, 'description'),
                            'name'            => $this->getValue($item, 'name'),
                            'sku'             => $this->getValue($item, 'sku'),
                        );
                        $sales_shipment_item_query = $this->createInsertQuery('sales_shipment_item',$sales_shipment_item_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_shipment_item_import = $this->importOrderData($sales_shipment_item_query);
                            if (!$sales_shipment_item_import) {
                                Bootstrap::logQuery($sales_shipment_item_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_shipment_item_query;
                        }
                    }
                }

                $shipment_comment = $this->getValue($shipment, 'comment');
                if ($shipment_comment && count($shipment_comment) > 0) {
                    foreach ($shipment_comment as $key => $item) {
                        $sales_shipment_comment_data  = array(
                            'parent_id'       => $sales_shipment_id,
                            'is_customer_notified'       => $this->getValue($item, 'is_customer_notified'),
                            'is_visible_on_front'           => $this->getValue($item, 'is_visible_on_front',0),
                            'comment'          => $this->getValue($item, 'comment'),
                            'created_at'             => $this->getValue($item, 'created_at',date("Y-m-d h:i:s")),
                        );
                        $sales_shipment_comment_query = $this->createInsertQuery('sales_shipment_comment',$sales_shipment_comment_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_shipment_item_import = $this->importOrderData($sales_shipment_comment_query);
                            if (!$sales_shipment_item_import) {
                                Bootstrap::logQuery($sales_shipment_comment_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_shipment_comment_query;
                        }
                    }
                }
            }


        }
        //end shipment
//        -------------------------------------------------------------------------------------------

        //begin creditmeno
        if (isset($convert['creditmemo'])) {

            $creditmemo            = $convert['creditmemo'];
            $creditmemo_data       = $convert['creditmemo']['data'];
            $sales_creditmemo_data = array(
                'store_id'                                     => $store_id,
                'adjustment_positive'                          => $this->getValue($creditmemo_data, 'adjustment_positive'),
                'base_shipping_tax_amount'                     => $this->getValue($creditmemo_data, 'base_shipping_tax_amount'),
                'store_to_order_rate'                          => $this->getValue($creditmemo_data, 'store_to_order_rate'),
                'send_email'                                   => $this->getValue($creditmemo_data, 'send_email'),
                'base_discount_amount'                         => $this->getValue($creditmemo_data, 'base_discount_amount'),
                'base_to_order_rate'                           => $this->getValue($creditmemo_data, 'base_to_order_rate'),
                'grand_total'                                  => $this->getValue($creditmemo_data, 'grand_total'),
                'base_adjustment_negative'                     => $this->getValue($creditmemo_data, 'base_adjustment_negative'),
                'base_subtotal_incl_tax'                       => $this->getValue($creditmemo_data, 'base_subtotal_incl_tax'),
                'shipping_amount'                              => $this->getValue($creditmemo_data, 'shipping_amount'),
                'subtotal_incl_tax'                            => $this->getValue($creditmemo_data, 'subtotal_incl_tax'),
                'adjustment_negative'                          => $this->getValue($creditmemo_data, 'adjustment_negative'),
                'base_shipping_amount'                         => $this->getValue($creditmemo_data, 'base_shipping_amount'),
                'store_to_base_rate'                           => $this->getValue($creditmemo_data, 'store_to_base_rate'),
                'base_to_global_rate'                          => $this->getValue($creditmemo_data, 'base_to_global_rate'),
                'base_adjustment'                              => $this->getValue($creditmemo_data, 'base_adjustment'),
                'base_subtotal'                                => $this->getValue($creditmemo_data, 'base_subtotal'),
                'discount_amount'                              => $this->getValue($creditmemo_data, 'discount_amount'),
                'subtotal'                                     => $this->getValue($creditmemo_data, 'subtotal'),
                'adjustment'                                   => $this->getValue($creditmemo_data, 'adjustment'),
                'base_grand_total'                             => $this->getValue($creditmemo_data, 'base_grand_total'),
                'base_adjustment_positive'                     => $this->getValue($creditmemo_data, 'base_adjustment_positive'),
                'base_tax_amount'                              => $this->getValue($creditmemo_data, 'base_tax_amount'),
                'shipping_tax_amount'                          => $this->getValue($creditmemo_data, 'shipping_tax_amount'),
                'tax_amount'                                   => $this->getValue($creditmemo_data, 'tax_amount'),
                'order_id'                                     => $order_id,
                'email_sent'                                   => $this->getValue($creditmemo_data, 'email_sent'),
                'state'                                        => $this->getValue($creditmemo_data, 'state'),
                'shipping_address_id'                          => $sales_address_shipping_id,
                'billing_address_id'                           => $sales_address_billing_id,
                'invoice_id'                                   => null,
                'store_currency_code'                          => $this->getValue($creditmemo_data, 'store_currency_code'),
                'order_currency_code'                          => $this->getValue($creditmemo_data, 'order_currency_code'),
                'base_currency_code'                           => $this->getValue($creditmemo_data, 'base_currency_code'),
                'global_currency_code'                         => $this->getValue($creditmemo_data, 'global_currency_code'),
                'transaction_id'                               => null,
                'increment_id'                                 => null,
                'created_at'                                   => $this->getValue($creditmemo_data, 'create_at', date("Y-m-d h:i:s")),
                'updated_at'                                   => $this->getValue($creditmemo_data, 'update_at', date("Y-m-d h:i:s")),
                'discount_tax_compensation_amount'             => $this->getValue($creditmemo_data, 'discount_tax_compensation_amount'),
                'base_discount_tax_compensation_amount'        => $this->getValue($creditmemo_data, 'base_discount_tax_compensation_amount'),
                'shipping_discount_tax_compensation_amount'    => null,
                'base_shipping_discount_tax_compensation_amnt' => null,
                'shipping_incl_tax'                            => $this->getValue($creditmemo_data, 'shipping_incl_tax'),
                'base_shipping_incl_tax'                       => $this->getValue($creditmemo_data, 'base_shipping_incl_tax'),
                'discount_description'                         => $this->getValue($creditmemo_data, 'discount_description'),
                'customer_note'                                => null,
                'customer_note_notify'                         => null,
            );

            $sales_creditmemo_query  = $this->createInsertQuery('sales_creditmemo',$sales_creditmemo_data);
            $sales_creditmemo_id = $this->importOrderData($sales_creditmemo_query);

            if($sales_creditmemo_id){
                $increment_id_creditmemo     = null;
                $new_increment_id_creditmemo = '' . $sales_creditmemo_id;
                while (strlen($new_increment_id_creditmemo) < self::LEN_INCREMENT) {
                    $new_increment_id_creditmemo = '0' . $new_increment_id_creditmemo;
                }

                $new_increment_id_creditmemo = '1' . $new_increment_id_creditmemo;
                $old_increment_id_creditmemo = $this->getValue($creditmemo_data, 'increment_id', $new_increment_id_creditmemo);

                if ($this->_notice['config']['pre_ord']) {
                    $increment_id_creditmemo = $old_increment_id_creditmemo;
                } else {
                    $increment_id_creditmemo = $new_increment_id_creditmemo;
                }
                $sales_creditmemo_update_query = $this->createUpdateQuery('sales_creditmemo',array('increment_id' => $increment_id_creditmemo),array('entity_id' => $sales_creditmemo_id));

                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_creditmemo_update = $this->importOrderData($sales_creditmemo_update_query);
                    if (!$sales_creditmemo_update) {
                        Bootstrap::logQuery($sales_creditmemo_update_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $sales_creditmemo_update_query;
                }
                $sales_creditmemo_grid_data  = array(
                    'entity_id'              => $sales_creditmemo_id,
                    'increment_id'           => $increment_id_creditmemo,
                    'created_at'             => $this->getValue($creditmemo_data, 'created_at'),
                    'updated_at'             => $this->getValue($creditmemo_data, 'updated_at'),
                    'order_id'               => $order_id,
                    'order_increment_id'     => $order_increment_id,
                    'order_created_at'       => $this->getValue($convert, 'created_at', date("Y-m-d h:i:s")),
                    'billing_name'           => $billing_full_name,
                    'state'                  => $this->getValue($creditmemo_data, 'state'),
                    'base_grand_total'       => $this->getValue($creditmemo_data, 'base_grand_total'),
                    'order_status'           => $order_status,
                    'store_id'               => $store_id,
                    'billing_address'        => $this->getValue($billing_address, 'address_1', $this->getValue($billing_address, 'address_2')),
                    'shipping_address'       => $this->getValue($shipping_address, 'address_1', $this->getValue($shipping_address, 'address_2')),
                    'customer_name'          => $customer_fullname,
                    'customer_email'         => $this->getValue($customer, 'email'),
                    'customer_group_id'      => $customer_group,
                    'payment_method'         => $this->getValue($convert, 'payment_method', 'cashondelivery'),
                    'shipping_information'   => null,
                    'subtotal'               => $this->getValue($creditmemo_data, 'subtotal'),
                    'shipping_and_handling'  => null,
                    'adjustment_positive'    => $this->getValue($creditmemo_data, 'adjustment_positive'),
                    'adjustment_negative'    => $this->getValue($creditmemo_data, 'adjustment_negative'),
                    'order_base_grand_total' => $convert['total']['amount'],
                );
                $sales_creditmemo_grid_query = $this->createInsertQuery('sales_creditmemo_grid',$sales_creditmemo_grid_data);

                if (Bootstrap::getConfig('dev_mode')) {
                    $sales_creditmemo_grid_import = $this->importOrderData($sales_creditmemo_grid_query);
                    if (!$sales_creditmemo_grid_import) {
                        Bootstrap::logQuery($sales_creditmemo_grid_query);
                        var_dump(1);
                        exit;

                    }
                } else {
                    $all_query[] = $sales_creditmemo_grid_query;
                }

                $creditmemo_item = $this->getValue($creditmemo, 'item');

                if ($creditmemo_item && count($creditmemo_item)) {
                    foreach ($creditmemo_item as $key => $item) {
                        $order_item_id_src = $this->getValue($item, 'order_item_id');
                        $order_item_id     = $this->getMapFieldBySource(self::TYPE_ORDER_ITEM, $order_item_id_src);
                        $product_id        = $this->getMapFieldBySource(self::TYPE_PRODUCT, $this->getValue($item, 'product_id',-1));
                        if (!$product_id) {
                            $product_id = null;
                        }
                        $sales_creditmemo_item_data  = array(
                            'parent_id'                             => $sales_creditmemo_id,
                            'base_price'                            => $this->getValue($item, 'base_price'),
                            'tax_amount'                            => $this->getValue($item, 'tax_amount'),
                            'base_row_total'                        => $this->getValue($item, 'base_row_total'),
                            'discount_amount'                       => $this->getValue($item, 'discount_amount'),
                            'row_total'                             => $this->getValue($item, 'row_total'),
                            'base_discount_amount'                  => $this->getValue($item, 'base_discount_amount'),
                            'price_incl_tax'                        => $this->getValue($item, 'price_incl_tax'),
                            'base_tax_amount'                       => $this->getValue($item, 'base_tax_amount'),
                            'base_price_incl_tax'                   => $this->getValue($item, 'base_price_incl_tax'),
                            'qty'                                   => $this->getValue($item, 'qty'),
                            'base_cost'                             => $this->getValue($item, 'base_cost'),
                            'price'                                 => $this->getValue($item, 'price'),
                            'base_row_total_incl_tax'               => $this->getValue($item, 'base_row_total_incl_tax'),
                            'row_total_incl_tax'                    => $this->getValue($item, 'row_total_incl_tax '),
                            'product_id'                            => $product_id,
                            'order_item_id'                         => $order_item_id,
                            'additional_data'                       => $this->getValue($item, 'additional_data'),
                            'description'                           => $this->getValue($item, 'description'),
                            'sku'                                   => $this->getValue($item, 'sku'),
                            'name'                                  => $this->getValue($item, 'name'),
                            'discount_tax_compensation_amount'      => null,
                            'base_discount_tax_compensation_amount' => null,
                            'tax_ratio'                             => null,
                            'weee_tax_applied'                      => null,
                            'weee_tax_applied_amount'               => null,
                            'weee_tax_applied_row_amount'           => null,
                            'weee_tax_disposition'                  => null,
                            'weee_tax_row_disposition'              => null,
                            'base_weee_tax_applied_amount'          => null,
                            'base_weee_tax_applied_row_amnt'        => null,
                            'base_weee_tax_disposition'             => null,
                            'base_weee_tax_row_disposition'         => null,
                        );
                        $sales_creditmemo_item_query = $this->createInsertQuery('sales_creditmemo_item',$sales_creditmemo_item_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_creditmemo_item_import = $this->importOrderData($sales_creditmemo_item_query);
                            if (!$sales_creditmemo_item_import) {
                                Bootstrap::logQuery($sales_creditmemo_item_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_creditmemo_item_query;
                        }
                    }
                }

                $creditmemo_comment = $this->getValue($creditmemo, 'comment');
                if ($creditmemo_comment && count($creditmemo_comment) > 0) {
                    foreach ($creditmemo_comment as $key => $item) {
                        $sales_creditmemo_comment_data  = array(
                            'parent_id'       => $sales_creditmemo_id,
                            'is_customer_notified'       => $this->getValue($item, 'is_customer_notified'),
                            'is_visible_on_front'           => $this->getValue($item, 'is_visible_on_front',0),
                            'comment'          => $this->getValue($item, 'comment'),
                            'created_at'             => $this->getValue($item, 'created_at',date("Y-m-d h:i:s")),
                        );
                        $sales_creditmemo_comment_query = $this->createInsertQuery('sales_creditmemo_comment',$sales_creditmemo_comment_data);

                        if (Bootstrap::getConfig('dev_mode')) {
                            $sales_creditmemo_item_import = $this->importOrderData($sales_creditmemo_comment_query);
                            if (!$sales_creditmemo_item_import) {
                                Bootstrap::logQuery($sales_creditmemo_comment_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $sales_creditmemo_comment_query;
                        }
                    }
                }
            }


        }

        //end creditmemo
//      -------------------------------------------------------------------------------------------
        $order_update_data  = array(
            'increment_id'        => $order_increment_id,
            'billing_address_id'  => $sales_address_billing_id,
            'shipping_address_id' => $sales_address_shipping_id,
        );
        $order_update_query = $this->createUpdateQuery('sales_order',$order_update_data,array('entity_id' => $order_id));

        if (Bootstrap::getConfig('dev_mode')) {
            $order_update_import = $this->importOrderData($order_update_query);
            if (!$order_update_import) {
                Bootstrap::logQuery($order_update_query);
                var_dump(1);
                exit;
            }
        } else {
            $all_query[] = $order_update_query;
        }

        if (!Bootstrap::getConfig('dev_mode')) {
            $all_import = $this->importMultipleData($all_query,'order');
        }


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
            'review_store'       => array(
                'type'  => 'select',
                'query' => "SELECT * FROM _DBPRF_review_store WHERE store_id != 0 AND review_id IN " . $review_id_query,
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
        if ($rating_option_vote && count($rating_option_vote) > 0) {
            $rating_vote = array();
            foreach ($rating_option_vote as $vote) {
                $rating_vote_data              = array();
                $rating_vote_data['remote_ip'] = $vote['remote_ip'];
                $rating_vote_data['percent']   = $vote['percent'];
                $rating_vote_data['rating_id'] = $vote['rating_id'];
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

        // multi store review
        $multi_review = $this->getListFromListByField($reviewsExt['data']['review_store'], 'review_id', $review['review_id']);
        if ($multi_review && count($multi_review) > 0) {
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
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');

        $product_id  = null;
        $customer_id = null;
        if (isset($convert['product']['id']) && $convert['product']['id'] != null) {

            $product_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $convert['product']['id']);
            if ($product_exists) {
                $product_id = $product_exists['id_desc'];
            }

        }
        if(!$product_id){
            return $this->warningSQL(array(
                'result' => 'warning',
                'msg' => "Review id ".$convert['id'].": Product don't exist",
            ));
        }
        $review_data = array(
            'created_at'      => $this->getValue($convert, 'created_at', date('Y-m-d H:i:s')),
            'status_id'       => $this->getValue($convert, 'status', 1),
            'entity_id'       => 1,
            'entity_pk_value' => $product_id,
        );
        $review_query = $this->createInsertQuery('review',$review_data);

        $review_id = $this->importReviewData($review_query);

        if (!$review_id) {
            //warning
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($review_query);
                var_dump(1);
                exit;
            }

            return $this->errorConnector();
        }




        $this->insertMap($url_src, $url_desc, self::TYPE_REVIEW, $convert['id'], $review_id, null, null);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $review_id,
        );
    }

    public function afterReviewImport($review_id, $convert, $review, $reviewsExt)
    {
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');
        $all_query = array();
        $product_id = null;

        if (isset($convert['product']['id']) && $convert['product']['id'] != null) {
            $product_exists = $this->selectMap($url_src, $url_desc, self::TYPE_PRODUCT, $convert['product']['id'], null, null);
            if ($product_exists) {
                $product_id = $product_exists['id_desc'];
            }
        }

        //review_store begin
        $multi_store   = $this->getValue($convert, 'multi_store', array());
        $multi_store[] = array(
            'review_id' => null,
            'store_id'  => 0,
        );
        foreach ($multi_store as $key => $store) {
            $store_id = $this->getMapStoreView($this->getValue($store, 'store_id', 0));
            $review_store_data = array(
                'review_id' => $review_id,
                'store_id'  => $store_id,
            );
            $review_store_query = $this->createInsertQuery('review_store',$review_store_data);

            if (Bootstrap::getConfig('dev_mode')) {
                $review_store_import = $this->importReviewData($review_store_query);

                if (!$review_store_import) {
                    //warning
                    Bootstrap::logQuery($review_store_query);
                    var_dump(1);
                    exit;

                }

            } else {
                $all_query[] = $review_store_query;
            }
            //review_entity_summary begin
            if (isset($convert['rating'][0]['rate']) && $convert['rating'][0]['rate'] != null) {
                $rating = $convert['rating'][0]['rate'];
                $rating_exist_data = array(
                    'entity_pk_value' => $product_id,
                    'store_id' => $store_id,
                );
                $primary_id = '';
                $count = 0;
                $rating_summary = 0;
                $rating_exist_query = "SELECT * FROM _DBPRF_review_entity_summary WHERE ".$this->arrayToWhereCondition($rating_exist_data);
                $rating_exist = $this->getConnectorData($url_query,array(
                    'query' => serialize(array(
                        'type' => 'select',
                        'query' => $rating_exist_query,
                    )),
                ));
                if(isset($rating_exist['data']['0'])){
                    $primary_id = $rating_exist['data'][0]['primary_id'];
                    $count = $rating_exist['data'][0]['reviews_count'];
                    $rating_summary = $rating_exist['data'][0]['rating_summary'];
                }
                if($primary_id){

                    $value = round((($rating_summary * $count) + $rating)/($count+1)/10)*10;
                    $update_summary = array(
                        'reviews_count' => $count+1,
                        'rating_summary' => $value
                    );
                    $update_summary_query = "UPDATE _DBPRF_review_entity_summary ".$this->arrayToSetCondition($update_summary). " WHERE `primary_id` = '".$primary_id."";
                    if (Bootstrap::getConfig('dev_mode')) {
                        $update = $this->getConnectorData($url_query,array(
                            'query' => serialize(array(
                                'type' => 'update',
                                'query' => $update_summary_query,
                            )),
                        ));
                    } else {
                        $all_query[] = array(
                            'type' => 'update',
                            'query' => $update_summary_query
                        );
                    }
                }else{
                    $review_entity_summary_data = array(
                        'entity_pk_value' => $product_id,
                        'entity_type'     => 1,
                        'reviews_count'   => 1,
                        'rating_summary'  => $convert['rating'][0]['rate'],
                        'store_id'        => $store_id,
                    );
                    $review_entity_summary_query = $this->createInsertQuery('review_entity_summary',$review_entity_summary_data);

                    if (Bootstrap::getConfig('dev_mode')) {
                        $review_entity_summary_import = $this->importReviewData($review_entity_summary_query);

                        if (!$review_entity_summary_import) {
                            //warning
                            Bootstrap::logQuery($review_entity_summary_query);
                            var_dump(1);
                            exit;

                        }
                    } else {
                        $all_query[] = $review_entity_summary_query;
                    }
                }




            }
        }


        //review_store end

        //review_detail begin
        $customer_id = null;

        if (isset($convert['customer']['id']) && $convert['customer']['id'] != null) {
            $customer_exists = $this->selectMap($url_src, $url_desc, self::TYPE_CUSTOMER, $convert['customer']['id'], null, null);
            if ($customer_exists) {
                $customer_id = $customer_exists['id_desc'];
            }
        }

        $review_detail_data = array(
            'review_id'   => $review_id,
            'store_id'    => 0,
            'title'       => $convert['title'],
            'detail'      => $convert['content'],
            'nickname'    => $convert['customer']['name'],
            'customer_id' => $customer_id,
        );
        $review_detail_query = $this->createInsertQuery('review_detail',$review_detail_data);


        if (Bootstrap::getConfig('dev_mode')) {
            $review_detail_import = $this->importReviewData($review_detail_query);

            if (!$review_detail_import) {
                //warning
                Bootstrap::logQuery($review_detail_query);
                var_dump(1);
                exit;
            }

        } else {
            $all_query[] = $review_detail_query;
        }

        //review_detail end

        //rating_option_vote begin

        if (isset($convert['rating_vote']) && count($convert['rating_vote']) > 0) {
            $rating_vote = $convert['rating_vote'];
            foreach ($rating_vote as $key => $vote) {
                $remote_ip      = $this->getValue($vote, 'remote_ip', '');
                $remote_ip_long = 0;
                if ($remote_ip) {
                    $remote_ip_long = ip2long($remote_ip);
                }
                $percent                  = $this->getValue($vote, 'percent', 0);
                $value                    = round($percent / 100 * 5);
                $percent                  = round($value / 5 * 100);
                $rating_id                = $this->getValue($vote, 'rating_id', 1);
                $rating_option_vote_data = array(
                    'option_id'       => 5 * ($rating_id - 1) + $value,
                    'remote_ip'       => $remote_ip,
                    'remote_ip_long'  => $remote_ip_long,
                    'customer_id'     => $customer_id,
                    'entity_pk_value' => $product_id,
                    'rating_id'       => $rating_id,
                    'review_id'       => $review_id,
                    'percent'         => $percent,
                    'value'           => $value,
                );
                $rating_option_vote_query = $this->createInsertQuery('rating_option_vote',$rating_option_vote_data);

                if (Bootstrap::getConfig('dev_mode')) {
                    $rating_option_vote_import = $this->importReviewData($rating_option_vote_query);

                    if (!$rating_option_vote_import) {
                        //warning
                        Bootstrap::logQuery($rating_option_vote_query);
                        var_dump(1);
                        exit;

                    }

                } else {
                    $all_query[] = $rating_option_vote_query;
                }

            }
        }
        //rating_option_vote end


        if (!Bootstrap::getConfig('dev_mode')) {
            $all_import = $this->importMultipleData($all_query,'review');
        }


        //review_entity_summary end
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
        return $convert['id'];
    }

    public function checkPageImport($convert, $page, $pagesExt)
    {
        return $this->getMapFieldBySource(self::TYPE_PAGE, $convert['id']);
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
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $router    = $this->getValue($convert, 'identifier');

        while ($this->checkExistUrlCms(self::TYPE_PAGE, $router)) {
            $router .= '-1';
        }
        $page_entity_data   = array(
            'title'                    => $this->getValue($convert, 'title'),
            'page_layout'              => $this->getPageLayout($this->getValue($convert, 'page_layout')),
            'meta_keywords'            => $this->getValue($convert, 'meta_keywords'),
            'meta_description'         => $this->getValue($convert, 'meta_description'),
            'identifier'               => $router,
            'content_heading'          => $this->getValue($convert, 'content_heading'),
            'content'                  => $this->getValue($convert, 'content'),
            'creation_time'            => $this->getValue($convert, 'creation_time', date('Y-m-d H:i:s')),
            'update_time'              => $this->getValue($convert, 'update_time', date('Y-m-d H:i:s')),
            'is_active'                => $this->getValue($convert, 'is_active', 1),
            'sort_order'               => $this->getValue($convert, 'sort_order', 0),
            'layout_update_xml'        => $this->getLayoutUpdateXml($this->getValue($convert, 'layout_update_xml')),
            'custom_theme'             => null,
            'custom_root_template'     => $this->getPageLayout($this->getValue($convert, 'custom_root_template')),
            'custom_layout_update_xml' => $this->getLayoutUpdateXml($this->getValue($convert, 'custom_layout_update_xml')),
            'custom_theme_from'        => $this->getValue($convert, 'custom_theme_from'),
            'custom_theme_to'          => $this->getValue($convert, 'custom_theme_to'),
            'meta_title'               => $this->getValue($convert, 'meta_title'),
        );
        $page_entity_query  = $this->createInsertQuery('cms_page',$page_entity_data);
        $page_id = $this->importPageData($page_entity_query);
        if (!$page_id) {
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($page_entity_query);
                var_dump(1);
                exit;
            }

            return $this->errorConnector();
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_PAGE, $convert['id'], $page_id, null, $router);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $page_id,
        );
    }

    public function afterPageImport($page_id, $convert, $page, $pagesExt)
    {
        $all_query = array();
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');
        $url       = $this->selectMap($url_src, $url_desc, self::TYPE_PAGE, $convert['id'], $page_id);

        if (isset($convert['stores'])) {
            foreach ($convert['stores'] as $key => $store_id) {
                $store_id         = $this->getMapStoreView($store_id);
                $page_store_data  = array(
                    'page_id'  => $page_id,
                    'store_id' => $store_id,
                );
                $page_store_query = $this->createInsertQuery('cms_page_store',$page_store_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $page_store_import = $this->importPageData($page_store_query);
                    if (!$page_store_import) {
                        Bootstrap::logQuery($page_store_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $page_store_query;
                }


                $request_path = null;
                if ($url) {
                    $request_path = $url['code_desc'];
                    if ($request_path) {
                        $request_path   = $this->getRequestPath('cms-page', $request_path, $store_id);
                        $page_url_data  = array(
                            'entity_type'      => 'cms-page',
                            'entity_id'        => $page_id,
                            'request_path'     => $request_path,
                            'target_path'      => 'cms/page/view/page_id/' . $page_id,
                            'redirect_type'    => 0,
                            'store_id'         => $store_id,
                            'description'      => null,
                            'is_autogenerated' => 1,
                            'metadata'         => null,
                        );
                        $page_url_query = $this->createInsertQuery('url_rewrite',$page_url_data);
                        if (Bootstrap::getConfig('dev_mode')) {
                            $page_url_import = $this->importPageData($page_url_query);
                            if (!$page_url_import) {
                                Bootstrap::logQuery($page_url_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $page_url_query;
                        }
                    }

                }
            }
            if (!Bootstrap::getConfig('dev_mode') && count($all_query) > 0) {
                $page_store_import = $this->importMultipleData($all_query,'page');
            }
        }

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
            'eav_attribute'   => array(
                'type'  => "select",
                'query' => 'SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = ' . $this->_notice['src']['extends']['catalog_category'],
            ),

        );
        $blocksExt          = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($blocks_ext_queries),
        ));
        if (!$blocksExt || $blocksExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $attribute_landing_page = $this->getRowValueFromListByField($blocksExt['data']['eav_attribute'], 'attribute_code', 'landing_page', 'attribute_id');
        $block_ext_rel_query    = array(
            'category_block' => array(
                'type'  => 'select',
                'query' => "SELECT value as block_id,entity_id as category_id,store_id FROM _DBPRF_catalog_category_entity_int WHERE attribute_id = '" . $attribute_landing_page . "' AND value IN " . $blockIdCondition,
            ),
        );
        $blocksExtRel           = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($block_ext_rel_query),
        ));
        if (!$blocksExtRel || $blocksExtRel['result'] != 'success') {
            return $this->errorConnector();
        }
        $blocksExt = $this->syncConnectorObject($blocksExt, $blocksExtRel);

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

        $block_data['category_block'] = $this->getListFromListByField($blocksExt['data']['category_block'], 'block_id', $block['block_id']);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $block_data,
        );
    }

    public function getBlockIdImport($convert, $block, $blocksExt)
    {
        return $convert['id'];
    }

    public function checkBlockImport($convert, $block, $blocksExt)
    {
        return $this->getMapFieldBySource(self::TYPE_BLOCK, $convert['id']);
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
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $router    = $this->getValue($convert, 'identifier');

        while ($this->checkExistUrlCms(self::TYPE_BLOCK, $router)) {
            $router .= '-1';
        }
        $block_entity_data   = array(
            'title'         => $this->getValue($convert, 'title'),
            'identifier'    => $router,
            'content'       => $this->getValue($convert, 'content'),
            'creation_time' => $this->getValue($convert, 'creation_time', date('Y-m-d H:i:s')),
            'update_time'   => $this->getValue($convert, 'update_time', date('Y-m-d H:i:s')),
            'is_active'     => $this->getValue($convert, 'is_active', 1),
        );
        $block_entity_query  = $this->createInsertQuery('cms_block',$block_entity_data);
        $block_id = $this->importBlockData($block_entity_query);
        if (!$block_id) {
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($block_entity_query);
                var_dump(1);
                exit;
            }

            return $this->errorConnector();
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_BLOCK, $convert['id'], $block_id, null, $router);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $block_id,
        );
    }

    public function afterBlockImport($block_id, $convert, $block, $blocksExt)
    {
        $all_query = array();

        if (isset($convert['stores'])) {
            foreach ($convert['stores'] as $key => $store_id) {
                $store_id          = $this->getMapStoreView($store_id);
                $block_store_data  = array(
                    'block_id' => $block_id,
                    'store_id' => $store_id,
                );
                $block_store_query = $this->createInsertQuery('cms_block_store',$block_store_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $block_store_import = $this->importBlockData($block_store_query);
                    if (!$block_store_import) {
                        Bootstrap::logQuery($block_store_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $block_store_query;
                }

            }
        }

        //link category with block

        if (isset($convert['category_block'])) {
            foreach ($convert['category_block'] as $key => $item) {
                $category_id = $this->getMapFieldBySource(self::TYPE_CATEGORY, $item['category_id']);
                if ($category_id) {
                    $category_block_data  = array(
                        'attribute_id' => 53,
                        'store_id'     => $this->getMapStoreView($item['store_id']),
                        'entity_id'    => $category_id,
                        'value'        => $block_id,
                    );
                    $category_block_query = $this->createInsertQuery('catalog_category_entity_int',$category_block_data);
                    if (Bootstrap::getConfig('dev_mode')) {
                        $category_block_import = $this->importBlockData($category_block_query);
                        if (!$category_block_import) {
                            Bootstrap::logQuery($category_block_query);
                            exit;
                        }
                    } else {
                        $all_query[] = $category_block_query;
                    }
                }
            }
        }

        if (!Bootstrap::getConfig('dev_mode') && count($all_query) > 0) {
            $page_store_import = $this->importMultipleData($all_query,'block');
        }

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
        return $convert['id'];
    }

    public function checkWidgetImport($convert, $widget, $widgetsExt)
    {
        return $this->getMapFieldBySource(self::TYPE_WIDGET, $convert['id']);
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
                'query' => 'SELECT * FROM _DBPRF_email_template WHERE template_id > ' . $id_src . '
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
        return $convert['id'];
    }

    public function checkTransactionImport($convert, $transaction, $transactionsExt)
    {
        return $this->getMapFieldBySource(self::TYPE_TRANSACTION, $convert['id']);
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
        $url_src            = $this->_notice['src']['cart_url'];
        $url_desc           = $this->_notice['target']['cart_url'];
        $template_code      = $this->getValue($convert, 'template_code', '');
        $transaction_data   = array(
            'template_code'           => $template_code,
//            'template_text'           => $this->getValue($convert, 'template_text', ''),
            'template_text'           => '',
            'template_styles'         => $this->getValue($convert, 'template_styles'),
            'template_type'           => $this->getValue($convert, 'template_type'),
            'template_subject'        => $this->getValue($convert, 'template_subject', ''),
            'template_sender_name'    => $this->getValue($convert, 'template_sender_name'),
            'template_sender_email'   => $this->getValue($convert, 'template_sender_email'),
            'added_at'                => $this->getValue($convert, 'added_at', date('Y-m-d H:i:s')),
            'modified_at'             => $this->getValue($convert, 'modified_at', date('Y-m-d H:i:s')),
            'orig_template_code'      => $this->getValue($convert, 'orig_template_code'),
            'orig_template_variables' => $this->getValue($convert, 'orig_template_variables'),
        );
        $transaction_query  = 'INSERT INTO _DBPRF_email_template ';
        $transaction_query  .= $this->arrayToInsertCondition($transaction_data);
        $transaction_import = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $transaction_query,
                'params' => array(
                    'insert_id' => true,
                ),
            )),

        ));
//        if (!$transaction_import) {
//            if (Bootstrap::getConfig('dev_mode')) {
//                Bootstrap::logQuery($transaction_query;
//                var_dump(1);
//                exit;
//            }
//
//            return $this->errorConnector();
//        }
        $transaction_id = $transaction_import['data'];
        if (!$transaction_import || $transaction_import['result'] != 'success' || !$transaction_id) {
            return array(
                'result' => 'warning',
                'msg'    => $this->consoleWarning('Transaction email: template code "' . $template_code . '"' . ' already exists'),
            );
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_TRANSACTION, $convert['id'], $transaction_id);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $transaction_id,
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
        $limit  = $this->_notice['setting']['rules'];
        $rules  = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_salesrule 
                WHERE rule_id > ' . $id_src . ' ORDER BY rule_id ASC LIMIT ' . $limit,
            )),
        ));
        if (!$rules || $rules['result'] != 'success') {
            return $this->errorConnector();
        }

        return $rules;
    }

    public function getRulesExtExport($rules)
    {
        $url_query          = $this->getConnectorUrl('query');
        $rule_ids           = $this->duplicateFieldValueFromList($rules['data'], 'rule_id');
        $rule_idCon         = $this->arrayToInCondition($rule_ids);
        $rulesExt           = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize(array(
                'salesrule_coupon'            => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_coupon WHERE rule_id IN ' . $rule_idCon,
                ),
                'salesrule_customer'          => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_customer WHERE rule_id IN ' . $rule_idCon,
                ),
                'salesrule_customer_group'    => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_customer_group WHERE rule_id IN ' . $rule_idCon,
                ),
                'salesrule_label'             => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_label WHERE rule_id IN ' . $rule_idCon,
                ),
//                'salesrule_website' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_salesrule_website WHERE rule_id IN '.$rule_idCon,
//                ),
                'salesrule_product_attribute' => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_salesrule_product_attribute WHERE rule_id IN ' . $rule_idCon,
                ),

            )),
        ));
        $coupon_ids         = $this->duplicateFieldValueFromList($rulesExt['data']['salesrule_coupon'], 'coupon_id');
        $coupon_id_con      = $this->arrayToInCondition($coupon_ids);
        $rulesRelExtQueries = array(
            'salesrule_coupon_usage' => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_salesrule_coupon_usage WHERE coupon_id IN ' . $coupon_id_con,
            ),
        );
////        $customer_group = $this->duplicateFieldValueFromList($rules['data'],'customer_group_ids');
//        if(!isset($rules['data'][0]['customer_group_ids'])){
//            $rulesRelExtQueries['salesrule_customer_group'] = array(
//                'type' => 'select',
//                'query' => 'SELECT * FROM _DBPRF_salesrule_customer_group WHERE rule_id IN '.$rule_idCon,
//
//            );
//        }
        $rulesRelExt = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($rulesRelExtQueries),
        ));
        if (!$rulesRelExt || $rulesRelExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $rulesExt = $this->syncConnectorObject($rulesExt, $rulesRelExt);

        return $rulesExt;
    }

    public function convertRuleExport($rule, $rulesExt)
    {
        $rule_id         = $rule['rule_id'];
        $rule_data       = $rule;
        $rule_data['id'] = $rule_id;
        unset($rule_data['rule_id']);
        $coupons               = $this->getListFromListByField($rulesExt['data']['salesrule_coupon'], 'rule_id', $rule_id);
        $rule_data['label']    = $this->getListFromListByField($rulesExt['data']['salesrule_label'], 'rule_id', $rule_id);
        $customers             = $this->getListFromListByField($rulesExt['data']['salesrule_customer'], 'rule_id', $rule_id);
        $rule_data['customer'] = array();
        foreach ($customers as $customer) {
            unset($customer['rule_customer_id']);
            unset($customer['rule_id']);
            $rule_data['customer'][] = $customer;
        }
        $rule_data['customer_group'] = array();
//        if(isset($rule['customer_group_ids'])){
//            $customer_group = explode(',',$rule['customer_group_ids']);
//            $customer_group_ids = array();
//            foreach($customer_group as $group){
//                $group_id   = $this->_notice['map']['customer_group'][$group];
//                if($group_id && !in_array($group_id,$customer_group_ids)){
//                    $customer_group_ids[] = $group;
//                }
//            }
//            $rule_data['customer_group'] = $customer_group_ids;
//        }
//        elseif(isset($rulesExt['data']['salesrule_customer_group'])){
        $customer_groups    = $this->getListFromListByField($rulesExt['data']['salesrule_customer_group'], 'rule_id', $rule_id);
        $customer_group_ids = array();
        foreach ($customer_groups as $customer_group) {
            $group_id = $this->_notice['map']['customer_group'][$customer_group['customer_group_id']];
            if ($group_id && !in_array($group_id, $customer_group_ids)) {
                $customer_group_ids[] = $group_id;
            }
        }
        $rule_data['customer_group'] = $customer_group_ids;
//        }
        $src_version = $this->_notice['src']['config']['version'];
        $src_version = $this->convertVersion($src_version, 2);
        if ($src_version < 220) {
            $rule_data['conditions'] = unserialize($rule_data['conditions_serialized']);
            $rule_data['actions']    = unserialize($rule_data['actions_serialized']);
        } else {
            $rule_data['conditions'] = json_decode($rule_data['conditions_serialized'], true);
            $rule_data['actions']    = json_decode($rule_data['actions_serialized'], true);
        }
        unset($rule_data['conditions_serialized']);
        unset($rule_data['actions_serialized']);
        $rule_data['coupon'] = array();
        foreach ($coupons as $coupon) {
            $usage = $this->getListFromListByField($rulesExt['data']['salesrule_coupon_usage'], 'coupon_id', $coupon['coupon_id']);
            unset($usage['coupon_id']);
            unset($coupon['coupon_id']);
            unset($coupon['rule_id']);
            $coupon_code[]         = $coupon['code'];
            $coupon_data           = array();
            $coupon_data['data']   = $coupon;
            $coupon_data['usage']  = $usage;
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
        return $convert['id'];
    }

    public function checkRuleImport($convert, $rule, $rulesExt)
    {
        return $this->getMapFieldBySource(self::TYPE_RULE, $convert['id']);
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
        $url_src     = $this->_notice['src']['cart_url'];
        $url_desc    = $this->_notice['target']['cart_url'];
        $product_ids = $this->getValue($convert, 'product_ids');
        if ($product_ids) {
            $product_id_arr     = explode(',', $product_ids);
            $product_id_map_arr = array();
            foreach ($product_id_arr as $key => $product_id) {
                $map_product_id = $this->getMapFieldBySource(self::TYPE_PRODUCT, $product_id);
                if ($map_product_id) {
                    $product_id_map_arr[] = $map_product_id;
                }
            }
            if (count($product_id_map_arr) > 0) {
                $product_ids = implode($product_id_map_arr, ',');
            } else {
                $product_ids = null;
            }
        }
        $conditions = $this->getValue($convert, 'conditions');
        if ($conditions) {
            $conditions = $this->getConditionsOption($conditions);
            if ($conditions) {
                $conditions = $this->mySerialize($conditions);
                $conditions = str_replace('\\', '\\\\', $conditions);
            }
        }
        $actions = $this->getValue($convert, 'actions');
        if ($actions) {
            $actions = $this->getConditionsOption($actions);
            if ($actions) {
                $actions = $this->mySerialize($actions);
                $actions = str_replace('\\', '\\\\', $actions);


            }
        }
        $rule_data  = array(
            'name'                  => $this->getValue($convert, 'name'),
            'description'           => $this->getValue($convert, 'description'),
            'from_date'             => $this->getValue($convert, 'from_date'),
            'to_date'               => $this->getValue($convert, 'to_date'),
            'uses_per_customer'     => $this->getValue($convert, 'uses_per_customer', 0),
            'is_active'             => $this->getValue($convert, 'is_active', 0),
            'conditions_serialized' => $conditions,
            'actions_serialized'    => $actions,
            'stop_rules_processing' => $this->getValue($convert, 'stop_rules_processing', 1),
            'is_advanced'           => $this->getValue($convert, 'is_advanced', 1),
            'product_ids'           => $product_ids,
            'sort_order'            => $this->getValue($convert, 'sort_order', 0),
            'simple_action'         => $this->getValue($convert, 'simple_action'),
            'discount_amount'       => $this->getValue($convert, 'discount_amount', '0.0000'),
            'discount_qty'          => $this->getValue($convert, 'discount_qty'),
            'discount_step'         => $this->getValue($convert, 'discount_step', 0),
            'apply_to_shipping'     => $this->getValue($convert, 'apply_to_shipping', 0),
            'times_used'            => $this->getValue($convert, 'times_used', 0),
            'is_rss'                => $this->getValue($convert, 'is_rss', 0),
            'coupon_type'           => $this->getValue($convert, 'coupon_type', 1),
            'use_auto_generation'   => $this->getValue($convert, 'use_auto_generation', 0),
            'uses_per_coupon'       => $this->getValue($convert, 'uses_per_coupon', 0),
            'simple_free_shipping'  => $this->getValue($convert, 'simple_free_shipping'),
        );
        $rule_query = $this->createInsertQuery('salesrule',$rule_data);
//        print_r($rule_query);exit;
        $rule_id = $this->importRuleData($rule_query);
        if (!$rule_id) {
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($rule_query);
                var_dump(1);
                exit;
            }

            return $this->errorConnector();
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_RULE, $convert['id'], $rule_id);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $rule_id,
        );
    }

    public function afterRuleImport($rule_id, $convert, $rule, $rulesExt)
    {
        $msg       = '';
        $url_query = $this->getConnectorUrl('query');
        $all_query = array();
        if (isset($convert['rule_website'])) {
            $website_ids = $this->getWebsiteIdsTargetByIdSrc($convert['rule_website']);
            foreach ($website_ids as $key => $website_id) {
                $rule_website_data  = array(
                    'rule_id'    => $rule_id,
                    'website_id' => $website_id,
                );
                $rule_website_query = $this->createInsertQuery('salesrule_website',$rule_website_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $rule_website_import = $this->importRuleData($rule_website_query);
                    if (!$rule_website_import) {
                        Bootstrap::logQuery($rule_website_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] =$rule_website_query;
                }
            }
        }

        foreach ($convert['customer'] as $key => $customer) {
            $customer_id = $this->getMapFieldBySource(self::TYPE_CUSTOMER, $customer['customer_id']);
            if (!$customer_id) {
                continue;
            }
            $rule_customer_data  = array(
                'rule_id'     => $rule_id,
                'customer_id' => $customer_id,
                'times_used'  => $this->getValue($customer, 'times_used', 0),
            );
            $rule_customer_query = $this->createInsertQuery('salesrule_customer',$rule_customer_data);
            if (Bootstrap::getConfig('dev_mode')) {
                $rule_customer_import = $this->importRuleData($rule_customer_query);
                if (!$rule_customer_import) {
                    Bootstrap::logQuery($rule_customer_query);
                    var_dump(1);
                    exit;
                }
            } else {
                $all_query[] = $rule_customer_query;
            }
        }

        foreach ($convert['customer_group'] as $key => $customer_group) {
            $rule_customer_group_data  = array(
                'rule_id'           => $rule_id,
                'customer_group_id' => $customer_group,
            );
            $rule_customer_group_query = $this->createInsertQuery('salesrule_customer_group',$rule_customer_group_data);
            if (Bootstrap::getConfig('dev_mode')) {
                $rule_customer_group_import = $this->importRuleData($rule_customer_group_query);
                if (!$rule_customer_group_import) {
                    Bootstrap::logQuery($rule_customer_group_query);
                    var_dump($rule_customer_group_import);
                    exit;
                }
            } else {
                $all_query[] =$rule_customer_group_query;
            }
        }
        if (isset($convert['label'])) {
            foreach ($convert['label'] as $key => $label) {
                $rule_label_data  = array(
                    'rule_id'  => $rule_id,
                    'store_id' => $this->getMapStoreView($label['store_id']),
                    'label'    => $this->getValue($label, 'label'),
                );
                $rule_label_query = $this->createInsertQuery('salesrule_label',$rule_label_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $rule_label_import = $this->importRuleData($rule_label_query);
                    if (!$rule_label_import) {
                        Bootstrap::logQuery($rule_label_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $rule_label_query;
                }

            }
        }


        foreach ($convert['coupon'] as $key => $coupon) {
            $data = $coupon['data'];

            $code = $this->getValue($data, 'code');
            if ($code) {
                $rule_coupon_data   = array(
                    'rule_id'            => $rule_id,
                    'code'               => $code,
                    'usage_limit'        => $this->getValue($data, 'usage_limit'),
                    'usage_per_customer' => $this->getValue($data, 'usage_per_customer'),
                    'times_used'         => $this->getValue($data, 'times_used', 0),
                    'expiration_date'    => $this->getValue($data, 'expiration_date'),
                    'is_primary'         => $this->getValue($data, 'is_primary'),
                    'created_at'         => $this->getValue($data, 'created_at'),
                    'type'               => $this->getValue($data, 'type'),
                );
                $rule_coupon_query = $this->createInsertQuery('salesrule_coupon',$rule_coupon_data);
                $coupon_id = $this->importRuleData($rule_coupon_query);
                if (!$coupon_id) {
                    if ($code) {
                        $msg .= $this->consoleWarning('Rules id ' . $rule_id . ': coupon code ' . $code . ' already exists');
                    }
                }
                if ($coupon_id && isset($coupon['usage'])) {
                    foreach ($coupon['usage'] as $key1 => $usage) {
                        $customer_id = $this->getMapFieldBySource(self::TYPE_CUSTOMER, $usage['customer_id']);
                        if (!$customer_id) {
                            continue;
                        }
                        $coupon_usage_data  = array(
                            'coupon_id'   => $coupon_id,
                            'customer_id' => $customer_id,
                            'times_used'  => $this->getValue($usage, 'times_used', 0),
                        );
                        $coupon_usage_query = $this->createInsertQuery('salesrule_coupon_usage',$coupon_usage_data);
                        if (Bootstrap::getConfig('dev_mode')) {
                            $coupon_usage_import = $this->importRuleData($coupon_usage_query);
                            if (!$coupon_usage_import) {
                                Bootstrap::logQuery($coupon_usage_query);
                                var_dump(1);
                                exit;
                            }
                        } else {
                            $all_query[] = $coupon_usage_query;
                        }
                    }
                }
            }


        }


        if (!Bootstrap::getConfig('dev_mode') && count($all_query) > 0) {
            $all_import = $this->importMultipleData($all_query,'rule');
        }

        return array(
            'result' => 'success',
            'msg'    => $msg,
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

        $id_src    = $this->_notice['process']['cartrules']['id_src'];
        $limit     = $this->_notice['setting']['cartrules'];
        $cartrules = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule WHERE rule_id > ' . $id_src . '
                            ORDER BY rule_id ASC LIMIT ' . $limit,
            )),
        ));
        if (!$cartrules || $cartrules['result'] != 'success') {
            return $this->errorConnector();
        }

        return $cartrules;
    }

    public function getCartrulesExtExport($cartrules)
    {

        $rule_ids          = $this->duplicateFieldValueFromList($cartrules['data'], 'rule_id');
        $rule_id_con       = $this->arrayToInCondition($rule_ids);
        $cartrules_queries = array(
            'catalogrule_customer_group' => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule_customer_group WHERE rule_id IN ' . $rule_id_con,
            ),
            'catalogrule_group_website'  => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule_group_website WHERE rule_id IN ' . $rule_id_con,
            ),
            'catalogrule_product'        => array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule_product WHERE rule_id IN ' . $rule_id_con,
            ),
//                'catalogrule_website' => array(
//                    'type' => 'select',
//                    'query' => 'SELECT * FROM _DBPRF_catalogrule_website WHERE rule_id IN '.$rule_id_con,
//                ),
        );
        $cartrulesExt      = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize($cartrules_queries),
        ));
        if (!$cartrulesExt || $cartrulesExt['result'] != 'success') {
            return $this->errorConnector();
        }
        $rule_product_ids    = $this->duplicateFieldValueFromList($cartrulesExt['data']['catalogrule_product'], 'product_id');
        $cartrulesExtQueries = array();
        if ($rule_product_ids && count($rule_product_ids) > 0) {
            $cartrulesExtQueries['catalogrule_product_price'] = array(
                'type'  => 'select',
                'query' => 'SELECT * FROM _DBPRF_catalogrule_product_price WHERE product_id IN ' . $this->arrayToInCondition($rule_product_ids),
            );

        }
////        $customer_group = $this->duplicateFieldValueFromList($cartrules['data'],'customer_group_ids');
//        if(!isset($cartrules['data'][0]['customer_group_ids'])){
//            $cartrulesExtQueries['catalogrule_customer_group'] = array(
//                'type' => 'select',
//                'query' => 'SELECT * FROM _DBPRF_catalogrule_customer_group WHERE rule_id IN '.$rule_id_con,
//
//            );
//        }
        if (count($cartrulesExtQueries) > 0) {
            $cartrulesRelExt = $this->getConnectorData($this->getConnectorUrl('query'), array(
                'serialize' => true,
                'query'     => serialize($cartrulesExtQueries),
            ));
            if (!$cartrulesRelExt || $cartrulesRelExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $cartrulesExt = $this->syncConnectorObject($cartrulesExt, $cartrulesRelExt);
        }

        return $cartrulesExt;
    }

    public function convertCartruleExport($cartrule, $cartrulesExt)
    {
        $cartrule_data = $cartrule;
        $id            = $cartrule['rule_id'];

        $cartrule_data['id'] = $id;
        unset($cartrule_data['rule_id']);
//        $cartrule_customer_group = $this->getListFromListByField($cartrulesExt['data']['catalogrule_customer_group'],'rule_id',$id);

        $cartrule_data['customer_group'] = array();
        $cartrule_customer_group         = $this->getListFromListByField($cartrulesExt['data']['catalogrule_customer_group'], 'rule_id', $id);
        $customer_group_ids              = array();
        foreach ($cartrule_customer_group as $customer_group) {
            $group_id = $this->_notice['map']['customer_group'][$customer_group['customer_group_id']];
            if ($group_id && !in_array($group_id, $customer_group_ids)) {
                $customer_group_ids[] = $group_id;
            }
        }
        $cartrule_data['customer_group'] = $customer_group_ids;
        $cartrule_data['product']        = array();
        $cartrule_data['product_price']  = array();
        $cartrule_data['product']        = $this->getListFromListByField($cartrulesExt['data']['catalogrule_product'], 'rule_id', $id);
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
        $src_version = $this->_notice['src']['config']['version'];
        $src_version = $this->convertVersion($src_version, 2);
        if ($src_version < 220) {
            $cartrule_data['conditions'] = unserialize($cartrule['conditions_serialized']);
            $cartrule_data['actions']    = unserialize($cartrule['actions_serialized']);
        } else {
            $cartrule_data['conditions'] = json_decode($cartrule['conditions_serialized'], true);
            $cartrule_data['actions']    = json_decode($cartrule['actions_serialized'], true);
        }
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
        return $convert['id'];
    }

    public function checkCartruleImport($convert, $cartrule, $cartrulesExt)
    {
        return $this->getMapFieldBySource(self::TYPE_CART_RULE, $convert['id']);
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
        $url_src    = $this->_notice['src']['cart_url'];
        $url_desc   = $this->_notice['target']['cart_url'];
        $conditions = $this->getValue($convert, 'conditions');
        if ($conditions) {
            $conditions = $this->getConditionsOption($conditions);
            if ($conditions) {
                $conditions = $this->mySerialize($conditions);
                $conditions = str_replace('\\', '\\\\', $conditions);
            }
        }
        $actions = $this->getValue($convert, 'actions');
        if ($actions) {
            $actions = $this->getConditionsOption($actions);
            if ($actions) {
                $actions = $this->mySerialize($actions);
                $actions = str_replace('\\', '\\\\', $actions);


            }
        }
        $cartrule_data   = array(
            'name'                  => $this->getValue($convert, 'name'),
            'description'           => $this->getValue($convert, 'description'),
            'from_date'             => $this->getValue($convert, 'from_date'),
            'to_date'               => $this->getValue($convert, 'to_date'),
            'is_active'             => $this->getValue($convert, 'is_active', 0),
            'conditions_serialized' => $conditions,
            'actions_serialized'    => $actions,
            'stop_rules_processing' => $this->getValue($convert, 'stop_rules_processing', 1),
            'sort_order'            => $this->getValue($convert, 'sort_order', 0),
            'simple_action'         => $this->getValue($convert, 'simple_action'),
            'discount_amount'       => $this->getValue($convert, 'discount_amount', '0.0000'),

        );
        $cartrule_query = $this->createInsertQuery('catalogrule',$cartrule_data);
        $cartrule_id = $this->importCartRuleData($cartrule_query);
        if (!$cartrule_id) {
            if (Bootstrap::getConfig('dev_mode')) {
                Bootstrap::logQuery($cartrule_query);
                var_dump(1);
                exit;
            }

            return $this->errorConnector();
        }
        $this->insertMap($url_src, $url_desc, self::TYPE_CART_RULE, $convert['id'], $cartrule_id);

        return array(
            'result' => 'success',
            'msg'    => '',
            'data'   => $cartrule_id,
        );
    }

    public function afterCartruleImport($cartrule_id, $convert, $cartrule, $cartrulesExt)
    {
        $all_query = array();
        $url_query = $this->getConnectorUrl('query');
        foreach ($convert['customer_group'] as $key => $customer_group) {
            $customer_group_data  = array(
                'rule_id'           => $cartrule_id,
                'customer_group_id' => $customer_group,
            );
            $customer_group_query = $this->createInsertQuery('catalogrule_customer_group',$customer_group_data);
            if (Bootstrap::getConfig('dev_mode')) {
                $customer_group_import = $this->importCartRuleData($customer_group_query);
                if (!$customer_group_import) {
                    Bootstrap::logQuery($customer_group_query);
                    var_dump(1);
                    exit;
                }
            } else {
                $all_query[] = $customer_group_query;
            }
        }
        foreach ($convert['product'] as $key => $product) {
            $product_id = $this->getMapFieldBySource(self::TYPE_PRODUCT, $product['product_id']);
            $group_id   = $this->_notice['map']['customer_group'][$this->getValue($product, 'customer_group_id', 0)];

            if ($product_id) {
                $cartrule_product_data  = array(
                    'rule_id'           => $cartrule_id,
                    'from_time'         => $this->getValue($product_id, 'from_time', 0),
                    'to_time'           => $this->getValue($product_id, 'to_time', 0),
                    'customer_group_id' => $group_id,
                    'product_id'        => $product_id,
                    'action_operator'   => $this->getValue($product_id, 'action_operator'),
                    'action_amount'     => $this->getValue($product_id, 'action_amount', '0.0000'),
                    'action_stop'       => $this->getValue($product_id, 'action_stop', 0),
                    'sort_order'        => $this->getValue($product_id, 'sort_order', 0),
                    'website_id'        => 0,
                );
                $cartrule_product_query = $this->createInsertQuery('catalogrule_product',$cartrule_product_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $cartrule_product_import = $this->importCartRuleData($cartrule_product_query);
                    if (!$cartrule_product_import) {
                        Bootstrap::logQuery($cartrule_product_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_query[] = $cartrule_product_query;
                }
            }
        }

//        foreach ($convert['product_price'] as $key => $product_price){
//            $product_id = $this->getMapStoreView(self::TYPE_PRODUCT,$product_price['product_id']);
//            $group_id                  = $this->_notice['map']['customer_group'][$this->getValue($product_price,'customer_group_id',0)];
//
//            if($product_id){
//                $product_price_data = array(
//                    'rule_date' => $this->getValue($product_price,'rule_date'),
//                    'customer_group_id' => $group_id,
//                    'product_id' => $product_id,
//                    'website_id' => 0,
//                    'rule_price' => $this->getValue($product_price,'rule_price','0.0000'),
//                    'latest_start_date' => $this->getValue($product_price,'latest_start_date'),
//                    'earliest_end_date' => $this->getValue($product_price,'earliest_end_date'),
//                );
//            $cartrule_product_price_query = 'INSERT INTO _DBPRF_catalogrule_product '.$this->arrayToInsertCondition($product_price_data);
//            if(Bootstrap::getConfig('dev_mode')){
//                $cartrule_product_price_import = $this->getConnectorData($url_query,array(
//                    'query' => serialize(array(
//                        'type' => 'insert',
//                        'query' => $cartrule_product_price_query,
//                    )),
//                ));
//                if(!$cartrule_product_price_import || $cartrule_product_price_import['result'] != 'success'){
//                    Bootstrap::logQuery($cartrule_product_price_query;
//                    var_dump(1);exit;
//                }
//            }else{
//                $all_query['cartrule_product_price_query'.$key] = array(
//                    'type' => 'insert',
//                    'query' => $cartrule_product_price_query,
//                );
//            }
//            }
//        }

        if (isset($convert['cartrule_website']) && count($convert['cartrule_website'])) {
            $cartrule_website_ids = $this->getWebsiteIdsTargetByIdSrc($convert['cartrule_website']);
            foreach ($cartrule_website_ids as $key => $website_id) {
                $rule_website_data  = array(
                    'rule_id'    => $cartrule_id,
                    'website_id' => $website_id,
                );
                $rule_website_query = $this->createInsertQuery('catalogrule_website',$rule_website_data);
                if (Bootstrap::getConfig('dev_mode')) {
                    $rule_website_import = $this->importCartRuleData($rule_website_query);
                    if (!$rule_website_import) {
                        Bootstrap::logQuery($rule_website_query);
                        var_dump(1);
                        exit;
                    }
                } else {
                    $all_import[] = $rule_website_query;
                }
            }
        }


        if (!Bootstrap::getConfig('dev_mode') && count($all_query) > 0) {
            $all_import = $this->importMultipleData($all_query,'cartrule');
        }

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

    public function getCountryNameByCode($iso_code)
    {
        $countries = json_decode(file_get_contents("http://country.io/names.json"), true);
        if (isset($countries[$iso_code])) {
            return $countries[$iso_code];
        }

        return null;
    }

    public function deleteTargetCustomer($customer_id)
    {
        if (!$customer_id) {
            return true;
        }

        // 'customer_entity',
        // 'customer_entity_datetime',
        // 'customer_entity_int',
        // 'customer_entity_varchar',
        // 'customer_entity_text',
        // 'customer_entity_decimal',
        // 'customer_grid_flat',
        // 'customer_address_entity',
        // 'customer_address_entity_datetime',
        // 'customer_address_entity_int',
        // 'customer_address_entity_varchar',
        // 'customer_address_entity_text',
        // 'customer_address_entity_decimal',

        $delete = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'customer_address_entity'  => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_address_entity WHERE parent_id = " . $this->escape($customer_id)
                ),
                'customer_grid_flat'       => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_grid_flat WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity_decimal'  => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity_decimal WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity_text'     => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity_text WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity_varchar'  => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity_varchar WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity_int'      => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity_int WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity_datetime' => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity_datetime WHERE entity_id = " . $this->escape($customer_id)
                ),
                'customer_entity'          => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_customer_entity WHERE entity_id = " . $this->escape($customer_id)
                ),
            )),
        ));

        return true;
    }

    public function deleteTargetOrder($order_id)
    {
        if (!$order_id) {
            return true;
        }

        // 'sales_order',
        // 'sales_order_grid',
        // 'sales_order_address',
        // 'sales_order_payment',
        // 'sales_order_status_history',
        // 'sales_order_item',

        $extOrder = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'invoice'    => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_sales_invoice WHERE order_id = ' . $order_id,
                ),
                'creditmemo' => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_sales_creditmemo WHERE order_id = ' . $order_id,
                ),
                'shipment'   => array(
                    'type'  => 'select',
                    'query' => 'SELECT * FROM _DBPRF_sales_shipment WHERE order_id = ' . $order_id,
                ),
            )),
        ));

        $invoice_ids    = $this->duplicateFieldValueFromList($extOrder['data']['invoice'], 'entity_id');
        $shipment_ids   = $this->duplicateFieldValueFromList($extOrder['data']['shipment'], 'entity_id');
        $creditmemo_ids = $this->duplicateFieldValueFromList($extOrder['data']['creditmemo'], 'entity_id');
        $delete_queries = array(
            'sales_order_item'           => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order_item WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_order_status_history' => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order_status_history WHERE parent_id = " . $this->escape($order_id),
            ),

            'sales_order_payment' => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order_payment WHERE parent_id = " . $this->escape($order_id),
            ),
            'sales_order_address' => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order_address WHERE parent_id = " . $this->escape($order_id),
            ),
            'sales_order_grid'    => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order_grid WHERE entity_id = " . $this->escape($order_id),
            ),

        );
        if ($invoice_ids && count($invoice_ids) > 0) {
            $delete_queries['sales_invoice_item'] = array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_invoice_item WHERE parent_id IN " . $this->arrayToInCondition($invoice_ids),
            );
        }
        if ($shipment_ids && count($shipment_ids) > 0) {
            $delete_queries['sales_shipment_item'] = array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_shipment_item WHERE parent_id IN " . $this->arrayToInCondition($shipment_ids),
            );
        }
        if ($creditmemo_ids && count($creditmemo_ids) > 0) {
            $delete_queries['sales_creditmemo_item'] = array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_creditmemo_item WHERE parent_id IN " . $this->arrayToInCondition($creditmemo_ids),
            );
        }
        $delete_queriesExt = array(
            'sales_invoice_grid'    => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_invoice_grid WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_invoice'         => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_invoice WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_shipment_grid'   => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_shipment_grid WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_shipment'        => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_shipment WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_creditmemo_grid' => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_creditmemo_grid WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_creditmemo'      => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_creditmemo WHERE order_id = " . $this->escape($order_id),
            ),
            'sales_order'           => array(
                'type'  => 'query',
                'query' => "DELETE FROM _DBPRF_sales_order WHERE entity_id = " . $this->escape($order_id),
            ),
        );
        $delete            = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize(array_merge($delete_queries, $delete_queriesExt)
            ),
        ));

        return true;
    }

    function deleteTargetExtOrder($type, $id)
    {
        if (!$id) {
            return true;
        }


        $delete = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'serialize' => true,
            'query'     => serialize(array(
                'item' => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_sales_" . $type . "_item WHERE parent_id = " . $id
                ),
                'grid' => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_sales_" . $type . "grid WHERE entity_id = " . $id
                ),
                'data' => array(
                    'type'  => 'query',
                    'query' => "DELETE FROM _DBPRF_sales_" . $type . " WHERE entity_id = " . $id,
                )
            )),
        ));

        return true;
    }

    function convertStringToDatetime($string)
    {
        $date = date_create($string);

        return date_format($date, 'Y-m-d H:i:s');
    }

    //LDV94begin

    public function createAttribute($attribute_code, $backend_type, $frontend_input, $frontend_label, $attribute_set_id)
    {
        $url_src   = $this->_notice['src']['cart_url'];
        $url_desc  = $this->_notice['target']['cart_url'];
        $url_query = $this->getConnectorUrl('query');
        //eav_attribute begin
        $eav_attribute_data = array(
            'entity_type_id'  => 4,
            'attribute_code'  => $attribute_code,
            'attribute_model' => null,
            'backend_model'   => null,
            'backend_type'    => $backend_type,
            'backend_table'   => null,
            'frontend_model'  => null,
            'frontend_input'  => $frontend_input,
            'frontend_label'  => $frontend_label,
            'frontend_class'  => null,
            'source_model'    => null,
            'is_required'     => 0,
            'is_user_defined' => 1,
            'default_value'   => null,
            'is_unique'       => 0,
            'note'            => null,
        );

        $eav_attribute_query  = "INSERT INTO _DBPRF_eav_attribute ";
        $eav_attribute_query  .= $this->arrayToInsertCondition($eav_attribute_data);
        $eav_attribute_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $eav_attribute_query,
                'params' => array(
                    'insert_id' => true,
                )
            )),
        ));

        if (!$eav_attribute_import) {
            //warning
            return $this->errorConnector();
        }

        if ($eav_attribute_import['result'] != 'success') {
            //warning
            return $this->warningSQL($eav_attribute_import);
        }

        $attribute_id = $eav_attribute_import['data'];
        if (!$attribute_id) {
            // warning
            return $this->errorConnector();
        }

//        $this->insertMap($url_src, $url_desc, self::TYPE_ATTR, $item['attribute_id'], $attribute_id, $item['attribute_code']);
        //eav_attribute end

        //eav_entity_attribute begin
        $eav_entity_attribute_data = array(
            'entity_type_id'     => 4,
            'attribute_set_id'   => $this->_notice['map']['attributes'][$attribute_set_id],
            'attribute_group_id' => 7,
            'attribute_id'       => $attribute_id,
            'sort_order'         => 15,
        );

        $eav_entity_attribute_query  = "INSERT INTO _DBPRF_eav_entity_attribute ";
        $eav_entity_attribute_query  .= $this->arrayToInsertCondition($eav_entity_attribute_data);
        $eav_entity_attribute_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $eav_entity_attribute_query,
                'params' => array(
                    'insert_id' => true,
                )
            )),
        ));

        if (!$eav_entity_attribute_import) {
            //warning
            return $this->errorConnector();
        }

        if ($eav_entity_attribute_import['result'] != 'success') {
            //warning
            return $this->warningSQL($eav_entity_attribute_import);
        }
        //eav_entity_attribute end

        //catalog_eav_attribute begin
        $catalog_eav_attribute_data = array(
            'attribute_id'                  => $attribute_id,
            'frontend_input_renderer'       => null,
            'is_global'                     => 1,
            'is_visible'                    => 1,
            'is_searchable'                 => 1,
            'is_filterable'                 => 1,
            'is_comparable'                 => 1,
            'is_visible_on_front'           => 1,
            'is_html_allowed_on_front'      => 1,
            'is_used_for_price_rules'       => 0,
            'is_filterable_in_search'       => 1,
            'used_in_product_listing'       => 0,
            'used_for_sort_by'              => 0,
            'apply_to'                      => null,
            'is_visible_in_advanced_search' => 1,
            'position'                      => 0,
            'is_wysiwyg_enabled'            => 0,
            'is_used_for_promo_rules'       => 0,
            'is_required_in_admin_store'    => 0,
            'is_used_in_grid'               => 0,
            'is_visible_in_grid'            => 0,
            'is_filterable_in_grid'         => 0,
            'search_weight'                 => 1,
            'additional_data'               => null,
        );

        $catalog_eav_attribute_query  = "INSERT INTO _DBPRF_catalog_eav_attribute ";
        $catalog_eav_attribute_query  .= $this->arrayToInsertCondition($catalog_eav_attribute_data);
        $catalog_eav_attribute_import = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'   => 'insert',
                'query'  => $catalog_eav_attribute_query,
                'params' => array(
                    'insert_id' => true,
                )
            )),
        ));

        if (!$catalog_eav_attribute_import) {
            //warning
            return $this->errorConnector();
        }

        if ($catalog_eav_attribute_import['result'] != 'success') {
            //warning
            return $this->warningSQL($catalog_eav_attribute_import);
        }

        //catalog_eav_attribute end

        return $attribute_id;
    }

    public function checkOptionExists($option_value, $attribute_code)
    {
        $url_src                            = $this->_notice['src']['cart_url'];
        $url_desc                           = $this->_notice['target']['cart_url'];
        $url_query                          = $this->getConnectorUrl('query');
        $option_value                       = $this->replaceSpecialWord($option_value);
        $result                             = false;
        $eav_attribute_option_value_queries = array(
            'eav_attribute_option_value' => array(
                'type'  => "select",
                'query' => "SELECT a.option_id FROM _DBPRF_eav_attribute_option_value as a, _DBPRF_eav_attribute_option as b, _DBPRF_eav_attribute as c  WHERE a.value = '" . $option_value . "' AND a.option_id = b.option_id AND b.attribute_id = c.attribute_id AND c.attribute_code = '" . $attribute_code . "'",
            ),
        );

        $eav_attribute_option_value = $this->getConnectorData($url_query, array(
            'serialize' => true,
            'query'     => serialize($eav_attribute_option_value_queries)
        ));

        if (isset($eav_attribute_option_value['data']['eav_attribute_option_value'])) {
            if (count($eav_attribute_option_value['data']['eav_attribute_option_value']) > 0) $result = $eav_attribute_option_value['data']['eav_attribute_option_value'][0]['option_id'];
        }

        return $result;
    }

    public function warningSQL($response)
    {
        $msg = $response['msg'];
        if (Bootstrap::getConfig('dev_mode')) {
            Bootstrap::log($msg,'warning');
        }
        $console = true;
        if ($console) {
            $msg = $this->consoleWarning($msg);
        }

        return array(
            'result' => 'warning',
            'msg'    => $msg,
        );
    }

    public function getProductTaxClassId($id)
    {
        $url_src            = $this->_notice['src']['cart_url'];
        $url_desc           = $this->_notice['target']['cart_url'];
        $tax_product_exists = $this->selectMap($url_src, $url_desc, self::TYPE_TAX_PRODUCT, $id, null, null);
        if ($tax_product_exists) {
            $tax_class_id = $tax_product_exists['id_desc'];
        } else {
            $tax_class_id = 0;
        }

        return $tax_class_id;
    }

    public function getIdByStateName($state_name)
    {
        $url_src    = $this->_notice['src']['cart_url'];
        $url_target = $this->_notice['target']['cart_url'];
        $url_query  = $this->getConnectorUrl('query');

        $regions = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "SELECT dcr.region_id FROM _DBPRF_directory_country_region as dcr WHERE default_name = '" . $state_name . "'"
            )),
        ));
        if (!$regions || $regions['result'] != 'success') {
            return $this->errorConnector();
        }
        if (isset($regions['data'][0])) {
            $region_id = $regions['data'][0]['region_id'];
        } else {
            $region_id = 1;
        }

        return $region_id;
    }

    public function makeMagentoImagePath($image)
    {
        $image           = basename($image);
        $character_array = str_split($image);
        $path            = '';
        if (isset($character_array[0])) {
            $path = $character_array[0];
            if (isset($character_array[1])) {
                $path = $path . '/' . $character_array[1];
            }
        }

        return $path = $path . '/';
    }


    public function formatUrlKey($str)
    {
        $str    = $this->format_magento1($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $str);
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Format URL key to product
     */


    /**
     * Format URL key to category
     */

    public function getNameStoreView($store_id)
    {
        $url_query  = $this->getConnectorUrl('query');
        $store_info = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => "select st.store_id, sw.website_id,st.name as store_name,sw.name as website_name,st.group_id,sg.name as group_name 
                            from _DBPRF_store as st 
                            JOIN _DBPRF_store_website as sw on st.website_id = sw.website_id 
                            JOIN _DBPRF_store_group as sg on st.group_id = sg.group_id 
                            WHERE st.store_id= " . $store_id,
            )),
        ));
        if (!$store_info || $store_info['result'] != 'success') {
            return '';
        }
        $store_info   = $store_info['data'][0];
        $store_name   = $store_info['store_name'];
        $website_name = $store_info['website_name'];
        $group_name   = $store_info['group_name'];

        return $website_name . " \n " . $group_name . " \n " . $store_name;
    }

    public function getCategoryUrlKey($url_key, $store_id, $attribute_id,$name){
        if(!$url_key && $name){
            $url_key = $this->generateCategoryUrlKey($name);
        }
        if(!$url_key){
            return false;
        }
        $i           = 0;
        $cur_url_key = $url_key;
        while ($this->checkExistUrlKey($cur_url_key, $store_id, $attribute_id, 'category')) {
            $i++;
            $cur_url_key = $url_key . '-' . $i;
        }
        return $cur_url_key;
    }

    public function generateCategoryUrlKey($name)
    {

        $string = $name;
        $string = preg_replace('/ä/', 'ae', $string);
        $string = preg_replace('/ü/', 'ue', $string);
        $string = preg_replace('/ö/', 'oe', $string);
        $string = preg_replace('/ß/', 'ss', $string);
        $name   = $string;

        $i           = 0;
        $url_def     = $this->formatUrlKey($name);
        $cur_url_key = $url_def;

        return $cur_url_key;
    }

    public function getProductUrlKey($url_key, $store_id, $attribute_id,$name){
        if(!$url_key){
            $url_key = $this->generateProductUrlKey($name);
        }
        $i           = 0;
        $cur_url_key = $url_key;
        while ($this->checkExistUrlKey($cur_url_key, $store_id, $attribute_id, 'product')) {
            $i++;
            $cur_url_key = $url_key . '-' . $i;
        }
        return $cur_url_key;
    }
    function getCategoryUrlPath($url_path, $store_id, $attribute_id,$default=null)
    {
        if(!$url_path){
            $url_path = $default;
        }
        if(!$url_path){
            return '';
        }
        $has_suffix = false;
        $no_suffix = '';
        if(strpos($url_path,'.html') !== false){
            $has_suffix = true;
            $no_suffix = str_replace('.html','',$url_path);
        }
        $cur_url_path = $url_path;
        $i    = 0;
        while ($this->checkExistUrlPath($cur_url_path, $store_id, $attribute_id, 'category')) {
            $i++;
            if($has_suffix){
                $cur_url_path = $no_suffix.'-'.$i.'.html';
            }else{
                $cur_url_path = $url_path . '-' . $i;
            }
        }

        return $cur_url_path;
    }
    function getProductUrlPath($url_path, $store_id, $attribute_id,$default=null)
    {
        if(!$url_path){
            $url_path = $default;
        }
        if(!$url_path){
            return '';
        }
        $has_suffix = false;
        $no_suffix = '';
        if(strpos($url_path,'.html') !== false){
            $has_suffix = true;
            $no_suffix = str_replace('.html','',$url_path);
        }
        $cur_url_path = $url_path;
        $i    = 0;
        while ($this->checkExistUrlPath($cur_url_path, $store_id, $attribute_id, 'product')) {
            $i++;
            if($has_suffix){
                $cur_url_path = $no_suffix.'-'.$i.'.html';
            }else{
                $cur_url_path = $url_path . '-' . $i;
            }
        }

        return $cur_url_path;
    }
    public function generateProductUrlKey($name)
    {

        $string = $name;
        $string = preg_replace('/ä/', 'ae', $string);
        $string = preg_replace('/ü/', 'ue', $string);
        $string = preg_replace('/ö/', 'oe', $string);
        $string = preg_replace('/ß/', 'ss', $string);
        $name   = $string;

        $i           = 0;
        $url_def     = $this->formatUrlKey($name);
        $cur_url_key = $url_def;

        return $cur_url_key;
    }

    // public function decodeStringFromSource($string){
    //     $string = preg_replace('/ÃŸ/', 'ß',$string);
    //     $string = preg_replace('/Ã¤/', 'ä',$string);
    //     $string = preg_replace('/Ã¶/', 'ö',$string);
    //     $string = preg_replace('/Ã¼/', 'ü',$string);
    //     $string = preg_replace('/Â°/', '°',$string);
    //     $string = preg_replace('/Ã˜/', 'Ø',$string);
    //     // $string = preg_replace('/&auml;/', 'ä',$string);
    //     // $string = preg_replace('/&ouml;/', 'ö',$string);
    //     // $string = preg_replace('/&uuml;/', 'ü',$string);
    //     // $string = preg_replace('/&szlig;/', 'ß',$string);

    //     // $string = utf8_decode($string);
    //     return $string;
    // }

    //code Magento begin
    protected $_convertTable_magento1 = array(
        '&amp;' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á'     => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae', 'Ç' => 'c',
        'È'     => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï'     => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø'     => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß'     => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ'     => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì'     => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô'     => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û'     => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'Ā' => 'a',
        'ā'     => 'a', 'Ă' => 'a', 'ă' => 'a', 'Ą' => 'a', 'ą' => 'a', 'Ć' => 'c',
        'ć'     => 'c', 'Ĉ' => 'c', 'ĉ' => 'c', 'Ċ' => 'c', 'ċ' => 'c', 'Č' => 'c',
        'č'     => 'c', 'Ď' => 'd', 'ď' => 'd', 'Đ' => 'd', 'đ' => 'd', 'Ē' => 'e',
        'ē'     => 'e', 'Ĕ' => 'e', 'ĕ' => 'e', 'Ė' => 'e', 'ė' => 'e', 'Ę' => 'e',
        'ę'     => 'e', 'Ě' => 'e', 'ě' => 'e', 'Ĝ' => 'g', 'ĝ' => 'g', 'Ğ' => 'g',
        'ğ'     => 'g', 'Ġ' => 'g', 'ġ' => 'g', 'Ģ' => 'g', 'ģ' => 'g', 'Ĥ' => 'h',
        'ĥ'     => 'h', 'Ħ' => 'h', 'ħ' => 'h', 'Ĩ' => 'i', 'ĩ' => 'i', 'Ī' => 'i',
        'ī'     => 'i', 'Ĭ' => 'i', 'ĭ' => 'i', 'Į' => 'i', 'į' => 'i', 'İ' => 'i',
        'ı'     => 'i', 'Ĳ' => 'ij', 'ĳ' => 'ij', 'Ĵ' => 'j', 'ĵ' => 'j', 'Ķ' => 'k',
        'ķ'     => 'k', 'ĸ' => 'k', 'Ĺ' => 'l', 'ĺ' => 'l', 'Ļ' => 'l', 'ļ' => 'l',
        'Ľ'     => 'l', 'ľ' => 'l', 'Ŀ' => 'l', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
        'Ń'     => 'n', 'ń' => 'n', 'Ņ' => 'n', 'ņ' => 'n', 'Ň' => 'n', 'ň' => 'n',
        'ŉ'     => 'n', 'Ŋ' => 'n', 'ŋ' => 'n', 'Ō' => 'o', 'ō' => 'o', 'Ŏ' => 'o',
        'ŏ'     => 'o', 'Ő' => 'o', 'ő' => 'o', 'Œ' => 'oe', 'œ' => 'oe', 'Ŕ' => 'r',
        'ŕ'     => 'r', 'Ŗ' => 'r', 'ŗ' => 'r', 'Ř' => 'r', 'ř' => 'r', 'Ś' => 's',
        'ś'     => 's', 'Ŝ' => 's', 'ŝ' => 's', 'Ş' => 's', 'ş' => 's', 'Š' => 's',
        'š'     => 's', 'Ţ' => 't', 'ţ' => 't', 'Ť' => 't', 'ť' => 't', 'Ŧ' => 't',
        'ŧ'     => 't', 'Ũ' => 'u', 'ũ' => 'u', 'Ū' => 'u', 'ū' => 'u', 'Ŭ' => 'u',
        'ŭ'     => 'u', 'Ů' => 'u', 'ů' => 'u', 'Ű' => 'u', 'ű' => 'u', 'Ų' => 'u',
        'ų'     => 'u', 'Ŵ' => 'w', 'ŵ' => 'w', 'Ŷ' => 'y', 'ŷ' => 'y', 'Ÿ' => 'y',
        'Ź'     => 'z', 'ź' => 'z', 'Ż' => 'z', 'ż' => 'z', 'Ž' => 'z', 'ž' => 'z',
        'ſ'     => 'z', 'Ə' => 'e', 'ƒ' => 'f', 'Ơ' => 'o', 'ơ' => 'o', 'Ư' => 'u',
        'ư'     => 'u', 'Ǎ' => 'a', 'ǎ' => 'a', 'Ǐ' => 'i', 'ǐ' => 'i', 'Ǒ' => 'o',
        'ǒ'     => 'o', 'Ǔ' => 'u', 'ǔ' => 'u', 'Ǖ' => 'u', 'ǖ' => 'u', 'Ǘ' => 'u',
        'ǘ'     => 'u', 'Ǚ' => 'u', 'ǚ' => 'u', 'Ǜ' => 'u', 'ǜ' => 'u', 'Ǻ' => 'a',
        'ǻ'     => 'a', 'Ǽ' => 'ae', 'ǽ' => 'ae', 'Ǿ' => 'o', 'ǿ' => 'o', 'ə' => 'e',
        'Ё'     => 'jo', 'Є' => 'e', 'І' => 'i', 'Ї' => 'i', 'А' => 'a', 'Б' => 'b',
        'В'     => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ж' => 'zh', 'З' => 'z',
        'И'     => 'i', 'Й' => 'j', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n',
        'О'     => 'o', 'П' => 'p', 'Р' => 'r', 'С' => 's', 'Т' => 't', 'У' => 'u',
        'Ф'     => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sch',
        'Ъ'     => '-', 'Ы' => 'y', 'Ь' => '-', 'Э' => 'je', 'Ю' => 'ju', 'Я' => 'ja',
        'а'     => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ж'     => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l',
        'м'     => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т'     => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш'     => 'sh', 'щ' => 'sch', 'ъ' => '-', 'ы' => 'y', 'ь' => '-', 'э' => 'je',
        'ю'     => 'ju', 'я' => 'ja', 'ё' => 'jo', 'є' => 'e', 'і' => 'i', 'ї' => 'i',
        'Ґ'     => 'g', 'ґ' => 'g', 'א' => 'a', 'ב' => 'b', 'ג' => 'g', 'ד' => 'd',
        'ה'     => 'h', 'ו' => 'v', 'ז' => 'z', 'ח' => 'h', 'ט' => 't', 'י' => 'i',
        'ך'     => 'k', 'כ' => 'k', 'ל' => 'l', 'ם' => 'm', 'מ' => 'm', 'ן' => 'n',
        'נ'     => 'n', 'ס' => 's', 'ע' => 'e', 'ף' => 'p', 'פ' => 'p', 'ץ' => 'C',
        'צ'     => 'c', 'ק' => 'q', 'ר' => 'r', 'ש' => 'w', 'ת' => 't', '™' => 'tm',
    );

    public function getConvertTable_magento1()
    {
        return $this->_convertTable_magento1;
    }

    public function format_magento1($string)
    {
        return strtr($string, $this->getConvertTable_magento1());
    }

    public function getWebsiteIdByStoreId($store_id)
    {
        if ($store_id == 0) {
            return 0;
        }

        return $this->_notice['target']['site'][$store_id];
//        $url_query  = $this->getConnectorUrl('query');
//        $website    = $this->getConnectorData($url_query, array(
//            'query' => serialize(array(
//                'type'  => 'select',
//                'query' => 'SELECT website_id FROM _DBPRF_store WHERE store_id = "' . $store_id . '"',
//            )),
//        ));
//        $website_id = $website['data'][0]['website_id'];
////        var_dump($website_id);exit;
//        if (!$website || !$website_id) {
//            return 1;
//        }
//
//        return $website_id;
    }

//    function getMapWebsiteId($website_src_id){
//
//    }
//

    function getRequestPath($type, $request_path, $store_id = null)
    {
        $has_suffix = false;
        $no_suffix = '';
        if(strpos($request_path,'.html') !== false){
            $has_suffix = true;
            $no_suffix = str_replace('.html','',$request_path);
        }
        $path = $request_path;
        $i    = 0;
        while ($this->checkExistRequestPath($type, $path, $store_id)) {
            $i++;
            if($has_suffix){
                $path = $no_suffix.'-'.$i.'.html';
            }else{
                $path = $request_path . '-' . $i;
            }
        }

        return $path;
    }

    function checkExistRequestPath($type, $request_path, $store_id)
    {
//        $query     = 'select * from _DBPRF_url_rewrite WHERE request_path = "' . $request_path . '"
//                            AND store_id = "' . $store_id . '"
//                            AND entity_type = "' . $type . '"';
        $url_query = $this->getConnectorUrl('query');
        $where = array(
            'url' => $request_path,
            'type' => $type
        );
        if($store_id){
            $where['store_id'] = $store_id;
        }
        $url_data  = $this->getConnectorData($url_query, array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'select * from _DBPRF_url_rewrite WHERE '.$this->arrayToWhereCondition($where),
            )),
        ));
        if (!$url_data || $url_data['result'] != 'success') {
            return false;
        }

        return count($url_data['data']) > 0;
    }

    function checkExistUrlKey($value, $store_id, $attribute_id, $type)
    {
        $query = 'SELECT * FROM _DBPRF_catalog_' . $type . '_entity_varchar WHERE store_id = "' . $store_id . '" and value = "' . $value . '" and attribute_id = "' . $attribute_id . '"';
        $res   = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => $query,
            )),
        ));
        if (!$res || $res['result'] != 'success') {
            return false;
        }

        return count($res['data']) > 0;

    }
    function checkExistUrlPath($value, $store_id, $attribute_id, $type)
    {
        $query = 'SELECT * FROM _DBPRF_catalog_' . $type . '_entity_varchar WHERE store_id = "' . $store_id . '" and value = "' . $value . '" and attribute_id = "' . $attribute_id . '"';
        $res   = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => $query,
            )),
        ));
        if (!$res || $res['result'] != 'success') {
            return false;
        }

        return count($res['data']) > 0;

    }

    function checkExistUrlCms($type, $url)
    {
        $query    = 'select * from _DBPRF_cms_' . $type . '
                  where identifier = "' . $url . '"';
        $url_data = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => $query,
            )),
        ));
        if (!$url_data || $url_data['result'] != 'success') {
            return false;
        }

        return count($url_data['data']) > 0;
    }

    function getPageLayout($page_layout)
    {
        $layouts = array(
            '1' => 'one_',
            '2' => 'two_',
            '3' => 'three_',
        );
        foreach ($layouts as $key => $layout) {
            $page_layout = preg_replace("/($layout)/i", $key, $page_layout);
        }
        $page_layout = str_replace('_', '-', $page_layout);

        return $page_layout;
    }

    function getLayoutUpdateXml($layout_xml)
    {
        preg_match_all('/\<block\>(.+)\<\/block\>/', $layout_xml, $match);
        if (count($match) > 0) {
            foreach ($match[0] as $key => $value) {
                $layout_xml = str_replace($value, '<block>' . str_replace('_', '/', $match[1][$key]) . '</block>', $layout_xml);
            }
        }

        return $layout_xml;

    }

    function getTypeRule($type)
    {
        if (!strpos($type, '_')) {
//            $type = str_replace('\\','\\\\',$type);
//            print_r(1);exit;
            return $type;
        }
//        print_r($type);exit;
        $types  = explode('/', $type);
        $models = explode('_', $types[1]);
        $res    = 'Magento\\' . ucfirst($types[0]) . '\\Model';
        foreach ($models as $model) {
            $res .= '\\' . ucfirst($model);
        }
        $res = str_replace('Salesrule', 'SalesRule', $res);
        $res = str_replace('Catalogrule', 'CatalogRule', $res);

        return $res;
    }

    function getConditionsOption($conditions)
    {
        $type = null;
        if (isset($conditions['conditions'])) {
            $type = 'conditions';
        } elseif (isset($conditions['actions'])) {
            $type = 'actions';
        }
        $attribute = array(
            'base_subtotal',
            'qty',
            'base_row_total',
            'quote_item_price',
            'quote_item_qty',
            'quote_item_row_total',
            'total_qty',
            'weight',
            'shipping_method',
            'postcode',
            'region',
            'region_id',
            'country_id'
        );
        switch ($conditions['attribute']) {
            case null:
                break;
            case 'category_ids':
                $category_ids = explode(',', $conditions['value']);
                if (count($category_ids) > 0) {
                    foreach ($category_ids as $key1 => $category_id) {
                        if (isset($this->_notice['map']['categoryData'][$category_id])) {
                            $category_ids[$key1] = $this->_notice['map']['categoryData'][$category_id];
                        } else {
                            $map_cate = $this->getMapFieldBySource(self::TYPE_CATEGORY, $category_id);
                            if ($map_cate) {
                                $category_ids[$key1] = $map_cate;
                            } else {
                                unset($category_ids[$key1]);
                            }
                        }

                    }
                }
                $category_ids        = implode(',', $category_ids);
                $conditions['value'] = $category_ids;
                break;
            case 'attribute_set_id':
                $conditions['value'] = $this->_notice['map']['attributes'][$conditions['value']];
                break;
            default:
                if (!in_array($conditions['attribute'], $attribute)) {
                    return null;
                }
                break;
        }
        $conditions['type'] = $this->getTypeRule($conditions['type']);
        if ($type && isset($conditions[$type])) {
            if (!is_array($conditions[$type])) {
                $condition = $this->getConditionsOption($conditions[$type]);
                if ($condition) {
                    $conditions[$type] = $condition;
                } else {
                    unset($conditions[$type]);
                }
            } else {
                foreach ($conditions[$type] as $key => $value) {
                    $condition = $this->getConditionsOption($value);
                    if ($condition) {
                        $conditions[$type][$key] = $condition;
                    } else {
                        unset($conditions[$type][$key]);
                    }
                }
            }

        }

        return $conditions;
    }

    function getAttributeGroupId($attribute_set_id, $group_name)
    {
        $group_ids = $this->getConnectorData($this->getConnectorUrl('query'), array(
            'query' => serialize(array(
                'type'  => 'select',
                'query' => 'SELECT * FROM `_DBPRF_eav_attribute_group` WHERE `attribute_set_id` = ' . $attribute_set_id . ' and (`attribute_group_name` = "' . $group_name . '" OR default_id = 1)',
            )),
        ));
        if (!$group_ids || $group_ids['result'] != 'success') {
            return 0;
        }
        $default  = 0;
        $group_id = null;
        foreach ($group_ids['data'] as $key => $value) {
            if ($group_name && $value['attribute_group_name'] == $group_name) {
                $group_id = $value['attribute_group_id'];

            }
            if ($value['default_id'] == 1) {
                $default = $value['attribute_group_id'];
            }
        }

        return $group_id ? $group_id : $default;
    }

    function mySerialize($array)
    {
        $version_target = $this->_notice['target']['config']['version'];
        $version_target = $this->convertVersion($version_target, 2);
        if ($version_target < 220) {
            return serialize($array);
        }

        return json_encode($array);
    }

    function replaceSpecialWord($str)
    {
        $str = str_replace("'", "\'", $str);
        $str = str_replace('"', '\"', $str);

        return $str;
    }

    function getWebsiteIdsTargetByIdSrc($website_ids)
    {
        $product_website_ids = array();
        foreach ($website_ids as $website_id_src) {
            $website_id_tar = $this->getValue($this->_notice['map']['site'], $website_id_src);
            if ($website_id_tar && is_array($website_id_tar)) {
                foreach ($website_id_tar as $website_id) {
                    if (!in_array($website_id, $product_website_ids)) {
                        $product_website_ids[] = $website_id;
                    }
                }
            }
        }

        return $product_website_ids;
    }
    //code Magento end
    function updateSeoCate($category,$categoriesExt){
        $id_src = $category['entity_id'];
        $id_target = $this->getMapFieldBySource(self::TYPE_CATEGORY, $id_src);
        if(!$id_target){
            return;
        }
        $url_query = $this->getConnectorUrl('query');
        $query_delete = "delete from _DBPRF_url_rewrite where entity_type ='category' and entity_id = '".$id_target."'";
        $this->getConnectorData($url_query,array(
            'query' => serialize(array(
                'type' => 'query',
                'query' => $query_delete,
            )),
        ));
        if ($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']) {
            $model_seo   = Bootstrap::getModel($this->_notice['config']['seo_plugin']);
            $url_rewrite = $model_seo->getCategoriesSeoExport($this, $category, $categoriesExt);
            foreach ($url_rewrite as $key => $rewrite) {
                $path     = $rewrite['request_path'];
                $store_id = $this->getMapStoreView($this->getValue($rewrite, 'store_id', 0));
                if (!$path) {
                    continue;
                }
                $path              = $this->getRequestPath('category', $path, $store_id);
                $url_rewrite_data  = array(
                    'entity_type'      => 'category',
                    'entity_id'        => $id_target,
                    'request_path'     => $path,
                    'target_path'      => 'catalog/category/view/id/' . $id_target,
                    'redirect_type'    => 0,
                    'store_id'         => $store_id,
                    'description'      => $this->getValue($rewrite, 'description'),
                    'is_autogenerated' => 1,
                    'metadata'         => null,
                );
                $url_rewrite_query = "INSERT INTO _DBPRF_url_rewrite ";
                $url_rewrite_query .= $this->arrayToInsertCondition($url_rewrite_data);

                $url_rewrite_import = $this->getConnectorData($this->getConnectorUrl('query'), array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $url_rewrite_query,
                        'params' => array(
                            'insert_id' => true,
                        ),
                    )),
                ));

                if (!$url_rewrite_import) {
                    //warning
                    if (Bootstrap::getConfig('dev_mode')) {
                        Bootstrap::logQuery($url_rewrite_query);
                        var_dump(1);
                        exit;
                    }

                    return $this->errorConnector();
                }

                if ($url_rewrite_import['result'] != 'success') {
                    //warning
                    return $this->warningSQL($url_rewrite_import);
                }

            }
        }
    }
    function updateSeoPrd($product,$productsExt){
        $id_src = $product['entity_id'];
        $id_target = $this->getMapFieldBySource(self::TYPE_PRODUCT, $id_src);
        if(!$id_target){
            return;
        }
        $url_query = $this->getConnectorUrl('query');
        $query_delete = "delete from _DBPRF_url_rewrite where entity_type ='product' and entity_id = '".$id_target."'";
        $this->getConnectorData($url_query,array(
            'query' => serialize(array(
                'type' => 'query',
                'query' => $query_delete,
            )),
        ));
        if ($this->_notice['config']['seo'] && $this->_notice['config']['seo_plugin']) {
            $model_seo   = Bootstrap::getModel($this->_notice['config']['seo_plugin']);
            $url_rewrite = $model_seo->getProductSeoExport($this, $product, $productsExt);
            $metadata    = null;
            $target_path = null;
            foreach ($url_rewrite as $key => $rewrite) {
                $category_id = $this->getValue($rewrite, 'category_id');
                $store_id    = $this->getMapStoreView($this->getValue($rewrite, 'store_id', 0));
                if ($category_id) {
                    $category_id = $this->getMapFieldBySource(self::TYPE_CATEGORY, $category_id);
                    if (!$category_id) {
                        continue;
                    }
                    $target_path = 'catalog/product/view/id/' . $id_target . '/category/' . $category_id;
                    $metadata    = $this->mySerialize(array(
                        'category_id' => $category_id,
                    ));
                } else {
                    $target_path = 'catalog/product/view/id/' . $id_target;
                }
                $request_path      = $this->getRequestPath('product', $rewrite['request_path'], $store_id);
                $url_rewrite_data  = array(
                    'entity_type'      => 'product',
                    'entity_id'        => $id_target,
                    'request_path'     => $request_path,
                    'target_path'      => $target_path,
                    'redirect_type'    => 0,
                    'store_id'         => $store_id,
                    'description'      => $this->getValue($rewrite, 'description'),
                    'is_autogenerated' => 1,
                    'metadata'         => $metadata,
                );
                $url_rewrite_query = 'INSERT INTO _DBPRF_url_rewrite ';
                $url_rewrite_query .= $this->arrayToInsertCondition($url_rewrite_data);
                $url_rewrite_import = $this->getConnectorData($url_query, array(
                    'query' => serialize(array(
                        'type'   => 'insert',
                        'query'  => $url_rewrite_query,
                        'params' => array(
                            'insert_id' => true,
                        ),
                    )),
                ));
                if (!$url_rewrite_import) {
                    Bootstrap::logQuery($url_rewrite_query);
                    var_dump(1);
                    exit;
                }
            }
        }
    }

    protected function importCategoryData($query,$params=true){
        return $this->importData($query,'category',$params);
    }
    protected function importProductData($query,$params=true){
        return $this->importData($query,'product',$params);
    }
    protected function importCustomerData($query,$params=true){
        return $this->importData($query,'customer',$params);
    }
    protected function importOrderData($query,$params=true){
        return $this->importData($query,'order',$params);
    }
    protected function importReviewData($query,$params=true){
        return $this->importData($query,'review',$params);
    }
    protected function importPageData($query,$params=true){
        return $this->importData($query,'page',$params);
    }
    protected function importBlockData($query,$params=true){
        return $this->importData($query,'block',$params);
    }
    protected function importRuleData($query,$params=true){
        return $this->importData($query,'rule',$params);
    }
    protected function importCartRuleData($query,$params=true){
        return $this->importData($query,'cartrule',$params);
    }
}
