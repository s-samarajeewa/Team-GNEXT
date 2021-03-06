<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Players_stock extends User_controller{
    var $data;
    
    public function __construct() {
        parent::__construct();
        $this->data = array(
            'site_name' => $this->config->item('site_name'),
            'company_name' => $this->config->item('company_name'),
            'title' => 'Players | Stock - ' . $this->config->item('site_name'),
            'usertype' => "players",
        );
        $this->load->model('m_company_stock');
        $this->load->model('m_player_stock');
        $this->load->model('m_stock_transaction');
        $this->load->model('m_clock');
        $this->load->model('m_bank_transaction');
    }
    
    public function index(){
        $data=$this->data;
        $data['scripts'][0]['src'] = base_url() . "assets/plugins/form-validation/jquery.validate.min.js";
        
        $data['header'] = $this->load->view('template/a_vheader', $data, TRUE);
        $data['footer'] = $this->load->view('template/a_vfooter', NULL, TRUE);

        $this->load->view('includes/v_include_header', $data);
        $this->load->view('players/v_play_game');
        $this->load->view('includes/v_include_footer');
    }
    
    public function save_buy() {
        $player_stock['company_stocks_company_stock_id']=  $this->input->post('company_stocks_company_stock_id');
        $player_stock['users_user_id']=  $this->session->userdata['user_id'];
        $player_stock['quantity']=  $this->input->post('quantity');
        $player_stock['price']=  $this->input->post('price');
        
        $player_stock_id=$this->m_player_stock->save_buy($player_stock);
        $this->m_company_stock->decrease_stock_qty($player_stock['company_stocks_company_stock_id'],$player_stock['quantity']);
        
        $turn=  $this->m_clock->get_current_turn();
        
        $stock_transaction['turn']=  $turn;
        $stock_transaction['type']=  'Buy';
        $stock_transaction['price']=  $player_stock['price'];
        $stock_transaction['player_stocks_player_stock_id']=  $player_stock_id;
        $stock_transaction['company_stocks_company_stock_id']=  $player_stock['company_stocks_company_stock_id'];
        $stock_transaction['quantity']=  $player_stock['quantity'];
        $this->m_stock_transaction->save_transaction($stock_transaction);
        
        $receiver=  $this->m_company_stock->get_broker_by_stock_id($player_stock['company_stocks_company_stock_id']);
        $bank_transaction['turn']= $turn;
        $bank_transaction['type']='Withdraw';
        $bank_transaction['amount']= $this->input->post('total');
        $bank_transaction['users_user_id']=$player_stock['users_user_id'];
        $bank_transaction['receiver']=$receiver;
        $this->m_bank_transaction->save_bank_transaction($bank_transaction);
    
        redirect(base_url('players/play-game'));
    }
}
