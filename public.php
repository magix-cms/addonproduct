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
    public $contentData;

    /**
     * frontend_controller_home constructor.
     * @param stdClass $t
     */
    public function __construct($t = null)
    {
        $this->template = $t instanceof frontend_model_template ? $t : new frontend_model_template();
        $formClean = new form_inputEscape();
        $this->data = new frontend_model_data($this, $this->template);
        $this->settingComp = new component_collections_setting();
        $this->settings = $this->settingComp->getSetting();

        if (http_request::isGet('id')) $this->id = $formClean->numeric($_GET['id']);
        if (http_request::isPost('contentData')) $this->contentData = $formClean->arrayClean($_POST['contentData']);
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
    public function add_unit_price($params){
        // Retourne le prix venant de l'attribut ou venant du produit si aucun attribut
        $id_adp = $params['param']['addonproduct'];
        if(isset($id_adp)){
            $addon = $this->getItems('page',array('id_adp'=> $id_adp), 'one', false);
            $unit_price = $addon['price_adp'];
            return $unit_price;
        }
    }
    /**
     * @param $params
     * @return string
     */
    public function impact_param_value($params){
        //print_r($params);
        if($params['params'] == "1"){
            $newData = 'ruban';
        }elseif($params['params'] == "2"){
            $newData = 'carte';
        }
        return $newData;
    }
    // ---- End Cartpay
}