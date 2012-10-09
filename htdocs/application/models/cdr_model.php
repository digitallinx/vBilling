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

class Cdr_model extends CI_Model {

	//get all cdr data 
	function get_all_cdr_data($num, $offset, $filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort, $filter_contents)
	{
		if($offset == ''){$offset='0';}

		$order_by = "";
		if($filter_sort == 'date_asc')
		{
			$order_by = "ORDER BY created_time ASC";
		}
		else if($filter_sort == 'date_dec')
		{
			$order_by = "ORDER BY created_time DESC";
		}
		else if($filter_sort == 'billduration_asc')
		{
			$order_by = "ORDER BY billsec ASC";
		}
		else if($filter_sort == 'billduration_dec')
		{
			$order_by = "ORDER BY billsec DESC";
		}
		else if($filter_sort == 'failedgateways_asc')
		{
			$order_by = "ORDER BY total_failed_gateways ASC";
		}
		else if($filter_sort == 'failedgateways_dec')
		{
			$order_by = "ORDER BY total_failed_gateways DESC";
		}
		else if($filter_sort == 'sellrate_asc')
		{
			$order_by = "ORDER BY sell_rate ASC";
		}
		else if($filter_sort == 'sellrate_dec')
		{
			$order_by = "ORDER BY sell_rate DESC";
		}
		else if($filter_sort == 'costrate_asc')
		{
			$order_by = "ORDER BY cost_rate ASC";
		}
		else if($filter_sort == 'costrate_dec')
		{
			$order_by = "ORDER BY cost_rate DESC";
		}
		else if($filter_sort == 'totcharges_asc')
		{
			$order_by = "ORDER BY total_sell_cost ASC";
		}
		else if($filter_sort == 'totcharges_dec')
		{
			$order_by = "ORDER BY total_sell_cost DESC";
		}
		else if($filter_sort == 'totcost_asc')
		{
			$order_by = "ORDER BY total_buy_cost ASC";
		}
		else if($filter_sort == 'totcost_dec')
		{
			$order_by = "ORDER BY total_buy_cost DESC";
		}
		else
		{
			$order_by = "ORDER BY created_time DESC";
		}

		$where = '';
		if($filter_contents == 'all') //get all customers and resellers which belongs to him 
		{
			$where .= "WHERE parent_id = '0' ";
		}
		else if($filter_contents == 'my') //only get those which are created by him 
		{
			$where .= "WHERE parent_id = '0' && parent_reseller_id = '0' ";
		}
		else //show all 
		{
			$where .= "WHERE parent_id = '0' ";
		}

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		if($filter_phonenum != '')
		{
			if(is_numeric($filter_phonenum))
			{
				$where .= 'AND destination_number = '.$this->db->escape($filter_phonenum).' ';
			}
		}

		if($filter_caller_ip != '')
		{
			if ($this->input->valid_ip($filter_caller_ip))
			{
				$where .= 'AND network_addr = '.$this->db->escape($filter_caller_ip).' ';
			}
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'AND customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		if($filter_groups != '')
		{
			if(is_numeric($filter_groups))
			{
				$groupRateTbl = 'SELECT * FROM groups WHERE id = '.$this->db->escape($filter_groups).' ';
				$groupQuery = $this->db->query($groupRateTbl);
				$row = $groupQuery->row();

				$where .= "AND customer_group_rate_table = '".$row->group_rate_table."' ";
			}
		}

		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'AND gateway = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		if($filter_call_type != '')
		{
			$where .= 'AND hangup_cause = '.$this->db->escape($filter_call_type).' ';
		}

		if($duration_from != '')
		{
			if(is_numeric($duration_from))
			{
				$where .= 'AND billsec >= '.$duration_from.' ';
			}
		}

		if($duration_to != '')
		{
			if(is_numeric($duration_to))
			{
				$where .= 'AND billsec <= '.$duration_to.' ';
			}
		}

		$sql = "SELECT * FROM cdr ".$where." ".$order_by." LIMIT $offset, $num";
		$query = $this->db->query($sql);
		return $query;
	}

	//cdr count for pagination 
	function get_cdr_main_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents)
	{
		$where = '';
		if($filter_contents == 'all') //get all customers and resellers which belongs to him 
		{
			$where .= "WHERE parent_id = '0' ";
		}
		else if($filter_contents == 'my') //only get those which are created by him 
		{
			$where .= "WHERE parent_id = '0' && parent_reseller_id = '0' ";
		}
		else //show all 
		{
			$where .= "WHERE parent_id = '0' ";
		}

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		if($filter_phonenum != '')
		{
			if(is_numeric($filter_phonenum))
			{
				$where .= 'AND destination_number = '.$this->db->escape($filter_phonenum).' ';
			}
		}

		if($filter_caller_ip != '')
		{
			if ($this->input->valid_ip($filter_caller_ip))
			{
				$where .= 'AND network_addr = '.$this->db->escape($filter_caller_ip).' ';
			}
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'AND customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		if($filter_groups != '')
		{
			if(is_numeric($filter_groups))
			{
				$groupRateTbl = 'SELECT * FROM groups WHERE id = '.$this->db->escape($filter_groups).' ';
				$groupQuery = $this->db->query($groupRateTbl);
				$row = $groupQuery->row();

				$where .= "AND customer_group_rate_table = '".$row->group_rate_table."' ";
			}
		}

		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'AND gateway = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		if($filter_call_type != '')
		{
			$where .= 'AND hangup_cause = '.$this->db->escape($filter_call_type).' ';
		}

		if($duration_from != '')
		{
			if(is_numeric($duration_from))
			{
				$where .= 'AND billsec >= '.$duration_from.' ';
			}
		}

		if($duration_to != '')
		{
			if(is_numeric($duration_to))
			{
				$where .= 'AND billsec <= '.$duration_to.' ';
			}
		}

		$sql = "SELECT * FROM cdr ".$where."";
		$query = $this->db->query($sql);
		$count = $query->num_rows();
		return $count;
	}
        
        //cdr total billsec 
   function get_cdr_mw_billsec_count($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_contents,$mw_result)
   {
      $where = '';
      if($filter_contents == 'all') //get all customers and resellers which belongs to him 
      {
         $where .= "WHERE parent_id = '0' ";
      }
      else if($filter_contents == 'my') //only get those which are created by him 
      {
         $where .= "WHERE parent_id = '0' && parent_reseller_id = '0' ";
      }
      else //show all 
      {
         $where .= "WHERE parent_id = '0' ";
      }

      if($filter_date_from != '')
      {
         $date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
         $where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
      }

      if($filter_date_to != '')
      {
         $date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
         $where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
      }

      if($filter_phonenum != '')
      {
         if(is_numeric($filter_phonenum))
         {
            $where .= 'AND destination_number = '.$this->db->escape($filter_phonenum).' ';
         }
      }

      if($filter_caller_ip != '')
      {
         if ($this->input->valid_ip($filter_caller_ip))
         {
            $where .= 'AND network_addr = '.$this->db->escape($filter_caller_ip).' ';
         }
      }

      if($filter_customers != '')
      {
         if(is_numeric($filter_customers))
         {
            $where .= 'AND customer_id = '.$this->db->escape($filter_customers).' ';
         }
      }

      if($filter_groups != '')
      {
         if(is_numeric($filter_groups))
         {
            $groupRateTbl = 'SELECT * FROM groups WHERE id = '.$this->db->escape($filter_groups).' ';
            $groupQuery = $this->db->query($groupRateTbl);
            $row = $groupQuery->row();

            $where .= "AND customer_group_rate_table = '".$row->group_rate_table."' ";
         }
      }

      if($filter_gateways != '')
      {
         if (strpos($filter_gateways,'|') !== false) {
            $explode = explode('|', $filter_gateways);
            $gateway = $explode[0];
            $profile = $explode[1];
            if(isset($gateway) && isset($profile))
            {
               if(is_numeric($profile))
               {
                  $where .= 'AND gateway = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
               }
            }
         }
      }

      if($filter_call_type != '')
      {
         $where .= 'AND hangup_cause = '.$this->db->escape($filter_call_type).' ';
      }

      if($duration_from != '')
      {
         if(is_numeric($duration_from))
         {
            $where .= 'AND billsec >= '.$duration_from.' ';
         }
      }

      if($duration_to != '')
      {
         if(is_numeric($duration_to))
         {
            $where .= 'AND billsec <= '.$duration_to.' ';
         }
      }

      $sql = "SELECT SUM($mw_result) AS $mw_result FROM cdr ".$where."";
      $query = $this->db->query($sql);
      $row = $query->row();
      return $row->$mw_result;
   }

	function get_parent_cdr_data($id)
	{
		$sql = "SELECT * FROM cdr WHERE parent_id = '".$id."'";
		$query = $this->db->query($sql);
		return $query;
	}
	/****************************** GATEWAYS STATS FUNCTIONS **********************************************/

	//get list of all available gateways 
	function get_all_gateways($num, $offset, $filter_gateways = '')
	{
		if($offset == ''){$offset='0';} 

		$where = '';
		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'WHERE gateway_name = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		$sql = "SELECT * FROM sofia_gateways ".$where." GROUP BY gateway_name, sofia_id LIMIT $offset,$num";
		$query = $this->db->query($sql);
		return $query;
	}

	function get_all_gateways_count($filter_gateways = '')
	{
		$where = '';
		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'WHERE gateway_name = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		$sql = "SELECT * FROM sofia_gateways ".$where." GROUP BY gateway_name, sofia_id";
		$query = $this->db->query($sql);
		$count = $query->num_rows();
		return $count;
	}

	//get total ASR
	function get_gateway_asr($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql            = "SELECT COUNT(*) AS total_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') && parent_id = '0'";
		$query          = $this->db->query($sql);
		$row            = $query->row();
		$my_total_calls = $row->total_calls;

		$sql            = "SELECT COUNT(*) AS total_answered_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') && parent_id = '0' && billsec > 0 ";
		$query          = $this->db->query($sql);
		$row            = $query->row();
		$answered_calls = $row->total_answered_calls;

		// ASR = call attempts answered / call attempts
		$asr = ($answered_calls / $my_total_calls) * 100;
		return $asr;

	}

	//get particular gateway total calls
	function get_gateway_total_calls($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql = "SELECT COUNT(*) AS total_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') && parent_id = '0'";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_calls;
	}

	//get particular gateway total answered calls  
	function get_gateway_total_answered_calls($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql = "SELECT COUNT(*) AS total_answered_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') && parent_id = '0' && billsec > 0 ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_answered_calls;
	}

	//get particular gateway total busy calls  
	function get_gateway_total_busy_calls($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql = "SELECT COUNT(*) AS total_busy_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && hangup_cause = 'USER_BUSY' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') && parent_id = '0' ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_busy_calls;
	}

	//get particular gateway total rejected calls   
	function get_gateway_total_rejected_calls($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql = "SELECT COUNT(*) AS total_rejected_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && hangup_cause = 'CALL_REJECTED' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_rejected_calls;
	}

	//get particular gateway total failed calls 
	function get_gateway_total_failed_calls($gateway, $sofia_id, $date_from, $date_to)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$sql = "SELECT COUNT(*) AS total_failed_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && hangup_cause != 'NORMAL_CLEARING' && hangup_cause != 'ALLOTTED_TIMEOUT' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_failed_calls;
	}

	//get particular gateway total minutes  
	function get_gateway_total_minutes($gateway, $sofia_id, $date_from, $date_to, $call_type)
	{
		$date_to    = strtotime($date_to) * 1000000; //convert into micro seconds
		$date_from  = strtotime($date_from) * 1000000; //convert into micro seconds

		$where = '';
		if($call_type == 'answered' || $call_type == 'all')
		{
			$where = "&& (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT')";
		}
		else if ($call_type == 'busy')
		{
			$where = "&& hangup_cause = 'USER_BUSY'";
		}
		else if ($call_type == 'rejected')
		{
			$where = "&& hangup_cause = 'CALL_REJECTED'";
		}
		else if ($call_type == 'failed')
		{
			$where = "&& hangup_cause != 'NORMAL_CLEARING' && hangup_cause != 'ALLOTTED_TIMEOUT'";
		}

		$sql = "SELECT SUM(billsec) AS total_seconds FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (created_time >= '".sprintf("%.0f", $date_from)."' && created_time <= '".sprintf("%.0f", $date_to)."') ".$where." ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_seconds;
	}

	/* //get particular gateway total calls 
	function get_gateway_total_calls($gateway, $sofia_id, $date_from, $date_to)
	{
	$sql = "SELECT COUNT(*) AS total_calls FROM cdr WHERE gateway = '".$gateway."' && sofia_id = '".$sofia_id."' && (created_time > '".$date_from."' && created_time < '".$date_to."') ";
	$query = $this->db->query($sql);
	$row = $query->row();
	return $row->total_calls;
	}*/


	/**************************** CUSTOMER STATS FUNCTIONS ***************************************/

	function get_all_customers($num, $offset, $filter_customers = '', $filter_contents = '')
	{
		if($offset == ''){$offset='0';}

		$where = '';
		if($filter_contents == 'all')
		{
			$where = "WHERE customer_id != '' ";
		}
		else
		{
			$where = "WHERE parent_id = '0' ";
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'WHERE customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		$sql = "SELECT * FROM customers ".$where." LIMIT $offset,$num";
		$query = $this->db->query($sql);
		return $query;
	}

	function get_all_customers_count($filter_customers = '', $filter_contents = '')
	{
		$where = '';
		if($filter_contents == 'all')
		{
			$where = "WHERE customer_id != '' ";
		}
		else
		{
			$where = "WHERE parent_id = '0' ";
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'WHERE customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		$sql = "SELECT * FROM customers ".$where."";
		$query = $this->db->query($sql);
		$count = $query->num_rows();
		return $count;
	}

	//get customer total mins 
	function get_customer_total_mins($customer_id, $time_period)
	{
		$current_date = date('Y-m-d');
		$current_date_in_sec = strtotime($current_date);
		$current_date_in_sec = $current_date_in_sec * 10000;
		$previous_date = '';
		$curr_time = time();
		$curr_time = $curr_time * 10000;

		if($time_period == 'yesterday')
		{
			$previous_date = $current_date_in_sec - 864000000;
		}
		else if($time_period == '2_days_ago')
		{
			$previous_date = $current_date_in_sec - 1728000000;
		}
		else if($time_period == '3_days_ago')
		{
			$previous_date = $current_date_in_sec - 2592000000;
		}
		else if($time_period == 'week_ago')
		{
			$previous_date = $current_date_in_sec - 7541520000;
		}
		else if($time_period == '2_week_ago')
		{
			$previous_date = $current_date_in_sec - 12096000000;
		}
		else if($time_period == 'month_ago')
		{
			$previous_date = $current_date_in_sec - 25920000000;
		}

		$where = '';

		if($time_period == 'today')
		{
			$where = "AND created_time >= '".sprintf("%.0f", $current_date_in_sec)."' AND created_time <= '".sprintf("%.0f", $curr_time)."' ";
		}
		else
		{
			$where = "AND created_time >= '".sprintf("%.0f", $previous_date)."' AND created_time <= '".sprintf("%.0f", $current_date_in_sec)."' ";
		}

		$sql = "SELECT SUM(billsec) AS total_seconds FROM cdr WHERE customer_id = '".$customer_id."' AND (hangup_cause = 'ALLOTTED_TIMEOUT' || hangup_cause = 'NORMAL_CLEARING') AND billsec > 0 ".$where." ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_seconds;
	}

	//get customer total calls 
	function get_customer_total_calls($customer_id, $time_period)
	{
		$current_date = date('Y-m-d');
		$current_date_in_sec = strtotime($current_date);
		$current_date_in_sec = $current_date_in_sec * 10000;
		$previous_date = '';
		$curr_time = time();
		$curr_time = $curr_time * 10000;

		if($time_period == 'yesterday')
		{
			$previous_date = $current_date_in_sec - 864000000;
		}
		else if($time_period == '2_days_ago')
		{
			$previous_date = $current_date_in_sec - 1728000000;
		}
		else if($time_period == '3_days_ago')
		{
			$previous_date = $current_date_in_sec - 2592000000;
		}
		else if($time_period == 'week_ago')
		{
			$previous_date = $current_date_in_sec - 7541520000;
		}
		else if($time_period == '2_week_ago')
		{
			$previous_date = $current_date_in_sec - 12096000000;
		}
		else if($time_period == 'month_ago')
		{
			$previous_date = $current_date_in_sec - 25920000000;
		}

		$where = '';

		if($time_period == 'today')
		{
			$where = "AND created_time >= '".sprintf("%.0f", $current_date_in_sec)."' AND created_time <= '".sprintf("%.0f", $curr_time)."' ";
		}
		else
		{
			$where = "AND created_time >= '".sprintf("%.0f", $previous_date)."' AND created_time <= '".sprintf("%.0f", $current_date_in_sec)."' ";
		}

		$sql = "SELECT COUNT(id) AS total_calls FROM cdr WHERE customer_id = '".$customer_id."' AND billsec > 0 ".$where."";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_calls;
	}

	//get customer total sell cost
	function get_customer_total_sell_cost($customer_id, $time_period)
	{
		$current_date = date('Y-m-d');
		$current_date_in_sec = strtotime($current_date);
		$current_date_in_sec = $current_date_in_sec * 10000; //in micro seconds
		$previous_date = '';
		$curr_time = time();
		$curr_time = $curr_time * 10000;

		if($time_period == 'yesterday')
		{
			$previous_date = $current_date_in_sec - 864000000; //in micro seconds
		}
		else if($time_period == '2_days_ago')
		{
			$previous_date = $current_date_in_sec - 1728000000; //in micro seconds
		}
		else if($time_period == '3_days_ago')
		{
			$previous_date = $current_date_in_sec - 2592000000; //in micro seconds
		}
		else if($time_period == 'week_ago')
		{
			$previous_date = $current_date_in_sec - 7541520000; //in micro seconds
		}
		else if($time_period == '2_week_ago')
		{
			$previous_date = $current_date_in_sec - 12096000000; //in micro seconds
		}
		else if($time_period == 'month_ago')
		{
			$previous_date = $current_date_in_sec - 25920000000; //in micro seconds
		}

		$where = '';

		if($time_period == 'today')
		{
			$where = "AND created_time >= '".sprintf("%.0f", $current_date_in_sec)."' AND created_time <= '".sprintf("%.0f", $curr_time)."' ";
		}
		else
		{
			$where = "AND created_time >= '".sprintf("%.0f", $previous_date)."' AND created_time <= '".sprintf("%.0f", $current_date_in_sec)."' ";
		}

		$sql = "SELECT SUM(total_sell_cost) AS total_sell_cost FROM cdr WHERE customer_id = '".$customer_id."' AND billsec > 0 ".$where."";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_sell_cost;
	}

	//get customer total buy cost
	function get_customer_total_buy_cost($customer_id, $filter_date_from, $filter_date_to)
	{
		$where = '';

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		$sql = "SELECT SUM(total_buy_cost) AS total_buy_cost FROM cdr WHERE customer_id = '".$customer_id."' AND billsec > 0 ".$where."";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_buy_cost;
	}

	/******************************CALL DESTINATION FUNCTIONS **************************/
	function get_all_countries($num, $offset, $filter_countries = '', $filter_date_from, $filter_date_to, $filter_contents)
	{
		if($offset == ''){$offset='0';}

		$where = '';
		if($filter_contents == 'all')
		{
			$where .= "WHERE (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') AND billsec > 0 AND parent_id = '0'";
		}
		else
		{
			$where .= "WHERE (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') AND billsec > 0 AND parent_id = '0' AND parent_reseller_id ='0' ";
		}

		if($filter_countries != '')
		{
			if(is_numeric($filter_countries))
			{
				$where .= 'AND country_id = '.$this->db->escape($filter_countries).' ';
			}
		}

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		$sql = "SELECT DISTINCT(country_id) AS id FROM cdr ".$where." GROUP BY country_id LIMIT $offset,$num";
		$query = $this->db->query($sql);
		return $query;
	}

	function get_all_countries_count($filter_countries = '', $filter_date_from, $filter_date_to, $filter_contents)
	{
		$where = '';
		if($filter_contents == 'all')
		{
			$where .= "WHERE (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') AND billsec > 0 AND parent_id = '0'";
		}
		else
		{
			$where .= "WHERE (hangup_cause = 'NORMAL_CLEARING' || hangup_cause = 'ALLOTTED_TIMEOUT') AND billsec > 0 AND parent_id = '0' AND parent_reseller_id ='0' ";
		}

		if($filter_countries != '')
		{
			if(is_numeric($filter_countries))
			{
				$where .= 'AND country_id = '.$this->db->escape($filter_countries).' ';
			}
		}

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		$sql = "SELECT DISTINCT(country_id) AS id FROM cdr ".$where." GROUP BY country_id";
		$query = $this->db->query($sql);
		$count = $query->num_rows();
		return $count;
	}

	//get customer total mins 
	function get_country_total_mins($country_id, $filter_date_from, $filter_date_to)
	{
		$where = '';

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		$sql = "SELECT SUM(billsec) AS total_seconds FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where." ";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_seconds;
	}

	//get customer total calls 
	function get_country_total_calls($country_id, $filter_date_from, $filter_date_to)
	{
		$where = '';

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

//		$sql = "SELECT COUNT(*) AS total_calls FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where."";
        $sql = "SELECT COUNT(*) AS total_calls FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where."";		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_calls;
	}

	//get customer total sell cost
	function get_country_total_sell_cost($country_id, $filter_date_from, $filter_date_to)
	{
		$where = '';

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

//		$sql = "SELECT SUM(total_sell_cost) AS total_sell_cost FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where."";
        $sql = "SELECT SUM(total_sell_cost) AS total_sell_cost FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where."";
        $query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_sell_cost;
	}

	//get customer total buy cost
	function get_country_total_buy_cost($country_id, $filter_date_from, $filter_date_to)
	{
		$where = '';

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		$sql = "SELECT SUM(total_buy_cost) AS total_buy_cost FROM cdr WHERE country_id = '".$country_id."' AND billsec > 0 ".$where."";
		$query = $this->db->query($sql);
		$row = $query->row();
		return $row->total_buy_cost;
	}

	/*********************** EXPORT FUNCTIONS ***************************/
	function export_cdr_data($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort)
	{
		$order_by = "";
		if($filter_sort == 'date_asc')
		{
			$order_by = "ORDER BY created_time ASC";
		}
		else if($filter_sort == 'date_dec')
		{
			$order_by = "ORDER BY created_time DESC";
		}
		else if($filter_sort == 'billduration_asc')
		{
			$order_by = "ORDER BY billsec ASC";
		}
		else if($filter_sort == 'billduration_dec')
		{
			$order_by = "ORDER BY billsec DESC";
		}
		else if($filter_sort == 'failedgateways_asc')
		{
			$order_by = "ORDER BY total_failed_gateways ASC";
		}
		else if($filter_sort == 'failedgateways_dec')
		{
			$order_by = "ORDER BY total_failed_gateways DESC";
		}
		else if($filter_sort == 'sellrate_asc')
		{
			$order_by = "ORDER BY sell_rate ASC";
		}
		else if($filter_sort == 'sellrate_dec')
		{
			$order_by = "ORDER BY sell_rate DESC";
		}
		else if($filter_sort == 'costrate_asc')
		{
			$order_by = "ORDER BY cost_rate ASC";
		}
		else if($filter_sort == 'costrate_dec')
		{
			$order_by = "ORDER BY cost_rate DESC";
		}
		else if($filter_sort == 'sellinit_asc')
		{
			$order_by = "ORDER BY sell_initblock ASC";
		}
		else if($filter_sort == 'sellinit_dec')
		{
			$order_by = "ORDER BY sell_initblock DESC";
		}
		else if($filter_sort == 'buyinit_asc')
		{
			$order_by = "ORDER BY buy_initblock ASC";
		}
		else if($filter_sort == 'buyinit_dec')
		{
			$order_by = "ORDER BY buy_initblock DESC";
		}
		else if($filter_sort == 'totcharges_asc')
		{
			$order_by = "ORDER BY total_sell_cost ASC";
		}
		else if($filter_sort == 'totcharges_dec')
		{
			$order_by = "ORDER BY total_sell_cost DESC";
		}
		else if($filter_sort == 'totcost_asc')
		{
			$order_by = "ORDER BY total_buy_cost ASC";
		}
		else if($filter_sort == 'totcost_dec')
		{
			$order_by = "ORDER BY total_buy_cost DESC";
		}
		else
		{
			$order_by = "ORDER BY created_time DESC";
		}

		$where = '';
		$where .= "WHERE id != '' ";

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		if($filter_phonenum != '')
		{
			if(is_numeric($filter_phonenum))
			{
				$where .= 'AND destination_number = '.$this->db->escape($filter_phonenum).' ';
			}
		}

		if($filter_caller_ip != '')
		{
			if ($this->input->valid_ip($filter_caller_ip))
			{
				$where .= 'AND network_addr = '.$this->db->escape($filter_caller_ip).' ';
			}
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'AND customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		if($filter_groups != '')
		{
			if(is_numeric($filter_groups))
			{
				$groupRateTbl = 'SELECT * FROM groups WHERE id = '.$this->db->escape($filter_groups).' ';
				$groupQuery = $this->db->query($groupRateTbl);
				$row = $groupQuery->row();

				$where .= "AND customer_group_rate_table = '".$row->group_rate_table."' ";
			}
		}

		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'AND gateway = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		if($filter_call_type != '')
		{
			$where .= 'AND hangup_cause = '.$this->db->escape($filter_call_type).' ';
		}

		if($duration_from != '')
		{
			if(is_numeric($duration_from))
			{
				$where .= 'AND billsec >= '.$this->db->escape($duration_from).' ';
			}
		}

		if($duration_to != '')
		{
			if(is_numeric($duration_to))
			{
				$where .= 'AND billsec <= '.$this->db->escape($duration_to).' ';
			}
		}

		$sql = "SELECT * FROM cdr ".$where." ".$order_by."";
		$query = $this->db->query($sql);
		return $query;
	}

	function export_cdr_data_csv($filter_date_from, $filter_date_to, $filter_phonenum, $filter_caller_ip, $filter_customers, $filter_groups, $filter_gateways, $filter_call_type, $duration_from, $duration_to, $filter_sort)
	{
		$order_by = "";
		if($filter_sort == 'date_asc')
		{
			$order_by = "ORDER BY created_time ASC";
		}
		else if($filter_sort == 'date_dec')
		{
			$order_by = "ORDER BY created_time DESC";
		}
		else if($filter_sort == 'billduration_asc')
		{
			$order_by = "ORDER BY billsec ASC";
		}
		else if($filter_sort == 'billduration_dec')
		{
			$order_by = "ORDER BY billsec DESC";
		}
		else if($filter_sort == 'failedgateways_asc')
		{
			$order_by = "ORDER BY total_failed_gateways ASC";
		}
		else if($filter_sort == 'failedgateways_dec')
		{
			$order_by = "ORDER BY total_failed_gateways DESC";
		}
		else if($filter_sort == 'sellrate_asc')
		{
			$order_by = "ORDER BY sell_rate ASC";
		}
		else if($filter_sort == 'sellrate_dec')
		{
			$order_by = "ORDER BY sell_rate DESC";
		}
		else if($filter_sort == 'costrate_asc')
		{
			$order_by = "ORDER BY cost_rate ASC";
		}
		else if($filter_sort == 'costrate_dec')
		{
			$order_by = "ORDER BY cost_rate DESC";
		}
		else if($filter_sort == 'sellinit_asc')
		{
			$order_by = "ORDER BY sell_initblock ASC";
		}
		else if($filter_sort == 'sellinit_dec')
		{
			$order_by = "ORDER BY sell_initblock DESC";
		}
		else if($filter_sort == 'buyinit_asc')
		{
			$order_by = "ORDER BY buy_initblock ASC";
		}
		else if($filter_sort == 'buyinit_dec')
		{
			$order_by = "ORDER BY buy_initblock DESC";
		}
		else if($filter_sort == 'totcharges_asc')
		{
			$order_by = "ORDER BY total_sell_cost ASC";
		}
		else if($filter_sort == 'totcharges_dec')
		{
			$order_by = "ORDER BY total_sell_cost DESC";
		}
		else if($filter_sort == 'totcost_asc')
		{
			$order_by = "ORDER BY total_buy_cost ASC";
		}
		else if($filter_sort == 'totcost_dec')
		{
			$order_by = "ORDER BY total_buy_cost DESC";
		}
		else
		{
			$order_by = "ORDER BY created_time DESC";
		}

		$where = '';
		$where .= "WHERE id != '' ";

		if($filter_date_from != '')
		{
			$date_from = strtotime($filter_date_from) * 1000000; //convert into micro seconds
			$where .= "AND created_time >= '".sprintf("%.0f", $date_from)."' ";
		}

		if($filter_date_to != '')
		{
			$date_to = strtotime($filter_date_to) * 1000000; //convert into micro seconds
			$where .= "AND created_time <= '".sprintf("%.0f", $date_to)."' ";
		}

		if($filter_phonenum != '')
		{
			if(is_numeric($filter_phonenum))
			{
				$where .= 'AND destination_number = '.$this->db->escape($filter_phonenum).' ';
			}
		}

		if($filter_caller_ip != '')
		{
			if ($this->input->valid_ip($filter_caller_ip))
			{
				$where .= 'AND network_addr = '.$this->db->escape($filter_caller_ip).' ';
			}
		}

		if($filter_customers != '')
		{
			if(is_numeric($filter_customers))
			{
				$where .= 'AND customer_id = '.$this->db->escape($filter_customers).' ';
			}
		}

		if($filter_groups != '')
		{
			if(is_numeric($filter_groups))
			{
				$groupRateTbl = 'SELECT * FROM groups WHERE id = '.$this->db->escape($filter_groups).' ';
				$groupQuery = $this->db->query($groupRateTbl);
				$row = $groupQuery->row();

				$where .= "AND customer_group_rate_table = '".$row->group_rate_table."' ";
			}
		}

		if($filter_gateways != '')
		{
			if (strpos($filter_gateways,'|') !== false) {
				$explode = explode('|', $filter_gateways);
				$gateway = $explode[0];
				$profile = $explode[1];
				if(isset($gateway) && isset($profile))
				{
					if(is_numeric($profile))
					{
						$where .= 'AND gateway = '.$this->db->escape($gateway).' AND sofia_id = '.$this->db->escape($profile).' ';
					}
				}
			}
		}

		if($filter_call_type != '')
		{
			$where .= 'AND hangup_cause = '.$this->db->escape($filter_call_type).' ';
		}

		if($duration_from != '')
		{
			if(is_numeric($duration_from))
			{
				$where .= 'AND billsec >= '.$this->db->escape($duration_from).' ';
			}
		}

		if($duration_to != '')
		{
			if(is_numeric($duration_to))
			{
				$where .= 'AND billsec <= '.$this->db->escape($duration_to).' ';
			}
		}

		$sql 	= "SELECT created_time, destination_number, billsec, hangup_cause, network_addr, username, sell_rate, sell_initblock, cost_rate, buy_initblock, total_sell_cost, total_buy_cost, gateway, ani, caller_id_number, sip_user_agent FROM cdr ".$where." ".$order_by."";
		// echo $sql; exit;
		$query 	= $this->db->query($sql);
		return $query;
	}
}
?>
