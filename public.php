<?php
/**
 * Class plugins_attribute_public
 */
class plugins_addonproduct_public extends plugins_addonproduct_db
{
    /**
     * @var object
     */
    protected $template, $data, $modelCatalog;

    /**
     * @var int $id
     */
    protected $id, $cart, $settingComp,$settings;
    public $contentData,$content;

    /**
     * frontend_controller_home constructor.
     * @param stdClass $t
     */
    public function __construct($t = null) {
        $this->template = $t instanceof frontend_model_template ? $t : new frontend_model_template();
        $formClean = new form_inputEscape();
        $this->data = new frontend_model_data($this, $this->template);
        $this->settingComp = new component_collections_setting();
        $this->settings = $this->settingComp->getSetting();

        if (http_request::isGet('id')) $this->id = $formClean->numeric($_GET['id']);
        if (http_request::isPost('contentData')) $this->contentData = $formClean->arrayClean($_POST['contentData']);
        /*if (http_request::isPost('addonContent')) {
            $array = $_POST['addonContent'];
            foreach($array as $key => $arr) {
                foreach($arr as $k => $v) {
                    $array[$key][$k] = ($k == 'content_adp' OR $k == 'infos_adp') ? $formClean->cleanQuote($v) : $formClean->simpleClean($v);
                }
            }
            $this->content = $array;
        }*/
        if (http_request::isPost('addonContent')) $this->content = $formClean->arrayClean($_POST['addonContent']);
    }

    /**
     * Assign data to the defined variable or return the data
     * @param string $type
     * @param string|int|null $id
     * @param string $context
     * @param boolean $assign
     * @return mixed
     */
    private function getItems($type, $id = null, $context = null, $assign = true)
    {
        return $this->data->getItems($type, $id, $context, $assign);
    }

    /**
     * @param $row
     * @return array
     */
    private function setItemData($row)
    {
        $data = array();
        if ($row != null) {
            $data['id'] = $row['id_adp'];
            $data['name'] = $row['name_adp'];
            $data['price'] = $row['price_adp'];
        }
        return $data;
    }

    /**
     * @return array|null
     */
    public function getBuildList(){
        $collection = $this->getItems('pagelang',array('iso'=> $this->template->lang), 'all', false);
        if($collection != null) {
            $newarr = array();
            foreach ($collection as &$item) {
                $newarr[] = $this->setItemData($item);
            }
            return $newarr;
        }
        else{
            return null;
        }
    }

	/**
	 * Update data
	 * @param $data
	 * @throws Exception
	 */
	private function add($data) {
		switch ($data['type']) {
			case 'cartpay':
				parent::insert(
					array(
						'context' => $data['context'],
						'type' => $data['type']
					),
					$data['data']
				);
				break;
		}
	}

    // ---- Cartpay

    /**
     * @return string
     */
    public function add_to_cart_params(): string {
        $this->template->assign('addonproduct',$this->getBuildList());
        return $this->template->fetch('addonproduct/add-to-cart.tpl');
    }

    /**
     * @param $params
     * @return mixed
     */
    /*public function impact_unit_price($params){
        // Retourne le prix venant de l'attribut ou venant du produit si aucun attribut
        $id_adp = $params['param']['addonproduct']['value'];
        if(isset($id_adp)){
            $addon = $this->getItems('page',array('id_adp'=> $id_adp), 'one', false);
            $unit_price = $addon['price_adp'];
            return $unit_price;
        }
    }*/

    /**
     * @param array $param
     * @return array
     */
    public function impact_price(array $param): array {
        // Retourne le prix venant de l'attribut ou venant du produit si aucun attribut
        $id_adp = $param['value'];
        $price = [];
        if(isset($id_adp)){
            $addon = $this->getItems('page',['id_adp'=> $id_adp], 'one', false);
            $price = [
                'price' => $addon['price_adp'],
                'vat' => 21
            ];
        }
        return $price;
    }

    /**
     * @param array $params
     * @return string
     */
    public function get_param_value(array $params): string {
        //print_r($params);
        $value = '';
        $id_adp = $params['value']['value'];
        if($id_adp) {
            $addon = $this->getItems('paramvalue', ['id_adp' => $id_adp], 'one', false);
            $cartpay = $this->getItems('cartpay', ['id' => $params['items'], 'id_adp' => $id_adp], 'one', false);
            if($cartpay == null){
                $this->add([
                    'type' => 'cartpay',
                    'data' => [
                        'id_items' => $params['items'],
                        'id_adp' => $id_adp,
                        'content_adp' => $params['value']['content_adp'],
                        'infos_adp' => $params['value']['infos_adp']
                    ]
                ]);
            }
            $value = $addon['name_adp'];
        }
        return $value;
    }

    /**
     * @param array $params
     * @return array
     */
    public function get_param_info(array $params): array {
        //print_r($params);
        $id_adp = $params['value']['value'];
        $cartpay = $this->getItems('cartpay', ['id' => $params['items'], 'id_adp' => $id_adp], 'one', false);
		$info = [];
        if(!empty($cartpay)) {
			$this->template->addConfigFile([component_core_system::basePath().'/plugins/addonproduct/i18n/'], ['public_local_'], false);
			$this->template->configLoad();
            $info = [
				'content' => [
					'name' => $this->template->getConfigVars('content_adp'),
					'value' => $cartpay['content_adp']
				],
				'infos' => [
					'name' => $this->template->getConfigVars('infos_adp'),
					'value' => $cartpay['infos_adp']
				]
			];
        }
        return $info;
    }

    /**
     * @param array $params
     * @return array
     */
    public function get_param_price(array $params): array {
        $id_adp = $params['value']['value'];
        $cartpay = $this->getItems('cartpay', ['id' => $params['items'], 'id_adp' => $id_adp], 'one', false);
        $price = [];
        if(!empty($cartpay)){
            $addon = $this->getItems('page',['id_adp'=> $id_adp], 'one', false);

            $price = [
                'price' => $addon['price_adp'],
                'vat' => 21
            ];
        }

        return $price;
    }

    // ---- End Cartpay
}