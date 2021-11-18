<?php
class plugins_addonproduct_db
{
    /**
     * @param $config
     * @param bool $params
     * @return mixed|null
     * @throws Exception
     */
    public function fetchData($config, $params = false)
    {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';
        $dateFormat = new component_format_date();

        if ($config['context'] === 'all') {
            switch ($config['type']) {
                case 'pages':
                    $limit = '';
                    if ($config['offset']) {
                        $limit = ' LIMIT 0, ' . $config['offset'];
                        if (isset($config['page']) && $config['page'] > 1) {
                            $limit = ' LIMIT ' . (($config['page'] - 1) * $config['offset']) . ', ' . $config['offset'];
                        }
                    }

                    $sql = "SELECT adp.id_adp, adp.price_adp, mapc.name_adp, adp.date_register
						FROM mc_addon_product AS adp
						JOIN mc_addon_product_content mapc on adp.id_adp = mapc.id_adp
						JOIN mc_lang AS lang ON ( mapc.id_lang = lang.id_lang )
							WHERE mapc.id_lang = :default_lang " . $limit;

                    if (isset($config['search'])) {
                        $cond = '';
                        if (is_array($config['search']) && !empty($config['search'])) {
                            $nbc = 1;
                            foreach ($config['search'] as $key => $q) {
                                if ($q !== '') {
                                    $cond .= 'AND ';
                                    $p = 'mapc' . $nbc;
                                    switch ($key) {
                                        case 'id_adp':
                                            $cond .= 'mapc.' . $key . ' = :' . $p . ' ';
                                            break;
                                        case 'name_adp':
                                            $cond .= 'mapc.' . $key . ' = :' . $p . ' ';
                                            break;
                                        case 'date_register':
                                            $q = $dateFormat->date_to_db_format($q);
                                            $cond .= "mapc." . $key . " LIKE CONCAT('%', :" . $p . ", '%') ";
                                            break;
                                    }
                                    $params[$p] = $q;
                                    $nbc++;
                                }
                            }

                            $sql = "SELECT adp.id_adp, adp.price_adp, mapc.name_adp, adp.date_register
						FROM mc_addon_product AS adp
						JOIN mc_addon_product_content mapc on adp.id_adp = mapc.id_adp
						JOIN mc_lang AS lang ON ( mapc.id_lang = lang.id_lang )
							WHERE mapc.id_lang = :default_lang
						$cond ORDER BY adp.id_adp" . $limit;
                        }
                    }
                    break;
                case 'page':
                    $sql = 'SELECT p.*,c.*,lang.*
							FROM mc_addon_product AS p
							JOIN mc_addon_product_content AS c ON(c.id_adp = p.id_adp)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE p.id_adp = :edit';
                    break;
                case 'pagelang':
                    $sql = 'SELECT p.id_adp,p.price_adp,c.name_adp
							FROM mc_addon_product AS p
							JOIN mc_addon_product_content AS c ON(c.id_adp = p.id_adp)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							WHERE lang.iso_lang = :iso';
                    break;
                case 'lastPages':
                    $sql = "SELECT p.*,c.*,lang.*
							FROM mc_addon_product AS p
							JOIN mc_addon_product_content AS c ON(c.id_adp = p.id_adp)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
							ORDER BY p.id_adp DESC
							LIMIT 1";
                    break;
            }

            return $sql ? component_routing_db::layer()->fetchAll($sql, $params) : null;
        }
		elseif ($config['context'] === 'one') {
            switch ($config['type']) {
                case 'root':
                    $sql = 'SELECT * FROM mc_addon_product ORDER BY id_adp DESC LIMIT 0,1';
                    break;
                case 'page':
                    $sql = 'SELECT * FROM mc_addon_product WHERE `id_adp` = :id_adp';
                    break;
                case 'contentPage':
                    $sql = 'SELECT * FROM `mc_addon_product_content` 
                        WHERE `id_adp` = :id_adp AND `id_lang` = :id_lang';
                    break;
                case 'paramvalue':
                    $sql = 'SELECT p.id_adp,p.price_adp,c.name_adp
							FROM mc_addon_product AS p
							JOIN mc_addon_product_content AS c ON(c.id_adp = p.id_adp)
							JOIN mc_lang AS lang ON(c.id_lang = lang.id_lang)
                            WHERE p.id_adp = :id_adp';
                    break;
                case 'cartpay':
                    $sql = 'SELECT * FROM `mc_cartpay_addon_product` WHERE id_items = :id AND id_adp = :id_adp';
                    break;
            }

            return $sql ? component_routing_db::layer()->fetch($sql, $params) : null;
        }
    }
    /**
     * @param $config
     * @param array $params
     * @return bool|string
     */
    public function insert($config,$params = array())
    {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';

        switch ($config['type']) {
            case 'page':
                $sql = "INSERT INTO mc_addon_product (price_adp, date_register)
                        VALUE (:price_adp, NOW())";
                break;
            case 'contentPage':
                $sql = 'INSERT INTO mc_addon_product_content (id_adp, id_lang, name_adp) 
				  		VALUES (:id_adp, :id_lang, :name_adp)';
                break;
            case 'cartpay':
                $sql = 'INSERT INTO mc_cartpay_addon_product (id_adp, id_items, content_adp, infos_adp)
				  		VALUES (:id_adp, :id_items, :content_adp, :infos_adp)';
                break;
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->insert($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }
    /**
     * @param $config
     * @param array $params
     * @return bool|string
     */
    public function update($config,$params = array())
    {
        if (!is_array($config)) return '$config must be an array';

        $sql = '';

        switch ($config['type']) {
            case 'page':
                $sql = 'UPDATE mc_addon_product 
						SET 
							price_adp = :price_adp

                		WHERE id_adp = :id_adp';
                break;
            case 'contentPage':
                $sql = 'UPDATE mc_addon_product_content 
						SET 
							name_adp = :name_adp
                		WHERE id_adp = :id_adp AND id_lang = :id_lang';
                break;
            /*case 'order':
                $sql = 'UPDATE mc_vat
						SET order_tr = :order_tr
                		WHERE id_vat = :id_vat';
                break;*/
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->update($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }
    /**
     * @param $config
     * @param array $params
     * @return bool|string
     */
    public function delete($config, $params = array())
    {
        if (!is_array($config)) return '$config must be an array';
        $sql = '';

        switch ($config['type']) {
            case 'delPages':
                $sql = 'DELETE FROM mc_vat 
						WHERE id_vat IN ('.$params['id'].')';
                $params = array();
                break;
        }

        if($sql === '') return 'Unknown request asked';

        try {
            component_routing_db::layer()->delete($sql,$params);
            return true;
        }
        catch (Exception $e) {
            return 'Exception reçue : '.$e->getMessage();
        }
    }
}