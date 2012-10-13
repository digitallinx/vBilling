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
?>
<br/>
<div class="success" id="success_div" style="display:none;"></div>

<!--********************************FILTER BOX************************-->
<div style="text-align:center;padding:10px">
  <div class="button white">
    <form method="get" action="<?php echo base_url();?>cdr/gateways_stats" >
      <table width="100%" cellspacing="0" cellpadding="0" border="0" id="filter_table">
        <tr>
          <td width="14%"> Date From </td>
          <td width="14%"> Date To </td>
          <td width="14%"> Gateways </td>
          <td width="14%"> Call Type </td>
          <td width="14%"> Display Results In </td>
          <td width="14%" rowspan="2"><input type="submit" name="searchFilter" value="SEARCH" class="button blue" style="float:right;margin-top:5px;margin-right:10px" /></td>
          <td width="9%" rowspan="2"><a href="#" id="reset" class="button orange" style="float:left;margin-top:5px;">RESET</a></td>
        </tr>
        <tr>
          <td><input type="text" name="filter_date_from" value="<?php echo $filter_date_from;?>" class="datepicker" readonly></td>
          <td><input type="text" name="filter_date_to" value="<?php echo $filter_date_to;?>" class="datepicker" readonly></td>
          <td><select name="filter_gateways">
              <?php 
                                if($filter_gateways != '')
                                {
                                    if (strpos($filter_gateways,'|') !== false) {
                                        $explode = explode('|', $filter_gateways);
                                        $gateway = $explode[0];
                                        $profile = $explode[1];
                                        if(isset($gateway) && isset($profile))
                                        {
                                            if(!is_numeric($profile))
                                            {
                                                $gateway = '';
                                                $profile = '';
                                            }
                                        }
                                        else
                                        {
                                            $gateway = '';
                                            $profile = '';
                                        }
                                    }
                                    else
                                    {
                                        $gateway = '';
                                        $profile = '';
                                    }
                                }
                                else
                                {
                                    $gateway = '';
                                    $profile = '';
                                }
                            ?>
              <?php echo gateways_drop_down($gateway, $profile);?>
            </select></td>
          <td><select name="filter_call_type">
              <option value="all" <?php if($filter_call_type == 'all'){ echo "selected";}?>>All Calls</option>
              <option value="answered" <?php if($filter_call_type == 'answered'){ echo "selected";}?>>Answered Calls</option>
              <option value="busy" <?php if($filter_call_type == 'busy'){ echo "selected";}?>>Busy Calls</option>
              <option value="rejected" <?php if($filter_call_type == 'rejected'){ echo "selected";}?>>Rejected Calls</option>
              <option value="failed" <?php if($filter_call_type == 'failed'){ echo "selected";}?>>Failed Calls</option>
            </select></td>
          <td><select name="filter_display_results">
              <option value="min" <?php if($filter_display_results == 'min'){ echo "selected";}?>>Minutes</option>
              <option value="sec" <?php if($filter_display_results == 'sec'){ echo "selected";}?>>Seconds</option>
            </select></td>
        </tr>
      </table>
    </form>
  </div>
</div>
<!--***************** END FILTER BOX ****************************-->

<table width="100%" cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tbody>
            <tr class="bottom_link">
              <td height="20" width="10%" align="center">Gateway</td>
              <td width="10%" align="center">Profile</td>
              <td width="10%" align="center">Call Type</td>
              <td width="10%" align="center">Total Calls</td>
              <td width="10%" align="center">Total Duration</td>
              <td width="10%" align="center">ASR</td>
              <td width="10%" align="center">ACD</td>
              <td width="10%" align="center">PDD</td>
            </tr>
            <tr>
              <td colspan="8" id="shadowDiv" style="height:5px;margin-top:-1px"></td>
            </tr>
            <?php if($all_gateways->num_rows() > 0) {?>
            <?php foreach ($all_gateways->result() as $row): ?>
            <tr class="main_text">
              <td align="center" height="30"><?php echo $row->gateway_name; ?></td>
              <td align="center"><?php echo sofia_profile_name($row->sofia_id); ?></td>
              <?php 
                                    if($filter_call_type == 'all')
                                    {
                                        $call_type  = 'All Calls';
                                        $result     = $this->cdr_model->get_gateway_total_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                    else if($filter_call_type == 'answered')
                                    {
                                        $call_type  = 'Aswered Calls';
                                        $result     = $this->cdr_model->get_gateway_total_answered_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                    else if ($filter_call_type == 'busy')
                                    {
                                        $call_type  = 'Busy Calls';
                                        $result     = $this->cdr_model->get_gateway_total_busy_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                    else if ($filter_call_type == 'rejected')
                                    {
                                        $call_type  = 'Rejected Calls';
                                        $result     = $this->cdr_model->get_gateway_total_rejected_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                    else if ($filter_call_type == 'failed')
                                    {
                                        $call_type  = 'Failed Calls';
                                        $result     = $this->cdr_model->get_gateway_total_failed_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                    else //defualt answered 
                                    {
                                        $call_type  = 'Aswered Calls';
                                        $result     = $this->cdr_model->get_gateway_total_answered_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                    }
                                ?>
              <td align="center"><?php echo $call_type; ?></td>
              <td align="center"><?php echo $result; ?></td>
              
              <!--**************GET TOTAL MINUTES FOR GATEWAY FOR PARTICULAR CALL TYPE ******************-->
              <?php 
                                    $total_duration = $this->cdr_model->get_gateway_total_minutes($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to, $filter_call_type);
                                    
                                    if($total_duration == '')
                                    {
                                        $total_duration = 0;
                                    }
                                    
                                    if($filter_display_results == 'sec')
                                    {
                                        $total_duration_in_min_sec = $total_duration; //be defualt it is in sec
                                    }
                                    else
                                    {
                                        $total_duration_in_min_sec = $total_duration / 60; //convert to min
                                        $total_duration_in_min_sec = v_round($total_duration_in_min_sec);
                                    }
                                ?>
              <td align="center"><?php echo $total_duration_in_min_sec.'&nbsp;'.$filter_display_results; ?></td>
              <td align="center"><?php
								if($filter_call_type == 'all')
								{
									$asr = $this->cdr_model->get_gateway_asr($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
									echo round($asr,2)." %";
								}
								else //defualt answered 
								{
									echo "N / A";
								}
								?></td>
              <td align="center"><?php
                                      $mw_gateway_total_answered_calls = $this->cdr_model->get_gateway_total_answered_calls($row->gateway_name, $row->sofia_id, $filter_date_from, $filter_date_to);
                                      if ($mw_gateway_total_answered_calls != 0){
                                        $acd = $total_duration_in_min_sec / $mw_gateway_total_answered_calls;
                                        echo round($acd,4).'&nbsp;'.$filter_display_results;
                                      }
                                      else echo "N / A";
                                ?></td>
              <td align="center"><?php echo "PDD Value" ?></td>
            </tr>
            <tr style="height:5px;">
              <td colspan="8" id="shadowDiv" style="height:5px;margin-top:0px;background-color:#fff"></td>
            </tr>
            <?php endforeach;?>
            <?php } else { echo '<tr><td align="center" style="color:red;" colspan="8">No Gateways Defined Yet</td></tr>'; } ?>
          </tbody>
        </table></td>
    </tr>
    <tr>
      <td id="paginationWKTOP"><?php echo $this->pagination->create_links();?></td>
    </tr>
  </tbody>
</table>
<script type="text/javascript">
        $('.datepicker').datetimepicker({
            showSecond: true,
            showMillisec: false,
            timeFormat: 'hh:mm:ss',
            dateFormat: 'yy-mm-dd'
        });
        
        $('#reset').live('click', function(){
            $('#filter_table input[type="text"]').val('');
            $('#filter_table select').val('');
            return false;
        });
    </script>
