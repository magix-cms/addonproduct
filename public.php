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
        }else{
            return null;
        }
    }

    // ---- Cartpay

    /**
     * @param $params
     * @return mixed
     */
    public function impact_unit_price($params){
        // Retourne le prix venant de l'attribut ou venant du produit si aucun attribut
        $id_adp = $params['param']['addonproduct'];
        if(isset($id_adp)){
            $addon = $this->getItems('page',array('id_adp'=> $id_adp), 'one', false);
            $unit_price = $addon['price_adp'];
            return $unit_price;
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

    /**
     * @param $params
     * @return string
     */
    public function get_param_value($params) {
        //print_r($params);
        $id_adp = $params['params'];
        $addon = $this->getItems('paramvalue', ['id_adp' => $id_adp], 'one', false);
        $cartpay = $this->getItems('cartpay', ['id' => $params['items'], 'id_adp' => $id_adp], 'one', false);
        if($cartpay == null){
            $this->add([
				'type' => 'cartpay',
				'data' => [
					'id_items' => $params['items'],
					'id_adp' => $id_adp,
					'content_adp' => $this->content['content_adp'],
					'infos_adp' => $this->content['infos_adp']
				]
			]);
        }
        return $addon['name_adp'];
    }

    /**
     * @param array $params
     * @return array
     */
    public function get_param_info(array $params): array {
        //print_r($params);
        $id_adp = $params['params'];
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
    // ---- End Cartpay
}