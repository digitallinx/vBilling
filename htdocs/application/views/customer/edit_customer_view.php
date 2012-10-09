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
<?php 
    $row = $customer->row();
?>

<table cellspacing="0" cellpadding="0" border="0" align="center" width="100%">
	<tbody><tr>
            <td width="21" height="35"></td>
            <td width="825" class="heading">
            Update Information            </td>
            <td width="178">
            <table cellspacing="0" cellpadding="0" width="170" height="42" class="search_col">
                <tbody><tr>
                    <td align="center" width="53" valign="bottom">&nbsp;</td>
                </tr>
                
                <tr>
                    <td align="center" width="53" valign="top">&nbsp;</td>
                </tr>
            </tbody></table>
            </td>
        </tr>
        <tr>
        <td background="<?php echo base_url();?>assets/images/line.png" height="7" colspan="3"></td>
        </tr>
        
        <?php require_once("pop_up_menu.php");?>

                <tr>
            <td height="10"></td>
            <td></td>
            <td></td>
        </tr>
        
        <tr>
        <td colspan="3"><div class="error" id="err_div" style="display:none;"></div></td>
        </tr>
        
        <tr>
        <td colspan="3"><div class="success" id="success_div" style="display:none;"></div></td>
        </tr>
              
<tr>
    <td align="center" height="20" colspan="3">
        <form enctype="multipart/form-data"  method="post" action="" name="addCust" id="addCust">
            <table cellspacing="3" cellpadding="2" border="0" width="95%" class="search_col">
                
                <input type="hidden" name="customer_id" id="customer_id" value="<?php echo $customer_id;?>"/>
                
                <tbody>
                
                <tr>
                    <td align="right" width="45%"><span class="required">*</span> Firstname:</td>
                    <td align="left" width="55%"><input type="text" value="<?php echo $row->customer_firstname;?>" name="firstname" id="firstname" maxlength="50" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right"><span class="required">*</span> Lastname:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_lastname;?>" name="lastname" id="lastname" maxlength="50" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right">Company Name:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_company;?>" name="companyname" id="companyname" maxlength="45" class="textfield"></td>
                </tr>
                
                <tr>
                    <td align="right"><span class="required">*</span> Address:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_address;?>" name="address" id="address" maxlength="150" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right"><span class="required">*</span> City:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_city;?>" name="city" id="city" maxlength="45" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right"><span class="required">*</span> State:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_state;?>" name="state" id="state" maxlength="45" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right"><span class="required">*</span> Zipcode:</td>
                    <td align="left"><input type="text" value="<?php echo $row->customer_zip;?>" name="zipcode" id="zipcode" maxlength="10" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right"><span class="required">*</span> Country:</td>
                    <td align="left">
                        <select class="textfield" name="country" id="country">
                            <?php echo all_countries($row->customer_country);?>
                        </select>
                </td></tr>
                <tr>
                    <td align="right"><span class="required">*</span> Phone:</td>
                    <td align="left">
                    <input type="text" style="width:55px" value="<?php echo $row->customer_phone_prefix;?>" name="prefix" id="prefix" maxlength="10" class="textfield" readonly >
                    <input type="text" style="width:119px" value="<?php echo $row->customer_phone;?>" name="phone" id="phone" maxlength="45" class="textfield"></td>
                </tr>
                <tr>
                    <td align="right">Timezone:</td>
                    <td align="left">
                        <select class="textfield" name="timezone" id="timezone">
                            <?php echo all_timezones($row->customer_timezone);?>                       
                        </select>
                </td></tr>
                <tr>
                    <td align="right"><span class="required">-----></span> Actual balance:</td>
                    <td align="left"><?php echo $row->customer_balance;?></td>
                </tr>         
                
                <tr>
                    <td align="right" colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td align="center" colspan="2"><input border="0" id="submitAddCustForm" type="image" src="<?php echo base_url();?>assets/images/btn-submit.png"></td>
                    
                    
                </tr>
            </tbody></table>
        </form>
    </td>
</tr>

<tr>
    <td>&nbsp;</td>
    <td></td>
    <td></td>
</tr>

<tr>
    <td height="5"></td>
    <td></td>
    <td></td>
</tr>


<tr>
    <td height="20" colspan="3">&nbsp;</td>
</tr>
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    </tbody></table>

<script type="text/javascript">
    $('#addCust').submit(function(){
        //show wait msg 
    $.blockUI({ css: { 
                    border: 'none', 
                    padding: '15px', 
                    backgroundColor: '#000', 
                    '-webkit-border-radius': '10px', 
                    '-moz-border-radius': '10px', 
                    opacity: .5, 
                    color: '#fff' 
                    } 
                });
                
        var firstname = $('#firstname').val();
        var lastname = $('#lastname').val();
        var companyname = $('#companyname').val();
        var address = $('#address').val();
        var city = $('#city').val();
        var state = $('#state').val();
        var zipcode = $('#zipcode').val();
        var country = $('#country').val();
        var prefix = $('#prefix').val();
        var phone = $('#phone').val();
        var timezone = $('#timezone').val();
        
        var required_error = 0;
        
        //common required fields check
        if(firstname == '' || lastname == '' || address == '' || city == '' || state == '' || zipcode == '' || country == '')
        {
            required_error = 1;
        }
        
        var text = "";
        
        if(required_error == 1)
        {
            text += "Fields With * Are Required Fields<br/>";
        }
        
        if(text != '')
        {
            $('.success').hide();
            $('.error').html(text);
            $('.error').fadeOut();
            $('.error').fadeIn();
            document.getElementById('err_div').scrollIntoView();
            $.unblockUI();
            return false;
        }
        else
        {
           var form = $('#addCust').serialize();
            $.ajax({
                    type: "POST",
					url: base_url+"customer/update_customer_db",
					data: form,
                    success: function(html){
                        $('.error').hide();
                        $('.success').html("Customer updated successfully.");
                        $('.success').fadeOut();
                        $('.success').fadeIn();
                        document.getElementById('success_div').scrollIntoView();
                        $.unblockUI();
                    }
				});
                
            return false;
        }
        return false;
    });
    
    $('#country').change(function(){
        var val = $(this).val();
        
        $.ajax({
            type: "POST",
            url: base_url+"customer/get_country_prefix",
            data: 'id='+val,
            success: function(html){
                $('#prefix').val('+'+html+'');
            }
       });
    });
</script>