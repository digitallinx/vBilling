<?php 
/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * The Original Code is "vBilling - VoIP Billing and Routing Platform"
 * 
 * The Initial Developer of the Original Code is 
 * Digital Linx [<] info at digitallinx.com [>]
 * Portions created by Initial Developer (Digital Linx) are Copyright (C) 2011
 * Initial Developer (Digital Linx). All Rights Reserved.
 *
 * Contributor(s)
 * "Digital Linx - <vbilling at digitallinx.com>"
 *
 * vBilling - VoIP Billing and Routing Platform
 * version 0.1.3
 *
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cdr extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('cdr_model');
        $this->load->model('groups_model');

		//validate login
		if (!user_login())
		{
			redirect ('home/');
		}
		else
		{
			if($this->session->userdata('user_type') == 'customer')
			{
				redirect ('customer/');
			}
            
            if($this->session->userdata('user_type') == 'reseller')
			{
				redirect ('reseller/');
			}
            
            if($this->session->userdata('user_type') == 'sub_admin')
            {
                if(sub_admin_access_any_cell($this->session->userdata('user_id'), 'view_cdr') == 0)
                {
                    redirect ('home/');
                }
            }
		}
	}

	function index()
	{

		$filter_display_results = 'sec';

		//this is defualt start and end time  
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_phonenum    = '';
		$filter_caller_ip   = '';
		$filter_customers   = '';
		$filter_groups      = '';
		$filter_gateways    = '';
		$filter_call_type   = '';
		$filter_quick       = '';
		$duration_from      = '';
		$duration_to        = '';
        $filter_sort        = '';
        $filter_contents    = 'all';
		$search             = '';

		$msg_records_found = "Records Found";

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_phonenum        = $this->input->get('filter_phonenum');
			$filter_caller_ip       = $this->input->get('filter_caller_ip');
			$filter_customers       = $this->input->get('filter_customers');
			$filter_groups          = $this->input->get('filter_groups');
			$filter_gateways        = $this->input->get('filter_gateways');
			$filter_call_type       = $this->input->get('filter_call_type');
			$filter_quick           = $this->input->get('filter_quick');
			$duration_from          = $this->input->get('duration_from');
			$duration_to            = $this->input->get('duration_to');
			$filter_display_results = $this->input->get('filter_display_results');
            $filter_sort            = $this->input->get('filter_sort');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$msg_records_found      = "Records Found Based On Search Criteria";
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data['filter_date_from']           = $filter_date_from;
		$data['filter_date_to']             = $filter_date_to;
		$data['filter_phonenum']            = $filter_phonenum;
		$data['filter_caller_ip']           = $filter_caller_ip;
		$data['filter_customers']           = $filter_customers;
		$data['filter_groups']              = $filter_groups;
		$data['filter_gateways']            = $filter_gateways;
		$data['filter_call_type']           = $filter_call_type;
		$data['filter_quick']               = $filter_quick;
		$data['duration_from']              = $duration_from;
		$data['duration_to']                = $duration_to;
        $data['filter_sort']                = $filter_sort;
        $data['filter_contents']            = $filter_contents;
		$data['filter_display_results']     = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'cdr/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_phonenum='.$filter_phonenum.'&filter_caller_ip='.$filter_caller_ip.'&filter_customers='.$filter_customers.'&filter_groups='.$filter_groups.'&filter_gateways='.$filter_gateways.'&filter_call_type='.$filter_call_type.'&filter_display_results='.$filter_display_results.'&filter_quick='.$filter_quick.'&duration_from='.$duration_from.'&duration_to='.$duration_to.'&filter_sort='.$filter_sort.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 6;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_cdr_main_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents);
                //MW modifications
                $data['billsec'] = $this->cdr_model->get_cdr_mw_billsec_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents, billsec);
                $data['totalsellcost'] = $this->cdr_model->get_cdr_mw_billsec_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents, total_sell_cost);
                $data['totalbuycost'] = $this->cdr_model->get_cdr_mw_billsec_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents, total_buy_cost);
                $mw_total_duration = round($data['billsec']/60,2);
                $mw_total_margin = round(($data['totalsellcost']-$data['totalbuycost']),2);
                $data['msg_duration_found'] = "&nbsp;_______&nbsp;&nbsp;Duration : ".$mw_total_duration."&nbsp;".$msg_duration_found."";
                $data['msg_margin_found'] = "&nbsp;_______&nbsp;&nbsp;Margin : ".$mw_total_margin."&nbsp;Euros";
                $data['msg_sell_found'] = "&nbsp;_______&nbsp;&nbsp;Buy : ".$data['totalbuycost']."&nbsp;Euros"."&nbsp;_______&nbsp;&nbsp;Sell : ".$data['totalsellcost']."&nbsp;Euros";
                //MW
                
                $config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['msg_records_found'] = "".$data['count']."&nbsp;".$msg_records_found."";

		$data['cdr']            =   $this->cdr_model->get_all_cdr_data($config['per_page'],$config['uri_segment'],$filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort, $filter_contents);
		$data['page_name']		=	'view_cdr_data';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'list_cdr';
		$data['page_title']		=	'CDR DETAILS';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/cdr_sub_menu';
		$data['main_content']	=	'cdr/cdr_view';
		$this->load->view('default/template',$data);
	}

	function gateways_stats()
	{
		if($this->session->userdata('user_type') == 'sub_admin')
        {
            if(sub_admin_access_any_cell($this->session->userdata('user_id'), 'view_gateway_stats') == 0)
            {
                redirect ('cdr/');
            }
        }
        
        $filter_display_results = 'min';

		//this is defualt start and end time  
		$startTime = time() - 86400; //last 24hrs 
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_gateways    = '';
		$filter_call_type   = 'answered';
		$search             = '';

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_gateways        = $this->input->get('filter_gateways');
			$filter_call_type       = $this->input->get('filter_call_type');
			$search                 = $this->input->get('searchFilter');
			$filter_display_results = $this->input->get('filter_display_results');
		}

		if($filter_call_type   == '')
		{
			$filter_call_type   = 'answered';
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}

		$data['filter_date_from']           = $filter_date_from;
		$data['filter_date_to']             = $filter_date_to;
		$data['filter_gateways']            = $filter_gateways;
		$data['filter_call_type']           = $filter_call_type;
		$data['filter_display_results']     = $filter_display_results;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'cdr/gateways_stats/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_gateways='.$filter_gateways.'&filter_call_type='.$filter_call_type.'&filter_display_results='.$filter_display_results.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_all_gateways_count($filter_gateways);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['all_gateways']   =   $this->cdr_model->get_all_gateways($config['per_page'],$config['uri_segment'],$filter_gateways);
		$data['page_name']		=	'view_gateways_stats';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'gateways_stats';
		$data['page_title']		=	'GATEWAYS STATS';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/cdr_sub_menu';
		$data['main_content']	=	'cdr/gateways_stats_view';
		$this->load->view('default/template',$data);
	}

	function customer_stats()
	{
		if($this->session->userdata('user_type') == 'sub_admin')
        {
            if(sub_admin_access_any_cell($this->session->userdata('user_id'), 'view_customer_stats') == 0)
            {
                redirect ('cdr/');
            }
        }
        
        $filter_display_results = 'min';

		$filter_customers   = '';
        $filter_contents    = 'all';
		$search             = '';

		if($this->input->get('searchFilter'))
		{
			$filter_customers       = $this->input->get('filter_customers');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$filter_display_results = $this->input->get('filter_display_results');
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data['filter_customers']           = $filter_customers;
		$data['filter_display_results']     = $filter_display_results;
        $data['filter_contents']            = $filter_contents;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'cdr/customer_stats/?searchFilter='.$search.'&filter_customers='.$filter_customers.'&filter_display_results='.$filter_display_results.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_all_customers_count($filter_customers, $filter_contents);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['customers']      =   $this->cdr_model->get_all_customers($config['per_page'],$config['uri_segment'], $filter_customers, $filter_contents);
		$data['page_name']		=	'customer_stats';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'customer_stats';
		$data['page_title']		=	'CUSTOMER STATISTICS';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/cdr_sub_menu';
		$data['main_content']	=	'cdr/customer_stats_view';
		$this->load->view('default/template',$data);
	}

	function call_destination()
	{
		if($this->session->userdata('user_type') == 'sub_admin')
        {
            if(sub_admin_access_any_cell($this->session->userdata('user_id'), 'view_call_destination') == 0)
            {
                redirect ('cdr/');
            }
        }
        
        $filter_display_results = 'min';

		//this is defualt start and end time  
		$startTime = time() - 86400; //last 24hrs 
		$endTime = time();

		//for filter & search
		$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		$filter_countries   = '';
        $filter_contents    = 'all';
		$search             = '';

		if($this->input->get('searchFilter'))
		{
			$filter_date_from       = $this->input->get('filter_date_from');
			$filter_date_to         = $this->input->get('filter_date_to');
			$filter_countries       = $this->input->get('filter_countries');
            $filter_contents        = $this->input->get('filter_contents');
			$search                 = $this->input->get('searchFilter');
			$filter_display_results = $this->input->get('filter_display_results');
		}

		if($filter_display_results   == '')
		{
			$filter_display_results   = 'min';
		}

		if($filter_display_results != 'min' && $filter_display_results != 'sec')
		{
			$filter_display_results   = 'min';
		}

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}
        
        if($filter_contents == '' || ($filter_contents != 'all' && $filter_contents != 'my'))
        {
            $filter_contents = "all";
        }

		$data['filter_date_from']           = $filter_date_from;
		$data['filter_date_to']             = $filter_date_to;
		$data['filter_countries']           = $filter_countries;
		$data['filter_display_results']     = $filter_display_results;
        $data['filter_contents']            = $filter_contents;

		//for pagging set information
		$this->load->library('pagination');
		$config['per_page'] = '20';
		$config['base_url'] = base_url().'cdr/call_destination/?searchFilter='.$search.'&filter_date_from='.$filter_date_from.'&filter_date_to='.$filter_date_to.'&filter_countries='.$filter_countries.'&filter_display_results='.$filter_display_results.'&filter_contents='.$filter_contents.'';
		$config['page_query_string'] = TRUE;

		$config['num_links'] = 2;

		$config['cur_tag_open'] = '<span class="current">';
		$config['cur_tag_close'] = '</span> ';

		$config['next_link'] = 'next';
		$config['next_tag_open'] = '<span class="next-site">';
		$config['next_tag_close'] = '</span>';

		$config['prev_link'] = 'previous';
		$config['prev_tag_open'] = '<span class="prev-site">';
		$config['prev_tag_close'] = '</span>';

		$config['first_link'] = 'first';
		$config['last_link'] = 'last';

		$data['count'] = $this->cdr_model->get_all_countries_count($filter_countries, $filter_date_from, $filter_date_to, $filter_contents);
		$config['total_rows'] = $data['count'];

		if(isset($_GET['per_page']))
		{
			if(is_numeric($_GET['per_page']))
			{
				$config['uri_segment'] = $_GET['per_page'];
			}
			else
			{
				$config['uri_segment'] = '';
			}
		}
		else
		{
			$config['uri_segment'] = '';
		}

		$this->pagination->initialize($config);

		$data['countries']      =   $this->cdr_model->get_all_countries($config['per_page'],$config['uri_segment'], $filter_countries, $filter_date_from, $filter_date_to, $filter_contents);
		$data['page_name']		=	'call_destination';
		$data['selected']		=	'cdr';
		$data['sub_selected']   =   'call_destination';
		$data['page_title']		=	'CALL DESTINATION DETAILS';
		$data['main_menu']	    =	'default/main_menu/main_menu';
		$data['sub_menu']	    =	'default/sub_menu/cdr_sub_menu';
		$data['main_content']	=	'cdr/call_destination_view';
		$this->load->view('default/template',$data);
	}

	function get_calculated_date_time()
	{
		$value = $this->input->post('val');

		$return_val = '';

		$current_date_time = date('Y-m-d H:i:s');
		$curr_date_starting_from_12_Am = "".date('Y-m-d')." 00:00:00";

		if($value == 'today' || $value == '')
		{
			$return_val = $curr_date_starting_from_12_Am.'|'.$current_date_time;
		}
		else if($value == 'last_hour')
		{
			$time = time();
			$last_hour = $time - 3600;
			$last_hour = date('Y-m-d H:i:s', $last_hour);
			$return_val = $last_hour.'|'.$current_date_time;
		}
		else if($value == 'last_24_hour')
		{
			$time = time();
			$last_24_hour = $time - 86400;
			$last_24_hour = date('Y-m-d H:i:s', $last_24_hour);
			$return_val = $last_24_hour.'|'.$current_date_time;
		}
		echo $return_val;
	}

	function export_pdf()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_caller_ip       = $this->input->get('filter_caller_ip');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_gateways        = $this->input->get('filter_gateways');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}

		$data_cdr   =   $this->cdr_model->export_cdr_data($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort);

		if($data_cdr->num_rows() > 0)
		{
			$this->load->library('pdf');

			// set document information
			$this->pdf->SetSubject('CDR Export');
			$this->pdf->SetKeywords('DigitalLinx, CDR, export');

			// add a page
			$this->pdf->AddPage();

			$this->pdf->SetFont('helvetica', '', 6);
            
			$sql12 = "SELECT * FROM settings WHERE customer_id = '".$this->session->userdata('customer_id')."'";
			$query12 = $this->db->query($sql12);
            $row12 = $query12->row();
            $data_array = explode(',',$row12->optional_cdr_fields_include);
            
			$tbl = '<table cellspacing="0" cellpadding="1" border="1" width="100%">
				<tr style="background-color:grey; color:#ffffff;">
                <td height="20" align="center">Date/Time</td>
				<td align="center">Destination</td>
				<td align="center">Bill Duration</td>
                <td align="center">Total Charges</td>';
            
                if(count($data_array) > 0)
                {
                    if(in_array('caller_id_number',$data_array))
                    {
                        $tbl .= '<td align="center">Caller ID Num</td>';
                    }
                    if(in_array('duration',$data_array))
                    {
                        $tbl .= '<td align="center">Duration</td>';
                    }
                    if(in_array('network_addr',$data_array))
                    {
                        $tbl .= '<td align="center">Network Address</td>';
                    }
                    if(in_array('username',$data_array))
                    {
                        $tbl .= '<td align="center">Username</td>';
                    }
                    if(in_array('sip_user_agent',$data_array))
                    {
                        $tbl .= '<td align="center">SIP User Agent</td>';
                    }
                    if(in_array('ani',$data_array))
                    {
                        $tbl .= '<td align="center">ANI</td>';
                    }
                    if(in_array('cidr',$data_array))
                    {
                        $tbl .= '<td align="center">CIDR</td>';
                    }
                    if(in_array('sell_rate',$data_array)) 
                    {
                        $tbl .= '<td align="center">Sell Rate</td>';
                    }
                    if(in_array('cost_rate',$data_array)) 
                    {
                        $tbl .= '<td align="center">Cost Rate</td>';
                    }
                    if(in_array('buy_initblock',$data_array)) 
                    {
                        $tbl .= '<td align="center">Buy Init Block</td>';
                    }
                    if(in_array('sell_initblock',$data_array)) 
                    {
                        $tbl .= '<td align="center">Sell Init Block</td>';
                    }
                    if(in_array('total_buy_cost',$data_array)) 
                    {
                        $tbl .= '<td align="center">Total Buy Cost</td>';
                    }
                    if(in_array('gateway',$data_array)) 
                    {
                        $tbl .= '<td align="center">Gateway</td>';
                    }
                    if(in_array('total_failed_gateways',$data_array)) 
                    {
                        $tbl .= '<td align="center">Total Failed Gateways</td>';
                    }
                }
                
				$tbl .= '</tr>';
			foreach ($data_cdr->result() as $row)
			{
				$tbl .=   '<tr>
					<td align="center" height="30">'.date("Y-m-d H:i:s", $row->created_time/1000000).'</td>
					<td align="center">'.$row->destination_number.'</td>
					<td align="center">'.$row->billsec.'</td>';
                    
                    if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                        $tbl .=  '<td align="center">'.$row->total_sell_cost.'</td>';
                    } else {
                        $tbl .= '<td align="center">0</td>';
                    }
                    
					if(count($data_array) > 0)
                    {
                        if(in_array('caller_id_number',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->caller_id_number.'</td>';
                        }
                        if(in_array('duration',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->duration.'</td>';
                        }
                        if(in_array('network_addr',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->network_addr.'</td>';
                        }
                        if(in_array('username',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->username.'</td>';
                        }
                        if(in_array('sip_user_agent',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->sip_user_agent.'</td>';
                        }
                        if(in_array('ani',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->ani.'</td>';
                        }
                        if(in_array('cidr',$data_array))
                        {
                            $tbl .= '<td align="center">'.$row->cidr.'</td>';
                        }
                        if(in_array('sell_rate',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->sell_rate.'</td>';
                        }
                        if(in_array('cost_rate',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->cost_rate.'</td>';
                        }
                        if(in_array('buy_initblock',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->buy_initblock.'</td>';
                        }
                        if(in_array('sell_initblock',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->sell_initblock.'</td>';
                        }
                        if(in_array('total_buy_cost',$data_array)) 
                        {
                            if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
                                $tbl .=  '<td align="center">'.$row->total_buy_cost.'</td>';
                            } else {
                                $tbl .= '<td align="center">0</td>';
                            }
                        }
                        if(in_array('gateway',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->gateway.'</td>';
                        }
                        if(in_array('total_failed_gateways',$data_array)) 
                        {
                            $tbl .= '<td align="center">'.$row->total_failed_gateways.'</td>';
                        }
                    }
                    $tbl .= '</tr>';   

			}

			$tbl .=  '</table>';

			$this->pdf->writeHTML($tbl, true, false, false, false, '');

			// Close and output PDF document
			// We use Session Username and Session last activity time
			$pdf_file_name = $this->session->userdata('username')."_".$this->session->userdata('last_activity');
			$this->pdf->Output($pdf_file_name, 'I');
		}
		else
		{
			redirect('cdr/');
		}
	}

	function export_excel()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_caller_ip       = $this->input->get('filter_caller_ip');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_gateways        = $this->input->get('filter_gateways');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}

		$data_cdr = $this->cdr_model->export_cdr_data($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort);

		if($data_cdr->num_rows() > 0)
		{
			$sql12 = "SELECT * FROM settings WHERE customer_id = '".$this->session->userdata('customer_id')."'";
			$query12 = $this->db->query($sql12);
            $row12 = $query12->row();
            $data_array = explode(',',$row12->optional_cdr_fields_include);

            $this->load->library('Spreadsheet_Excel_Writer');
			$workbook = new Spreadsheet_Excel_Writer();

			$format_bold =& $workbook->addFormat();
			$format_bold->setBold();

			$format_head =& $workbook->addFormat();
			$format_head->setBold();
			$format_head->setPattern(1);
			$format_head->setFgColor('red');
			$format_head->setBgColor('white');
			$format_head->setAlign('merge');

			$format_title =& $workbook->addFormat();
			$format_title->setBold();
			$format_title->setPattern(1);
			$format_title->setFgColor('white');
			$format_title->setBgColor('grey');
			$format_title->setAlign('merge');

			$format_cell =& $workbook->addFormat();
			$format_cell->setAlign('merge');

			$worksheet =& $workbook->addWorksheet();
			// Put a nice heading in xls file with the logged in user's company name

			if (count ($row12->company_name) > 0)
			{
				$company_name = $row12->company_name;
				$worksheet->write(0, 0, "CDR EXPORT for Customer :: (".$company_name.")", $format_head);
			}
			else {
				$worksheet->write(0, 0, "CDR EXPORT for Customer(s)", $format_head);
			}

			// These four are mandatory cells
			$worksheet->write(1, 0, "Date/Time", $format_title);
			$worksheet->write(1, 1, "Destination", $format_title);
			$worksheet->write(1, 2, "Bill Duration", $format_title);
            $worksheet->write(1, 3, "Total Charges", $format_title);
			
            $increment = 3;
            if(count($data_array) > 0)
            {
                if(in_array('caller_id_number',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Caller ID Num", $format_title);
                }
                if(in_array('duration',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Duration", $format_title);
                }
                if(in_array('network_addr',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Network Address", $format_title);
                }
                if(in_array('username',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Username", $format_title);
                }
                if(in_array('sip_user_agent',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "SIP User Agent", $format_title);
                }
                if(in_array('ani',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "ANI", $format_title);
                }
                if(in_array('cidr',$data_array))
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "CIDR", $format_title);
                }
                if(in_array('sell_rate',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Sell Rate", $format_title);
                }
                if(in_array('cost_rate',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Cost Rate", $format_title);
                }
                if(in_array('buy_initblock',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Buy Init Block", $format_title);
                }
                if(in_array('sell_initblock',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Sell Init Block", $format_title);
                }
                if(in_array('total_buy_cost',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Total Buy Cost", $format_title);
                }
                if(in_array('gateway',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Gateway", $format_title);
                }
                if(in_array('total_failed_gateways',$data_array)) 
                {
                    $increment = $increment + 1;
                    $worksheet->write(1, $increment, "Failed Gateways", $format_title);
                }
            }

			$count = 2;
			foreach($data_cdr->result() as $row)
			{
				$worksheet->write($count, 0, "".date("Y-m-d H:i:s", $row->created_time/1000000)."", $format_cell);
				$worksheet->write($count, 1, "".$row->destination_number."", $format_cell);
				$worksheet->write($count, 2, "".$row->billsec."", $format_cell);
                if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
					$worksheet->write($count, 3, "".$row->total_sell_cost."", $format_cell);
				}
				else{
					$worksheet->write($count, 3, "0", $format_cell);
				}
                
                $increment = 3;
				if(count($data_array) > 0)
				                {
				                    if(in_array('caller_id_number',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->caller_id_number."", $format_cell);
				                    }
				                    if(in_array('duration',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->duration."", $format_cell);
				                    }
				                    if(in_array('network_addr',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->network_addr."", $format_cell);
				                    }
				                    if(in_array('username',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->username."", $format_cell);
				                    }
				                    if(in_array('sip_user_agent',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->sip_user_agent."", $format_cell);
				                    }
				                    if(in_array('ani',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->ani."", $format_cell);
				                    }
				                    if(in_array('cidr',$data_array))
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->cidr."", $format_cell);
				                    }
				                    if(in_array('sell_rate',$data_array)) 
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->sell_rate."", $format_cell);
				                    }
				                    if(in_array('cost_rate',$data_array)) 
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->cost_rate."", $format_cell);
				                    }
				                    if(in_array('buy_initblock',$data_array)) 
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->buy_initblock."", $format_cell);
				                    }
				                    if(in_array('sell_initblock',$data_array)) 
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->sell_initblock."", $format_cell);
				                    }
				                    if(in_array('total_buy_cost',$data_array)) 
				                    {
				                        if(($row->hangup_cause == 'NORMAL_CLEARING' || $row->hangup_cause == 'ALLOTTED_TIMEOUT') && $row->billsec > 0) {
				                            $increment = $increment + 1;
				                            $worksheet->write($count, $increment, "".$row->total_buy_cost."", $format_cell);
				                        } else {
				                            $increment = $increment + 1;
				                            $worksheet->write($count, $increment, "0", $format_cell);
				                        }
				                    }
				                    if(in_array('gateway',$data_array)) 
				                    {
				                        if($row->gateway != '')
				                        {
				                            $increment = $increment + 1;
				                            $worksheet->write($count, $increment, "".$row->gateway."", $format_cell);
				                        }
				                        else
				                        {
				                            $increment = $increment + 1;
				                            $worksheet->write($count, $increment, "-", $format_cell);
				                        }
				                    }
				                    if(in_array('total_failed_gateways',$data_array)) 
				                    {
				                        $increment = $increment + 1;
				                        $worksheet->write($count, $increment, "".$row->total_failed_gateways."", $format_cell);
				                    }
				                }
                
				$count = $count + 1;
			}
			// $workbook->send('test.xls');
			// We use Session Username and Session last activity time

			$workbook_name = $this->session->userdata('username')."_".$this->session->userdata('last_activity');
			$workbook->send($workbook_name."xls");
			$workbook->close();
		}
		else
		{
			redirect('cdr/');
		}
	}

	function export_csv()
	{
		$startTime = date('Y-m-d');
		$startTime = strtotime($startTime);
		$endTime = time();

		$filter_date_from       = $this->input->get('filter_date_from');
		$filter_date_to         = $this->input->get('filter_date_to');
		$filter_phonenum        = $this->input->get('filter_phonenum');
		$filter_caller_ip       = $this->input->get('filter_caller_ip');
		$filter_customers       = $this->input->get('filter_customers');
		$filter_groups          = $this->input->get('filter_groups');
		$filter_gateways        = $this->input->get('filter_gateways');
		$filter_call_type       = $this->input->get('filter_call_type');
		$filter_quick           = $this->input->get('filter_quick');
		$duration_from          = $this->input->get('duration_from');
		$duration_to            = $this->input->get('duration_to');
		$filter_display_results = $this->input->get('filter_display_results');
        $filter_sort            = $this->input->get('filter_sort');

		if($filter_date_from == '')
		{
			$filter_date_from   = date('Y-m-d H:i:s', $startTime);
		}
		else
		{
			if (!checkdateTime($filter_date_from))
			{
				$filter_date_from   = date('Y-m-d H:i:s', $startTime);
			}
		}

		if($filter_date_to == '')
		{
			$filter_date_to     = date('Y-m-d H:i:s', $endTime);
		}
		else
		{
			if (!checkdateTime($filter_date_to))
			{
				$filter_date_to   = date('Y-m-d H:i:s', $endTime);
			}
		}

		$data_cdr   =   $this->cdr_model->export_cdr_data_csv($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort);

		if($data_cdr->num_rows() > 0)
		{
		//  $headers 	= array('Date/Time', 'Destination', 'Bill Duration', 'Hangup Cause', 'IP Address', 'Username', 'Sell Rate', 'Sell Init Block', 'Cost Rate','Buy Init Block', 'Total Charges', 'Total Cost', 'Margin', 'Markup');
		//	$headers 	= array( 'network_addr' => 'IP Address', 'username' => 'Username', 'sell_rate'  => 'Sell Rate', 'sell_initblock' => 'Sell Init Block','cost_rate' => 'Cost Rate','buy_initblock' => 'Buy Init Block','total_buy_cost' => 'Total Cost');
			
		/*
		$headers = array(
				 'created_time'        => 'Data / Time'
				, 'destination_number' => 'Destination'
				, 'billsec'            => 'Call Duration'
				, 'total_sell_cost'    => 'Total Charges'
				, 'caller_id_number'   => 'CalledID Number'
				, 'network_addr'       => 'IP Address'
				, 'username'           => 'Username'
				, 'sip_user_agent'     => 'SIP User Agent'
				, 'ani'                => 'ANI'
				, 'cost_rate'          => 'Cost Rate'
				, 'sell_rate'          => 'Sell Rate'
				, 'buy_initblock'      => 'Buy Init Block'
				, 'sell_initblock'     => 'Sell Init Block'
				, 'gateway'            => 'Gateway' 
				);
		*/	
			
			
			
		$headers = array(
				 'caller_id_number'   => 'CalledID Number'
				, 'network_addr'       => 'IP Address'
				, 'username'           => 'Username'
				, 'sip_user_agent'     => 'SIP User Agent'
				, 'ani'                => 'ANI'
				, 'cost_rate'          => 'Cost Rate'
				, 'sell_rate'          => 'Sell Rate'
				, 'buy_initblock'      => 'Buy Init Block'
				, 'sell_initblock'     => 'Sell Init Block'
				, 'gateway'            => 'Gateway' 
				);
			
			
			
			$hdatakey[]	=	'created_time';
			$hdata[]	=	'Data / Time';
				
			$hdatakey[]	=	'destination_number';
			$hdata[]	=	'Destination';
			
			$hdatakey[]	=	'billsec';
			$hdata[]	=	'Call Duration';
			
			$hdatakey[]	=	'total_sell_cost';
			$hdata[]	=	'Total Charges';
			
			$sql12 		= "SELECT * FROM settings WHERE customer_id = '".$this->session->userdata('customer_id')."'";
			$query12 	= $this->db->query($sql12);
            $row12 		= $query12->row();
            $data_array = explode(',',$row12->optional_cdr_fields_include);
			
			/*
			foreach($data_array as $x):
			
				echo $x." ";
			
			endforeach;
			echo 'above user requerment<br>';
			
			*/
			
			foreach($headers as $hkey => $hvalue): 
					foreach($data_array as $key):
						if($hkey == $key)
						{  
							$hdata[]	= $hvalue;
							$hdatakey[] = $hkey;
						}
					endforeach;
			endforeach;
			/*
			foreach($hdatakey as $y):			
				echo $y."|";
			endforeach;
			*/
			
			/* this is show the date in proper date formated not in unix format 
			foreach ($data_cdr->result() as $row):
						$odata 		=	array();
							foreach($hdatakey as $key):
								if($key == 'created_time')
								{
									$odata[] = date("Y-m-d H:i:s", $row->$key/1000000);
										
								}else
								{
									$odata[] 	=	$row->$key;				
								}
							endforeach;
						   print '"' . stripslashes(implode('","',$odata)) . "\"\n";
			endforeach;
*/
			/*
			foreach($hdata as $y):			
				echo $y." ";
			endforeach;
			*/

			$csv_file_name = $this->session->userdata('username')."_".$this->session->userdata('last_activity');

			$fp = fopen('php://output', 'w');
			if ($fp) {
				header('Content-Type: text/csv');
				header("Content-Disposition: attachment; filename=".$csv_file_name);
				header('Pragma: no-cache');
				header('Expires: 0');
				fputcsv($fp, $hdata);
				

				foreach ($data_cdr->result() as $row):
						$odata 		=	array();
							foreach($hdatakey as $key):
								if($key == 'created_time')
								{
									$odata[] = date("Y-m-d H:i:s", $row->$key/1000000);
										
								}else
								{
									$odata[] 	=	$row->$key;				
								}
							endforeach;
						   print '"' . stripslashes(implode('","',$odata)) . "\"\n";
				endforeach;

				/*
					foreach ($data_cdr->result() as $row):
						$odata 		=	array();
							foreach($hdatakey as $key):
								
								$odata[] 	=	$row->$key;				
									  
							endforeach;
						print '"' . stripslashes(implode('","',$odata)) . "\"\n";	
					endforeach;
				*/	
				
				
				/*
				foreach ($data_cdr->result_array() as $row)
				{
					print '"' . stripslashes(implode('","',$row)) . "\"\n";
				}
				*/
				die;
			}
		}
		else
		{
			redirect('cdr/');
		}
	}
    
    function tooltip()
    {
        $id = $this->input->post('id');
        $data = $this->cdr_model->get_parent_cdr_data($id);
        
        if($data->num_rows() > 0)
        {
        $txt = '<table><tr><td width="100px" style="color:#000">GATEWAY</td><td width="100px" style="color:#000">HANGUP CAUSE</td></tr>';
        foreach($data->result() as $row)
        {
            $txt .= '<tr><td>'.$row->gateway.'</td><td>'.$row->hangup_cause.'</td></tr>';
        }
        $txt .= '<table>';
        echo $txt;
        }
        else
        {
            echo "No Result Found";
        }
    }
}