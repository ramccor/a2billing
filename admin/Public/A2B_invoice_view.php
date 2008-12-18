<?php
include ("../lib/admin.defines.php");
include ("../lib/admin.module.access.php");
include ("../lib/admin.smarty.php");
include ("../lib/support/classes/invoice.php");
include ("../lib/support/classes/invoiceItem.php");

if (! has_rights (ACX_INVOICING)){
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");
	die();
}


getpost_ifset(array('id'));

if (empty($id))
{
Header ("Location: A2B_entity_invoice.php?atmenu=payment&section=13");
}



$invoice = new invoice($id);
$items = $invoice->loadItems();

//load customer
$DBHandle  = DbConnect();
$card_table = new Table('cc_card','*');
$card_clause = "id = ".$invoice->getCard();
$card_result = $card_table -> Get_list($DBHandle, $card_clause, 0);
$card = $card_result[0];

$smarty->display('main.tpl');
if (empty($card)) {
	echo "Customer doesn't exist or is not correctly defined for this invoice !";
	die();
}

//Load invoice conf
$invoice_conf_table = new Table('cc_invoice_conf','value');
$conf_clause = "key_val = 'company_name'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$company_name = $result[0][0];

$conf_clause = "key_val = 'address'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$address = $result[0][0];

$conf_clause = "key_val = 'zipcode'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$zipcode = $result[0][0];

$conf_clause = "key_val = 'city'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$city = $result[0][0];

$conf_clause = "key_val = 'country'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$country = $result[0][0];

$conf_clause = "key_val = 'web'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$web = $result[0][0];

$conf_clause = "key_val = 'phone'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$phone = $result[0][0];

$conf_clause = "key_val = 'fax'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$fax = $result[0][0];

$conf_clause = "key_val = 'email'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$email = $result[0][0];

$conf_clause = "key_val = 'vat'";
$result = $invoice_conf_table -> Get_list($DBHandle, $conf_clause, 0);
$vat_invoice = $result[0][0];


$vat_invoice

?>

<div class="invoice-wrapper">
  <table class="invoice-table">
  <thead>
  <tr class="one">  
    <td class="one">
     <h1><?php echo gettext("INVOICE"); ?></h1>
     <div class="client-wrapper">
     	<div class="company-name break"><?php echo $card['company_name'] ?></div>
     	<div class="fullname"><?php echo $card['lastname']." ".$card['firstname'] ?></div>
       	<div class="address"><span class="street"><?php echo $card['address'] ?></span> </div>
       	<div class="zipcode-city"><span class="zipcode"><?php echo $card['zipcode'] ?></span> <span class="city"><?php echo $card['city'] ?></span></div>
      	<div class="country break"><?php echo $card['country'] ?></div>
       	<div class="vat-number"><?php echo gettext("VAT nr.")." : ".$card['VAT_RN']; ?></div>
     </div>
    </td>
    <td class="two">
    
    </td>
    <td class="three">
     <div class="supplier-wrapper">
       <div class="company-name"><?php echo $company_name ?></div>
       <div class="address"><span class="street"><?php echo $address ?></span> </div>
       <div class="zipcode-city"><span class="zipcode"><?php echo $zipcode ?></span> <span class="city"><?php echo $city ?></span></div>
       <div class="country break"><?php echo $country ?></div>
       <div class="phone"><?php echo $phone ?></div>
       <div class="fax"><?php echo $fax ?> </div>
       <div class="email"><?php echo $email ?></div>
       <div class="web"><?php echo $web ?></div>
     </div>
    </td>
  </tr>
  <tr class="two">
    <td colspan="3" class="invoice-details">
      <table class="invoice-details"> 
        <tbody><tr>
          <td class="one">
            <strong><?php echo gettext("Date"); ?></strong>
            <div><?php echo $invoice->getDate() ?></div>
          </td>
          <td class="two">
            <strong><?php echo gettext("Invoice number"); ?></strong>
            <div><?php echo $invoice->getReference() ?></div>
          </td>
          <td class="three">
           <strong>Client number</strong>
            <div><?php echo $card['username'] ?></div>
          </td>
                 </tr>       
      </tbody></table>
    </td>
  </tr>
  </thead>
  <tbody>
    <tr>
      <td colspan="3" class="items">
        <table class="items">
          <tbody>
          <tr class="one">
	          <th style="text-align:left;"><?php echo gettext("Date"); ?></th>
	          <th class="description"><?php echo gettext("Description"); ?></th>
	          <th><?php echo gettext("Cost excl. VAT"); ?></th>
	          <th><?php echo gettext("VAT"); ?></th>
	          <th><?php echo gettext("Cost incl. VAT"); ?></th>
          </tr>
          <?php 
          $i=0;
          foreach ($items as $item){ ?>
			<tr style="vertical-align:top;" class="<?php if($i%2==0) echo "odd"; else echo "even";?>" >
				<td style="text-align:left;">
					<?php echo $item->getDate(); ?>
				</td>
				<td class="description">
					<?php echo $item->getDescription(); ?>
				</td>
				<td align="right">
					<?php echo money_format('%.2n',round($item->getPrice(),2)); ?>
				</td>
				<td align="right">
					<?php echo money_format('%.2n',round($item->getVAT(),2))."%"; ?>
				</td>
				<td align="right">
					<?php echo money_format('%.2n',round($item->getPrice()*(1+($item->getVAT()/100)),2)); ?>
				</td>
			</tr>  
			 <?php  $i++;} ?>	
          
          
        </tbody></table>        
      </td>
    </tr>
    <?php
		$price_without_vat = 0;
		$price_with_vat = 0;
		$vat_array = array();
    	foreach ($items as $item){  
    	 	$price_without_vat = $price_without_vat + $item->getPrice();
    		$price_with_vat = $price_with_vat + ($item->getPrice()*(1+($item->getVAT()/100)));
    		if(array_key_exists("".$item->getVAT(),$vat_array)){
    			$vat_array[$item->getVAT()] = $vat_array[$item->getVAT()] + $item->getPrice()*($item->getVAT()/100) ;
    		}else{
    			$vat_array[$item->getVAT()] =  $item->getPrice()*($item->getVAT()/100) ;
    		}
    	 } 
    	
    	 ?>
    	
    <tr>
      <td colspan="3">
        <table class="total">
         <tbody><tr class="extotal">
           <td class="one"></td>
           <td class="two"><?php echo gettext("Subtotal excl. VAT:"); ?>Subtotal excl. VAT:</td>
           <td class="three"><?php echo money_format('%.2n',round($price_without_vat,2)); ?></td>
         </tr>
         	
         <?php foreach ($vat_array as $key => $val) { ?>
                 <tr class="vat">
                   <td class="one"></td>
                   <td class="two"><?php echo gettext("VAT $key%:") ?></td>
                   <td class="three"><?php echo money_format('%.2n',round($val,2)); ?></td>
                 </tr> 
         <?php } ?>
         <tr class="inctotal">
           <td class="one"></td>
           <td class="two"><?php echo gettext("Total incl. VAT:") ?></td>
           <td class="three"><div class="inctotal"><div class="inctotal inner"><?php echo money_format('%.2n',round($price_with_vat,2)); ?></div></div></td>
         </tr>
        </tbody></table>
      </td>
    </tr>
    <tr>
    <td colspan="3" class="additional-information">
      <div class="invoice-description">
      <?php echo $invoice->getDescription() ?>
     </div></td>
    </tr>
  </tbody>
  <tfoot>
    <tr>
      <td colspan="3" class="footer">
        <?php echo $company_name." | ".$address.", ".$zipcode." ".$city." ".$country." | VAT nr.".$vat_invoice; ?> 
      </td>
    </tr> 
  </tfoot>
  </table></div>



