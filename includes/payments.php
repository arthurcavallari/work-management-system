<?php
	class Payments {
		function WorkedHours()
		{
			// Start constructor
			
			// End constructor
		}
		function index()
		{
			// Start index
			?>
            <section>
              <h2>Payments</h2>
              <p>This is where you can manage and log each payment you have received.</p>
              <ul>
              <li><a href="?page=payments&task=view">View Payments</a></li>
              <li><a href="?page=payments&task=add">Add New Payment</a></li>
              </ul>
            </section>
            <?php 
			// End index
		}
		function view()
		{
			// Start view
			global $mysqli, $current_page;
			$sqlSelect = "select p.*, e.Name, (p.GrossAmount - p.DeductedAmount) as PaidAmount";
			$sqlFrom = "from payments p left join employers e on p.Employer_id=e.id";
			$sqlWhere = "";
			$sqlOrderBy = "order by Date_Received desc, Work_Reference_id desc";
			$sqlLimit = "";
			
			$sqlQueryCount = implode(" ", array("select count(*) as `count`", $sqlFrom, $sqlWhere, $sqlOrderBy));
			$resultCount = $mysqli->query($sqlQueryCount, MYSQLI_USE_RESULT);
			
			if ($resultCount)
			{
				$count = mysqli_fetch_array($resultCount);
				
				$totalItems = $count['count'];
				$itemsPerPage = 12;
				$startItem = @$_REQUEST["p"];
				$navigation = new pageNavigation($totalItems, $itemsPerPage, $startItem);
				$resultCount->close();
				
			}
	
			if(!isset($startItem))
			{
				$startItem = 0;
			}
			
			$sqlLimit = "limit " . $startItem . ", 12";
			$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlOrderBy, $sqlLimit));
			
			//$data .= "Listing ".$totalItems." items<br /><br />";
			$data = "	<center><h3>";
			if ($navigation->noOfPages > 1) 
			{
				 
				if ($navigation->previousStartItem != -1) 
				{
					$data .= " <a href=\"?page=payments&task=view&p=".$navigation->previousStartItem."\">Prev</a> ";
				}
				 
				foreach ( $navigation->pages as $page ) 
				{
					if ($page["pageno"] == -1) 
					{
						$data .= " <b>.</b> ";
					}
					elseif ($page["pageno"] == $navigation->currPage) 
					{
						$data .= " <b>".$page["pageno"]."</b> ";
					}
					else 
					{
						$data .= " <a href=\"?page=payments&task=view&p=".$page["startitem"]."\">".$page["pageno"]."</a> ";
					}
				}
				 
				if ($navigation->nextStartItem != -1) 
				{
					$data .= " <a href=\"?page=payments&task=view&p=".$navigation->nextStartItem."\">Next</a> ";
				}
			}
			$data .= "	</h3></center>";
		
			$result = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
			$data .= "	<table width='100%' id='box-table-a'>
			<thead>
				<tr>
					<th width='20%'>Employer</th>	
					<th>Net Amount</th>							
					<th>Gross Amount</th>
					<th>Deducted Amount</th>
					<th width='20%'>Date Received</th>
					<th width='15%'>Reported to Centrelink</th>
					<th width='10%'>Ref. ID</th>
					<th width='8%'>Actions</th>
				</tr>
			</thead>
			<tbody>
	";
			if ($result)
			{	
				while ($row = mysqli_fetch_array($result)) 
				{
					
					$dateReceived = date("d/m/Y", strtotime($row['Date_Received']));
					
					$data .= "			<tr>
					<td>{$row['Name']}</td>
					<td>$" . number_format($row['PaidAmount'], 2, '.', '') . "</td>
					<td>$" . number_format($row['GrossAmount'], 2, '.', '') . "</td>
					<td>$" . number_format($row['DeductedAmount'], 2, '.', '') . "</td>
					<td>{$dateReceived}</td>
					<td>{$row['Centrelink_Reported']}</td>
					<td>{$row['Work_Reference_id']}</td>
					<td><a href='?page={$current_page}&task=edit&id={$row['id']}'>EDIT</a> / <a href='?page={$current_page}&task=delete&id={$row['id']}'>DELETE</a></td>
				</tr>
	";	
				}
				$data .= "			</tbody>
			</table>";	
				$result->close();
				
			}	
				
			if(isset($data))
			{
				echo $data;
			}
			else
			{
				echo $sqlQuery;
			}
			// End view 
		}
		
		
		function add()
		{
			// Start add
			global $mysqli, $current_page;
			function displayAddForm()
			{
				// Start displayAddForm
				$employers = new Employers(); 
				$workedHours = new WorkedHours(); 
				?>				
				<script type="text/javascript">
                    function isNumber(n) {
                      return !isNaN(parseFloat(n)) && isFinite(n);
                    }
                    function validateForm(e)
                    {
                        var errorList = "";
                        var i = 0;
                        if(employersList.value == -1) 
                        {
                            ++i;
                            errorList = '- Please select an employer!';
                        }
						if(paymentsForm.Date_Received.value == "")
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Date Received is invalid!';
                        }
                        if(!validateAmount(paymentsForm.amount))
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Amount is invalid!';
                        }
                        if(Work_Reference_id.value == -1) 
                        {
							if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Please select a reference id!';
                        }
                        
                        if(errorList != "")
                        {
                            alert("There " + (i > 1 ? "are" : "is") + " " + i + " error" + (i > 1 ? "s" : "") + ":\n" + errorList);
                            return false;	
                        }
                        else
                        {		
                            return true;
                        }
                    }
                    
                    
                  $(document).ready(function() {
                        $("#datepicker").datepicker();
                  });
                
                
                    function validateAmount(inputField) {
                        var isValid = isNumber(inputField.value);
                        if (isValid) {
                            inputField.style.backgroundColor = '#bfa';
                        } else {
                            inputField.style.backgroundColor = '#fba';
                        }
                
                        return isValid;
                    }
					
					function updateAmount(value)
					{
						value = value.substring(value.indexOf("(") + 2, value.indexOf(")"));
						paymentsForm.grossamount.value = value;
						paymentsForm.deductedamount.value = "0.00";
					}
                    
                    function updateRefIds(index)
                    {
                        if (index == -1)
                        {
                            //paymentsForm.Work_Reference_id.value = "";
							$("#Work_Reference_id option").each(function() {          
								$(this).show();								
							});
                            return;
                        }
						
						
						$("#Work_Reference_id option").each(function() {
							var id = $(this).text().split("/");
							
							if (typeof(id[1]) != "undefined") {
								if(id[1].substring(0, id[1].indexOf(" ")) != (index) && $(this).value != "-1")  {                   
									$(this).hide();
								}
								else
									$(this).show();
							}
					
						});
						
						Work_Reference_id.selectedIndex = 0;
                       
                    }
                </script>
				<div id="stylized" class="myform">
				<form id="paymentsForm" action="?page=payments&task=add" method="post" name="paymentsForm" onsubmit="return validateForm(this);">
				<h1>Add Payment</h1>
				<p></p>				
				<div style="float:left;">
					<label>Employer
					<span class="small">Select an employer</span>
					</label>
					<select name="employersList" id="employersList" style="min-width:206px; max-width:206px;" onchange="updateRefIds(employersList.value);">
						<option value="-1" selected="selected">(Select an employer)</option>
					<?php foreach ($employers->listEmployers() as $str)	{ ?>
                     	<option value="<? echo $str[0]; ?>"><? echo $str[1]; ?></option><?  } ?>
					</select>
					<div class="spacer"></div>
					<label>Date Received
					<span class="small">When was the payment received?</span>
					</label>
					<input name="Date_Received" id="datepicker" />
					<div class="spacer"></div>
					
					<label>Gross Amount
					<span class="small">How much were you payed?</span>
					</label>
					<input name="grossamount" type="text" />  
					<div class="spacer"></div>
                    
                    <label>Deducted Amount
					<span class="small">How much was deducted (tax, etc)?</span>
					</label>
					<input name="deductedamount" type="text" />  
					<div class="spacer"></div>			
					
				</div>
                <div style="float:right;">
					<label>Centrelink Reporting
					<span class="small">Have you reported to Centrelink?</span>
					</label>
					<select name="Centrelink_Reported" id="Centrelink_Reported" style="min-width:206px; max-width:206px;">
						<option value="N" selected="selected">No</option>
						<option value="Y">Yes</option>
					</select>          
					<div class="spacer"></div>
					
					<label>Reference Id
					<span class="small">Select the work reference id</span>
					</label>
					<select name="Work_Reference_id" id="Work_Reference_id" style="min-width:206px; max-width:206px;">
						<option value="-1" selected="selected">(Select a Reference Id)</option>
					<?php foreach ($workedHours->listReferenceIds() as $str)	{ ?>
                        <option onclick="updateAmount(this.innerHTML);" value="<? echo $str[1]; ?>"><? echo $str[1]; ?> ($<? echo number_format($str[2], 2, '.', ''); ?>)</option><?  } ?>
					</select> 
					<div class="spacer"></div>
					
				</div>
				<div class="spacer"></div>
				<div style="text-align:center;">
					<button type="submit" name="paymentsFormButton" class="submitButton">Submit</button> <button type="reset" class="resetButton">Reset</button>
				</div>
				<div class="spacer"></div>
				
				</form>
				</div>
				<?php
				// End displayAddForm
			}
			
			
			$submittedForm = @$_REQUEST['paymentsFormButton'];
			if(isset($submittedForm))
			{
				// Process data	
				$employerId = (@$_REQUEST['employersList']);
				$Work_Reference_id = (@$_REQUEST['Work_Reference_id']);						
				$Date_Received = (@$_REQUEST['Date_Received']);
				$grossamount = (@$_REQUEST['grossamount']);
				$deductedamount = (@$_REQUEST['deductedamount']);
				$Centrelink_Reported = (@$_REQUEST['Centrelink_Reported']);

				
				
				if(isset($employerId, $Work_Reference_id, $Date_Received, $grossamount, $deductedamount, $Centrelink_Reported))
				{
					$employerId = mysqli_real_escape_string($mysqli,@$_REQUEST['employersList']);
					$Work_Reference_id = mysqli_real_escape_string($mysqli,@$_REQUEST['Work_Reference_id']);						
					$Date_Received = mysqli_real_escape_string($mysqli,@$_REQUEST['Date_Received']);
					$grossamount = mysqli_real_escape_string($mysqli,@$_REQUEST['grossamount']);
					$deductedamount = mysqli_real_escape_string($mysqli,@$_REQUEST['deductedamount']);
					$Centrelink_Reported = mysqli_real_escape_string($mysqli,@$_REQUEST['Centrelink_Reported']);
					
					$processedDate = date("Y-m-d", strtotime($Date_Received));
					
	
					$sqlInsertQuery = 
					"insert into payments (`Employer_id`,`Date_Received`,`Work_Reference_id`,`GrossAmount`,`DeductedAmount`,`Centrelink_Reported`) ";
	
					$sqlValues = 
					"VALUES ('$employerId', '$processedDate', '$Work_Reference_id', '$grossamount', '$deductedamount', '$Centrelink_Reported')";
					
					$sqlQuery = implode(" ", array($sqlInsertQuery, $sqlValues));
					
					$sqlUpdateQuery = "update work w set w.Paid = 'Y' where w.Reference_id = '$Work_Reference_id'";
							if (!$mysqli->query($sqlUpdateQuery, MYSQLI_USE_RESULT)) 
							{
								echo "<p>Error message: " . $mysqli->error . "<br>$sqlUpdateQuery</p>";
							}
							echo $sqlUpdateQuery;
					
					$resultCount = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
					
					?>
					<h1>Entry added successfully!</h1>
					<?php Payments::view();
				}
			}
			else
			{
				 displayAddForm();
			}
			
			// End add
		}
		
		function edit($id)
		{
			// Start edit
			global $mysqli, $current_page;
			function displayEditForm($id)
			{
				// Start displayEditForm
				global $mysqli;
				$employers = new Employers(); 
				$workedHours = new WorkedHours(); 
				
				$sqlSelect = "select p.*, e.Name";
				$sqlFrom = "from payments p left join employers e on p.Employer_id=e.id";
				$sqlWhere = "where p.id='$id'"; 	
				$sqlOrderBy = "order by Date_Received desc, Work_Reference_id desc";
				$sqlLimit = "";
				
				$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlOrderBy));
				
				$result = $mysqli->query($sqlQuery, MYSQLI_STORE_RESULT);

				if ($result && ($result->num_rows > 0))
				{
					$row = mysqli_fetch_array($result);
					
					$dateReceived = date("m/d/Y", strtotime($row['Date_Received']));
									
					$result->free();
					
				}
				else
				{
					die('No no no! Something went wrong, maybe you are trying to hack me :(' . "<br>" . $mysqli->error);	
				}
				?>				
				<script type="text/javascript">
                    function isNumber(n) {
                      return !isNaN(parseFloat(n)) && isFinite(n);
                    }
                    function validateForm(e)
                    {
                        var errorList = "";
                        var i = 0;
                        if(employersList.value == -1) 
                        {
                            ++i;
                            errorList = '- Please select an employer!';
                        }
						if(paymentsForm.Date_Received.value == "")
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Date Received is invalid!';
                        }
                        if(!validateAmount(paymentsForm.grossamount))
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Gross Amount is invalid!';
                        }
						if(!validateAmount(paymentsForm.deductedamount))
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Deducted Amount is invalid!';
                        }
                        if(Work_Reference_id.value == -1) 
                        {
							if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Please select a reference id!';
                        }
                        
                        if(errorList != "")
                        {
                            alert("There " + (i > 1 ? "are" : "is") + " " + i + " error" + (i > 1 ? "s" : "") + ":\n" + errorList);
                            return false;	
                        }
                        else
                        {		
                            return true;
                        }
                    }
                    
                    
                  $(document).ready(function() {
                        $("#datepicker").datepicker();
						updateRefIds(paymentsForm.employersList.value);
						Work_Reference_id.selectedIndex = <?php $refIndex = 0; 
						foreach ($workedHours->listReferenceIds('Y') as $str)	
						{
							++$refIndex; 
							if($row['Work_Reference_id'] == $str[1]) 
							{ 
								echo $refIndex; 
							} 
						} ?>;
                  });
                
                
                    function validateAmount(inputField) {
                        var isValid = isNumber(inputField.value);
                        if (isValid) {
                            inputField.style.backgroundColor = '#bfa';
                        } else {
                            inputField.style.backgroundColor = '#fba';
                        }
                
                        return isValid;
                    }
                    
                    function updateRefIds(index)
                    {
                        if (index == -1)
                        {
							$("#Work_Reference_id option").each(function() {          
								$(this).show();								
							});
                            return;
                        }
						
						
						$("#Work_Reference_id option").each(function() {
							var id = $(this).text().split("/");
							
							if (id[1] != (index) && $(this).value != "-1")  {                   
								$(this).hide();
							}
							else
								$(this).show();					
						});
						
						Work_Reference_id.selectedIndex = 0;
                       
                    }
                </script>
				<div id="stylized" class="myform">
				<form id="paymentsForm" action="?page=payments&task=edit&id=<?php echo $id; ?>" method="post" name="paymentsForm" onsubmit="return validateForm(this);">
				<h1>Edit Payment</h1>
				<p></p>
				<div style="float:right;">
					<label>Centrelink Reporting
					<span class="small">Have you reported to Centrelink?</span>
					</label>
					<select name="Centrelink_Reported" id="Centrelink_Reported" style="min-width:206px; max-width:206px;">
						<option value="N"<?php if($row['Centrelink_Reported'] == 'N') { echo ' selected="selected"'; } ?>>No</option>
						<option value="Y"<?php if($row['Centrelink_Reported'] == 'Y') { echo ' selected="selected"'; } ?>>Yes</option>
					</select>        
					<div class="spacer"></div>
					
					<label>Reference Id
					<span class="small">Select the work reference id</span>
					</label>
					<select name="Work_Reference_id" id="Work_Reference_id" style="min-width:206px; max-width:206px;">
						<option value="-1">(Select a Reference Id)</option>
<?php foreach ($workedHours->listReferenceIds('Y') as $str)	{ ?>
                     	<option value="<? echo $str[1]; ?>"<?php if($row['Work_Reference_id'] == $str[1]) { echo ' selected="selected"'; } ?>><? echo $str[1]; ?></option>
<?  } echo $row['Work_Reference_id']; ?>
					</select> 
					<div class="spacer"></div>
					
				</div>
				<div style="float:left;">
					<label>Employer
					<span class="small">Select an employer</span>
					</label>
					<select name="employersList" id="employersList" style="min-width:206px; max-width:206px;" onchange="updateRate(employersList.value);">
						<option value="-1">(Select an employer)</option>
					<?php foreach ($employers->listEmployers() as $str)	{ ?>
                     	<option value="<?php echo $str[0]; ?>"<?php if($str[1] == $row['Name']){echo ' selected="selected"';} ?>><? echo $str[1]; ?></option><?  } ?>
					</select>
					<div class="spacer"></div>
					<label>Date Received
					<span class="small">When was the payment received?</span>
					</label>
					<input name="Date_Received" id="datepicker" value="<?php echo $dateReceived; ?>" />
					<div class="spacer"></div>
					
					<label>Gross Amount
					<span class="small">How much were you payed?</span>
					</label>
					<input name="grossamount" type="text" value="<?php echo $row['GrossAmount']; ?>" />  
					<div class="spacer"></div>
                    
                    <label>Deducted Amount
					<span class="small">How much was deducted (tax, etc)?</span>
					</label>
					<input name="deductedamount" type="text" value="<?php echo $row['DeductedAmount']; ?>" />  
					<div class="spacer"></div>
					
					
				</div>
				<div class="spacer"></div>
				<div style="text-align:center;">
					<button type="submit" name="paymentsFormButton" class="submitButton">Submit</button> <button type="reset" class="resetButton">Reset</button>
				</div>
				<div class="spacer"></div>
				
				</form>
				</div>
				<?php
				// End displayEditForm
			}
			
			if(isset($id))
			{
				$submittedForm = @$_REQUEST['paymentsFormButton'];
				if(isset($submittedForm))
				{
					// Process data	
					$employerId = (@$_REQUEST['employersList']);
					$Work_Reference_id = (@$_REQUEST['Work_Reference_id']);						
					$Date_Received = (@$_REQUEST['Date_Received']);
					$grossamount = (@$_REQUEST['grossamount']);
					$deductedamount = (@$_REQUEST['deductedamount']);
					$Centrelink_Reported = (@$_REQUEST['Centrelink_Reported']);
					
					if(isset($employerId, $Work_Reference_id, $Date_Received, $grossamount, $deductedamount, $Centrelink_Reported))
					{
						$processedDate = date("Y-m-d", strtotime($Date_Received));
						
						
						$employerId = mysqli_real_escape_string($mysqli,@$_REQUEST['employersList']);
						$Work_Reference_id = mysqli_real_escape_string($mysqli,@$_REQUEST['Work_Reference_id']);						
						$Date_Received = mysqli_real_escape_string($mysqli,@$_REQUEST['Date_Received']);
						$grossamount = mysqli_real_escape_string($mysqli,@$_REQUEST['grossamount']);
						$deductedamount = mysqli_real_escape_string($mysqli,@$_REQUEST['deductedamount']);
						$Centrelink_Reported = mysqli_real_escape_string($mysqli,@$_REQUEST['Centrelink_Reported']);
					
						$sqlEditQuery = 
							"update payments set `Employer_id` = '$employerId',
											 `Date_Received` = '$processedDate',
											 `Work_Reference_id` = '$Work_Reference_id',
											 `GrossAmount` = '$grossamount',
											 `DeductedAmount` = '$deductedamount',
											 `Centrelink_Reported` = '$Centrelink_Reported' ";
			
							$sqlEditWhere = "where `id` = '$id'";
							
							$sqlQuery = implode(" ", array($sqlEditQuery, $sqlEditWhere));
							
													
							
							$resultCount = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
						?>
						<h1>Payment entry edited successfully!</h1>
						<?php Payments::view();
					}
				}
				else
				{
					 displayEditForm($id);
				}
			}
			else
			{
				// id not set, so we just display the view
				Payments::view();	
			}			
			// End edit
		}
		
		function delete()
		{
			// Start delete
			echo "Delete";
			// End delete
		}
	}
?>