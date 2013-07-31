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
		
		function buildquery(&$currentFilter, $filterToAdd)
		{
			if($currentFilter == "")
			{
				$currentFilter = $filterToAdd;
			}
			else
			{
				$currentFilter .= " and " . $filterToAdd;
			}
		}

		function calculateXWeeksIncome($numberOfWeeks = 8)
		{
			global $mysqli;
			$centrelink = ($numberOfWeeks / 2) * 268.2;
			$sqlSelect = "select sum(w.Hours_Worked * w.Hourly_Rate) + ($centrelink) as Total";
			$sqlFrom = "from work w left join employers e on w.Employer_id=e.id";
			$sqlWhere = "where Date_Worked >= '" . date('Y-m-d', strtotime('-' . ($numberOfWeeks * 7) . ' days')) . "' and Date_Worked <= '" . date('Y-m-d') . "'";		
			$sqlOrderBy = "order by Date_Worked desc, Time_Start desc";
			$sqlLimit = "";
			$sqlGroupBy = "";
			$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlGroupBy, $sqlOrderBy, $sqlLimit));

			$result = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
			$row = mysqli_fetch_array($result);
			return "$" . number_format($row['Total'], 2, '.', '');;
		}
		
		function view()
		{
			// Start view
			global $mysqli, $current_page;
			
			$clearFilter = @$_REQUEST["clearFilter"];
			if(isset($clearFilter))
			{
				foreach($_SESSION as $k => $v) 
				{
					if(strstr($k, "w_"))
					{
						$_SESSION[$k] = NULL;	
					}
				}
				
			}
				
			// Possible comparison methods: 
			//   field		>				x
			//   field		>=				x
			//   field		<				x
			//   field		<=				x
			//   field		==				x
			//   field		!= 				x
			//   field		contains		x
			//   field		between_in		x and y
			//   field		between_ex		x and y
			$filterField = "";			
			
			WorkedHours::filter($filterField);
			
			// TODO: add range filters for each column
			// for each field, if comparisonMethod is "==, !=, contains", check if isset(field1) 
			// else, check if isset(field1, field2)
			
			$sqlSelect = "select w.*, e.Name";
			$sqlFrom = "from work w left join employers e on w.Employer_id=e.id";
			if($filterField != "")
			{
				$sqlWhere = "where $filterField";	
			}
			else
			{
				$sqlWhere = ""; //where Date_Worked >= '2013-05-16' and Date_Worked <= '2013-05-29'";	
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
			//echo $sqlQuery . "<br />";
			//$data .= "Listing ".$totalItems." items<br /><br />";
			?>
            <table>
            	<tr>
            		<td><center>Income earned:</center>
                        <table class='box-table-class'>
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>8 weeks</th>
                                <th>4 weeks</th>
                                <th>2 weeks</th>                    
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>&nbsp;</td>
                                <td><?php echo WorkedHours::calculateXWeeksIncome(8); ?></td>
                                <td><?php echo WorkedHours::calculateXWeeksIncome(4); ?></td>
                                <td><?php echo WorkedHours::calculateXWeeksIncome(2); ?></td>
                                <td>&nbsp;</td>
                            </tr>
                        </tbody>
                        </table>
                    </td>
                    <td>
                    	<center>Next Reporting date:</center>
                        <?php
						
							$nextDate = strtotime('+2 weeks', mktime(0,0,0,6,12,2013));

							$now = mktime(0,0,0);
							//echo "next: " . date('d/m/Y', $nextDate) . " <br /> now: " . date('d/m/Y', $now) . "<br />";
							while($nextDate <= $now)
							{
								$nextDate = strtotime('+2 weeks', $nextDate);
								//echo "next: " . date('d/m/Y', $nextDate) . " <br />";
							}
							$today = $nextDate;

							 
							echo date('d/m/Y', $today); // . "<br>" . $initialDate . "<br />";
							//echo $diff;
						?>
                        <br /><br />
                        <center>Last Reporting date:</center>
                        <?php
							$today = strtotime('-2 weeks', $nextDate);
							 
							echo date('d/m/Y', $today); 

						?>
                        
                    </td>
                </tr>
            </table>
            
            
            
            
            	<center><h3><?php
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
        <script type="text/javascript">
		
		$(document).ready(function() {
			$("#Date_Worked1").datepicker();
			$("#Date_Worked2").datepicker();
			
			
			$('select[class="smallSelect"]').change(function(e) {
                var targetId = (e.target.name.replace("ComparisonMethod", "")) + "2";
			    if($(this).attr("value").indexOf("between",0) > -1)
				{	
					$("input#" + targetId).css("display", "block");
				}
				else
				{
					$("input#" + targetId).css("display", "none");
				}
            });
			
			$("#resetFilter").click(function(e) {
                $("#filterForm input").prop("value", "");
				$("#filterForm select").prop("selectedIndex", "0");
            });
			
			$('select[class="smallSelect"]').change(); // calls the change method for all the ComparisonMethod selects to do the first update on on input boxes
        });
				  
		</script>
		<table id='box-table-a'>
			<thead>
				<tr>
					<th width='100px'>
						Employer
					</th>
					<th width='20px'>
						Date
					</th>
					<th width='50px'>
						Start Time
					</th>
					<th width='50px'>
						End Time
					</th>
					<th width='75px'>
						Hours Worked
					</th>
					<th width='85px'>
						Deducted Hours
					</th>
					<th width='10px'>
						Paid
					</th>
					<th width='10px'>
						Rate
					</th>
					<th width='100px'>
						Notes
					</th>
					<th width='95px'>
						Ref. ID
					</th>
					<th width='80px'>
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
            <div style="float:left; width:440px;">
            <!--
            //   field         	>      			x
			//   field         	>=     			x
			//   field         	<      			x
			//   field         	<=     			x
			//   field     		=      			x
			//   field     		!=     			x
			//   field  		contains  		x
            //	 field  		between_in  	x and y
            //	 field  		between_ex  	x and y
            -->            
                <label>Employer</label>
                <select name="employer1" id="employer1" >
						<option value="-1" <?php if(@$_SESSION['w_employer1'] == "-1"){echo 'selected="selected"'; } ?>>(Select an employer)</option>
<?php $employers = new Employers(); foreach ($employers->listEmployers() as $str)	{ ?>                     	<option value="<? echo $str[0]; ?>" <?php if(@$_SESSION['w_employer1'] == $str[0]){echo 'selected="selected"'; } ?>><? echo $str[1]; ?></option>
<?  } ?>
				</select>
                <div class="spacer"></div>
                <label>Start Time</label>
                <select name="startTimeComparisonMethod" class="smallSelect" >
                    <option value="="  <?php if(@$_SESSION['w_startTimeComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_startTimeComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_startTimeComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_startTimeComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_startTimeComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_startTimeComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                </select>
                <input type="text" name="startTime1" id="startTime1" value="<?php echo @$_SESSION['w_startTime1']; ?>" />
                <input type="text" name="startTime2" id="startTime2" value="<?php echo @$_SESSION['w_startTime2']; ?>" style="display:none;" />
              <div class="spacer"></div>
                <label>End Time</label>
                <select name="endTimeComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_endTimeComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_endTimeComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_endTimeComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_endTimeComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_endTimeComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_endTimeComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                </select>
                <input type="text" name="endTime1" id="endTime1" value="<?php echo @$_SESSION['w_endTime1']; ?>" />
                <input type="text" name="endTime2" id="endTime2" value="<?php echo @$_SESSION['w_endTime2']; ?>" style="display:none;" />
                <div class="spacer"></div>
                <label>Hours Worked</label>             
                <select name="hoursWorkedComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_hoursWorkedComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                </select>
                <input type="text" name="hoursWorked1" id="hoursWorked1" value="<?php echo @$_SESSION['w_hoursWorked1']; ?>" />
                <input type="text" name="hoursWorked2" id="hoursWorked2" value="<?php echo @$_SESSION['w_hoursWorked2']; ?>" style="display:none;" />
                <div class="spacer"></div>
                <label>Hours Deducted</label>
                <select name="hoursDeductedComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_hoursDeductedComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                </select>
                <input type="text" name="hoursDeducted1" id="hoursDeducted1" value="<?php echo @$_SESSION['w_hoursDeducted1']; ?>" />
                <input type="text" name="hoursDeducted2" id="hoursDeducted2" value="<?php echo @$_SESSION['w_hoursDeducted2']; ?>" style="display:none;" />
                <div class="spacer"></div>
            </div>
            <div style="float:right; width:440px;">                
                <label>Paid</label>
                <select name="paid1" >
                    <option value="-1" <?php if(@$_SESSION['w_paid1'] == "-1"){echo 'selected="selected"'; } ?>>(Select a value)</option>
                    <option value="Y" <?php if(@$_SESSION['w_paid1'] == "Y"){echo 'selected="selected"'; } ?>>Yes</option>
                    <option value="N" <?php if(@$_SESSION['w_paid1'] == "N"){echo 'selected="selected"'; } ?>>No</option>
                </select>
                <div class="spacer"></div>
                <label>Date Worked</label>
                <select name="Date_WorkedComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                    <option value="contains" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "contains"){echo 'selected="selected"'; } ?>>contains</option>
					<option value="between_in" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "between_in"){echo 'selected="selected"'; } ?>>between inclusive</option>
					<option value="between_ex" <?php if(@$_SESSION['w_Date_WorkedComparisonMethod'] == "between_ex"){echo 'selected="selected"'; } ?>>between exclusive</option>
                </select>
                <input type="text" name="Date_Worked1" id="Date_Worked1" value="<?php echo @$_SESSION['w_Date_Worked1']; ?>" />
                <input type="text" name="Date_Worked2" id="Date_Worked2" value="<?php echo @$_SESSION['w_Date_Worked2']; ?>" style="display:none;" />
                <div class="spacer"></div>                
                <label>Rate</label>
                <select name="rateComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_rateComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_rateComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value=">"  <?php if(@$_SESSION['w_rateComparisonMethod'] == ">" ){echo 'selected="selected"'; } ?>>&gt;</option>
                    <option value=">=" <?php if(@$_SESSION['w_rateComparisonMethod'] == ">="){echo 'selected="selected"'; } ?>>&gt;=</option>
                    <option value="<"  <?php if(@$_SESSION['w_rateComparisonMethod'] == "<" ){echo 'selected="selected"'; } ?>>&lt;</option>
                    <option value="<=" <?php if(@$_SESSION['w_rateComparisonMethod'] == "<="){echo 'selected="selected"'; } ?>>&lt;=</option>
                </select>
                <input type="text" name="rate1" id="rate1" value="<?php echo @$_SESSION['w_rate1']; ?>" />
                <input type="text" name="rate2" id="rate2" value="<?php echo @$_SESSION['w_rate2']; ?>" style="display:none;" /> 
                <div class="spacer"></div>
                <label>Notes</label>
                <select name="notesComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_notesComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_notesComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value="contains" <?php if(@$_SESSION['w_notesComparisonMethod'] == "contains"){echo 'selected="selected"'; } ?>>contains</option>
                </select>
                <input type="text" name="notes1" id="notes1" value="<?php echo @$_SESSION['w_notes1']; ?>" />
                <div class="spacer"></div>
                <label>Ref. ID</label>
                <select name="refIdComparisonMethod" class="smallSelect">
                    <option value="="  <?php if(@$_SESSION['w_refIdComparisonMethod'] == "=" ){echo 'selected="selected"'; } ?>>==</option>
                    <option value="!=" <?php if(@$_SESSION['w_refIdComparisonMethod'] == "!="){echo 'selected="selected"'; } ?>>!=</option>
                    <option value="contains" <?php if(@$_SESSION['w_refIdComparisonMethod'] == "contains"){echo 'selected="selected"'; } ?>>contains</option>
                </select>
                <input type="text" name="refId1" id="refId1" value="<?php echo @$_SESSION['w_refId1']; ?>" />
                <div class="spacer"></div>
                
            </div>
            <div class="spacer"></div>
            
            
            <div style="text-align:center;">
                <button type="submit" name="paymentsFormButton" class="submitButton">Submit</button> <button id="resetFilter" class="resetButton">Reset</button>
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
			$sqlSelect = "select id, Reference_id, sum(Hours_Worked * Hourly_Rate) as Amount";
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
					$data[$i] = array($row['id'], $row['Reference_id'], $row['Amount']);
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
		
		function filter(&$filterField)
		{
			$employer1 = @$_REQUEST['employer1'];
			
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
			
			$rateValue1 = @$_REQUEST['rate1'];
			$rateValue2 = @$_REQUEST['rate2'];
			$rateComparisonMethod = @$_REQUEST['rateComparisonMethod']; 					// any
			
			$notes1 = @$_REQUEST['notes1'];
			$notesComparisonMethod = @$_REQUEST['notesComparisonMethod'];					// ==, !=, contains
			
			$refId1 = @$_REQUEST['refId1'];
			$refIdComparisonMethod = @$_REQUEST['refIdComparisonMethod'];					// ==, !=, contains
			
			$singleFieldComparison = array('=', '!=', '>', '>=', '<', '<=');
			$multiFieldComparison = array('between_in', 'between_ex');
			
			if((isset($employer1) && $employer1 != "-1")  ||  (isset($_SESSION['w_employer1']) && $_SESSION['w_employer1'] != "-1"))
			{
				if(isset($employer1))
				{
					$_SESSION['w_employer1'] = $employer1 = @$_REQUEST['employer1'];
				}
				$fieldValue1 = $_SESSION['w_employer1'];
				if($fieldValue1 != "-1")
					WorkedHours::buildquery($filterField, "w.Employer_id='$fieldValue1'");
			}
			if((isset($paidValue1) && $paidValue1 != "-1")  ||  (isset($_SESSION['w_paid1']) && $_SESSION['w_paid1'] != "-1"))
			{
				if(isset($paidValue1))
				{
					$_SESSION['w_paid1'] = $paidValue1 = @$_REQUEST['paid1'];
				}
				$fieldValue1 = $_SESSION['w_paid1'];
				if($fieldValue1 != "-1")
					WorkedHours::buildquery($filterField, "w.Paid='$fieldValue1'");
			}
			if((isset($Date_WorkedValue1) && $Date_WorkedValue1 != "")  ||  (isset($_SESSION['w_Date_Worked1']) && $_SESSION['w_Date_Worked1'] != "") )
			{
				$fieldName = "w.Date_Worked";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($Date_WorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_Date_Worked1'] = @$_REQUEST['Date_Worked1'];
					$fieldValue2 = $_SESSION['w_Date_Worked2'] = @$_REQUEST['Date_Worked2'];
					$c_operator = $_SESSION['w_Date_WorkedComparisonMethod'] = @$_REQUEST['Date_WorkedComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_Date_Worked1'];
					$fieldValue2 = $_SESSION['w_Date_Worked2'];
					$c_operator = $_SESSION['w_Date_WorkedComparisonMethod'];
				}
				
				
				if($fieldValue1 != "")
				{				
					$fieldValue1 = date("Y-m-d", strtotime($fieldValue1));
					$fieldValue2 = date("Y-m-d", strtotime($fieldValue2));
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}
				}
			}
			
			if((isset($startTimeValue1) && $startTimeValue1 != "")  ||  (isset($_SESSION['w_startTime1']) && $_SESSION['w_startTime1'] != "") )
			{
				$fieldName = "w.Time_Start";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($startTimeValue1))
				{
					$fieldValue1 = $_SESSION['w_startTime1'] = @$_REQUEST['startTime1'];
					$fieldValue2 = $_SESSION['w_startTime2'] = @$_REQUEST['startTime2'];
					$c_operator = $_SESSION['w_startTimeComparisonMethod'] = @$_REQUEST['startTimeComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_startTime1'];
					$fieldValue2 = $_SESSION['w_startTime2'];
					$c_operator = $_SESSION['w_startTimeComparisonMethod'];
				}
								
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}	
					else
					{
						echo  "$fieldName $c_operator '$fieldValue1'<br>";
					}
				}
			}
			
			if((isset($endTimeValue1) && $endTimeValue1 != "")  ||  (isset($_SESSION['w_endTime1']) && $_SESSION['w_endTime1'] != "") )
			{
				$fieldName = "w.Time_End";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($endTimeValue1))
				{
					$fieldValue1 = $_SESSION['w_endTime1'] = @$_REQUEST['endTime1'];
					$fieldValue2 = $_SESSION['w_endTime2'] = @$_REQUEST['endTime2'];
					$c_operator = $_SESSION['w_endTimeComparisonMethod'] = @$_REQUEST['endTimeComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_endTime1'];
					$fieldValue2 = $_SESSION['w_endTime2'];
					$c_operator = $_SESSION['w_endTimeComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}	
					else
					{
						echo  "$fieldName $c_operator '$fieldValue1'<br>";
					}
				}
			}
			
			if((isset($hoursWorkedValue1) && $hoursWorkedValue1 != "")  ||  (isset($_SESSION['w_hoursWorked1']) && $_SESSION['w_hoursWorked1'] != "") )
			{
				$fieldName = "w.Hours_Worked";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($hoursWorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_hoursWorked1'] = @$_REQUEST['hoursWorked1'];
					$fieldValue2 = $_SESSION['w_hoursWorked2'] = @$_REQUEST['hoursWorked2'];
					$c_operator =  $_SESSION['w_hoursWorkedComparisonMethod'] = @$_REQUEST['hoursWorkedComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_hoursWorked1'];
					$fieldValue2 = $_SESSION['w_hoursWorked2'];
					$c_operator = $_SESSION['w_hoursWorkedComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}	
					else
					{
						echo  "$fieldName $c_operator '$fieldValue1'<br>";
					}
				}
			}
			
			if((isset($hoursDeductedValue1) && $hoursDeductedValue1 != "")  ||  (isset($_SESSION['w_hoursDeducted1']) && $_SESSION['w_hoursDeducted1'] != "") )
			{
				$fieldName = "w.Deducted_Hours";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($hoursWorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_hoursDeducted1'] 				   = @$_REQUEST['hoursDeducted1'];
					$fieldValue2 = $_SESSION['w_hoursDeducted2'] 				   = @$_REQUEST['hoursDeducted2'];
					$c_operator  =  $_SESSION['w_hoursDeductedComparisonMethod'] = @$_REQUEST['hoursDeductedComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_hoursDeducted1'];
					$fieldValue2 = $_SESSION['w_hoursDeducted2'];
					$c_operator  = $_SESSION['w_hoursDeductedComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}	
					else
					{
						echo  "$fieldName $c_operator '$fieldValue1'<br>";
					}
				}
			}
			
			if((isset($hoursDeductedValue1) && $hoursDeductedValue1 != "")  ||  (isset($_SESSION['w_rate1']) && $_SESSION['w_rate1'] != "") )
			{
				$fieldName = "w.Hourly_Rate";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				$fieldValue2 = NULL; 
				
				if(isset($hoursWorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_rate1'] 				   = @$_REQUEST['rate1'];
					$fieldValue2 = $_SESSION['w_rate2'] 				   = @$_REQUEST['rate2'];
					$c_operator  = $_SESSION['w_rateComparisonMethod']   = @$_REQUEST['rateComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_rate1'];
					$fieldValue2 = $_SESSION['w_rate2'];
					$c_operator  = $_SESSION['w_rateComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'between_in')
					{					
						WorkedHours::buildquery($filterField, "$fieldName >= '$fieldValue1' and $fieldName <= '$fieldValue2'");
					}
					elseif($c_operator == 'between_ex')
					{
						WorkedHours::buildquery($filterField, "$fieldName > '$fieldValue1' and $fieldName < '$fieldValue2'");
					}	
					else
					{
						echo  "$fieldName $c_operator '$fieldValue1'<br>";
					}
				}
			}
			
			if((isset($refId1) && $refId1 != "")  ||  (isset($_SESSION['w_refId1']) && $_SESSION['w_refId1'] != "") )
			{
				$fieldName = "w.Reference_id";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				
				if(isset($hoursWorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_refId1'] 				   = @$_REQUEST['refId1'];
					$c_operator  = $_SESSION['w_refIdComparisonMethod']  = @$_REQUEST['refIdComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_refId1'];
					$c_operator  = $_SESSION['w_refIdComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'contains')
					{
						WorkedHours::buildquery($filterField, "$fieldName LIKE '%$fieldValue1%'");
					}
				}
			}
			
			if((isset($notes1) && $notes1 != "")  ||  (isset($_SESSION['w_notes1']) && $_SESSION['w_notes1'] != "") )
			{
				$fieldName = "w.Notes";
				$c_operator = NULL;
				$fieldValue1 = NULL;
				
				if(isset($hoursWorkedValue1))
				{
					$fieldValue1 = $_SESSION['w_notes1'] 				   = @$_REQUEST['notes1'];
					$c_operator  = $_SESSION['w_notesComparisonMethod']  = @$_REQUEST['notesComparisonMethod'];
				}
				else
				{
					$fieldValue1 = $_SESSION['w_notes1'];
					$c_operator  = $_SESSION['w_notesComparisonMethod'];
				}
				if($fieldValue1 != "")
				{
					if(in_array($c_operator, $singleFieldComparison))
					{					
						WorkedHours::buildquery($filterField, "$fieldName $c_operator '$fieldValue1'");
					}
					elseif($c_operator == 'contains')
					{
						WorkedHours::buildquery($filterField, "$fieldName LIKE '%$fieldValue1%'");
					}
				}
			}	
		}
	}
?>