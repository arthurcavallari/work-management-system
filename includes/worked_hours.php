<?php
	class WorkedHours {
		function WorkedHours()
		{
			
		}
		function index()
		{
			?>
			 <section>
			  <h2>Worked Hours</h2>
			  <p>This is where you can manage and log the hours you have worked for each employer.</p>
			  <ul>
			  <li><a href="?page=workedhours&task=view">View Worked Hours</a></li>
			  <li><a href="?page=workedhours&task=add">Add Worked Hours</a></li>
			  </ul>
			</section>
			<?php 
		}
		function view()
		{
			// Start view
			global $mysqli, $current_page;
		
			$employer1 = @$_REQUEST['employer1'];
//			$employerComparisonMethod = @$_REQUEST['employerComparisonMethod'];				// ==, !=, contains
			
			$Date_WorkedValue1 = @$_REQUEST['Date_Worked1'];
			$Date_WorkedValue2 = @$_REQUEST['Date_Worked2'];
			$Date_WorkedComparisonMethod = @$_REQUEST['Date_WorkedComparisonMethod']; 		// any
			
			$startTimeValue1 = @$_REQUEST['startTime1'];
			$startTimeValue2 = @$_REQUEST['startTime2'];
			$startTimeComparisonMethod = @$_REQUEST['startTimeComparisonMethod']; 			// any
			
			$endTimeValue1 = @$_REQUEST['endTime1'];
			$endTimeValue2 = @$_REQUEST['endTime2'];
			$endTimeComparisonMethod = @$_REQUEST['endTimeComparisonMethod'];				// any
			
			$hoursWorkedValue1 = @$_REQUEST['hoursWorked1'];
			$hoursWorkedValue2 = @$_REQUEST['hoursWorked2'];
			$hoursWorkedComparisonMethod = @$_REQUEST['hoursWorkedComparisonMethod']; 		// any
			
			$hoursDeductedValue1 = @$_REQUEST['hoursDeducted1'];
			$hoursDeductedValue2 = @$_REQUEST['hoursDeducted2'];
			$hoursDeductedComparisonMethod = @$_REQUEST['hoursDeductedComparisonMethod']; 	// any
			
			$paidValue1 = @$_REQUEST['paid1'];
//			$paidComparisonMethod = @$_REQUEST['paidComparisonMethod']; 					// ==, !=
			
			$rateValue1 = @$_REQUEST['rate1'];
			$rateValue2 = @$_REQUEST['rate2'];
			$rateComparisonMethod = @$_REQUEST['rateComparisonMethod']; 					// any
			
			$notes1 = @$_REQUEST['notes1'];
			$notesComparisonMethod = @$_REQUEST['notesComparisonMethod'];					// ==, !=, contains
			
			$refId1 = @$_REQUEST['refId1'];
			$refIdComparisonMethod = @$_REQUEST['refIdComparisonMethod'];					// ==, !=, contains
			
			// Possible comparison methods: 
			//   x         >      y 
			//   x         >=     y
			//   x         <      y 
			//   x         <=     y 
			//   field     ==     x
			//   field     !=     x
			//   field  contains  x
	
			
			
			// TODO: add range filters for each column
			// for each field, if comparisonMethod is "==, !=, contains", check if isset(field1) 
			// else, check if isset(field1, field2)
			
			$sqlSelect = "select w.*, e.Name";
			$sqlFrom = "from work w left join employers e on w.Employer_id=e.id";
			if(isset($filterField, $filterValue))
			{
				$sqlWhere = "where $filterField";	
			}
			else
			{
				$sqlWhere = ""; //where Date_Worked >= '2013-04-18' and Date_Worked <= '2013-05-01'";	
			}		
			$sqlOrderBy = "order by Date_Worked desc, Time_Start desc";
			$sqlLimit = "";
			$sqlGroupBy = "";
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
			$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlGroupBy, $sqlOrderBy, $sqlLimit));
			echo $sqlQuery;
			//$data .= "Listing ".$totalItems." items<br /><br />";
			?>	<center><h3><?php
			if ($navigation->noOfPages > 1) 
			{
				 
				if ($navigation->previousStartItem != -1) 
				{
					echo " <a href=\"?page=workedhours&task=view&p=".$navigation->previousStartItem."\">Prev</a> ";
				}
				 
				foreach ( $navigation->pages as $page ) 
				{
					if ($page["pageno"] == -1) 
					{
						echo " <b>.</b> ";
					}
					elseif ($page["pageno"] == $navigation->currPage) 
					{
						echo " <b>".$page["pageno"]."</b> ";
					}
					else 
					{
						echo " <a href=\"?page=workedhours&task=view&p=".$page["startitem"]."\">".$page["pageno"]."</a> ";
					}
				}
				 
				if ($navigation->nextStartItem != -1) 
				{
					echo " <a href=\"?page=workedhours&task=view&p=".$navigation->nextStartItem."\">Next</a> ";
				}
			}
			?>	</h3></center><?php
			
			$result = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
			
			
			/*			
			
			$employer1 = @$_REQUEST['employer1'];
			$employerComparisonMethod = @$_REQUEST['employerComparisonMethod'];				// ==, !=, contains
			
			*/
			
			?>
        
		<table width='100%' id='box-table-a'>
			<thead>
				<tr>
					<th width='20%'>
						Employer
					</th>
					<th width='6%'>
						Date
					</th>
					<th width='8%'>
						Start Time
					</th>
					<th width='8%'>
						End Time
					</th>
					<th width='9%'>
						Hours Worked
					</th>
					<th width='10%'>
						Deducted Hours
					</th>
					<th width='1%'>
						Paid
					</th>
					<th width='5%'>
						Rate
					</th>
					<th width='15%'>
						Notes
					</th>
					<th width='10%'>
						Ref. ID
					</th>
					<th width='8%'>
						Actions
					</th>
				</tr>
			</thead>
			<tbody>
	<?php
			if ($result)
			{	
				while ($row = mysqli_fetch_array($result)) 
				{
					
					$dateWorked = date("d/m/Y", strtotime($row['Date_Worked']));
					$startTime = date("h:i A", strtotime($row['Time_Start']));
					$endTime = date("h:i A", strtotime($row['Time_End']));
					$workedHours = $row['Hours_Worked'];
					$deductedHours = $row['Deducted_Hours'];
					
					
					
					
	?>			<tr>
					<td><?php echo $row['Name']; ?></td>
					<td><?php echo $dateWorked; ?></td>
					<td><?php echo $startTime; ?></td>
					<td><?php echo $endTime; ?></td>
					<td><?php echo $workedHours; ?></td>
					<td><?php echo $deductedHours; ?></td>
					<td><?php echo $row['Paid']; ?></td>
					<td>$<?php echo number_format($row['Hourly_Rate'], 2, '.', ''); ?></td>
					<td><?php echo $row['Notes']; ?></td>
					<td><?php echo $row['Reference_id']; ?></td>
					<td><a href='?page=<?php echo $current_page; ?>&task=edit&id=<?php echo $row['id']; ?>'>EDIT</a> / <a href='?page=<?php echo $current_page; ?>&task=delete&id=<?php echo $row['id']; ?>'>DELETE</a></td>
				</tr>
	<?php	
				}
?>		</tbody>
		</table>
        <div id="stylizedView" class="myform">
        <h1>Filter Options</h1>
		<p></p>
		<form id="filterForm" action="?page=workedhours&task=view&p=<?php echo $startItem; ?>" method="post" name="filterForm">
            <div style="float:left;">
            <!--
            //   x         >      y 
			//   x         >=     y
			//   x         <      y 
			//   x         <=     y 
			//   field     ==     x
			//   field     !=     x
			//   field  contains  x
            -->
                <label>Employer</label>
                <select name="employer1" id="employer1" >
						<option value="-1" selected="selected">(Select an employer)</option>
					<?php $employers = new Employers(); foreach ($employers->listEmployers() as $str)	{ ?>
                     	<option value="<? echo $str[0]; ?>"><? echo $str[1]; ?></option><?  } ?>
				</select>
                <div class="spacer"></div>
                <label>Start Time</label>
                <select name="startTimeComparisonMethod" class="smallSelect" >
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="startTime1" />
                <input type="text" name="startTime2" />
              <div class="spacer"></div>
                <label>End Time</label>
                <select name="endTimeComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="endTime1" />
                <input type="text" name="endTime2" />
                <div class="spacer"></div>
                <label>Hours Worked</label>             
                <select name="hoursWorkedComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="hoursWorked1" />
                <input type="text" name="hoursWorked2" />
                <div class="spacer"></div>
                <label>Hours Deducted</label>
                <select name="hoursDeductedComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="hoursDeducted1" />
                <input type="text" name="hoursDeducted2" />
                <div class="spacer"></div>
            </div>
            <div style="float:right;">                
                <label>Paid</label>
                <select name="paid1" >
                    <option value="-1" selected="selected">(Select a value)</option>
                    <option value="Y">Yes</option>
                    <option value="N">No</option>
                </select>
                <div class="spacer"></div>
                <label>Date Worked</label>
                <select name="Date_WorkedComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="Date_Worked1" />
                <input type="text" name="Date_Worked2" />
                <div class="spacer"></div>                
                <label>Rate</label>
                <select name="rateComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value=">">&gt;</option>
                    <option value=">=">&gt;=</option>
                    <option value="<">&lt;</option>
                    <option value="<=">&lt;=</option>
                </select>
                <input type="text" name="rate1" />
                <input type="text" name="rate2" /> 
                <div class="spacer"></div>
                <label>Notes</label>
                <select name="notesComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="notes1" />
                <div class="spacer"></div>
                <label>Ref. ID</label>
                <select name="refIdComparisonMethod" class="smallSelect">
                    <option value="==">==</option>
                    <option value="!=">!=</option>
                    <option value="contains">contains</option>
                </select>
                <input type="text" name="refId1" />
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
				$result->close();
				
			}	
			
				
			/*if(isset($data))
			{
				echo $data;
			}
			else
			{
				echo $sqlQuery;
			}*/
			// End view 
		}
		
		function listReferenceIds($paid = 'N')
		{
			global $mysqli;
			$sqlSelect = "select id, Reference_id";
			$sqlFrom = "from work";
			$sqlWhere = ($paid == 'N' ? "where Paid = '$paid'" : ""); //where Employer_id = '$Employer_id'";
			$sqlGroupBy = "group by Reference_id";
			$sqlOrderBy = "order by Date_Worked, Time_Start";
			$sqlLimit = "";
			
			$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlGroupBy, $sqlOrderBy, $sqlLimit));
			
			//$data .= "Listing ".$totalItems." items<br /><br />";
			
			$result = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);		
			
			if ($result)
			{	
				$data = array();
				$i = 0;
				while ($row = mysqli_fetch_array($result)) 
				{
					$data[$i] = array($row['id'], $row['Reference_id']);
					++$i;
				}
				$result->close();
				
			}					
			
			if(isset($data))
			{
				return $data;
			}
			else
			{
				return $sqlQuery;
			}
		}
		
		
		function add()
		{
			// Start add
			global $mysqli, $current_page;
			function displayAddForm()
			{
				// Start displayAddForm
				$employers = new Employers(); 
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
						if(workedHoursForm.Date_Worked.value == "")
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Date Worked is invalid!';
                        }
                        if(!validTime(workedHoursForm.startTime))
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Start time is invalid!';
                        }
                        if(!validTime(workedHoursForm.endTime)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- End time is invalid!';
                        }
                        if(!isNumber(workedHoursForm.hoursWorked.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Hours Worked is invalid!';
                        }
                        if(!isNumber(workedHoursForm.hoursDeducted.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Hours Deducted is invalid!';
                        }
                        if(!isNumber(workedHoursForm.rate.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Rate is invalid!';
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
                
                
                    function validTime(inputField) {
                        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField.value);
                        if(inputField.value == "")
                        {
                            inputField.style.backgroundColor = '#fff';	
                        }
                        else if (isValid) {
                            inputField.style.backgroundColor = '#bfa';
                        } else {
                            inputField.style.backgroundColor = '#fba';
                        }
                
                        return isValid;
                    }
                    
                    function diff(start, end) {
                        start = start.split(":");
                        end = end.split(":");
                        var startDate = new Date(0, 0, 0, start[0], start[1], 0);
                        var endDate = new Date(0, 0, 0, end[0], end[1], 0);
                        var diff = endDate.getTime() - startDate.getTime();
                        var hours = Math.floor(diff / 1000 / 60 / 60);
                        diff -= hours * 1000 * 60 * 60;
                        var minutes = Math.floor(diff / 1000 / 60);
                    
                        return hours + (minutes / 60);
                    }
                    
                    function validateTimeInput()
                    {		
                        if( validTime(workedHoursForm.startTime) && validTime(workedHoursForm.endTime) && workedHoursForm.endTime.value != "" && workedHoursForm.startTime.value != "")
                        {
                            workedHoursForm.hoursWorked.value = diff(workedHoursForm.startTime.value, workedHoursForm.endTime.value);
                        }	
                    }
                    
                    function updateRate(index)
                    {
                        if (index == -1)
                        {
                            workedHoursForm.rate.value = "";
                            return;
                        }
                        var rates=[<?php $list = ""; 
                
                    foreach ($employers->listEmployers() as $str) 
                    {
                        if($list == "")
                        {
                            $list = '"' . number_format($str[2], 2, '.', '') . '"';
                        }
                        else
                        {
                            $list = $list . ', "' . number_format($str[2], 2, '.', '') . '"';
                        }
                    }
                    echo $list;
                ?>];
                        workedHoursForm.rate.value = rates[index-1];
                    }
                </script>
				<div id="stylized" class="myform">
				<form id="workedHoursForm" action="?page=workedhours&task=add" method="post" name="workedHoursForm" onsubmit="return validateForm(this);">
				<h1>Add Worked Hour</h1>
				<p></p>				
				<div style="float:left;">
					<label>Employer
					<span class="small">Select an employer</span>
					</label>
					<select name="employersList" id="employersList" style="min-width:206px; max-width:206px;" onchange="updateRate(employersList.value);">
						<option value="-1" selected="selected">(Select an employer)</option>
					<?php foreach ($employers->listEmployers() as $str)	{ ?>
                     	<option value="<? echo $str[0]; ?>"><? echo $str[1]; ?></option><?  } ?>
					</select>
					<div class="spacer"></div>
					<label>Date
					<span class="small">What day was your shift?</span>
					</label>
					<input name="Date_Worked" id="datepicker" />
					<div class="spacer"></div>
					
					<label>Start Time
					<span class="small">What time did you start work?</span>
					</label>
					<input name="startTime" type="text" onchange="validateTimeInput();" onfocus="validateTimeInput();"/>  
					<div class="spacer"></div>
					
					<label>End Time
					<span class="small">What time did you finish work?</span>
					</label>
					<input name="endTime" type="text" onchange="validateTimeInput();" onfocus="validateTimeInput();"/>            
					<div class="spacer"></div>
					
					<label>Hours Worked
					<span class="small">How many hours did you work?</span>
					</label>
					<input name="hoursWorked" type="text" value="0"/>            
					<div class="spacer"></div>
					
					<label>Hours Deducted
					<span class="small">Hours deducted (break, etc)?</span>
					</label>
					<input name="hoursDeducted" type="text" value="0"/>            
					<div class="spacer"></div>
					
				</div>
                <div style="float:right;">
					<label>Payment received
					<span class="small">Have you already been paid?</span>
					</label>
					<select name="paid" id="paid" style="min-width:206px; max-width:206px;">
						<option value="N" selected="selected">No</option>
						<option value="Y">Yes</option>
					</select>          
					<div class="spacer"></div>
					
					<label>Rate
					<span class="small">What is your rate per hour?</span>
					</label>
					<input name="rate" type="text"/>   
					<div class="spacer"></div>
					
					<label>Notes
					<span class="small">Enter short description or notes</span>
					</label>
					<textarea name="notes" rows="10"></textarea>  
					<div class="spacer"></div>
				</div>
				<div class="spacer"></div>
				<div style="text-align:center;">
					<button type="submit" name="workedHoursFormButton" class="submitButton">Submit</button> <button type="reset" class="resetButton">Reset</button>
				</div>
				<div class="spacer"></div>
				
				</form>
				</div>
				<?php
				// End displayAddForm
			}
			
			
			$submittedForm = @$_REQUEST['workedHoursFormButton'];
			if(isset($submittedForm))
			{
				// Process data	
				$employerId = (@$_REQUEST['employersList']);						
				$Date_Worked = (@$_REQUEST['Date_Worked']);
				$startTime = (@$_REQUEST['startTime']);
				$endTime = (@$_REQUEST['endTime']);
				$hoursWorked = (@$_REQUEST['hoursWorked']);
				$hoursDeducted = (@$_REQUEST['hoursDeducted']);
				$paid = (@$_REQUEST['paid']);
				$rate = (@$_REQUEST['rate']);
				$notes = (@$_REQUEST['notes']);			
				
				if(isset($employerId, $Date_Worked, $startTime, $endTime, $hoursWorked, $hoursDeducted, $paid, $rate, $notes))
				{
					$employerId = mysqli_real_escape_string($mysqli,@$_REQUEST['employersList']);						
					$Date_Worked = mysqli_real_escape_string($mysqli,@$_REQUEST['Date_Worked']);
					$startTime = mysqli_real_escape_string($mysqli,@$_REQUEST['startTime']);
					$endTime = mysqli_real_escape_string($mysqli,@$_REQUEST['endTime']);
					$hoursWorked = mysqli_real_escape_string($mysqli,@$_REQUEST['hoursWorked']);
					$hoursDeducted = mysqli_real_escape_string($mysqli,@$_REQUEST['hoursDeducted']);
					$paid = mysqli_real_escape_string($mysqli,@$_REQUEST['paid']);
					$rate = mysqli_real_escape_string($mysqli,@$_REQUEST['rate']);
					$notes = mysqli_real_escape_string($mysqli,@$_REQUEST['notes']);
				
				
					$dayOfWork = date('l', strtotime($Date_Worked));
					
					switch ($dayOfWork)
					{
						case "Sunday":
							$addDaysToMonday = "-6";
							break;
						case "Monday":
							$addDaysToMonday = "+0";
							break;
						case "Tuesday":
							$addDaysToMonday = "-1";
							break;
						case "Wednesday":
							$addDaysToMonday = "-2";
							break;
						case "Thursday":
							$addDaysToMonday = "-3";
							break;
						case "Friday":
							$addDaysToMonday = "-4";
							break;
						case "Saturday":
							$addDaysToMonday = "-5";
							break;
						default:	
							$addDaysToMonday = "err";
					}
					$week_start = date('d-Y-m', strtotime($addDaysToMonday . ' days', strtotime($Date_Worked) ));
					$refId = "WK" . $week_start . "/$employerId";
					
										
					$processedDate = date("Y-m-d", strtotime($Date_Worked));
					$processedStartTime = date("H:i:s", strtotime($startTime));
					$processedEndTime = date("H:i:s", strtotime($endTime));
					
	
					$sqlInsertQuery = 
					"insert into work (`Employer_id`,`Date_Worked`,`Time_Start`,`Time_End`,`Hours_Worked`,`Deducted_Hours`,`Paid`,`Hourly_Rate`,`Notes`,`Reference_id`) ";
	
					$sqlValues = 
					"VALUES ('$employerId','$processedDate','$processedStartTime','$processedEndTime','$hoursWorked','$hoursDeducted','$paid','$rate','$notes','$refId')";
					
					$sqlQuery = implode(" ", array($sqlInsertQuery, $sqlValues));
					
					$resultCount = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
					
					
					?>
					<h1>Entry added successfully!</h1>
					<?php /* change this to header('location: xxxx?add=true') or something */ WorkedHours::view();
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
				$sqlSelect = "select w.*, e.Name";
				$sqlFrom = "from work w left join employers e on w.Employer_id=e.id";
				$sqlWhere = "where w.id='$id'"; 	
				$sqlOrderBy = "order by Date_Worked desc, Time_Start desc";
				$sqlLimit = "";
				
				$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlOrderBy));
				
				$result = $mysqli->query($sqlQuery, MYSQLI_STORE_RESULT);

				if ($result && ($result->num_rows > 0))
				{
					$row = mysqli_fetch_array($result);
					
					$dateWorked = date("m/d/Y", strtotime($row['Date_Worked']));
					$startTime = date("H:i", strtotime($row['Time_Start']));
					$endTime = date("H:i", strtotime($row['Time_End']));
					$workedHours = $row['Hours_Worked'];
					$deductedHours = $row['Deducted_Hours'];					
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
                        if(!validTime(workedHoursForm.startTime))
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Start time is invalid!';
                        }
                        if(!validTime(workedHoursForm.endTime)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- End time is invalid!';
                        }
                        if(!isNumber(workedHoursForm.hoursWorked.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Hours Worked is invalid!';
                        }
                        if(!isNumber(workedHoursForm.hoursDeducted.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Hours Deducted is invalid!';
                        }
                        if(!isNumber(workedHoursForm.rate.value)) 
                        {
                            if(errorList != "") errorList += '\n';
                            ++i;
                            errorList += '- Rate is invalid!';
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
                
                
                    function validTime(inputField) {
                        var isValid = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/.test(inputField.value);
                        if(inputField.value == "")
                        {
                            inputField.style.backgroundColor = '#fff';	
                        }
                        else if (isValid) {
                            inputField.style.backgroundColor = '#bfa';
                        } else {
                            inputField.style.backgroundColor = '#fba';
                        }
                
                        return isValid;
                    }
                    
                    function diff(start, end) {
                        start = start.split(":");
                        end = end.split(":");
                        var startDate = new Date(0, 0, 0, start[0], start[1], 0);
                        var endDate = new Date(0, 0, 0, end[0], end[1], 0);
                        var diff = endDate.getTime() - startDate.getTime();
                        var hours = Math.floor(diff / 1000 / 60 / 60);
                        diff -= hours * 1000 * 60 * 60;
                        var minutes = Math.floor(diff / 1000 / 60);
                    
                        return hours + (minutes / 60);
                    }
                    
                    function validateTimeInput()
                    {		
                        if( validTime(editWorkedHoursForm.startTime) && validTime(editWorkedHoursForm.endTime) && editWorkedHoursForm.endTime.value != "" && editWorkedHoursForm.startTime.value != "")
                        {
                            editWorkedHoursForm.hoursWorked.value = diff(editWorkedHoursForm.startTime.value, editWorkedHoursForm.endTime.value);
                        }	
                    }
                    
                    function updateRate(index)
                    {
                        if (index == -1)
                        {
                            editWorkedHoursForm.rate.value = "";
                            return;
                        }
                        var rates=[<?php $list = ""; 
                
                    foreach ($employers->listEmployers() as $str) 
                    {
                        if($list == "")
                        {
                            $list = '"' . number_format($str[2], 2, '.', '') . '"';
                        }
                        else
                        {
                            $list = $list . ', "' . number_format($str[2], 2, '.', '') . '"';
                        }
                    }
                    echo $list;
                ?>];
                        editWorkedHoursForm.rate.value = rates[index-1];
                    }
                </script>
				<div id="stylized" class="myform">
				<form id="editWorkedHoursForm" action="?page=workedhours&task=edit&id=<?php echo $id; ?>" method="post" name="editWorkedHoursForm" onsubmit="return validateForm(this);">
				<h1>Edit Worked Hour</h1>
				<p></p>
				<div style="float:right;">
					<label>Payment received
					<span class="small">Have you already been paid?</span>
					</label>
					<select name="paid" id="paid" style="min-width:206px; max-width:206px;">
						<option value="N"<?php if($row['Paid'] == 'N') { echo ' selected="selected"'; } ?>>No</option>
						<option value="Y"<?php if($row['Paid'] == 'Y') { echo ' selected="selected"'; } ?>>Yes</option>
					</select>          
					<div class="spacer"></div>
					
					<label>Rate
					<span class="small">What is your rate per hour?</span>
					</label>
					<input name="rate" type="text" value="<?php echo number_format($row['Hourly_Rate'], 2, '.', ''); ?>"/>   
					<div class="spacer"></div>
					
					<label>Notes
					<span class="small">Enter short description or notes</span>
					</label>
					<textarea name="notes" rows="10"><?php echo $row['Notes']; ?></textarea>  
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
					<label>Date
					<span class="small">What day was your shift?</span>
					</label>
					<input name="Date_Worked" id="datepicker" value="<?php echo $dateWorked; ?>" />
					<div class="spacer"></div>
					
					<label>Start Time
					<span class="small">What time did you start work?</span>
					</label>
					<input name="startTime" type="text" onchange="validateTimeInput();" onfocus="validateTimeInput();" value="<?php echo $startTime; ?>" />  
					<div class="spacer"></div>
					
					<label>End Time
					<span class="small">What time did you finish work?</span>
					</label>
					<input name="endTime" type="text" onchange="validateTimeInput();" onfocus="validateTimeInput();" value="<?php echo $endTime; ?>" />            
					<div class="spacer"></div>
					
					<label>Hours Worked
					<span class="small">How many hours did you work?</span>
					</label>
					<input name="hoursWorked" type="text" value="<?php echo $workedHours; ?>" />            
					<div class="spacer"></div>
					
					<label>Hours Deducted
					<span class="small">Hours deducted (break, etc)?</span>
					</label>
					<input name="hoursDeducted" type="text" value="<?php echo $deductedHours; ?>"/>            
					<div class="spacer"></div>
					
				</div>
				<div class="spacer"></div>
				<div style="text-align:center;">
					<button type="submit" name="workedHoursEditFormButton" class="submitButton">Submit</button> <button type="reset" class="resetButton">Reset</button>
				</div>
				<div class="spacer"></div>
				
				</form>
				</div>
				<?php
				// End displayEditForm
			}
			

			if(isset($id))
			{
				$submittedForm = @$_REQUEST['workedHoursEditFormButton'];

				if(isset($submittedForm))
				{
					// Process data	
					$employerId = (@$_REQUEST['employersList']);						
					$Date_Worked = (@$_REQUEST['Date_Worked']);
					$startTime = (@$_REQUEST['startTime']);
					$endTime = (@$_REQUEST['endTime']);
					$hoursWorked = (@$_REQUEST['hoursWorked']);
					$hoursDeducted = (@$_REQUEST['hoursDeducted']);
					$paid = (@$_REQUEST['paid']);
					$rate = (@$_REQUEST['rate']);
					$notes = (@$_REQUEST['notes']);			
					
					//$processedDate = date("Y-m-d", strtotime($Date_Worked));
					//echo $Date_Worked . ' - ' . $processedDate;
					
					if(isset($employerId, $Date_Worked, $startTime, $endTime, $hoursWorked, $hoursDeducted, $paid, $rate, $notes))
					{
						$employerId = mysqli_real_escape_string($mysqli,@$_REQUEST['employersList']);						
						$Date_Worked = mysqli_real_escape_string($mysqli,@$_REQUEST['Date_Worked']);
						$startTime = mysqli_real_escape_string($mysqli,@$_REQUEST['startTime']);
						$endTime = mysqli_real_escape_string($mysqli,@$_REQUEST['endTime']);
						$hoursWorked = mysqli_real_escape_string($mysqli,@$_REQUEST['hoursWorked']);
						$hoursDeducted = mysqli_real_escape_string($mysqli,@$_REQUEST['hoursDeducted']);
						$paid = mysqli_real_escape_string($mysqli,@$_REQUEST['paid']);
						$rate = mysqli_real_escape_string($mysqli,@$_REQUEST['rate']);
						$notes = mysqli_real_escape_string($mysqli,@$_REQUEST['notes']);
						
						$dayOfWork = date('l', strtotime($Date_Worked));
					
					switch ($dayOfWork)
					{
						case "Sunday":
							$addDaysToMonday = "-6";
							break;
						case "Monday":
							$addDaysToMonday = "+0";
							break;
						case "Tuesday":
							$addDaysToMonday = "-1";
							break;
						case "Wednesday":
							$addDaysToMonday = "-2";
							break;
						case "Thursday":
							$addDaysToMonday = "-3";
							break;
						case "Friday":
							$addDaysToMonday = "-4";
							break;
						case "Saturday":
							$addDaysToMonday = "-5";
							break;
						default:	
							$addDaysToMonday = "err";
					}
					$week_start = date('d-Y-m', strtotime($addDaysToMonday . ' days', strtotime($Date_Worked) ));
					//$refId = "WK" . $week_start . "-" . date("Y-m", strtotime($Date_Worked)) . "/$employerId";
					$refId = "WK" . $week_start . "/$employerId";
						
						$processedDate = date("Y-m-d", strtotime($Date_Worked));
						$processedStartTime = date("H:i:s", strtotime($startTime));
						$processedEndTime = date("H:i:s", strtotime($endTime));
						
		
						$sqlEditQuery = 
						"update work set `Employer_id` = '$employerId',
								   		 `Date_Worked` = '$processedDate',
										 `Time_Start` = '$processedStartTime',
										 `Time_End` = '$processedEndTime',
										 `Hours_Worked` = '$hoursWorked',
										 `Deducted_Hours` = '$hoursDeducted',
										 `Paid` = '$paid',
										 `Hourly_Rate` = '$rate',
										 `Notes` = '$notes',
										 `Reference_id` = '$refId' ";
		
						$sqlEditWhere = "where `id` = '$id'";
						
						$sqlQuery = implode(" ", array($sqlEditQuery, $sqlEditWhere));
						
						$resultCount = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
						
						?>
						<h1>Work entry edited successfully!</h1>
						<?php WorkedHours::view();
					}
				}
				else
				{
					// Display form
					displayEditForm($id);
				}
			}
			else
			{
				// id not set, so we just display the view
				WorkedHours::view();	
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