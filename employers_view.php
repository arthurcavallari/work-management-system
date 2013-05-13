<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Employers | Work Management System</title>
<link href="css/styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/util.js"></script>
<!--[if lt IE 9]>
<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
</head>

<body>

<div class="container">
  <header>
    <div class="header_text">
        <h1><a href="#">Work Management System</a></h1>
        <!--<h3>Employers</h3>-->
    </div>
  <div class="sidebar1"> 
    <nav>
      <ul>
        <li><a href="?page=home">Home</a></li>
        <li><a href="?page=employers" class="active_link">Employers</a>
            <ul>
            	<li><a href="?page=employers&task=view">View</a></li>
                <li><a href="?page=employers&task=add">Add</a></li>
            </ul>
        </li>
        <li><a href="?page=workedhours">Worked Hours</a>
            <ul>
            	<li><a href="?page=workedhours&task=view">View</a></li>
                <li><a href="?page=workedhours&task=add">Add</a></li>
            </ul>
        </li>
        <li><a href="?page=payments">Payments</a>
            <ul>
            	<li><a href="?page=payments&task=view">View</a></li>
                <li><a href="?page=payments&task=add">Add</a></li>
            </ul>
        </li>
      </ul>     
    </nav>
  </div> <!-- end .sidebar1 -->   
  </header>
  
  
 <article class="content">
  <?php
			include('./functions.php');
			global $mysqli, $current_page;
			$sqlSelect = "select *";
			$sqlFrom = "from employers";
			$sqlWhere = "";
			$sqlOrderBy = "";
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
			
			$sqlLimit = "limit " . $startItem . ", " . ($startItem + 12);
			$sqlQuery = implode(" ", array($sqlSelect, $sqlFrom, $sqlWhere, $sqlOrderBy, $sqlLimit));
			
			//$data .= "Listing ".$totalItems." items<br /><br />";
			$data = "	<center><h3>";
			if ($navigation->noOfPages > 1) 
			{
				 
				if ($navigation->previousStartItem != -1) 
				{
					$data .= " <a href=\"?page=employers&task=view&p=".$navigation->previousStartItem."\">Prev</a> ";
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
						$data .= " <a href=\"?page=employers&task=view&p=".$page["startitem"]."\">".$page["pageno"]."</a> ";
					}
				}
				 
				if ($navigation->nextStartItem != -1) 
				{
					$data .= " <a href=\"?page=employers&task=view&p=".$navigation->nextStartItem."\">Next</a> ";
				}
			}
			$data .= "	</h3></center>";
			$result = $mysqli->query($sqlQuery, MYSQLI_USE_RESULT);
			$data .= "	<table width='100%' id='box-table-a'>
			<thead>
				<tr>
					<th width='20%'>Name</th>
					<th width='10%'>ABN</th>
					<th width='15%'>Work Period</th>
					<th width='15%'>Contact Name</th>
					<th width='12%'>Contact Number</th>
					<th width='15%'>Contact Email</th>
					<th width='5%'>Rate</th>
					<th width='8%'>Actions</th>
				</tr>
			</thead>
			<tbody>
	";
			if ($result)
			{	
				while ($row = mysqli_fetch_array($result)) 
				{
					
					$work_period = date("d/m/Y", strtotime($row['Start_Date']));
					if($row['End_Date'] == "")
					{
						$work_period .= " - " . date("d/m/Y", strtotime($row['End_Date']));
					}
					else
					{
						$work_period .= " - Present";	
					}
					$data .= "			<tr>
					<td>{$row['Name']}</td>
					<td>{$row['ABN']}</td>
					<td>{$work_period}</td>
					<td>{$row['Contact_Name']}</td>
					<td>{$row['Contact_Number']}</td>
					<td>{$row['Contact_Email']}</td>
					<td>$" . number_format($row['Hourly_Rate'], 2, '.', '') . "</td>
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

  ?>
    </article>
  <footer>
    <p>Work Management System - Copyright © 2013. All rights reserved. </p>
  </footer>
  <!-- end .container --></div>
</body>
</html>
